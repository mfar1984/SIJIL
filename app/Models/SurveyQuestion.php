<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'question_text',
        'question_type',
        'description',
        'options',
        'required',
        'validation_rules',
        'order',
    ];

    protected $casts = [
        'options' => 'array',
        'validation_rules' => 'array',
        'required' => 'boolean',
    ];

    /**
     * Get the survey that owns the question.
     */
    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * Get responses data for this question.
     * 
     * @return array
     */
    public function getResponsesData()
    {
        $survey = $this->survey;
        $responses = $survey->responses()->where('completed', true)->get();
        $data = [];
        
        foreach ($responses as $response) {
            if (!isset($response->response_data[$this->id])) {
                continue;
            }

            $value = $response->response_data[$this->id];

            // Handle different question types
            switch ($this->question_type) {
                case 'checkbox':
                    // Multiple selections, each should be counted separately
                    if (is_array($value)) {
                        foreach ($value as $option) {
                            if (!isset($data[$option])) {
                                $data[$option] = 0;
                            }
                            $data[$option]++;
                        }
                    }
                    break;
                    
                case 'multiple_choice':
                case 'dropdown':
                case 'rating':
                    // Single selection
                    if (!isset($data[$value])) {
                        $data[$value] = 0;
                    }
                    $data[$value]++;
                    break;

                case 'text':
                case 'textarea':
                case 'date':
                    // For text-based responses, just collect them
                    $data[] = $value;
                    break;
            }
        }
        
        return $data;
    }

    /**
     * Get statistics for this question for charts and analysis.
     * 
     * @return array
     */
    public function getStatistics()
    {
        $data = $this->getResponsesData();
        $total = 0;
        $stats = [];

        // For question types with predefined options
        if (in_array($this->question_type, ['multiple_choice', 'checkbox', 'dropdown', 'rating'])) {
            // Calculate totals first
            foreach ($data as $count) {
                $total += $count;
            }

            // Format the data for charts
            if ($this->question_type === 'rating') {
                // For ratings, make sure we have all values from 1 to 5
                for ($i = 1; $i <= 5; $i++) {
                    $count = isset($data[$i]) ? $data[$i] : 0;
                    $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
                    $stats[] = [
                        'label' => $i,
                        'count' => $count,
                        'percentage' => $percentage,
                    ];
                }
            } else {
                // For options-based questions
                foreach ($this->options as $option) {
                    $count = isset($data[$option]) ? $data[$option] : 0;
                    $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
                    $stats[] = [
                        'label' => $option,
                        'count' => $count,
                        'percentage' => $percentage,
                    ];
                }
            }
            
            return [
                'total' => $total,
                'data' => $stats,
            ];
        } 
        
        // For text-based responses, just return the raw data
        return [
            'total' => count($data),
            'data' => $data,
        ];
    }
}
