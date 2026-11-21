<?php

namespace App\Support;

/**
 * The profile fields an account holder fills in, and their validation rules.
 *
 * Shared by User Management (create and edit) and the public checkout, so a
 * buyer is validated exactly like an account an administrator creates. The
 * admin-only fields (organization, role_id, status, password) are not here;
 * each caller adds its own.
 */
class UserProfileRules
{
    public const FIELDS = [
        'name',
        'email',
        'phone',
        'address_line1',
        'address_line2',
        'postcode',
        'city',
        'state',
        'country',
        'org_type',
        'org_name',
        'org_address_line1',
        'org_address_line2',
        'org_state',
        'org_city',
        'org_postcode',
        'org_country',
        'org_telephone',
        'org_fax',
        'org_email',
        'org_website',
    ];

    /**
     * Rules for the 21 profile fields. The email rule has no uniqueness check;
     * the caller appends the one that fits (unique on create, unique ignoring
     * the user on edit, none at checkout).
     *
     * @return array<string, list<mixed>>
     */
    public static function rules(): array
    {
        return [
            // Basic Information
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:15'],

            // Address Information
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'postcode' => ['nullable', 'string', 'max:10'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],

            // Organization Information
            'org_type' => ['nullable', 'string', 'in:company,government,ngo,other'],
            'org_name' => ['nullable', 'string', 'max:255'],
            'org_address_line1' => ['nullable', 'string', 'max:255'],
            'org_address_line2' => ['nullable', 'string', 'max:255'],
            'org_state' => ['nullable', 'string', 'max:100'],
            'org_city' => ['nullable', 'string', 'max:100'],
            'org_postcode' => ['nullable', 'string', 'max:10'],
            'org_country' => ['nullable', 'string', 'max:100'],
            'org_telephone' => ['nullable', 'string', 'max:20'],
            'org_fax' => ['nullable', 'string', 'max:20'],
            'org_email' => ['nullable', 'email', 'max:255'],
            'org_website' => ['nullable', 'url', 'max:255'],
        ];
    }

    /**
     * rules() with an extra rule appended to the email field.
     *
     * @return array<string, list<mixed>>
     */
    public static function rulesWithEmail(mixed $extraEmailRule): array
    {
        $rules = static::rules();
        $rules['email'][] = $extraEmailRule;

        return $rules;
    }
}
