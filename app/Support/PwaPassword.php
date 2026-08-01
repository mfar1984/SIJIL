<?php

namespace App\Support;

use App\Models\PwaSetting;
use App\Models\User;

/**
 * Generates passwords for PWA accounts according to the PWA settings.
 *
 * Before this existed the code called Str::random(12) in four places, so the
 * password length and complexity options on the settings page had no effect at
 * all no matter what they were set to.
 */
class PwaPassword
{
    private const UPPERCASE = 'ABCDEFGHJKLMNPQRSTUVWXYZ';  // no I or O
    private const LOWERCASE = 'abcdefghijkmnopqrstuvwxyz'; // no l
    private const NUMBERS = '23456789';                    // no 0 or 1
    private const SPECIAL = '!@#$%&*?';

    /** Bounds enforced by the settings form. */
    private const MIN_LENGTH = 6;
    private const MAX_LENGTH = 16;

    /**
     * Build a password using the effective settings for the given user.
     */
    public static function generate(?User $user = null): string
    {
        $settings = PwaSetting::resolveFor($user);

        $length = (int) ($settings['password_length'] ?? 8);
        $length = max(self::MIN_LENGTH, min(self::MAX_LENGTH, $length));

        $pools = [];

        if (!empty($settings['include_uppercase'])) {
            $pools[] = self::UPPERCASE;
        }
        if (!empty($settings['include_lowercase'])) {
            $pools[] = self::LOWERCASE;
        }
        if (!empty($settings['include_numbers'])) {
            $pools[] = self::NUMBERS;
        }
        if (!empty($settings['include_special_chars'])) {
            $pools[] = self::SPECIAL;
        }

        // Every complexity box turned off would leave nothing to choose from.
        if (!$pools) {
            $pools = [self::LOWERCASE, self::NUMBERS];
        }

        // One character from each selected pool first, so the result really does
        // satisfy every box that is ticked.
        $characters = [];

        foreach ($pools as $pool) {
            $characters[] = $pool[random_int(0, strlen($pool) - 1)];
        }

        $all = implode('', $pools);

        while (count($characters) < $length) {
            $characters[] = $all[random_int(0, strlen($all) - 1)];
        }

        // Shuffle so the guaranteed characters are not always at the front.
        for ($i = count($characters) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$characters[$i], $characters[$j]] = [$characters[$j], $characters[$i]];
        }

        return implode('', $characters);
    }
}
