<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PwaParticipantController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\ParticipantSearchController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Legal Content API (Public - No Auth Required)
Route::get('/legal/disclaimer', function() {
    $content = view('legal.disclaimer')->render();
    // Extract content from HTML
    preg_match('/<div class="prose.*?>(.*?)<\/div>\s*<\/div>\s*<!-- Back Button -->/s', $content, $matches);
    return response()->json([
        'success' => true,
        'title' => 'Disclaimer',
        'html' => $matches[1] ?? strip_tags($content)
    ]);
});

Route::get('/legal/privacy', function() {
    $content = view('legal.privacy')->render();
    preg_match('/<div class="prose.*?>(.*?)<\/div>\s*<\/div>\s*<!-- Back Button -->/s', $content, $matches);
    return response()->json([
        'success' => true,
        'title' => 'Privacy Policy',
        'html' => $matches[1] ?? strip_tags($content)
    ]);
});

Route::get('/legal/terms', function() {
    $content = view('legal.terms')->render();
    preg_match('/<div class="prose.*?>(.*?)<\/div>\s*<\/div>\s*<!-- Back Button -->/s', $content, $matches);
    return response()->json([
        'success' => true,
        'title' => 'Terms & Conditions',
        'html' => $matches[1] ?? strip_tags($content)
    ]);
});

// PWA Participant Routes (Public)
Route::post('/participant/login', [PwaParticipantController::class, 'login']);
Route::post('/participant/register', [PwaParticipantController::class, 'register']);
Route::get('/participant/lookup', [PwaParticipantController::class, 'lookupByIdentity']);
Route::post('/participant/reset-password', [PwaParticipantController::class, 'resetPassword']);

// PWA Participant Routes (Protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/participant/profile', [PwaParticipantController::class, 'profile']);
    Route::put('/participant/profile', [PwaParticipantController::class, 'updateProfile']);
    Route::post('/participant/change-password', [PwaParticipantController::class, 'changePassword']);
    Route::get('/participant/events', [PwaParticipantController::class, 'getEvents']);
    Route::get('/participant/certificates', [PwaParticipantController::class, 'getCertificates']);
    Route::get('/participant/certificates/{certificateId}/download', [PwaParticipantController::class, 'downloadCertificate']);
    Route::get('/participant/attendance-history', [PwaParticipantController::class, 'getAttendanceHistory']);
    Route::post('/participant/checkin', [PwaParticipantController::class, 'checkIn']);
    Route::post('/participant/logout', [PwaParticipantController::class, 'logout']);

    // Event details for PWA drawer
    Route::get('/events/{eventId}', [EventController::class, 'show']);
    
    // Attendance scan (QR code check-in/out)
    Route::post('/attendance/scan', [PwaParticipantController::class, 'scanAttendance']);
});

// Regular Participants Search API (for PWA auto-assign functionality)
Route::get('/participants/search', [ParticipantSearchController::class, 'search']); 