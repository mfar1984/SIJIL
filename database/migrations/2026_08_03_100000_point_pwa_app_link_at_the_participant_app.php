<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Sends welcome and reset emails to the app instead of the admin site.
 *
 * `pwa_app_link` is the address participants are told to sign in at. It was
 * saved as the admin host, so every welcome email pointed people at the staff
 * login page, where their credentials cannot work: PWA accounts live in
 * pwa_participants, not in users. There is no /pwa/login route either, so even
 * the path was a dead end.
 *
 * Correcting the default in PwaSetting::DEFAULTS is not enough on its own,
 * because a saved settings row overrides it, and both the global row and an
 * organizer row already hold the wrong value.
 *
 * Only the known-wrong host is rewritten. Anything else was set deliberately and
 * is left alone.
 */
return new class extends Migration
{
    private const WRONG = 'https://apps.e-certificate.com.my';
    private const RIGHT = 'https://user.e-certificate.com.my';

    public function up(): void
    {
        $this->rewrite(self::WRONG, self::RIGHT);
    }

    public function down(): void
    {
        $this->rewrite(self::RIGHT, self::WRONG);
    }

    private function rewrite(string $from, string $to): void
    {
        foreach (DB::table('pwa_settings')->get(['id', 'settings']) as $row) {
            $settings = json_decode((string) $row->settings, true);

            if (!is_array($settings)) {
                continue;
            }

            $current = rtrim((string) ($settings['pwa_app_link'] ?? ''), '/');

            if ($current !== rtrim($from, '/')) {
                continue;
            }

            $settings['pwa_app_link'] = $to;

            DB::table('pwa_settings')
                ->where('id', $row->id)
                ->update(['settings' => json_encode($settings)]);
        }
    }
};
