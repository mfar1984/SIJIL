<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PwaEmailLog extends Model
{
    use HasFactory;

    protected $table = 'pwa_email_logs';

    protected $fillable = [
        'template_id',
        'action',
        'quantity',
        'meta',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'meta' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(PwaEmailTemplate::class, 'template_id');
    }
}


