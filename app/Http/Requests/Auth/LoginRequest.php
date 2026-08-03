<?php

namespace App\Http\Requests\Auth;

use App\Support\SecurityAlert;
use App\Support\SecurityPolicy;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            // The lockout length comes from the Security tab. Calling hit() with
            // no decay used the framework default of one minute, so a configured
            // 15 minute lockout released after 60 seconds.
            RateLimiter::hit($this->throttleKey(), SecurityPolicy::lockoutSeconds());

            SecurityPolicy::audit('failed_login', 'Failed login attempt', [
                'email' => $this->email,
                'ip_address' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'reason' => 'Invalid credentials',
                'attempts_remaining' => max(0, SecurityPolicy::maxLoginAttempts() - RateLimiter::attempts($this->throttleKey())),
            ]);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // Check user status after successful credential match
        $user = Auth::user();
        if (isset($user->status) && in_array(strtolower($user->status), ['inactive', 'banned'])) {
            Auth::logout();
            RateLimiter::hit($this->throttleKey(), SecurityPolicy::lockoutSeconds());

            // Always recorded, whatever the failed-login switch says: this is not
            // a mistyped password, it is a disabled account being used.
            SecurityPolicy::audit('blocked_login', 'Login blocked - Account ' . $user->status, [
                'email' => $this->email,
                'ip_address' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'reason' => 'Account ' . $user->status,
                'status' => $user->status,
            ], $user);
            
            $message = $user->status === 'banned' ? 'Your account is banned. Please contact support.' : 'Your account is inactive. Please contact support.';
            throw ValidationException::withMessages([
                'email' => $message,
            ]);
        }

        // Update last login timestamp
        $user->last_login_at = now();
        $user->save();

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        // Was a literal 5, so the Max Login Attempts setting did nothing.
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), SecurityPolicy::maxLoginAttempts())) {
            return;
        }

        event(new Lockout($this));

        // A lockout is worth knowing about even with failed-login logging off,
        // and it is what the security alert is for.
        SecurityPolicy::audit('lockout', 'Login rate limit exceeded', [
            'email' => $this->email,
            'ip_address' => $this->ip(),
            'user_agent' => $this->userAgent(),
            'reason' => 'Too many login attempts',
            'max_attempts' => SecurityPolicy::maxLoginAttempts(),
            'lockout_minutes' => (int) round(SecurityPolicy::lockoutSeconds() / 60),
        ]);

        SecurityAlert::send('Account lockout triggered', [
            'Email' => (string) $this->email,
            'IP address' => (string) $this->ip(),
            'Attempts allowed' => (string) SecurityPolicy::maxLoginAttempts(),
            'Locked for' => (int) round(SecurityPolicy::lockoutSeconds() / 60) . ' minute(s)',
        ]);

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
