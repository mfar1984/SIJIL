<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PublicSurveyController extends Controller
{
    /**
     * Session key holding the ids of surveys already answered in this browser.
     */
    private const ANSWERED_SESSION_KEY = 'answered_surveys';

    private function findSurvey(string $slug): Survey
    {
        return Survey::with('questions', 'event')
            ->where('slug', $slug)
            ->firstOr(fn () => abort(404, 'Survey not found'));
    }

    /**
     * Show the survey form.
     */
    public function show(Request $request, string $slug)
    {
        $survey = $this->findSurvey($slug);

        if (! $survey->isOpen()) {
            return redirect()->route('public.survey.expired', $survey->slug);
        }

        $participant = $this->resolveParticipant($request, $survey);

        // Participant-only surveys need to know who is answering before showing
        // the questions.
        if ($survey->isParticipantsOnly() && ! $participant) {
            return view('public.survey.identify', compact('survey'));
        }

        if (! $survey->allow_multiple_responses && $this->hasAlreadyResponded($request, $survey, $participant)) {
            return view('public.survey.completed', compact('survey'));
        }

        return view('public.survey.show', compact('survey', 'participant'));
    }

    /**
     * Identify a participant by email or IC/passport before letting them answer.
     */
    public function identify(Request $request, string $slug)
    {
        $survey = $this->findSurvey($slug);

        if (! $survey->isOpen()) {
            return redirect()->route('public.survey.expired', $survey->slug);
        }

        $request->validate([
            'identifier' => 'required|string|max:255',
        ], [
            'identifier.required' => 'Enter the email or IC/passport number you registered with.',
        ]);

        $identifier = trim($request->input('identifier'));
        $digits = preg_replace('/\D+/', '', $identifier);

        $participant = Participant::where('event_id', $survey->event_id)
            ->where(function ($query) use ($identifier, $digits) {
                $query->where('email', $identifier)
                      ->orWhere('passport_no', $identifier);

                if ($digits !== '') {
                    $query->orWhereRaw("REPLACE(REPLACE(identity_card, '-', ''), ' ', '') = ?", [$digits]);
                }
            })
            ->first();

        if (! $participant) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'We could not find your registration for this event. Check the details and try again.');
        }

        $request->session()->put($this->participantSessionKey($survey), $participant->id);

        return redirect()->route('public.survey.show', $survey->slug);
    }

    /**
     * Submit the survey response.
     */
    public function submit(Request $request, string $slug)
    {
        $survey = $this->findSurvey($slug);

        if (! $survey->isOpen()) {
            return redirect()->route('public.survey.expired', $survey->slug);
        }

        $participant = $this->resolveParticipant($request, $survey);

        if ($survey->isParticipantsOnly() && ! $participant) {
            return redirect()->route('public.survey.show', $survey->slug)
                ->with('error', 'Please identify yourself before submitting.');
        }

        if (! $survey->allow_multiple_responses && $this->hasAlreadyResponded($request, $survey, $participant)) {
            return view('public.survey.completed', compact('survey'));
        }

        $validated = $request->validate(
            $this->rulesFor($survey, $participant),
            $this->messagesFor($survey)
        );

        $responseData = [];

        foreach ($survey->questions as $question) {
            $responseData[$question->id] = $validated['question_' . $question->id] ?? null;
        }

        $response = new SurveyResponse([
            'survey_id' => $survey->id,
            'response_data' => $responseData,
            'session_id' => $request->session()->getId(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'completed' => true,
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        if ($participant) {
            $response->participant_id = $participant->id;
            $response->respondent_type = 'participant';
            $response->respondent_name = $participant->name;
            $response->respondent_email = $participant->email;
        } elseif (auth()->check()) {
            $response->user_id = auth()->id();
            $response->respondent_type = 'user';
        } else {
            $response->respondent_type = 'anonymous';
        }

        if ($survey->require_respondent_details && ! $participant) {
            $response->respondent_name = $validated['respondent_name'] ?? null;
            $response->respondent_email = $validated['respondent_email'] ?? null;
            $response->respondent_phone = $validated['respondent_phone'] ?? null;
        }

        $response->save();

        $this->rememberSubmission($request, $survey);

        return redirect()->route('public.survey.thankyou', $survey->slug);
    }

    /**
     * Build validation rules from the survey questions.
     */
    private function rulesFor(Survey $survey, ?Participant $participant): array
    {
        $rules = [];

        if ($survey->require_respondent_details && ! $participant) {
            $rules['respondent_name'] = 'required|string|max:255';
            $rules['respondent_email'] = 'required|email|max:255';
            $rules['respondent_phone'] = 'nullable|string|max:30';
        }

        foreach ($survey->questions as $question) {
            $field = 'question_' . $question->id;
            $required = $question->required ? 'required' : 'nullable';

            switch ($question->question_type) {
                case 'checkbox':
                    $rules[$field] = $required . '|array';
                    $rules[$field . '.*'] = ['string', Rule::in($question->options ?? [])];
                    break;

                case 'multiple_choice':
                case 'dropdown':
                    $rules[$field] = [$required, 'string', Rule::in($question->options ?? [])];
                    break;

                case 'rating':
                    $rules[$field] = [$required, 'integer', Rule::in($question->scaleValues())];
                    break;

                case 'date':
                    $rules[$field] = $required . '|date';
                    break;

                case 'email':
                    $rules[$field] = $required . '|email|max:255';
                    break;

                case 'number':
                    $rules[$field] = $required . '|numeric';
                    break;

                case 'textarea':
                    $rules[$field] = $required . '|string|max:5000';
                    break;

                default:
                    $rules[$field] = $required . '|string|max:1000';
                    break;
            }
        }

        return $rules;
    }

    /**
     * Use the question text in error messages instead of "question_12".
     */
    private function messagesFor(Survey $survey): array
    {
        $messages = [];

        foreach ($survey->questions as $question) {
            $field = 'question_' . $question->id;
            $label = $question->question_text;

            foreach (['required', 'in', 'date', 'email', 'numeric', 'array', 'string'] as $rule) {
                $messages["{$field}.{$rule}"] = match ($rule) {
                    'required' => "\"{$label}\" is required.",
                    'in' => "Select a valid option for \"{$label}\".",
                    default => "Enter a valid answer for \"{$label}\".",
                };
            }

            $messages["{$field}.*.in"] = "Select a valid option for \"{$label}\".";
        }

        return $messages;
    }

    /**
     * Session key that stores the identified participant for a survey.
     */
    private function participantSessionKey(Survey $survey): string
    {
        return 'survey_participant_' . $survey->id;
    }

    /**
     * The participant answering, when one can be determined.
     */
    private function resolveParticipant(Request $request, Survey $survey): ?Participant
    {
        if (! $survey->event_id) {
            return null;
        }

        $participantId = $request->session()->get($this->participantSessionKey($survey));

        if (! $participantId) {
            return null;
        }

        return Participant::where('event_id', $survey->event_id)->find($participantId);
    }

    /**
     * Whether this respondent already submitted.
     *
     * Identified participants are checked against the database. Anonymous
     * respondents can only be checked against their own session, which is the best
     * that is possible without asking who they are.
     */
    private function hasAlreadyResponded(Request $request, Survey $survey, ?Participant $participant): bool
    {
        if ($participant) {
            return $survey->responses()
                ->where('participant_id', $participant->id)
                ->where('completed', true)
                ->exists();
        }

        return in_array($survey->id, (array) $request->session()->get(self::ANSWERED_SESSION_KEY, []), true);
    }

    private function rememberSubmission(Request $request, Survey $survey): void
    {
        $answered = (array) $request->session()->get(self::ANSWERED_SESSION_KEY, []);
        $answered[] = $survey->id;

        $request->session()->put(self::ANSWERED_SESSION_KEY, array_unique($answered));
    }

    public function thankYou(string $slug)
    {
        return view('public.survey.thankyou', ['survey' => $this->findSurvey($slug)]);
    }

    public function expired(string $slug)
    {
        return view('public.survey.expired', ['survey' => $this->findSurvey($slug)]);
    }
}
