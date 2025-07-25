<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * DeliveryConfig Model
 * 
 * This model stores email and SMS delivery configurations for each organizer.
 * Each organizer can configure their own email and SMS settings independently.
 * 
 * The settings are stored as JSON in the database, allowing for flexible configuration
 * options for different providers (SMTP, Mailgun, SES, Twilio, Nexmo, AWS SNS, Infobip).
 */
class DeliveryConfig extends Model
{
    use HasFactory;

    protected $table = 'delivery_configs';

    protected $fillable = [
        'user_id',
        'config_type',  // 'email' or 'sms'
        'provider',     // 'smtp', 'mailgun', 'ses', 'sendmail', 'twilio', 'nexmo', 'aws_sns', 'infobip'
        'is_active',
        'settings',     // JSON encoded settings
        'default_template',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the delivery configuration.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get email configuration for a user
     * 
     * @param int $userId
     * @return self|null
     */
    public static function getEmailConfig($userId)
    {
        return self::where('user_id', $userId)
            ->where('config_type', 'email')
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get SMS configuration for a user
     * 
     * @param int $userId
     * @return self|null
     */
    public static function getSmsConfig($userId)
    {
        return self::where('user_id', $userId)
            ->where('config_type', 'sms')
            ->where('is_active', true)
            ->first();
    }
} 