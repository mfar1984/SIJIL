<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PwaParticipantController;
use App\Http\Controllers\Api\PwaHelpdeskController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\CertificateVerificationController;
use App\Http\Controllers\Api\PublicCertificateVerificationController;
use App\Http\Controllers\Api\EventRegistrationGateController;
use App\Http\Controllers\Api\IntegrationController;

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

/*
| Legal Content API (public, no auth required).
|
| Rate limited because each call renders a Blade view and runs a regex over the
| result. They were previously public with no limit of any kind, so they were the
| cheapest way to make this server do real work on demand.
*/
Route::middleware('throttle:api-public')->group(function () {

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

});

/*
| PWA Participant Routes (Public)
|
| Everything open here is rate limited. Three endpoints that once sat in this
| group were removed for handing personal data to anyone who asked. Two have
| since been rebuilt for the registration form, in a shape that does not repeat
| the mistake; see the group further down and the controller it points at.
|
|   POST /participant/register  - created an account for any email that did not yet
|       have one and returned a working token immediately. Since events and
|       certificates are matched by email address, claiming an unclaimed
|       participant's email was enough to read their certificates.
|       REBUILT: now requires an open event's registration token, refuses an email
|       that already has an account, and issues no token at all.
|
|   GET /participant/lookup     - returned a participant's full profile (name,
|       email, phone, IC, passport, address, date of birth, gender, race, job
|       title) for any IC or passport number, unauthenticated. Malaysian IC
|       numbers are structured and therefore guessable, so this was enumerable.
|       REBUILT as POST: answers only whether an account exists, returns masked
|       addresses, and releases nothing personal until a password is checked.
|
|   GET /debug/certificates/{email} - dumped participants and their certificates
|       for any address. Its own comment said "remove after testing". Not
|       reinstated, and should not be.
*/
Route::middleware('throttle:8,1')->group(function () {
    Route::post('/participant/login', [PwaParticipantController::class, 'login']);
    Route::post('/participant/reset-password', [PwaParticipantController::class, 'resetPassword']);
});

/*
| Identity check in front of the public registration form.
|
| These replace the two endpoints described above and are built so that the
| problems that got those removed cannot recur. Each one requires the
| registration token of an open event, so they are only reachable as part of a
| real registration rather than as a general lookup service. The lookup answers
| only whether an account exists and returns masked addresses; nothing personal
| is released until a password has been checked, and no API token is ever issued.
|
| Rate limited harder than sign-in because guessing document numbers is the abuse
| worth preventing: three attempts a minute is ample for someone typing their own
| IC and correcting a typo.
|
| See App\Http\Controllers\Api\EventRegistrationGateController.
*/
Route::middleware('throttle:3,1')->group(function () {
    Route::post('/participant/lookup', [EventRegistrationGateController::class, 'lookup']);
});

Route::middleware('throttle:6,1')->group(function () {
    Route::post('/participant/verify', [EventRegistrationGateController::class, 'verify']);
    Route::post('/participant/register', [EventRegistrationGateController::class, 'register']);
});

// Reset is limited hardest of all: it changes a password and sends an email, so
// repeating it is a way to lock someone out of their own account and fill their
// inbox.
Route::middleware('throttle:2,1')->group(function () {
    Route::post('/participant/reset-password-for-account', [EventRegistrationGateController::class, 'resetPasswordForAccount']);
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

/*
| Certificate verification (public by design - anyone holding a certificate
| number may confirm it is genuine).
|
| Now rate limited. It was public with no limit, and since a certificate number
| is the only thing needed to call it, that made walking the number space free.
| The equivalent web route has been throttled at 20/min all along; this one was
| the same lookup with the guard missing.
|
| Exempt from the API kill switch: a verification link that was handed to an
| employer should not break because the participant app was taken offline.
*/
Route::middleware('throttle:api-public')
    ->post('/certificate/verify', [CertificateVerificationController::class, 'verify']);

/*
| Public endpoints for the marketing website (www.e-certificate.com.my).
|
| The site's /verify page used to send visitors here to the app. It now verifies
| on its own page by calling this endpoint from the visitor's browser, with a
| typed certificate number or the encrypted payload read from the certificate's
| QR code. The lookup and the fields returned are the same as this app's public
| /verify page, through App\Services\PublicCertificateVerifier.
|
| Kept under public/v1 so it never mixes with the API-key v1 group below, and so
| one path prefix carries the CORS allow-list for the marketing origins
| (config/public_api.php, App\Http\Middleware\PublicApiCors). The CHIP checkout
| planned for the pricing page is meant to live under the same prefix.
|
| Limited to 20 per minute per IP, the same as the /verify page, on top of the
| api group's limit. Exempt from the API kill switch for the same reason as the
| verification endpoint above.
*/
Route::prefix('public/v1')->middleware('throttle:public-verify')->group(function () {
    Route::get('/certificates/verify', [PublicCertificateVerificationController::class, 'verify'])
        ->name('api.public.certificates.verify');
});

/*
| CHIP Collect checkout for the marketing site's /pricing, /checkout and
| /payment/status pages. Same public/v1 prefix and CORS allow-list as the
| verification endpoint, each route with its own limiter. The packages list is
| read server-side by the site; checkout and status are called from the
| visitor's browser. Exempt from the API kill switch (EnsureApiIsEnabled).
*/
Route::prefix('public/v1')->group(function () {
    Route::get('/packages', [App\Http\Controllers\Api\PublicPackageController::class, 'index'])
        ->middleware('throttle:public-packages')
        ->name('api.public.packages');
    Route::post('/checkout', [App\Http\Controllers\Api\PublicCheckoutController::class, 'store'])
        ->middleware('throttle:public-checkout')
        ->name('api.public.checkout');
    Route::get('/checkout/{reference}', [App\Http\Controllers\Api\PublicCheckoutController::class, 'show'])
        ->where('reference', '[0-9A-HJKMNP-TV-Z]{26}')
        ->middleware('throttle:public-checkout-status')
        ->name('api.public.checkout.show');
});

/*
| Inbound CHIP deliveries: the per-purchase success_callback and the portal
| webhook. Signed by CHIP (X-Signature); a valid delivery only triggers a
| server-side GET /purchases/{id}/. Register the webhook URL in the CHIP portal.
| No CORS (server to server), exempt from the API kill switch.
*/
Route::prefix('payments/chip')->group(function () {
    Route::post('/callback', [App\Http\Controllers\Api\ChipWebhookController::class, 'callback'])
        ->name('api.payments.chip.callback');
    Route::post('/webhook', [App\Http\Controllers\Api\ChipWebhookController::class, 'webhook'])
        ->name('api.payments.chip.webhook');
});

/*
|--------------------------------------------------------------------------
| Integration API (v1)
|--------------------------------------------------------------------------
|
| Read-only endpoints for external systems, authenticated with an API key issued
| from Settings > Global Config > API & Integrations. Each route names the
| ability it requires, so a key granted only certificates.read cannot read the
| participant register.
|
| Deliberately separate from the /api/participant endpoints above. Those serve
| the participant app, which authenticates with a Sanctum bearer token; putting
| an API key requirement on them would lock every participant out.
*/
Route::prefix('v1')->group(function () {
    Route::middleware('api.key:events.read')->group(function () {
        Route::get('/events', [IntegrationController::class, 'events']);
        Route::get('/events/{event}', [IntegrationController::class, 'event']);
    });

    Route::middleware('api.key:participants.read')
        ->get('/participants', [IntegrationController::class, 'participants']);

    Route::middleware('api.key:certificates.read')
        ->get('/certificates', [IntegrationController::class, 'certificates']);

    // Answers which key is calling and what it may do. Useful for a subscriber
    // confirming their credential works before wiring anything else up.
    Route::middleware('api.key')->get('/whoami', [IntegrationController::class, 'whoami']);
});