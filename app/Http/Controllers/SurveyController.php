<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SurveyController extends Controller
{
    /** Tabs rendered by the survey workspace. */
    private const WORKSPACE_TABS = ['questions', 'settings', 'share'];

    /**
     * Whether the current user may act on the given survey.
     */
    private function canManage(Survey $survey): bool
    {
        return auth()->user()->hasRole('Administrator') || $survey->user_id == auth()->id();
    }

    /**
     * Stop the request unless the current user owns the survey.
     */
    private function authorizeSurvey(Survey $survey): void
    {
        abort_unless($this->canManage($survey), 403, 'You do not have permission to manage this survey.');
    }

    /**
     * Events the current user is allowed to attach a survey to.
     */
    private function availableEvents()
    {
        $query = Event::orderBy('name');

        if (! auth()->user()->hasRole('Administrator')) {
            $query->where('user_id', auth()->id());
        }

        return $query->get();
    }

    /**
     * Events that may still be linked to a survey.
     *
     * An event carries at most one survey. Two surveys on the same event would
     * split its responses across two report pages with no way to tell which one
     * participants were meant to answer, so an event that already has one is not
     * offered here.
     *
     * The survey being edited keeps its own event in the list: without that, the
     * settings tab would render with the link missing and quietly drop it on save.
     *
     * The lookup is deliberately not scoped to the current account. The rule is
     * about the event, not about who is looking at it.
     */
    private function linkableEvents(?Survey $survey = null)
    {
        $taken = Survey::whereNotNull('event_id')
            ->when($survey && $survey->exists, fn ($q) => $q->whereKeyNot($survey->getKey()))
            ->pluck('event_id')
            ->all();

        return $this->availableEvents()
            ->reject(fn ($event) => in_array($event->id, $taken))
            ->values();
    }

    /**
     * The rule that keeps one event to one survey.
     *
     * Soft-deleted surveys are ignored, so deleting a survey releases its event.
     */
    private function eventLinkRules(?Survey $survey = null): array
    {
        $unique = Rule::unique('surveys', 'event_id')->whereNull('deleted_at');

        if ($survey && $survey->exists) {
            $unique->ignore($survey->getKey());
        }

        return ['nullable', 'exists:events,id', $unique];
    }

    /**
     * The chosen event, or null when the "not linked" option was picked.
     *
     * That option posts an empty string. Passed through as-is it reaches MySQL as
     * '' and a strict-mode server rejects it, so it is normalised here instead of
     * relying on the ConvertEmptyStringsToNull middleware being in the stack.
     */
    private function eventIdOrNull(array $validated): ?int
    {
        $eventId = $validated['event_id'] ?? null;

        return ($eventId === null || $eventId === '') ? null : (int) $eventId;
    }

    /**
     * Display a listing of the surveys.
     */
    public function index(Request $request)
    {
        $query = Survey::with('event')
            ->withCount([
                'questions',
                'responses as completed_responses_count' => fn ($q) => $q->where('completed', true),
            ]);

        if (! auth()->user()->hasRole('Administrator')) {
            $query->where('user_id', auth()->id());
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('audience')) {
            $query->where('audience', $request->audience);
        }

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        $surveys = $query->orderBy('created_at', 'desc')
            ->paginate(\App\Support\SystemSettings::perPage($request, 10))
            ->withQueryString();

        return view('survey.index', [
            'surveys' => $surveys,
            'events' => $this->availableEvents(),
        ]);
    }

    /**
     * Show the form for creating a new survey.
     *
     * Kept deliberately short: the survey only needs a title to exist, everything
     * else is configured in the workspace afterwards.
     */
    public function create()
    {
        return view('survey.create', [
            'events' => $this->linkableEvents(),
            'linkedElsewhere' => $this->countEventsAlreadyLinked(),
        ]);
    }

    /**
     * How many of this account's events are missing from the dropdown because a
     * survey is already attached. Shown as a note so the gap is explained rather
     * than looking like events went missing.
     */
    private function countEventsAlreadyLinked(?Survey $survey = null): int
    {
        return $this->availableEvents()->count() - $this->linkableEvents($survey)->count();
    }

    /**
     * Store a newly created survey and drop the user straight into the builder.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'event_id' => $this->eventLinkRules(),
        ], [
            'event_id.unique' => 'That event already has a survey. An event can only have one.',
        ]);

        $survey = new Survey();
        $survey->title = $validated['title'];
        $survey->description = $validated['description'] ?? null;
        $survey->event_id = $this->eventIdOrNull($validated);
        $survey->user_id = auth()->id();
        $survey->status = 'draft';
        $survey->audience = Survey::AUDIENCE_ANYONE;
        $survey->save();

        return redirect()->route('survey.show', [$survey, 'tab' => 'questions'])
            ->with('success', 'Survey created. Add your questions next.');
    }

    /**
     * The survey workspace: questions, settings and sharing in one place.
     */
    public function show(Request $request, Survey $survey)
    {
        $this->authorizeSurvey($survey);

        $tab = $request->get('tab', 'questions');

        if (! in_array($tab, self::WORKSPACE_TABS, true)) {
            $tab = 'questions';
        }

        $survey->loadCount([
            'questions',
            'responses as completed_responses_count' => fn ($q) => $q->where('completed', true),
        ]);

        return view('survey.workspace', [
            'survey' => $survey,
            'tab' => $tab,
            'questions' => $survey->questions()->get(),
            'events' => $this->linkableEvents($survey),
            'linkedElsewhere' => $this->countEventsAlreadyLinked($survey),
            'questionTypes' => SurveyQuestion::TYPES,
        ]);
    }

    /**
     * The workspace replaced the separate edit screen.
     */
    public function edit(Survey $survey)
    {
        $this->authorizeSurvey($survey);

        return redirect()->route('survey.show', [$survey, 'tab' => 'settings']);
    }

    /**
     * Update the survey settings.
     */
    public function update(Request $request, Survey $survey)
    {
        $this->authorizeSurvey($survey);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'event_id' => $this->eventLinkRules($survey),
            'audience' => ['required', Rule::in([Survey::AUDIENCE_ANYONE, Survey::AUDIENCE_PARTICIPANTS])],
            'require_respondent_details' => 'nullable|boolean',
            'allow_multiple_responses' => 'nullable|boolean',
            'opens_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:opens_at',
        ], [
            'expires_at.after' => 'The closing date must be later than the opening date.',
            'event_id.unique' => 'That event already has a survey. An event can only have one.',
        ]);

        $eventId = $this->eventIdOrNull($validated);

        // Answers can only be tied to participants when an event is attached.
        if ($validated['audience'] === Survey::AUDIENCE_PARTICIPANTS && $eventId === null) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Select an event before limiting the survey to its participants.');
        }

        $survey->fill([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'event_id' => $eventId,
            'audience' => $validated['audience'],
            'require_respondent_details' => $request->boolean('require_respondent_details'),
            'allow_multiple_responses' => $request->boolean('allow_multiple_responses'),
            'opens_at' => $validated['opens_at'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
        ])->save();

        return redirect()->route('survey.show', [$survey, 'tab' => 'settings'])
            ->with('success', 'Survey settings saved.');
    }

    /**
     * Soft delete the survey.
     *
     * Questions and responses are left in place so a restore brings back a complete
     * survey. The database cascades them only if the survey is force deleted.
     */
    public function destroy(Survey $survey)
    {
        $this->authorizeSurvey($survey);

        $survey->delete();

        return redirect()->route('survey.index')
            ->with('success', 'Survey deleted.');
    }

    /**
     * Publish or unpublish the survey.
     */
    public function togglePublish(Survey $survey)
    {
        $this->authorizeSurvey($survey);

        if ($survey->status === 'published') {
            $survey->status = 'draft';
            $survey->save();

            return redirect()->back()->with('success', 'Survey moved back to draft.');
        }

        $blockers = $survey->publishBlockers();

        if (! empty($blockers)) {
            return redirect()->back()->with('error', 'Cannot publish yet: ' . implode(' ', $blockers));
        }

        $survey->status = 'published';
        $survey->published_at = $survey->published_at ?? now();
        $survey->save();

        return redirect()->back()->with('success', 'Survey published. It is now accepting responses.');
    }

    /**
     * Validation rules shared by question create and update.
     */
    private function questionRules(): array
    {
        return [
            'question_text' => 'required|string|max:500',
            'question_type' => ['required', Rule::in(array_keys(SurveyQuestion::TYPES))],
            'description' => 'nullable|string|max:500',
            'options' => 'nullable|array',
            'options.*' => 'nullable|string|max:255',
            'required' => 'nullable|boolean',
            'scale_min' => 'nullable|integer|min:0|max:9',
            'scale_max' => 'nullable|integer|min:2|max:10',
            'scale_min_label' => 'nullable|string|max:60',
            'scale_max_label' => 'nullable|string|max:60',
        ];
    }

    /**
     * Turn the submitted question payload into attributes for the model.
     */
    private function questionAttributes(Request $request): array
    {
        $type = $request->input('question_type');

        $options = null;

        if (in_array($type, SurveyQuestion::OPTION_TYPES, true)) {
            $options = collect($request->input('options', []))
                ->map(fn ($option) => trim((string) $option))
                ->filter(fn ($option) => $option !== '')
                ->values()
                ->all();
        }

        $attributes = [
            'question_text' => $request->input('question_text'),
            'question_type' => $type,
            'description' => $request->input('description'),
            'options' => $options,
            'required' => $request->boolean('required'),
        ];

        if ($type === 'rating') {
            $min = (int) $request->input('scale_min', 1);
            $max = (int) $request->input('scale_max', 5);

            if ($max <= $min) {
                $max = $min + 1;
            }

            $attributes['scale_min'] = $min;
            $attributes['scale_max'] = $max;
            $attributes['scale_min_label'] = $request->input('scale_min_label');
            $attributes['scale_max_label'] = $request->input('scale_max_label');
        }

        return $attributes;
    }

    /**
     * Options are mandatory for the choice based types.
     */
    private function optionsAreMissing(array $attributes): bool
    {
        return in_array($attributes['question_type'], SurveyQuestion::OPTION_TYPES, true)
            && count($attributes['options'] ?? []) < 1;
    }

    /**
     * Store a new question for a survey.
     */
    public function storeQuestion(Request $request, Survey $survey)
    {
        $this->authorizeSurvey($survey);

        $request->validate($this->questionRules());

        $attributes = $this->questionAttributes($request);

        if ($this->optionsAreMissing($attributes)) {
            return redirect()->back()->withInput()
                ->with('error', 'Add at least one option for a ' . SurveyQuestion::TYPES[$attributes['question_type']] . ' question.');
        }

        $attributes['order'] = (int) $survey->questions()->max('order') + 1;

        $survey->questions()->create($attributes);

        return redirect()->route('survey.show', [$survey, 'tab' => 'questions'])
            ->with('success', 'Question added.');
    }

    /**
     * Update the order of questions.
     */
    public function updateQuestionOrder(Request $request, Survey $survey)
    {
        $this->authorizeSurvey($survey);

        $request->validate([
            'questions' => 'required|array',
            'questions.*' => 'integer|exists:survey_questions,id',
        ]);

        foreach ($request->input('questions') as $index => $questionId) {
            SurveyQuestion::where('id', $questionId)
                ->where('survey_id', $survey->id)
                ->update(['order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Update a specific question.
     */
    public function updateQuestion(Request $request, Survey $survey, SurveyQuestion $question)
    {
        $this->authorizeSurvey($survey);

        abort_unless($question->survey_id === $survey->id, 404);

        $request->validate($this->questionRules());

        $attributes = $this->questionAttributes($request);

        if ($this->optionsAreMissing($attributes)) {
            return redirect()->back()->withInput()
                ->with('error', 'Add at least one option for a ' . SurveyQuestion::TYPES[$attributes['question_type']] . ' question.');
        }

        $question->update($attributes);

        return redirect()->route('survey.show', [$survey, 'tab' => 'questions'])
            ->with('success', 'Question updated.');
    }

    /**
     * Delete a specific question.
     */
    public function destroyQuestion(Survey $survey, SurveyQuestion $question)
    {
        $this->authorizeSurvey($survey);

        abort_unless($question->survey_id === $survey->id, 404);

        $question->delete();

        return redirect()->route('survey.show', [$survey, 'tab' => 'questions'])
            ->with('success', 'Question deleted.');
    }

    /**
     * Show survey responses.
     */
    public function showResponses(Survey $survey)
    {
        $this->authorizeSurvey($survey);

        $responses = $survey->responses()
            ->with('user', 'participant')
            ->where('completed', true)
            ->orderBy('completed_at', 'desc')
            ->paginate(20);

        $survey->loadCount([
            'questions',
            'responses as completed_responses_count' => fn ($q) => $q->where('completed', true),
        ]);

        return view('survey.responses', compact('survey', 'responses'));
    }

    /**
     * Show survey analytics.
     */
    public function showAnalytics(Survey $survey)
    {
        $this->authorizeSurvey($survey);

        $questions = $survey->questions()->get();

        foreach ($questions as $question) {
            if ($question->isChartable()) {
                $question->statistics = $question->getStatistics();
            }
        }

        $responsesByDate = $survey->responses()
            ->where('completed', true)
            ->selectRaw('DATE(completed_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($item) => [
                'date' => Carbon::parse($item->date)->format('d M'),
                'count' => $item->count,
            ]);

        $survey->loadCount([
            'questions',
            'responses as completed_responses_count' => fn ($q) => $q->where('completed', true),
        ]);

        return view('survey.analytics', compact('survey', 'questions', 'responsesByDate'));
    }

    /**
     * Delete a survey response.
     */
    public function destroyResponse(Survey $survey, $response)
    {
        $this->authorizeSurvey($survey);

        $survey->responses()->findOrFail($response)->delete();

        return redirect()->route('survey.responses', $survey)
            ->with('success', 'Response deleted.');
    }

    /**
     * View a survey response detail (AJAX).
     */
    public function viewResponse(Survey $survey, $response)
    {
        $this->authorizeSurvey($survey);

        $surveyResponse = $survey->responses()
            ->with('user', 'participant')
            ->findOrFail($response);

        return response()->json([
            'response' => [
                'id' => $surveyResponse->id,
                'respondent_display_name' => $surveyResponse->respondent_display_name,
                'respondent_display_email' => $surveyResponse->respondent_display_email,
                'completed_at' => $surveyResponse->completed_at?->format('d M Y H:i'),
                'ip_address' => $surveyResponse->ip_address,
                'time_taken' => $surveyResponse->time_taken,
                'user_agent' => $surveyResponse->user_agent,
                'response_data' => $surveyResponse->response_data ?? [],
            ],
            'questions' => $survey->questions()->get(),
        ]);
    }

    /**
     * Export survey responses to CSV.
     */
    public function exportResponses(Survey $survey)
    {
        $this->authorizeSurvey($survey);

        $questions = $survey->questions()->get();

        $responses = $survey->responses()
            ->with('user', 'participant')
            ->where('completed', true)
            ->orderBy('completed_at', 'desc')
            ->get();

        $columns = ['Respondent Name', 'Email', 'Submitted Date', 'Submitted Time', 'Source', 'Time Taken (minutes)'];

        foreach ($questions as $question) {
            $columns[] = $question->question_text;
        }

        $callback = function () use ($responses, $questions, $columns) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM so Excel picks up the encoding
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns);

            foreach ($responses as $response) {
                $row = [
                    $response->respondent_display_name,
                    $response->respondent_display_email,
                    $response->completed_at?->format('Y-m-d'),
                    $response->completed_at?->format('H:i:s'),
                    $response->sourceLabel(),
                    $response->time_taken,
                ];

                foreach ($questions as $question) {
                    $row[] = $question->formatAnswer($response->response_data[$question->id] ?? null);
                }

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . Str::slug($survey->title) . '-responses.csv"',
        ]);
    }

    /**
     * Download QR code image for the survey link.
     */
    public function downloadQrCodeImage(Survey $survey)
    {
        $this->authorizeSurvey($survey);

        $renderer = new \BaconQrCode\Renderer\Image\SvgImageBackEnd();
        $style = new \BaconQrCode\Renderer\RendererStyle\RendererStyle(800);
        $imageRenderer = new \BaconQrCode\Renderer\ImageRenderer($style, $renderer);
        $writer = new \BaconQrCode\Writer($imageRenderer);

        return response($writer->writeString($survey->public_url))
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="survey-' . $survey->id . '-qrcode.svg"');
    }
}
