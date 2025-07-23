<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CertificateTemplate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'pdf_file',
        'background_pdf',
        'orientation',
        'placeholders',
        'template_data',
        'is_active',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'placeholders' => 'array',
        'template_data' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the certificates that use this template.
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class, 'template_id');
    }

    /**
     * Get the user who created the template.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Convert placeholder coordinates from pixels to mm if needed.
     * This is a helper method for migration from old format.
     */
    public function convertPlaceholdersToMm()
    {
        if (!$this->placeholders) {
            return;
        }

        $placeholders = $this->placeholders;
        $mmToPxRatio = 3.779528; // 1mm = ~3.78px at 96 DPI
        $needsConversion = false;

        // Check if any placeholder has coordinates > 100 (likely in pixels)
        foreach ($placeholders as $placeholder) {
            if ($placeholder['x'] > 100 || $placeholder['y'] > 100) {
                $needsConversion = true;
                break;
            }
        }

        if ($needsConversion) {
            $convertedPlaceholders = [];
            foreach ($placeholders as $placeholder) {
                $convertedPlaceholder = $placeholder;
                $convertedPlaceholder['x'] = round($placeholder['x'] / $mmToPxRatio, 1);
                $convertedPlaceholder['y'] = round($placeholder['y'] / $mmToPxRatio, 1);
                
                // Convert font size if it exists
                if (isset($placeholder['fontSize'])) {
                    $convertedPlaceholder['fontSize'] = round($placeholder['fontSize'] / $mmToPxRatio, 1);
                }
                
                // Ensure placeholder type uses {{name}} format
                if (isset($placeholder['type'])) {
                    $type = $placeholder['type'];
                    if (strpos($type, '{{') !== 0 || strpos($type, '}}') !== strlen($type) - 2) {
                        // Remove any existing braces
                        $cleanType = trim($type, '{}');
                        $convertedPlaceholder['type'] = '{{' . $cleanType . '}}';
                    }
                }
                
                $convertedPlaceholders[] = $convertedPlaceholder;
            }
            
            $this->placeholders = $convertedPlaceholders;
            $this->save();
        }
    }
}
