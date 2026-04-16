<?php

namespace App\Http\Requests\Auth;

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
            RateLimiter::hit($this->throttleKey());
            
            // Log failed login attempt
            activity('security')
                ->withProperties([
                    'email' => $this->email,
                    'ip_address' => $this->ip(),
                    'user_agent' => $this->userAgent(),
                    'reason' => 'Invalid credentials'
                ])
                ->log('Failed login attempt');

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // Check user status after successful credential match
        $user = Auth::user();
        if (isset($user->status) && in_array(strtolower($user->status), ['inactive', 'banned'])) {
            Auth::logout();
            RateLimiter::hit($this->throttleKey());
            
            // Log blocked login attempt
            activity('security')
                ->causedBy($user)
                ->withProperties([
                    'email' => $this->email,
                    'ip_address' => $this->ip(),
                    'user_agent' => $this->userAgent(),
                    'reason' => 'Account ' . $user->status,
                    'status' => $user->status
                ])
                ->log('Login blocked - Account ' . $user->status);
            
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
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));
        
        // Log rate limit exceeded
        activity('security')
            ->withProperties([
                'email' => $this->email,
                'ip_address' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'reason' => 'Too many login attempts'
            ])
            ->log('Login rate limit exceeded');

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
