<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Support\SecurityAlert;
use App\Support\SecurityPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validated();

        // Handled separately below; filling them would try to write
        // current_password to a column that does not exist and would store the
        // new password in clear.
        unset($validated['password'], $validated['current_password']);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // The Change Password card on this page submitted into nothing. The
        // fields were validated by no rule and read by no code, so the password
        // never changed while the page said it had.
        $passwordChanged = false;

        if (filled($request->input('password'))) {
            $user->password = Hash::make($request->input('password'));
            $user->password_changed_at = now();
            $passwordChanged = true;
        }

        // The page has offered a picture upload all along and nothing read it, so
        // the file was posted and discarded while the page reported success.
        if ($request->hasFile('profile_image')) {
            $request->validate([
                'profile_image' => 'image|mimes:jpeg,jpg,png,webp|max:2048',
            ]);

            $file = $request->file('profile_image');

            // The extension comes from the verified MIME type, never from the
            // filename the browser supplied.
            $extension = match ($file->getMimeType()) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => null,
            };

            if (! $extension) {
                return Redirect::route('profile.edit')
                    ->withErrors(['profile_image' => 'That image type is not supported. Use JPEG, PNG or WebP.']);
            }

            $previous = $user->profile_image;

            $user->profile_image = $file->storeAs(
                'avatars',
                'avatar_' . $user->id . '_' . time() . '_' . Str::random(6) . '.' . $extension,
                'public'
            );

            // Replaced, so the old file is no longer referenced by anything.
            if ($previous && Storage::disk('public')->exists($previous)) {
                Storage::disk('public')->delete($previous);
            }
        }

        $user->save();

        if ($passwordChanged) {
            SecurityPolicy::audit('password', 'Password changed', [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'self_service' => true,
            ], $user, $user);

            SecurityAlert::send('Password changed', [
                'Account' => (string) $user->email,
                'Changed by' => 'the account holder',
                'IP address' => (string) $request->ip(),
            ]);

            // Every other session for this account is invalidated, so a password
            // change actually evicts whoever prompted it.
            Auth::logoutOtherDevices($request->input('password'));

            return Redirect::route('profile.edit')->with('status', 'password-updated');
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Remove the profile picture.
     */
    public function destroyImage(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->profile_image) {
            if (Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            $user->profile_image = null;
            $user->save();
        }

        return Redirect::route('profile.edit')->with('status', 'profile-image-removed');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
