<?php

namespace App\Http\Controllers\Pwa;

use App\Http\Controllers\Controller;
use App\Models\PwaEmailTemplate;
use App\Models\PwaEmailLog;
use Illuminate\Http\Request;
use App\Helpers\EmailHelper;
use App\Models\DeliveryConfig;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class PwaTemplatesController extends Controller
{
    /**
     * Display PWA email templates with role-based access
     */
    public function index()
    {
        $user = Auth::user();
        
        // Multi-tenant templates based on user role
        if ($user->hasRole('Administrator')) {
            // Administrator sees global templates
            $templates = PwaEmailTemplate::where('scope', 'global')->get();
            
            // If no global templates exist, create defaults
            if ($templates->isEmpty()) {
                $templates = $this->createDefaultTemplates($user->id, 'global');
            }
        } else {
            // Organizer sees their own templates
            $templates = PwaEmailTemplate::where('scope', 'organizer')
                ->where('user_id', $user->id)
                ->get();
            
            // If no organizer templates exist, create from global defaults
            if ($templates->isEmpty()) {
                $globalTemplates = PwaEmailTemplate::where('scope', 'global')->get();
                
                if ($globalTemplates->isEmpty()) {
                    $templates = $this->createDefaultTemplates($user->id, 'organizer', $user->id);
                } else {
                    $templates = $this->copyGlobalTemplates($globalTemplates, $user->id);
                }
            }
        }

        // Get email statistics (real from logs)
        $emailStats = $this->getEmailStatistics($user);

        // Convenience: pick a primary template (prefer welcome)
        $primaryTemplate = $templates->firstWhere('type', 'welcome') ?? $templates->first();

        return view('ecertificate.templates', compact('templates', 'emailStats', 'primaryTemplate'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('ecertificate.templates-create');
    }

    /**
     * Store new template
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:welcome,password_reset,event_reminder,custom',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        PwaEmailTemplate::create([
            'name' => $request->name,
            'type' => $request->type,
            'subject' => $request->subject,
            'content' => $request->content,
            'scope' => $user->hasRole('Administrator') ? 'global' : 'organizer',
            'user_id' => $user->hasRole('Administrator') ? null : $user->id,
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        return redirect()->route('pwa.templates')->with('success', 'Template created successfully.');
    }

    /**
     * Show the form for editing the specified template
     */
    public function edit(PwaEmailTemplate $template)
    {
        $user = Auth::user();
        
        // Check if user can edit this template
        if (!$user->hasRole('Administrator')) {
            if ($template->scope !== 'organizer' || $template->user_id !== $user->id) {
                abort(403, 'You can only edit your own templates.');
            }
        }

        return view('ecertificate.templates.edit', compact('template'));
    }

    /**
     * Update the specified template
     */
    public function update(Request $request, PwaEmailTemplate $template)
    {
        $user = Auth::user();
        
        // Check if user can edit this template
        if (!$user->hasRole('Administrator')) {
            if ($template->scope !== 'organizer' || $template->user_id !== $user->id) {
                abort(403, 'You can only edit your own templates.');
            }
        }

        $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'is_active' => 'boolean'
        ]);

        $template->update([
            'subject' => $request->subject,
            'content' => $request->content,
            'is_active' => $request->boolean('is_active'),
            'updated_by' => $user->id
        ]);

        return redirect()->route('pwa.templates')->with('success', 'Email template updated successfully.');
    }

    /**
     * Delete template
     */
    public function destroy(PwaEmailTemplate $template)
    {
        $user = Auth::user();
        if (!$user->hasRole('Administrator')) {
            if ($template->scope !== 'organizer' || $template->user_id !== $user->id) {
                abort(403, 'You can only delete your own templates.');
            }
        }
        $template->delete();
        return redirect()->route('pwa.templates')->with('success', 'Template deleted successfully.');
    }

    /**
     * Export templates as CSV
     */
    public function export()
    {
        $user = Auth::user();
        $query = PwaEmailTemplate::query();
        if (!$user->hasRole('Administrator')) {
            $query->where('scope', 'organizer')->where('user_id', $user->id);
        }
        $templates = $query->orderBy('updated_at', 'desc')->get();

        $filename = 'pwa_templates_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($templates) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, ['Name','Type','Subject','Active','Times Used','Last Used At','Scope','Organizer ID','Created At','Updated At']);
            foreach ($templates as $t) {
                fputcsv($file, [
                    $t->name,
                    $t->type,
                    $t->subject,
                    $t->is_active ? 'Yes' : 'No',
                    $t->times_used ?? 0,
                    optional($t->last_used_at)->toDateTimeString(),
                    $t->scope,
                    $t->user_id,
                    optional($t->created_at)->toDateTimeString(),
                    optional($t->updated_at)->toDateTimeString(),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Trigger bulk email send (stub: record intent and update template usage)
     */
    public function bulkEmail(Request $request)
    {
        $user = Auth::user();
        $templateId = $request->input('template_id');

        $templateQuery = PwaEmailTemplate::query();
        if (!$user->hasRole('Administrator')) {
            $templateQuery->where('scope', 'organizer')->where('user_id', $user->id);
        }
        if ($templateId) {
            $templateQuery->where('id', $templateId);
        }
        $template = $templateQuery->first();
        if (!$template) {
            return redirect()->route('pwa.templates')->with('error', 'Template not found or not permitted.');
        }

        // Determine recipient count (organizer's participants or all if admin)
        if ($user->hasRole('Administrator')) {
            $recipients = 0; // in real impl, count all pwa participants
        } else {
            $recipients = 0; // in real impl, count participants linked to organizer events
        }

        // Update usage stats and log a sent event
        $template->incrementUsage();
        PwaEmailLog::create([
            'template_id' => $template->id,
            'action' => 'sent',
            'quantity' => $recipients,
            'meta' => ['bulk' => true]
        ]);

        return redirect()->route('pwa.templates')->with('success', 'Bulk email queued (simulated) using template: ' . $template->name . '. Recipients: ' . $recipients);
    }

    /**
     * Reset template content/subject to default by type
     */
    public function resetDefault(PwaEmailTemplate $template)
    {
        $user = Auth::user();
        if (!$user->hasRole('Administrator')) {
            if ($template->scope !== 'organizer' || $template->user_id !== $user->id) {
                abort(403, 'You can only reset your own templates.');
            }
        }

        switch ($template->type) {
            case 'welcome':
                $template->subject = 'Welcome to E-Certificate Online - Your PWA Access';
                $template->content = $this->getDefaultWelcomeContent();
                break;
            case 'password_reset':
                $template->subject = 'Password Reset Request - E-Certificate Online';
                $template->content = $this->getDefaultPasswordResetContent();
                break;
            case 'event_reminder':
                $template->subject = 'Event Reminder - {event_name}';
                $template->content = $this->getDefaultEventReminderContent();
                break;
            default:
                // custom: clear to blank
                $template->subject = 'Custom Email';
                $template->content = '<p>Hello @{{name}},</p>';
        }

        $template->updated_by = $user->id;
        $template->save();

        return redirect()->route('pwa.templates')->with('success', 'Template reset to default.');
    }

    /**
     * Send test email (simulated)
     */
    public function sendTest(Request $request, PwaEmailTemplate $template)
    {
        $user = Auth::user();
        if (!$user->hasRole('Administrator')) {
            if ($template->scope !== 'organizer' || $template->user_id !== $user->id) {
                abort(403, 'You can only use your own templates.');
            }
        }

        // Validate email if provided
        $request->validate([
            'email_address' => 'nullable|email'
        ]);

        // Compose content + subject with variables and tracking
        $recipientEmail = $request->input('email_address', Auth::user()->email);
        $sampleData = [
            'name' => 'Test User',
            'email' => $recipientEmail,
            'password' => 'TempPass123',
            'pwa_link' => 'https://apps.e-certificate.com.my',
            'event_name' => 'Sample Event',
            'organization' => 'E-Certificate Online',
            'login_url' => 'https://pwa.e-certificate.com.my/login',
            'support_email' => 'support@e-certificate.com.my'
        ];

        $subject = $this->replaceVariables($template->subject, $sampleData);
        $body = $this->replaceVariables($template->content, $sampleData);
        $html = EmailHelper::cleanHtml($body);
        $html = EmailHelper::replaceLinksWithTracking($html, $template->id, $recipientEmail);
        $html = EmailHelper::appendOpenTrackingPixel($html, $template->id, $recipientEmail);

        // Load active email config for this user
        $config = DeliveryConfig::getEmailConfig(Auth::id());
        if (!$config) {
            return redirect()->route('pwa.templates')->with('error', 'No active email configuration found. Configure it at Config → Deliver.');
        }

        // Configure mailer based on provider
        $settings = $config->settings ?? [];
        $fromName = $settings['from_name'] ?? 'SIJIL System';
        $fromAddress = $settings['from_address'] ?? 'no-reply@example.com';

        switch ($config->provider) {
            case 'smtp':
                config([
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp.host' => $settings['host'] ?? 'smtp.mailtrap.io',
                    'mail.mailers.smtp.port' => $settings['port'] ?? '2525',
                    'mail.mailers.smtp.encryption' => ($settings['encryption'] ?? null) === 'none' ? null : ($settings['encryption'] ?? null),
                    'mail.mailers.smtp.username' => $settings['username'] ?? '',
                    'mail.mailers.smtp.password' => $settings['password'] ?? '',
                    'mail.from.address' => $fromAddress,
                    'mail.from.name' => $fromName,
                ]);
                break;
            case 'mailgun':
                config([
                    'mail.default' => 'mailgun',
                    'services.mailgun.domain' => $settings['domain'] ?? '',
                    'services.mailgun.secret' => $settings['secret'] ?? '',
                    'services.mailgun.endpoint' => $settings['endpoint'] ?? 'api.mailgun.net',
                    'mail.from.address' => $fromAddress,
                    'mail.from.name' => $fromName,
                ]);
                break;
            case 'ses':
                config([
                    'mail.default' => 'ses',
                    'services.ses.key' => $settings['key'] ?? '',
                    'services.ses.secret' => $settings['secret'] ?? '',
                    'services.ses.region' => $settings['region'] ?? 'us-east-1',
                    'mail.from.address' => $fromAddress,
                    'mail.from.name' => $fromName,
                ]);
                break;
            case 'sendmail':
                config([
                    'mail.default' => 'sendmail',
                    'mail.mailers.sendmail.path' => $settings['path'] ?? '/usr/sbin/sendmail -bs',
                    'mail.from.address' => $fromAddress,
                    'mail.from.name' => $fromName,
                ]);
                break;
        }

        try {
            Mail::html($html, function ($message) use ($recipientEmail, $subject, $fromName, $fromAddress) {
                $message->to($recipientEmail)
                        ->subject($subject)
                        ->from($fromAddress, $fromName);
            });

            // Record 'sent'
            $template->incrementUsage();
            PwaEmailLog::create([
                'template_id' => $template->id,
                'action' => 'sent',
                'quantity' => 1,
                'meta' => ['test' => true, 'to' => $recipientEmail]
            ]);

            return redirect()->route('pwa.templates')->with('success', 'Test email sent to ' . $recipientEmail . '.');
        } catch (\Throwable $e) {
            \Log::error('PWA test email send failed', ['error' => $e->getMessage()]);
            return redirect()->route('pwa.templates')->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

    /**
     * Preview email template
     */
    public function preview(Request $request, PwaEmailTemplate $template)
    {
        $user = Auth::user();
        
        // Check if user can preview this template
        if (!$user->hasRole('Administrator')) {
            if ($template->scope !== 'organizer' || $template->user_id !== $user->id) {
                abort(403, 'You can only preview your own templates.');
            }
        }

        // Replace variables with sample data
        $sampleData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'TempPass123',
            'pwa_link' => 'https://apps.e-certificate.com.my',
            'event_name' => 'Sample Event 2024',
            'organization' => 'Sample Organization',
            'login_url' => 'https://pwa.e-certificate.com.my/login',
            'support_email' => 'support@e-certificate.com.my'
        ];

        $previewContent = $this->replaceVariables($template->content, $sampleData);
        $previewSubject = $this->replaceVariables($template->subject, $sampleData);

        return response()->json([
            'subject' => $previewSubject,
            'content' => $previewContent
        ]);
    }

    /**
     * Create default email templates
     */
    private function createDefaultTemplates($userId, $scope, $organizerId = null)
    {
        $templates = [];

        // Welcome Email Template
        $templates[] = PwaEmailTemplate::create([
            'name' => 'Welcome Email',
            'type' => 'welcome',
            'subject' => 'Welcome to E-Certificate Online - Your PWA Access',
            'content' => $this->getDefaultWelcomeContent(),
            'scope' => $scope,
            'user_id' => $organizerId,
            'is_active' => true,
            'created_by' => $userId
        ]);

        // Password Reset Template
        $templates[] = PwaEmailTemplate::create([
            'name' => 'Password Reset',
            'type' => 'password_reset',
            'subject' => 'Password Reset Request - E-Certificate Online',
            'content' => $this->getDefaultPasswordResetContent(),
            'scope' => $scope,
            'user_id' => $organizerId,
            'is_active' => true,
            'created_by' => $userId
        ]);

        // Event Reminder Template
        $templates[] = PwaEmailTemplate::create([
            'name' => 'Event Reminder',
            'type' => 'event_reminder',
            'subject' => 'Event Reminder - {event_name}',
            'content' => $this->getDefaultEventReminderContent(),
            'scope' => $scope,
            'user_id' => $organizerId,
            'is_active' => true,
            'created_by' => $userId
        ]);

        return collect($templates);
    }

    /**
     * Copy global templates for an organizer
     */
    private function copyGlobalTemplates($globalTemplates, $organizerId)
    {
        $templates = [];

        foreach ($globalTemplates as $globalTemplate) {
            $templates[] = PwaEmailTemplate::create([
                'name' => $globalTemplate->name,
                'type' => $globalTemplate->type,
                'subject' => $globalTemplate->subject,
                'content' => $globalTemplate->content,
                'scope' => 'organizer',
                'user_id' => $organizerId,
                'is_active' => $globalTemplate->is_active,
                'created_by' => $organizerId
            ]);
        }

        return collect($templates);
    }

    /**
     * Get email statistics
     */
    private function getEmailStatistics($user)
    {
        $logQuery = PwaEmailLog::query();

        // Scope logs by organizer if not admin (via template's user_id)
        if (!$user->hasRole('Administrator')) {
            $logQuery->whereHas('template', function($q) use ($user) {
                $q->where('scope', 'organizer')->where('user_id', $user->id);
            });
        }

        $totals = $logQuery->selectRaw(
            "SUM(CASE WHEN action='sent' THEN quantity ELSE 0 END) as sent, " .
            "SUM(CASE WHEN action='open' THEN quantity ELSE 0 END) as opens, " .
            "SUM(CASE WHEN action='click' THEN quantity ELSE 0 END) as clicks, " .
            "SUM(CASE WHEN action='bounce' THEN quantity ELSE 0 END) as bounces"
        )->first();

        $sent = (int)($totals->sent ?? 0);
        $opens = (int)($totals->opens ?? 0);
        $clicks = (int)($totals->clicks ?? 0);
        $bounces = (int)($totals->bounces ?? 0);

        // Derived rates (avoid divide-by-zero)
        $openRate = $sent > 0 ? round(($opens / $sent) * 100, 1) : 0;
        $clickRate = $sent > 0 ? round(($clicks / $sent) * 100, 1) : 0;
        $bounceRate = $sent > 0 ? round(($bounces / $sent) * 100, 1) : 0;

        // Recent activity counts by type (template type buckets)
        $welcomeEmails = PwaEmailLog::where('action', 'sent')
            ->whereHas('template', function($q) use ($user) {
                $q->where('type', 'welcome');
                if (!$user->hasRole('Administrator')) {
                    $q->where('scope', 'organizer')->where('user_id', $user->id);
                }
            })->sum('quantity');

        $passwordResets = PwaEmailLog::where('action', 'sent')
            ->whereHas('template', function($q) use ($user) {
                $q->where('type', 'password_reset');
                if (!$user->hasRole('Administrator')) {
                    $q->where('scope', 'organizer')->where('user_id', $user->id);
                }
            })->sum('quantity');

        $failed = $bounces; // treat bounces as failed for now

        return [
            'total_sent' => $sent,
            'open_rate' => $openRate,
            'click_rate' => $clickRate,
            'bounce_rate' => $bounceRate,
            'welcome_emails' => (int)$welcomeEmails,
            'password_resets' => (int)$passwordResets,
            'failed_deliveries' => (int)$failed,
        ];
    }

    /**
     * Replace variables in content
     */
    private function replaceVariables($content, $data)
    {
        foreach ($data as $key => $value) {
            $content = str_replace('@{{' . $key . '}}', $value, $content);
        }
        return $content;
    }

    /**
     * Get default welcome email content
     */
    private function getDefaultWelcomeContent()
    {
        return '<p><strong>Dear @{{name}},</strong></p>
<p>Welcome to <strong>E-Certificate Online</strong>! Your account has been successfully created and you now have access to our mobile application.</p>

<div class="bg-gray-50 p-3 rounded my-4">
    <p class="text-sm font-medium mb-2">Your Login Credentials:</p>
    <p class="text-sm"><strong>Email:</strong> @{{email}}</p>
    <p class="text-sm"><strong>Temporary Password:</strong> @{{password}}</p>
</div>

<p><strong>Important:</strong> For security reasons, you will be required to change your password on your first login.</p>

<div class="bg-blue-50 p-3 rounded my-4">
    <p class="text-sm font-medium mb-2">Getting Started:</p>
    <ol class="text-sm list-decimal list-inside space-y-1">
        <li>Download our mobile app or visit: @{{pwa_link}}</li>
        <li>Login with your email and temporary password</li>
        <li>Change your password when prompted</li>
        <li>Start exploring your events and certificates!</li>
    </ol>
</div>

<p>If you have any questions or need assistance, please contact us at @{{support_email}}.</p>

<p>Best regards,<br>
<strong>E-Certificate Online Team</strong></p>';
    }

    /**
     * Get default password reset content
     */
    private function getDefaultPasswordResetContent()
    {
        return '<p><strong>Dear @{{name}},</strong></p>
<p>We received a request to reset your password for your E-Certificate Online account.</p>

<div class="bg-gray-50 p-3 rounded my-4">
    <p class="text-sm font-medium mb-2">Your New Password:</p>
    <p class="text-sm"><strong>@{{password}}</strong></p>
</div>

<p><strong>Important:</strong> For security reasons, you will be required to change this password on your next login.</p>

<p>If you did not request this password reset, please contact us immediately at @{{support_email}}.</p>

<p>Best regards,<br>
<strong>E-Certificate Online Team</strong></p>';
    }

    /**
     * Get default event reminder content
     */
    private function getDefaultEventReminderContent()
    {
        return '<p><strong>Dear @{{name}},</strong></p>
<p>This is a friendly reminder about your upcoming event.</p>

<div class="bg-blue-50 p-3 rounded my-4">
    <p class="text-sm font-medium mb-2">Event Details:</p>
    <p class="text-sm"><strong>Event:</strong> @{{event_name}}</p>
    <p class="text-sm"><strong>Organization:</strong> @{{organization}}</p>
</div>

<p>Please make sure to:</p>
<ul class="text-sm list-disc list-inside space-y-1">
    <li>Arrive on time for check-in</li>
    <li>Bring your mobile device for QR code scanning</li>
    <li>Have your login credentials ready</li>
</ul>

<p>If you have any questions, please contact us at @{{support_email}}.</p>

<p>Best regards,<br>
<strong>E-Certificate Online Team</strong></p>';
    }
} 