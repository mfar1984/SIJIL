<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('global_configs', function (Blueprint $table) {
            $table->id();
            
            // Organization Settings
            $table->string('org_name')->default('Sijil Event Management');
            $table->string('org_email')->default('contact@sijilevents.com');
            $table->string('timezone')->default('Asia/Kuala_Lumpur');
            $table->string('date_format')->default('d/m/Y');
            $table->string('org_logo')->nullable();
            
            // System Settings
            $table->boolean('maintenance_mode')->default(false);
            $table->boolean('debug_mode')->default(false);
            $table->integer('cache_lifetime')->default(60);
            $table->integer('pagination')->default(25);
            $table->boolean('error_reporting')->default(true);
            $table->boolean('activity_logging')->default(true);
            
            // Event Settings
            $table->integer('event_expiry')->default(48);
            $table->string('default_event_status')->default('published');
            $table->text('registration_message')->nullable();
            $table->boolean('allow_multiple_registrations')->default(true);
            $table->boolean('auto_confirmation_emails')->default(true);
            
            // Security Settings
            $table->integer('min_password_length')->default(8);
            $table->integer('password_expiry')->default(90);
            $table->boolean('require_special_chars')->default(true);
            $table->boolean('require_numbers')->default(true);
            $table->boolean('require_uppercase')->default(true);
            $table->integer('max_login_attempts')->default(5);
            $table->integer('lockout_duration')->default(15);
            $table->integer('session_timeout')->default(120);
            $table->boolean('enable_2fa')->default(true);
            $table->boolean('force_ssl')->default(true);
            $table->boolean('log_failed_logins')->default(true);
            $table->boolean('log_password_changes')->default(true);
            $table->boolean('log_permission_changes')->default(true);
            $table->boolean('enable_security_alerts')->default(true);
            
            // Appearance Settings
            $table->string('primary_color')->default('#004aad');
            $table->string('secondary_color')->default('#38bdf8');
            $table->string('default_theme')->default('light');
            $table->string('font_family')->default('inter');
            $table->boolean('allow_user_theme_choice')->default(true);
            $table->string('favicon')->nullable();
            $table->string('login_background')->nullable();
            $table->text('custom_css')->nullable();
            $table->string('sidebar_default')->default('expanded');
            $table->string('table_density')->default('default');
            $table->boolean('show_welcome_message')->default(true);
            $table->boolean('show_help_icons')->default(true);
            
            // Notification Settings
            $table->boolean('email_new_user_registration')->default(true);
            $table->boolean('email_event_registration')->default(true);
            $table->boolean('email_event_reminder')->default(true);
            $table->boolean('email_certificate_generated')->default(true);
            $table->boolean('email_password_reset')->default(true);
            $table->boolean('sms_event_registration')->default(false);
            $table->boolean('sms_event_reminder')->default(false);
            $table->integer('sms_reminder_hours')->default(24);
            $table->boolean('admin_system_errors')->default(true);
            $table->boolean('admin_new_registrations')->default(false);
            $table->boolean('admin_security_alerts')->default(true);
            $table->string('admin_notification_email')->default('admin@sijilevents.com');
            
            // API Settings
            $table->boolean('api_enabled')->default(true);
            $table->integer('api_rate_limit')->default(60);
            $table->boolean('enable_api_keys')->default(true);
            $table->boolean('enable_oauth')->default(true);
            $table->boolean('api_cors_enabled')->default(true);
            $table->text('cors_domains')->nullable();
            
            // Integration Settings
            $table->boolean('google_calendar_enabled')->default(false);
            $table->boolean('microsoft_teams_enabled')->default(false);
            $table->boolean('stripe_enabled')->default(true);
            $table->boolean('zoom_enabled')->default(true);
            
            // Webhook Settings
            $table->boolean('enable_webhooks')->default(true);
            $table->string('webhook_secret')->nullable();
            $table->text('webhook_events')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_configs');
    }
};
