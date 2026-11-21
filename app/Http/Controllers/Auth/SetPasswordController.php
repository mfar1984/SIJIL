<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\SecurityPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * The single-use set-password link emailed after a paid purchase created an
 * Organizer account (password broker "purchases"). Setting the password also
 * proves the buyer owns the email address, so it marks the email verified.
 */
class SetPasswordController extends Controller
{
    public const INVALID_MESSAGE = 'This link is invalid or has expired. Contact support@e-certificate.com.my for a new one.';

    public function create(Request $request, #[\SensitiveParameter] string $token): View
    {
        return view('auth.set-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
            'policy' => SecurityPolicy::describe(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', SecurityPolicy::passwordRule()],
        ]);

        $user = null;

        $status = Password::broker('purchases')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $account, #[\SensitiveParameter] string $password) use (&$user) {
                $values = [
                    'password' => Hash::make($password),
                    'password_changed_at' => now(),
                    'remember_token' => Str::random(60),
                ];

                if ($account->email_verified_at === null) {
                    $values['email_verified_at'] = now();
                }

                $account->forceFill($values)->save();
                $user = $account;
            }
        );

        if ($status !== Password::PASSWORD_RESET || ! $user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => self::INVALID_MESSAGE]);
        }

        SecurityPolicy::audit('payment', 'Password set from purchase link', [
            'ip_address' => $request->ip(),
        ], $user, $user);

        return redirect('/')->with('status', 'Your password has been set. Sign in to continue.');
    }
}
