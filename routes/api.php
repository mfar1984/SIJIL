<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PwaParticipantController;
use App\Http\Controllers\Api\PwaHelpdeskController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\CertificateVerificationController;

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

/*
| PWA Participant Routes (Public)
|
| Only these two are open, and both are rate limited. Three others used to sit
| here and were removed because each handed out personal data to anyone who asked:
|
|   POST /participant/register  - created an account for any email that did not yet
|       have one and returned a working token immediately. Since events and
|       certificates are matched by email address, claiming an unclaimed
|       participant's email was enough to read their certificates. Accounts are
|       created by the organizer under Configuration > PWA Participants, which is
|       the intended route, and the app never called this endpoint.
|
|   GET /participant/lookup     - returned a participant's full profile (name,
|       email, phone, IC, passport, address, date of birth, gender, race, job
|       title) for any IC or passport number, unauthenticated. Malaysian IC
|       numbers are structured and therefore guessable, so this was enumerable.
|       The app never called it either.
|
|   GET /debug/certificates/{email} - dumped participants and their certificates
|       for any address. Its own comment said "remove after testing".
*/
Route::middleware('throttle:8,1')->group(function () {
    Route::post('/participant/login', [PwaParticipantController::class, 'login']);
    Route::post('/participant/reset-password', [PwaParticipantController::class, 'resetPassword']);
});

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

    /*
    | Support tickets raised from the app. These are assigned to the Administrator on
    | creation; an organizer never sees them, because the backend list scopes
    | non-administrators to their own user_id and an app ticket has none.
    */
    Route::get('/participant/tickets', [PwaHelpdeskController::class, 'index']);
    Route::post('/participant/tickets', [PwaHelpdeskController::class, 'store']);
    Route::get('/participant/tickets/{ticketId}', [PwaHelpdeskController::class, 'show']);
    Route::post('/participant/tickets/{ticketId}/reply', [PwaHelpdeskController::class, 'reply']);
});

/*
| The participant search used by the PWA account form lives in routes/web.php.
|
| It was here, unauthenticated. Routes in this file are stateless, so the
| Auth::user() call the controller used for its multi-tenant filter was always
| null and the filter never ran - it looked like tenant isolation while doing
| nothing. The endpoint returned whole Participant models, so `?search=a` handed
| fifty complete records, IC numbers and addresses included, to anyone.
|
| Moving it to the web routes puts it behind the session it always assumed it had.
| The URL is unchanged, so the form needs no edit.
*/

// Certificate verification (public by design - anyone holding a certificate
// number may confirm it is genuine).
Route::post('/certificate/verify', [CertificateVerificationController::class, 'verify']);