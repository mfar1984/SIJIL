<?php

namespace App\Http\Middleware;

use App\Support\SecurityPolicy;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Re-checks an authenticated account on every request.
 *
 * Account status was only ever examined at the moment of signing in. Banning or
 * deactivating someone therefore had no effect until their session expired,
 * which with a 120 minute lifetime and any activity at all could be indefinite.
 * The one action an administrator takes in an emergency did nothing immediate.
 *
 * Also enforces password expiry, which has been a setting on the Security tab
 * with nothing to enforce it.
 */
class EnsureAccountIsUsable
{
    /**
     * Routes the holder must still reach while being told to change a password,
     * otherwise the redirect would loop.
     *
     * @var array<int, string>
     */
    private const PASSWORD_CHANGE_EXEMPT = [
        'profile',
        'profile/*',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        $status = strtolower((string) ($user->status ?? 'active'));

        if (in_array($status, ['inactive', 'banned'], true)) {
            SecurityPolicy::audit('blocked_session', 'Session ended - account ' . $status, [
                'email' => $user->email,
                'ip_address' => $request->ip(),
                'status' => $status,
            ], $user);

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = $status === 'banned'
                ? 'Your account has been banned. Please contact support.'
                : 'Your account has been deactivated. Please contact support.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }

            return redirect('/')->withErrors(['email' => $message]);
        }

        if ($this->passwordHasExpired($user) && ! $this->isExempt($request)) {
            return $request->expectsJson()
                ? response()->json([
                    'success' => false,
                    'message' => 'Your password has expired. Set a new one on your profile.',
                ], 403)
                : redirect()->route('profile.edit')->with(
                    'warning',
                    'Your password has expired after ' . SecurityPolicy::passwordExpiryDays()
                    . ' days. Set a new one below to continue.'
                );
        }

        return $next($request);
    }

    private function passwordHasExpired($user): bool
    {
        if (! SecurityPolicy::passwordExpiryEnabled()) {
            return false;
        }

        /*
         * Everything here sits inside the try, including reading the attribute.
         * The datetime cast runs on property access, so an unparseable stored
         * value throws before any code of ours gets a chance to look at it.
         *
         * This middleware runs on every authenticated request, so a bad date must
         * degrade to "not expired" rather than return 500 for the whole site -
         * which is what happened while the cast was missing from the model.
         */
        try {
            $changedAt = $user->password_changed_at ?? $user->created_at;

            if (! $changedAt) {
                return false;
            }

            $changedAt = $changedAt instanceof \DateTimeInterface
                ? \Illuminate\Support\Carbon::instance($changedAt)
                : \Illuminate\Support\Carbon::parse((string) $changedAt);

            return $changedAt->addDays(SecurityPolicy::passwordExpiryDays())->isPast();
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function isExempt(Request $request): bool
    {
        foreach (self::PASSWORD_CHANGE_EXEMPT as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }
}
