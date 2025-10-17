<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SurveyController extends Controller
{
    /**
     * Display a listing of the surveys.
     */
    public function index(Request $request)
    {
        // Start with base query
        $query = Survey::with(['event', 'questions']);

        // Non-admin users only see their own surveys
        if (!auth()->user()->hasRole('Administrator')) {
            $query->where('user_id', auth()->id());
        }

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by access type
        if ($request->filled('access_type')) {
            $query->where('access_type', $request->access_type);
        }

        // Filter by event
        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        // Get paginated results with per_page parameter
        $perPage = $request->get('per_page', 10);
        $surveys = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Get events for filter dropdown (respect role scoping)
        $eventsQuery = Event::orderBy('name');
        if (!auth()->user()->hasRole('Administrator')) {
            $eventsQuery->where('user_id', auth()->id());
        }
        $events = $eventsQuery->get();

        return view('survey.index', compact('surveys', 'events'));
    }

    /**
     * Show the form for creating a new survey.
     */
    public function create()
    {
        $eventsQuery = Event::orderBy('name');
        if (!auth()->user()->hasRole('Administrator')) {
            $eventsQuery->where('user_id', auth()->id());
        }
        $events = $eventsQuery->get();
        return view('survey.create', compact('events'));
    }

    /**
     * Store a newly created survey in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'access_type' => 'required|in:public,private,registered',
            'event_id' => 'nullable|exists:events,id',
            'allow_anonymous' => 'nullable|boolean',
        ]);

        $survey = new Survey();
        $survey->title = $request->title;
        $survey->description = $request->description;
        $survey->user_id = auth()->id();
        $survey->event_id = $request->event_id;
        $survey->access_type = $request->access_type;
        $survey->allow_anonymous = $request->has('allow_anonymous');
        $survey->status = 'draft';
        $survey->slug = Str::slug($request->title) . '-' . Str::random(8);
        $survey->save();

        return redirect()->route('survey.edit', $survey)
            ->with('success', 'Survey created successfully. Now you can add questions.');
    }

    /**
     * Display the specified survey.
     */
    public function show(Survey $survey)
    {
        $responsesCount = $survey->responses()->where('completed', true)->count();
        return view('survey.show', compact('survey', 'responsesCount'));
    }

    /**
     * Show the form for editing the specified survey.
     */
    public function edit(Survey $survey)
    {
        $eventsQuery = Event::orderBy('name');
        if (!auth()->user()->hasRole('Administrator')) {
            $eventsQuery->where('user_id', auth()->id());
        }
        $events = $eventsQuery->get();
        return view('survey.edit', compact('survey', 'events'));
    }

    /**
     * Update the specified survey in storage.
     */
    public function update(Request $request, Survey $survey)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'access_type' => 'required|in:public,private,registered',
            'event_id' => 'nullable|exists:events,id',
            'allow_anonymous' => 'nullable|boolean',
        ]);

        $survey->title = $request->title;
        $survey->description = $request->description;
        $survey->event_id = $request->event_id;
        $survey->access_type = $request->access_type;
        $survey->allow_anonymous = $request->has('allow_anonymous');
        $survey->save();

        return redirect()->route('survey.edit', $survey)
            ->with('success', 'Survey updated successfully.');
    }

    /**
     * Remove the specified survey from storage.
     */
    public function destroy(Survey $survey)
    {
        // Delete related questions and responses
        $survey->questions()->delete();
        $survey->responses()->delete();
        
        // Delete the survey
        $survey->delete();

        return redirect()->route('survey.index')
            ->with('success', 'Survey deleted successfully.');
    }

    /**
     * Toggle the published status of a survey.
     */
    public function togglePublish(Survey $survey)
    {
        if ($survey->status === 'draft') {
            // Check if the survey has questions before publishing
            if ($survey->questions->isEmpty()) {
                return redirect()->back()
                    ->with('error', 'Cannot publish a survey without questions. Please add at least one question.');
            }

            $survey->status = 'published';
            $survey->published_at = now();
        } else {
            $survey->status = 'draft';
        }

        $survey->save();

        $statusMessage = $survey->status === 'published' ? 'published' : 'unpublished';
        return redirect()->back()
            ->with('success', "Survey {$statusMessage} successfully.");
    }

    /**
     * Store a new question for a survey.
     */
    public function storeQuestion(Request $request, Survey $survey)
    {
        $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:text,textarea,multiple_choice,checkbox,dropdown,rating,date',
            'description' => 'nullable|string',
            'options' => 'nullable|array',
            'options.*' => 'nullable|string',
            'required' => 'nullable|boolean',
        ]);

        // Clean empty options
        $options = null;
        if ($request->has('options') && in_array($request->question_type, ['multiple_choice', 'checkbox', 'dropdown'])) {
            $options = array_filter($request->options, fn($option) => !empty($option));
        }

        $order = $survey->questions()->max('order') + 1;

        $question = new SurveyQuestion([
            'question_text' => $request->question_text,
            'question_type' => $request->question_type,
            'description' => $request->description,
            'options' => $options,
            'required' => $request->has('required'),
            'order' => $order,
        ]);

        $survey->questions()->save($question);

        return redirect()->route('survey.edit', $survey)
            ->with('success', 'Question added successfully.');
    }

    /**
     * Update the order of questions.
     */
    public function updateQuestionOrder(Request $request, Survey $survey)
    {
        $request->validate([
            'questions' => 'required|array',
            'questions.*' => 'exists:survey_questions,id',
        ]);

        $questions = $request->input('questions');
        foreach ($questions as $index => $questionId) {
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
        // Make sure the question belongs to the survey
        if ($question->survey_id !== $survey->id) {
            abort(404);
        }

        $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:text,textarea,multiple_choice,checkbox,dropdown,rating,date',
            'description' => 'nullable|string',
            'options' => 'nullable|array',
            'options.*' => 'nullable|string',
            'required' => 'nullable|boolean',
        ]);

        // Clean empty options
        $options = null;
        if ($request->has('options') && in_array($request->question_type, ['multiple_choice', 'checkbox', 'dropdown'])) {
            $options = array_filter($request->options, fn($option) => !empty($option));
        }

        $question->question_text = $request->question_text;
        $question->question_type = $request->question_type;
        $question->description = $request->description;
        $question->options = $options;
        $question->required = $request->has('required');
        $question->save();

        return redirect()->route('survey.edit', $survey)
            ->with('success', 'Question updated successfully.');
    }

    /**
     * Delete a specific question.
     */
    public function destroyQuestion(Survey $survey, SurveyQuestion $question)
    {
        // Make sure the question belongs to the survey
        if ($question->survey_id !== $survey->id) {
            abort(404);
        }

        $question->delete();

        return redirect()->route('survey.edit', $survey)
            ->with('success', 'Question deleted successfully.');
    }

    /**
     * Show survey responses.
     */
    public function showResponses(Survey $survey)
    {
        $responses = $survey->responses()
            ->with('user', 'participant')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('survey.responses', compact('survey', 'responses'));
    }

    /**
     * Show survey analytics.
     */
    public function showAnalytics(Survey $survey)
    {
        // Get statistics for each question
        $questions = $survey->questions()->orderBy('order')->get();
        
        foreach ($questions as $question) {
            if (in_array($question->question_type, ['multiple_choice', 'checkbox', 'dropdown', 'rating'])) {
                $question->statistics = $question->getStatistics();
            }
        }

        // Get response rate over time
        $responsesByDate = $survey->responses()
            ->where('completed', true)
            ->selectRaw('DATE(completed_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('d M'),
                    'count' => $item->count
                ];
            });

        return view('survey.analytics', compact('survey', 'questions', 'responsesByDate'));
    }
    
    /**
     * Delete a survey response.
     */
    public function destroyResponse(Survey $survey, $response)
    {
        // Find the response and make sure it belongs to the survey
        $surveyResponse = $survey->responses()->findOrFail($response);
        
        // Delete the response
        $surveyResponse->delete();
        
        return redirect()->route('survey.responses', $survey)
            ->with('success', 'Response deleted successfully.');
    }
    
    /**
     * View a survey response detail (AJAX).
     */
    public function viewResponse(Survey $survey, $response)
    {
        // Find the response and make sure it belongs to the survey
        $surveyResponse = $survey->responses()
            ->with('user', 'participant')
            ->findOrFail($response);
        
        // Get questions in correct order
        $questions = $survey->questions()->orderBy('order')->get();
        
        // Format response data for display
        $formattedResponse = [
            'id' => $surveyResponse->id,
            'respondent_display_name' => $surveyResponse->respondent_display_name,
            'respondent_display_email' => $surveyResponse->respondent_display_email,
            'completed_at' => $surveyResponse->completed_at ? $surveyResponse->completed_at->format('d M Y H:i') : null,
            'ip_address' => $surveyResponse->ip_address,
            'time_taken' => $surveyResponse->time_taken,
            'user_agent' => $surveyResponse->user_agent,
            'response_data' => $surveyResponse->response_data ?? [],
        ];
        
        return response()->json([
            'response' => $formattedResponse,
            'questions' => $questions
        ]);
    }
    
    /**
     * Export survey responses to CSV
     */
    public function exportResponses(Survey $survey)
    {
        // Get questions
        $questions = $survey->questions()->orderBy('order')->get();
        
        // Get completed responses
        $responses = $survey->responses()
            ->with('user', 'participant')
            ->where('completed', true)
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Create CSV headers
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . Str::slug($survey->title) . '-responses.csv"',
        ];
        
        $columns = [
            'Respondent Name',
            'Email',
            'Submitted Date',
            'Submitted Time',
            'Source',
            'Time Taken (minutes)',
        ];
        
        // Add question text as columns
        foreach ($questions as $question) {
            $columns[] = $question->question_text;
        }
        
        $callback = function() use ($responses, $questions, $columns) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel
            fputs($file, "\xEF\xBB\xBF");
            
            // Add headers
            fputcsv($file, $columns);
            
            // Add data rows
            foreach ($responses as $response) {
                $row = [
                    $response->respondent_display_name,
                    $response->respondent_display_email,
                    $response->completed_at ? $response->completed_at->format('Y-m-d') : 'N/A',
                    $response->completed_at ? $response->completed_at->format('H:i:s') : 'N/A',
                    $response->user_id ? 'Registered User' : 
                        ($response->participant_id ? 'Participant' : 'Public'),
                    $response->time_taken ?? 'N/A',
                ];
                
                // Add answers for each question
                foreach ($questions as $question) {
                    $answer = $response->response_data[$question->id] ?? 'No response';
                    
                    // Format answer based on type
                    if ($question->question_type === 'checkbox' && is_array($answer)) {
                        $row[] = implode(', ', $answer);
                    } else {
                        $row[] = $answer;
                    }
                }
                
                fputcsv($file, $row);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
