<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],

            /*
             * The profile page has always shown a "Change Password" card, and
             * neither this request nor the controller looked at it. The fields
             * were posted, dropped, and the page reported success, so anyone who
             * believed they had changed their password had not.
             *
             * The current password is required alongside a new one so that a
             * borrowed or hijacked session cannot lock the owner out of their own
             * account.
             */
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', \App\Support\SecurityPolicy::passwordRule()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required_with' => 'Enter your current password to set a new one.',
            'current_password.current_password' => 'That is not your current password.',
        ];
    }
}
