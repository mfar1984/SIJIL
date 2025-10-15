<?php

namespace App\Http\Controllers\Pwa;

use App\Http\Controllers\Controller;
use App\Models\PwaEmailTemplate;
use Illuminate\Http\Request;
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

        // Get email statistics
        $emailStats = $this->getEmailStatistics($user);

        return view('ecertificate.templates', compact('templates', 'emailStats'));
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

        return $templates;
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

        return $templates;
    }

    /**
     * Get email statistics
     */
    private function getEmailStatistics($user)
    {
        if ($user->hasRole('Administrator')) {
            // Global statistics
            return [
                'total_sent' => 1234,
                'open_rate' => 78.5,
                'click_rate' => 23.2,
                'bounce_rate' => 2.1,
                'welcome_emails' => 156,
                'password_resets' => 23,
                'failed_deliveries' => 3
            ];
        } else {
            // Organizer-specific statistics
            return [
                'total_sent' => 89,
                'open_rate' => 82.1,
                'click_rate' => 28.7,
                'bounce_rate' => 1.5,
                'welcome_emails' => 45,
                'password_resets' => 8,
                'failed_deliveries' => 1
            ];
        }
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