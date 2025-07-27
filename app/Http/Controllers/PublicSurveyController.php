<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicSurveyController extends Controller
{
    /**
     * Show the survey form.
     */
    public function show($slug)
    {
        $survey = Survey::where('slug', $slug)->first();
        
        if (!$survey) {
            abort(404, 'Survey not found');
        }

        // Check if the survey is published
        if ($survey->status !== 'published') {
            return redirect()->route('public.survey.expired', $survey->slug);
        }
        
        // Check if the survey is expired
        if ($survey->expires_at && now()->gt($survey->expires_at)) {
            return redirect()->route('public.survey.expired', $survey->slug);
        }

        // Get or create a response (for anonymous users to save progress)
        $response = $this->getOrCreateResponse($survey);
        
        return view('public.survey.show', compact('survey', 'response'));
    }

    /**
     * Submit the survey response.
     */
    public function submit(Request $request, $slug)
    {
        $survey = Survey::where('slug', $slug)->first();
        
        if (!$survey) {
            abort(404, 'Survey not found');
        }

        // Check if the survey is published
        if ($survey->status !== 'published') {
            return redirect()->route('public.survey.expired', $survey->slug);
        }
        
        // Check if the survey is expired
        if ($survey->expires_at && now()->gt($survey->expires_at)) {
            return redirect()->route('public.survey.expired', $survey->slug);
        }

        // Prepare validation rules
        $rules = [];
        $requiredQuestions = [];

        foreach ($survey->questions as $question) {
            $fieldName = 'question_' . $question->id;
            
            if ($question->required) {
                $requiredQuestions[$fieldName] = 'required';
            }
            
            // Add specific validation rules based on question type
            switch ($question->question_type) {
                case 'checkbox':
                    $rules[$fieldName] = 'nullable|array';
                    break;
                case 'date':
                    $rules[$fieldName] = 'nullable|date';
                    break;
                default:
                    $rules[$fieldName] = 'nullable';
                    break;
            }
        }
        
        // Merge required and type-specific rules
        $rules = array_merge($rules, $requiredQuestions);
        
        // Validate request
        $request->validate($rules);

        // Prepare response data
        $responseData = [];
        foreach ($survey->questions as $question) {
            $fieldName = 'question_' . $question->id;
            $responseData[$question->id] = $request->input($fieldName);
        }
        
        // Get or create a response
        $response = $this->getOrCreateResponse($survey);
        
        // Update the response with user data if provided
        if (!$survey->allow_anonymous) {
            $response->respondent_name = $request->input('respondent_name');
            $response->respondent_email = $request->input('respondent_email');
            $response->respondent_phone = $request->input('respondent_phone');
        }
        
        // Save response data
        $response->response_data = $responseData;
        $response->completed = true;
        $response->completed_at = now();
        $response->save();
        
        // Redirect to thank you page
        return redirect()->route('public.survey.thankyou', $survey->slug);
    }

    /**
     * Display the thank you page.
     */
    public function thankYou($slug)
    {
        $survey = Survey::where('slug', $slug)->first();
        
        if (!$survey) {
            abort(404, 'Survey not found');
        }
        
        return view('public.survey.thankyou', compact('survey'));
    }

    /**
     * Display the expired/unavailable page.
     */
    public function expired($slug)
    {
        $survey = Survey::where('slug', $slug)->first();
        
        if (!$survey) {
            abort(404, 'Survey not found');
        }
        
        return view('public.survey.expired', compact('survey'));
    }

    /**
     * Get or create a response for the current session.
     */
    private function getOrCreateResponse($survey)
    {
        // Get the session ID
        $sessionId = session()->getId();
        
        // Try to find an existing incomplete response for this session
        $response = $survey->responses()
            ->where('session_id', $sessionId)
            ->where('completed', false)
            ->first();
            
        // If no response exists, create a new one
        if (!$response) {
            $response = new SurveyResponse([
                'survey_id' => $survey->id,
                'session_id' => $sessionId,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'started_at' => now(),
                'response_data' => [], // Add empty array as default value
            ]);
            
            // If user is authenticated, associate the response with the user
            if (auth()->check()) {
                $response->user_id = auth()->id();
                $response->respondent_type = 'user';
            } else {
                $response->respondent_type = 'anonymous';
            }
            
            $response->save();
        }
        
        return $response;
    }
}
