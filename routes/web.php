<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\RoleManagementController;
use App\Http\Controllers\EventManagementController;
use App\Http\Controllers\ParticipantsController;
use App\Http\Controllers\AttendanceController;
use App\Http\Middleware\CheckPermission as PermissionMiddleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

Route::get('/', function () {
    return view('auth.login');
});

// Test route for debugging permissions
Route::get('/test-permission', function () {
    $user = auth()->user();
    return response()->json([
        'user' => $user ? $user->name : 'Not authenticated',
        'has_view_roles' => $user ? $user->hasPermissionTo('view_roles') : false,
        'has_role_administrator' => $user ? $user->hasRole('Administrator') : false,
        'permissions_count' => $user ? $user->getAllPermissions()->count() : 0,
        'all_permissions' => $user ? $user->getAllPermissions()->pluck('name')->toArray() : [],
    ]);
})->middleware(['auth', 'verified'])->name('test.permission');

// Test route for role management without permission middleware
Route::get('/test-role-management', function () {
    return view('settings.role-index', [
        'roles' => \App\Models\Role::with('permissions')->paginate(10)
    ]);
})->middleware(['auth', 'verified'])->name('test.role.management');

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Role Management Routes with permission middleware
Route::get('/role-management', [App\Http\Controllers\RoleManagementController::class, 'index'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':view_roles'])
    ->name('role.management');

Route::get('/role-management/create', [App\Http\Controllers\RoleManagementController::class, 'create'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':create_roles'])
    ->name('role.create');

Route::post('/role-management', [App\Http\Controllers\RoleManagementController::class, 'store'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':create_roles'])
    ->name('role.store');

Route::get('/role-management/{role}/edit', [App\Http\Controllers\RoleManagementController::class, 'edit'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':edit_roles'])
    ->name('role.edit');

Route::put('/role-management/{role}', [App\Http\Controllers\RoleManagementController::class, 'update'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':edit_roles'])
    ->name('role.update');

Route::get('/role-management/{role}', [App\Http\Controllers\RoleManagementController::class, 'show'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':view_roles'])
    ->name('role.show');

Route::delete('/role-management/{role}', [App\Http\Controllers\RoleManagementController::class, 'destroy'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':delete_roles'])
    ->name('role.destroy');

// Role Check Route
Route::get('/role-check', [App\Http\Controllers\RoleCheckController::class, 'index'])->middleware(['auth', 'verified'])->name('role.check');

// User Management Routes
Route::get('/user-management', [App\Http\Controllers\UserManagementController::class, 'index'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':view_users'])
    ->name('user.management');

Route::get('/user-management/create', [App\Http\Controllers\UserManagementController::class, 'create'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':create_users'])
    ->name('user.create');

Route::post('/user-management', [App\Http\Controllers\UserManagementController::class, 'store'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':create_users'])
    ->name('user.store');

Route::get('/user-management/{user}/edit', [App\Http\Controllers\UserManagementController::class, 'edit'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':edit_users'])
    ->name('user.edit');

Route::put('/user-management/{user}', [App\Http\Controllers\UserManagementController::class, 'update'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':edit_users'])
    ->name('user.update');

Route::get('/user-management/{user}', [App\Http\Controllers\UserManagementController::class, 'show'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':view_users'])
    ->name('user.show');

Route::delete('/user-management/{user}', [App\Http\Controllers\UserManagementController::class, 'destroy'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':delete_users'])
    ->name('user.destroy');

Route::patch('/user-management/{user}/status/{status}', [App\Http\Controllers\UserManagementController::class, 'toggleStatus'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':edit_users'])
    ->name('user.status');

// Event Management Routes
Route::get('/event-management', [App\Http\Controllers\EventManagementController::class, 'index'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':view_events'])
    ->name('event.management');

Route::get('/event-management/create', [App\Http\Controllers\EventManagementController::class, 'create'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':create_events'])
    ->name('event.create');

Route::post('/event-management', [App\Http\Controllers\EventManagementController::class, 'store'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':create_events'])
    ->name('event.store');

Route::get('/event-management/{event}/edit', [App\Http\Controllers\EventManagementController::class, 'edit'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':edit_events'])
    ->name('event.edit');

Route::put('/event-management/{event}', [App\Http\Controllers\EventManagementController::class, 'update'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':edit_events'])
    ->name('event.update');

Route::get('/event-management/{event}', [App\Http\Controllers\EventManagementController::class, 'show'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':view_events'])
    ->name('event.show');

Route::delete('/event-management/{event}', [App\Http\Controllers\EventManagementController::class, 'destroy'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':delete_events'])
    ->name('event.destroy');

// QR Code and Registration Routes
Route::get('/event-management/{event}/qrcode', [App\Http\Controllers\EventManagementController::class, 'generateQrCode'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':view_events'])
    ->name('event.qrcode');

// Download QR code image only (PNG)
Route::get('/event-management/{event}/qrcode-image', [App\Http\Controllers\EventManagementController::class, 'downloadQrCodeImage'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':view_events'])
    ->name('event.qrcode-image');

// Public route for registration (no auth required)
Route::get('/event/register/{token}', [App\Http\Controllers\EventManagementController::class, 'register'])
    ->name('event.register');

Route::post('/event/register/{token}', [App\Http\Controllers\EventManagementController::class, 'registerSubmit'])
    ->name('event.register.submit');

// Participants
Route::get('/participants', [App\Http\Controllers\ParticipantsController::class, 'index'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':view_participants'])
    ->name('participants');
    
Route::get('/participants/create', [App\Http\Controllers\ParticipantsController::class, 'create'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':create_participants'])
    ->name('participants.create');

Route::post('/participants', [App\Http\Controllers\ParticipantsController::class, 'store'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':create_participants'])
    ->name('participants.store');

Route::get('/participants/{participant}/edit', [App\Http\Controllers\ParticipantsController::class, 'edit'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':edit_participants'])
    ->name('participants.edit');

Route::put('/participants/{participant}', [App\Http\Controllers\ParticipantsController::class, 'update'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':edit_participants'])
    ->name('participants.update');

Route::get('/participants/{participant}', [App\Http\Controllers\ParticipantsController::class, 'show'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':view_participants'])
    ->name('participants.show');

Route::delete('/participants/{participant}', [App\Http\Controllers\ParticipantsController::class, 'destroy'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':delete_participants'])
    ->name('participants.destroy');

// Attendance Management
Route::get('/attendance', [AttendanceController::class, 'index'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':manage_attendance'])
    ->name('attendance.index');
Route::get('/attendance/create', [AttendanceController::class, 'create'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':manage_attendance'])
    ->name('attendance.create');
Route::post('/attendance', [AttendanceController::class, 'store'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':manage_attendance'])
    ->name('attendance.store');

// Move archive route here before the dynamic {attendance} route
Route::get('/attendance/archive', [AttendanceController::class, 'archive'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':view_archives'])
    ->name('attendance.archive');

Route::post('/attendance/{attendance}/archive', [AttendanceController::class, 'archiveAction'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':manage_attendance'])
    ->name('attendance.archive-action');

Route::post('/attendance/{attendance}/unarchive', [AttendanceController::class, 'unarchiveAction'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':manage_attendance'])
    ->name('attendance.unarchive-action');

Route::get('/attendance/{attendance}', [AttendanceController::class, 'show'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':manage_attendance'])
    ->name('attendance.show');
Route::get('/attendance/{attendance}/edit', [AttendanceController::class, 'edit'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':manage_attendance'])
    ->name('attendance.edit');
Route::put('/attendance/{attendance}', [AttendanceController::class, 'update'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':manage_attendance'])
    ->name('attendance.update');
Route::delete('/attendance/{attendance}', [AttendanceController::class, 'destroy'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':manage_attendance'])
    ->name('attendance.destroy');
Route::get('/attendance/{attendance}/qrcode', [AttendanceController::class, 'qrcode'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':manage_attendance'])
    ->name('attendance.qrcode');

Route::get('/attendance-list', [AttendanceController::class, 'list'])
    ->middleware(['auth', 'verified', PermissionMiddleware::class.':manage_attendance'])
    ->name('attendance.list');

// API endpoints for dynamic attendance list
Route::get('/api/attendance-sessions', [AttendanceController::class, 'apiSessions'])->name('api.attendance.sessions');
Route::get('/api/attendance-participants', [AttendanceController::class, 'apiParticipants'])->name('api.attendance.participants');

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Template Designer Routes
    Route::get('/template-designer', [App\Http\Controllers\TemplateDesignerController::class, 'index'])->name('template.designer');
    Route::get('/template-designer/create', [App\Http\Controllers\TemplateDesignerController::class, 'create'])->name('template.create');
    Route::post('/template-designer', [App\Http\Controllers\TemplateDesignerController::class, 'store'])->name('template.store');
    Route::get('/template-designer/{id}/edit', [App\Http\Controllers\TemplateDesignerController::class, 'edit'])->name('template.edit');
    Route::put('/template-designer/{id}', [App\Http\Controllers\TemplateDesignerController::class, 'update'])->name('template.update');
    Route::delete('/template-designer/{id}', [App\Http\Controllers\TemplateDesignerController::class, 'destroy'])->name('template.destroy');
    Route::get('/template-designer/designer/{id?}', [App\Http\Controllers\TemplateDesignerController::class, 'designer'])->name('template.designer.create');
    Route::post('/template-designer/{id}/duplicate', [App\Http\Controllers\TemplateDesignerController::class, 'duplicate'])->name('template.duplicate');
    Route::post('/template-designer/upload-background', [App\Http\Controllers\TemplateDesignerController::class, 'uploadBackground'])->name('template.upload-background');
    Route::get('/template-designer/{id}/show', [App\Http\Controllers\TemplateDesignerController::class, 'show'])->name('template.show');
});

// Certificate Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/certificates', [App\Http\Controllers\CertificateController::class, 'index'])->name('certificates.index');
    Route::get('/certificates/create', [App\Http\Controllers\CertificateController::class, 'create'])->name('certificates.create');
    Route::post('/certificates', [App\Http\Controllers\CertificateController::class, 'store'])->name('certificates.store');
    Route::post('/certificates/preview', [App\Http\Controllers\CertificateController::class, 'preview'])->name('certificates.preview');
    Route::get('/api/certificates/participants', [App\Http\Controllers\CertificateController::class, 'getParticipants'])->name('api.certificates.participants');
    Route::delete('/certificates/{certificate}', [App\Http\Controllers\CertificateController::class, 'destroy'])
        ->middleware(['auth', 'verified'])
        ->name('certificates.destroy');
});

require __DIR__.'/auth.php';

// Debug route
Route::get('/debug-alpine', function() {
    return view('templates.debug-alpine');
})->name('debug.alpine');

Route::get('/debug-template', function() {
    return view('debug-template');
})->name('debug.template');

// Reports Routes
Route::prefix('reports')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [App\Http\Controllers\ReportsController::class, 'attendanceIndex'])
        ->name('reports.attendance.index');
    
    Route::get('/attendance/{id}', [App\Http\Controllers\ReportsController::class, 'attendanceShow'])
        ->name('reports.attendance.show');
    
    Route::delete('/attendance/{id}', [App\Http\Controllers\ReportsController::class, 'attendanceDelete'])
        ->name('reports.attendance.delete');
    
    Route::post('/attendance/export', [App\Http\Controllers\ReportsController::class, 'attendanceExport'])
        ->name('reports.attendance.export');
    
    Route::get('/statistics', [App\Http\Controllers\ReportsStatisticsController::class, 'index'])
        ->name('reports.statistics');
    
    Route::get('/certificates', [App\Http\Controllers\ReportsCertificateController::class, 'index'])
        ->name('reports.certificates');
    
    Route::get('/certificates/{id}', [App\Http\Controllers\ReportsCertificateController::class, 'show'])
        ->name('reports.certificates.show');
    
    Route::get('/certificates/{id}/download', [App\Http\Controllers\ReportsCertificateController::class, 'download'])
        ->name('reports.certificates.download');
    
    Route::delete('/certificates/{id}', [App\Http\Controllers\ReportsCertificateController::class, 'destroy'])
        ->name('reports.certificates.delete');
});

Route::post('/reports/certificates/{id}/send-email', [App\Http\Controllers\ReportsCertificateController::class, 'sendEmail'])->name('reports.certificates.sendEmail');

// Campaign Routes
Route::prefix('campaign')->group(function () {
    Route::get('/', [App\Http\Controllers\CampaignController::class, 'index'])
        ->middleware(['auth', 'verified', PermissionMiddleware::class.':view_campaigns'])
        ->name('campaign.index');
    
    Route::get('/create', [App\Http\Controllers\CampaignController::class, 'create'])
        ->middleware(['auth', 'verified', PermissionMiddleware::class.':create_campaigns'])
        ->name('campaign.create');
    
    Route::post('/', [App\Http\Controllers\CampaignController::class, 'store'])
        ->middleware(['auth', 'verified', PermissionMiddleware::class.':create_campaigns'])
        ->name('campaign.store');
    
    Route::get('/{campaign}', [App\Http\Controllers\CampaignController::class, 'show'])
        ->middleware(['auth', 'verified', PermissionMiddleware::class.':view_campaigns'])
        ->name('campaign.show');
    
    Route::get('/{campaign}/edit', [App\Http\Controllers\CampaignController::class, 'edit'])
        ->middleware(['auth', 'verified', PermissionMiddleware::class.':edit_campaigns'])
        ->name('campaign.edit');
    
    Route::put('/{campaign}', [App\Http\Controllers\CampaignController::class, 'update'])
        ->middleware(['auth', 'verified', PermissionMiddleware::class.':edit_campaigns'])
        ->name('campaign.update');
    
    Route::delete('/{campaign}', [App\Http\Controllers\CampaignController::class, 'destroy'])
        ->middleware(['auth', 'verified', PermissionMiddleware::class.':delete_campaigns'])
        ->name('campaign.destroy');
    
    Route::post('/{campaign}/process', [App\Http\Controllers\CampaignController::class, 'process'])
        ->middleware(['auth', 'verified', PermissionMiddleware::class.':edit_campaigns'])
        ->name('campaign.process');
});

// Campaign tracking routes (no authentication required)
Route::get('/track/open/{campaign}/{recipient}', [App\Http\Controllers\CampaignController::class, 'trackOpen'])
    ->name('track.open');
Route::get('/track/click/{campaign}/{recipient}/{url}', [App\Http\Controllers\CampaignController::class, 'trackClick'])
    ->name('track.click');

// Config Routes
Route::prefix('config')->group(function () {
    Route::get('/deliver', [App\Http\Controllers\DeliveryConfigController::class, 'index'])
        ->middleware(['auth', 'verified', PermissionMiddleware::class.':manage_delivery'])
        ->name('config.deliver');
        
    Route::post('/deliver/email', [App\Http\Controllers\DeliveryConfigController::class, 'saveEmailConfig'])
        ->middleware(['auth', 'verified', PermissionMiddleware::class.':manage_delivery'])
        ->name('config.deliver.email');
        
    Route::post('/deliver/sms', [App\Http\Controllers\DeliveryConfigController::class, 'saveSmsConfig'])
        ->middleware(['auth', 'verified', PermissionMiddleware::class.':manage_delivery'])
        ->name('config.deliver.sms');
        
    Route::post('/deliver/test-email', [App\Http\Controllers\DeliveryConfigController::class, 'sendTestEmail'])
        ->middleware(['auth', 'verified', PermissionMiddleware::class.':manage_delivery'])
        ->name('config.deliver.test-email');
        
    Route::post('/deliver/test-email-to-address', [App\Http\Controllers\DeliveryConfigController::class, 'sendTestEmailToAddress'])
        ->middleware(['auth', 'verified', PermissionMiddleware::class.':manage_delivery'])
        ->name('config.deliver.test-email-to-address');
        
    Route::post('/deliver/test-sms', [App\Http\Controllers\DeliveryConfigController::class, 'sendTestSms'])
        ->middleware(['auth', 'verified', PermissionMiddleware::class.':manage_delivery'])
        ->name('config.deliver.test-sms');
});

// Helpdesk Routes
Route::middleware(['auth'])->prefix('helpdesk')->name('helpdesk.')->group(function () {
    Route::get('/', [App\Http\Controllers\HelpdeskController::class, 'index'])->name('index');
    Route::post('/', [App\Http\Controllers\HelpdeskController::class, 'store'])->name('store');
    Route::get('{id}', [App\Http\Controllers\HelpdeskController::class, 'show'])->name('show');
    Route::post('{id}/reply', [App\Http\Controllers\HelpdeskController::class, 'reply'])->name('reply');
    Route::post('{id}/status', [App\Http\Controllers\HelpdeskController::class, 'updateStatus'])->name('status');
    Route::get('message/{messageId}/attachment/{attachmentIndex}', [App\Http\Controllers\HelpdeskController::class, 'downloadAttachment'])->name('attachment');
    
    // Notification Routes
    Route::get('/notifications', [App\Http\Controllers\HelpdeskController::class, 'getNotifications'])->name('notifications');
    Route::post('/notifications/mark-read', [App\Http\Controllers\HelpdeskController::class, 'markNotificationsAsRead'])->name('notifications.mark-read');
});

// Settings Routes
Route::prefix('settings')->group(function () {
               Route::get('/log-activity', [App\Http\Controllers\Settings\LogActivityController::class, 'index'])
               ->middleware(['auth', 'verified', PermissionMiddleware::class.':view_settings'])
               ->name('settings.log-activity');
           
           Route::get('/log-activity/{activity}/details', [App\Http\Controllers\Settings\LogActivityController::class, 'showDetails'])
               ->middleware(['auth', 'verified', PermissionMiddleware::class.':view_settings'])
               ->name('settings.log-activity.details');
           
           Route::delete('/log-activity/clear', [App\Http\Controllers\Settings\LogActivityController::class, 'clearLogs'])
               ->middleware(['auth', 'verified', PermissionMiddleware::class.':manage_settings'])
               ->name('settings.log-activity.clear');
    
               Route::get('/security-audit', [App\Http\Controllers\Settings\SecurityAuditController::class, 'index'])
               ->middleware(['auth', 'verified', PermissionMiddleware::class.':view_settings'])
               ->name('settings.security-audit');
           
           Route::get('/security-audit/{activity}/details', [App\Http\Controllers\Settings\SecurityAuditController::class, 'showDetails'])
               ->middleware(['auth', 'verified', PermissionMiddleware::class.':view_settings'])
               ->name('settings.security-audit.details');
           
           Route::delete('/security-audit/clear', [App\Http\Controllers\Settings\SecurityAuditController::class, 'clearSecurityLogs'])
               ->middleware(['auth', 'verified', PermissionMiddleware::class.':manage_settings'])
               ->name('settings.security-audit.clear');
    
    Route::get('/global-config', [App\Http\Controllers\GlobalConfigController::class, 'index'])
        ->middleware(['auth', 'verified', PermissionMiddleware::class.':manage_settings'])
        ->name('settings.global-config');
        
    Route::post('/global-config', [App\Http\Controllers\GlobalConfigController::class, 'update'])
        ->middleware(['auth', 'verified', PermissionMiddleware::class.':manage_settings'])
        ->name('settings.global-config.update');
        
    Route::post('/global-config/regenerate-webhook-secret', [App\Http\Controllers\GlobalConfigController::class, 'regenerateWebhookSecret'])
        ->middleware(['auth', 'verified', PermissionMiddleware::class.':manage_settings'])
        ->name('settings.global-config.regenerate-webhook');
        
    Route::post('/global-config/reset', [App\Http\Controllers\GlobalConfigController::class, 'reset'])
        ->middleware(['auth', 'verified', PermissionMiddleware::class.':manage_settings'])
        ->name('settings.global-config.reset');
        
    Route::get('/global-config/api', [App\Http\Controllers\GlobalConfigController::class, 'getConfig'])
        ->middleware(['auth', 'verified', PermissionMiddleware::class.':manage_settings'])
        ->name('settings.global-config.api');
});

Route::get('/reports/attendance', [App\Http\Controllers\ReportsController::class, 'attendanceIndex'])
    ->middleware(['auth', 'verified'])
    ->name('reports.attendance.index');
Route::get('/reports/attendance/{id}', [App\Http\Controllers\ReportsController::class, 'attendanceShow'])
    ->middleware(['auth', 'verified'])
    ->name('reports.attendance.show');
Route::post('/reports/attendance/export', [App\Http\Controllers\ReportsController::class, 'attendanceExport'])
    ->middleware(['auth', 'verified'])
    ->name('reports.attendance.export');
Route::delete('/reports/attendance/{id}', [App\Http\Controllers\ReportsController::class, 'attendanceDelete'])
    ->middleware(['auth', 'verified'])
    ->name('reports.attendance.delete');

// Survey Routes - Admin
Route::middleware(['auth'])->prefix('survey')->name('survey.')->group(function () {
    Route::get('/', [App\Http\Controllers\SurveyController::class, 'index'])
        ->middleware(PermissionMiddleware::class.':view_surveys')
        ->name('index');
        
    Route::get('/create', [App\Http\Controllers\SurveyController::class, 'create'])
        ->middleware(PermissionMiddleware::class.':create_surveys')
        ->name('create');
        
    Route::post('/', [App\Http\Controllers\SurveyController::class, 'store'])
        ->middleware(PermissionMiddleware::class.':create_surveys')
        ->name('store');
        
    Route::get('/{survey}', [App\Http\Controllers\SurveyController::class, 'show'])
        ->middleware(PermissionMiddleware::class.':view_surveys')
        ->name('show');
        
    Route::get('/{survey}/edit', [App\Http\Controllers\SurveyController::class, 'edit'])
        ->middleware(PermissionMiddleware::class.':edit_surveys')
        ->name('edit');
        
    Route::put('/{survey}', [App\Http\Controllers\SurveyController::class, 'update'])
        ->middleware(PermissionMiddleware::class.':edit_surveys')
        ->name('update');
        
    Route::delete('/{survey}', [App\Http\Controllers\SurveyController::class, 'destroy'])
        ->middleware(PermissionMiddleware::class.':delete_surveys')
        ->name('destroy');
        
    Route::post('/{survey}/publish', [App\Http\Controllers\SurveyController::class, 'togglePublish'])
        ->middleware(PermissionMiddleware::class.':edit_surveys')
        ->name('toggle-publish');
    
    // Question management
    Route::post('/{survey}/questions', [App\Http\Controllers\SurveyController::class, 'storeQuestion'])
        ->middleware(PermissionMiddleware::class.':manage_survey_questions')
        ->name('questions.store');
        
    Route::put('/{survey}/questions/order', [App\Http\Controllers\SurveyController::class, 'updateQuestionOrder'])
        ->middleware(PermissionMiddleware::class.':manage_survey_questions')
        ->name('questions.order');
        
    Route::put('/{survey}/questions/{question}', [App\Http\Controllers\SurveyController::class, 'updateQuestion'])
        ->middleware(PermissionMiddleware::class.':manage_survey_questions')
        ->name('questions.update');
        
    Route::delete('/{survey}/questions/{question}', [App\Http\Controllers\SurveyController::class, 'destroyQuestion'])
        ->middleware(PermissionMiddleware::class.':manage_survey_questions')
        ->name('questions.destroy');
    
    // Responses and analytics
    Route::get('/{survey}/responses', [App\Http\Controllers\SurveyController::class, 'showResponses'])
        ->middleware(PermissionMiddleware::class.':view_survey_responses')
        ->name('responses');
        
    // Export responses route (harus sebelum route dengan parameter)
    Route::get('/{survey}/responses/export', [App\Http\Controllers\SurveyController::class, 'exportResponses'])
        ->middleware(PermissionMiddleware::class.':export_survey_responses')
        ->name('responses.export');
        
    // Route for AJAX view response detail
    Route::get('/{survey}/responses/{response}', [App\Http\Controllers\SurveyController::class, 'viewResponse'])
        ->middleware(PermissionMiddleware::class.':view_survey_responses')
        ->name('responses.view');
        
    Route::delete('/{survey}/responses/{response}', [App\Http\Controllers\SurveyController::class, 'destroyResponse'])
        ->middleware(PermissionMiddleware::class.':view_survey_responses')
        ->name('responses.destroy');
        
    Route::get('/{survey}/analytics', [App\Http\Controllers\SurveyController::class, 'showAnalytics'])
        ->middleware(PermissionMiddleware::class.':view_survey_responses')
        ->name('analytics');
});

// Public Survey Routes
Route::prefix('s')->name('public.survey.')->group(function () {
    Route::get('/{slug}', [App\Http\Controllers\PublicSurveyController::class, 'show'])->name('show');
    Route::post('/{slug}/submit', [App\Http\Controllers\PublicSurveyController::class, 'submit'])->name('submit');
    Route::get('/{slug}/thankyou', [App\Http\Controllers\PublicSurveyController::class, 'thankYou'])->name('thankyou');
    Route::get('/{slug}/expired', [App\Http\Controllers\PublicSurveyController::class, 'expired'])->name('expired');
});

// PWA Management Routes (Multi-Tenant)
Route::middleware(['auth', 'verified'])->prefix('pwa')->name('pwa.')->group(function () {
    Route::get('/settings', [App\Http\Controllers\Pwa\PwaSettingsController::class, 'index'])
        ->middleware(PermissionMiddleware::class.':manage_ecertificate_settings')
        ->name('settings');
    
    Route::post('/settings', [App\Http\Controllers\Pwa\PwaSettingsController::class, 'update'])
        ->middleware(PermissionMiddleware::class.':manage_ecertificate_settings')
        ->name('settings.update');
    
    Route::get('/participants', [App\Http\Controllers\Pwa\PwaParticipantsController::class, 'index'])
        ->middleware(PermissionMiddleware::class.':view_ecertificate_participants')
        ->name('participants');
    
    Route::get('/participants/create', [App\Http\Controllers\Pwa\PwaParticipantsController::class, 'create'])
        ->middleware(PermissionMiddleware::class.':create_ecertificate_participants')
        ->name('participants.create');
    
    Route::post('/participants', [App\Http\Controllers\Pwa\PwaParticipantsController::class, 'store'])
        ->middleware(PermissionMiddleware::class.':create_ecertificate_participants')
        ->name('participants.store');
    
    Route::get('/participants/{participant}/edit', [App\Http\Controllers\Pwa\PwaParticipantsController::class, 'edit'])
        ->middleware(PermissionMiddleware::class.':edit_ecertificate_participants')
        ->name('participants.edit');
    
    Route::put('/participants/{participant}', [App\Http\Controllers\Pwa\PwaParticipantsController::class, 'update'])
        ->middleware(PermissionMiddleware::class.':edit_ecertificate_participants')
        ->name('participants.update');
    
    Route::delete('/participants/{participant}', [App\Http\Controllers\Pwa\PwaParticipantsController::class, 'destroy'])
        ->middleware(PermissionMiddleware::class.':delete_ecertificate_participants')
        ->name('participants.destroy');
    
    Route::get('/participants/{participant}', [App\Http\Controllers\Pwa\PwaParticipantsController::class, 'show'])
        ->middleware(PermissionMiddleware::class.':view_ecertificate_participants')
        ->name('participants.show');
    
    Route::post('/participants/{participant}/reset-password', [App\Http\Controllers\Pwa\PwaParticipantsController::class, 'resetPassword'])
        ->middleware(PermissionMiddleware::class.':edit_ecertificate_participants')
        ->name('participants.reset-password');
    
    Route::get('/templates', [App\Http\Controllers\Pwa\PwaTemplatesController::class, 'index'])
        ->middleware(PermissionMiddleware::class.':manage_ecertificate_templates')
        ->name('templates');
    
    Route::get('/templates/{template}/edit', [App\Http\Controllers\Pwa\PwaTemplatesController::class, 'edit'])
        ->middleware(PermissionMiddleware::class.':manage_ecertificate_templates')
        ->name('templates.edit');
    
    Route::put('/templates/{template}', [App\Http\Controllers\Pwa\PwaTemplatesController::class, 'update'])
        ->middleware(PermissionMiddleware::class.':manage_ecertificate_templates')
        ->name('templates.update');
    
    Route::post('/templates/{template}/preview', [App\Http\Controllers\Pwa\PwaTemplatesController::class, 'preview'])
        ->middleware(PermissionMiddleware::class.':manage_ecertificate_templates')
        ->name('templates.preview');
    
    Route::get('/analytics', [App\Http\Controllers\Pwa\PwaAnalyticsController::class, 'index'])
        ->middleware(PermissionMiddleware::class.':view_ecertificate_analytics')
        ->name('analytics');
    
    Route::get('/analytics/export', [App\Http\Controllers\Pwa\PwaAnalyticsController::class, 'export'])
        ->middleware(PermissionMiddleware::class.':export_ecertificate_analytics')
        ->name('analytics.export');
});
