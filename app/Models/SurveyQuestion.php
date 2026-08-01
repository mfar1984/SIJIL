<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyQuestion extends Model
{
    use HasFactory;

    /**
     * Supported question types, with the label shown in the builder.
     *
     * @return array<string, string>
     */
    public const TYPES = [
        'text' => 'Short answer',
        'textarea' => 'Paragraph',
        'multiple_choice' => 'Multiple choice',
        'checkbox' => 'Checkboxes',
        'dropdown' => 'Dropdown',
        'rating' => 'Linear scale',
        'date' => 'Date',
        'email' => 'Email',
        'number' => 'Number',
    ];

    /** Types that require a list of options. */
    public const OPTION_TYPES = ['multiple_choice', 'checkbox', 'dropdown'];

    /** Types that can be summarised as a chart. */
    public const CHARTABLE_TYPES = ['multiple_choice', 'checkbox', 'dropdown', 'rating'];

    protected $fillable = [
        'survey_id',
        'question_text',
        'question_type',
        'description',
        'options',
        'scale_min',
        'scale_max',
        'scale_min_label',
        'scale_max_label',
        'required',
        'validation_rules',
        'order',
    ];

    protected $casts = [
        'options' => 'array',
        'validation_rules' => 'array',
        'required' => 'boolean',
        'scale_min' => 'integer',
        'scale_max' => 'integer',
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->question_type] ?? ucfirst(str_replace('_', ' ', (string) $this->question_type));
    }

    public function needsOptions(): bool
    {
        return in_array($this->question_type, self::OPTION_TYPES, true);
    }

    public function isChartable(): bool
    {
        return in_array($this->question_type, self::CHARTABLE_TYPES, true);
    }

    /**
     * The values on a linear scale, honouring the configured range.
     *
     * @return array<int, int>
     */
    public function scaleValues(): array
    {
        $min = max(0, (int) ($this->scale_min ?? 1));
        $max = (int) ($this->scale_max ?? 5);

        if ($max <= $min) {
            $max = $min + 1;
        }

        return range($min, $max);
    }

    /**
     * Answers given to this question across all submitted responses.
     *
     * Option based types return a count per option; free text types return the
     * raw list of answers.
     */
    public function getResponsesData(): array
    {
        $responses = $this->survey->completedResponses()->get();
        $data = [];

        foreach ($responses as $response) {
            $answers = $response->response_data ?? [];

            if (! array_key_exists($this->id, $answers)) {
                continue;
            }

            $value = $answers[$this->id];

            if ($value === null || $value === '' || $value === []) {
                continue;
            }

            switch ($this->question_type) {
                case 'checkbox':
                    foreach ((array) $value as $option) {
                        $data[$option] = ($data[$option] ?? 0) + 1;
                    }
                    break;

                case 'multiple_choice':
                case 'dropdown':
                case 'rating':
                    $key = is_array($value) ? reset($value) : $value;
                    $data[$key] = ($data[$key] ?? 0) + 1;
                    break;

                default:
                    $data[] = $value;
                    break;
            }
        }

        return $data;
    }

    /**
     * Chart ready statistics for this question.
     */
    public function getStatistics(): array
    {
        $data = $this->getResponsesData();

        if (! $this->isChartable()) {
            return [
                'total' => count($data),
                'data' => $data,
            ];
        }

        $total = array_sum($data);
        $stats = [];

        $labels = $this->question_type === 'rating'
            ? $this->scaleValues()
            : ($this->options ?? []);

        foreach ($labels as $label) {
            $count = $data[$label] ?? 0;
            $stats[] = [
                'label' => (string) $label,
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
            ];
        }

        return [
            'total' => $total,
            'data' => $stats,
        ];
    }

    /**
     * Format a stored answer for display in tables and exports.
     */
    public function formatAnswer(mixed $value): string
    {
        if ($value === null || $value === '' || $value === []) {
            return '—';
        }

        if (is_array($value)) {
            return implode(', ', $value);
        }

        return (string) $value;
    }
}
