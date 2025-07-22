<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\RoleManagementController;
use App\Http\Controllers\EventManagementController;
use App\Http\Controllers\ParticipantsController;
use App\Http\Controllers\AttendanceController;
use App\Http\Middleware\CheckPermission as PermissionMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
});

require __DIR__.'/auth.php';
