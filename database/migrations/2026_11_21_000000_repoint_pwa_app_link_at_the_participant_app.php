<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Sends welcome and reset emails to the participant app, second attempt.
 *
 * 2026_08_03_100000 made this change and is recorded as run, but production
 * still holds the staff host in every pwa_settings row. Its code was not the
 * problem: it did fix the local database at 22:31 on 2026-08-03. The local
 * database was then refreshed from production's 22:29 export, which still had
 * the staff host, while its migrations table kept the 100000 record. That copy
 * was imported into production, so the fix was lost and `migrate` will never run
 * 100000 again. A new migration is the only way to get it to run.
 *
 * Only the staff host itself is rewritten, ignoring surrounding whitespace,
 * trailing slashes and letter case. Any other link was set deliberately and is
 * left alone. Every other key keeps its value and type: the JSON is decoded to
 * objects, not arrays, so an empty object stays {} instead of turning into [],
 * and a float such as 1.0 keeps its fraction.
 *
 * The rewritten JSON is plain ASCII (json_encode escapes non-ASCII characters),
 * so it stores the same in the utf8mb4_bin column whatever the table default.
 *
 * Nothing caches PWA settings: PwaSetting::resolveFor() reads the rows on every
 * call, so there is no cache to clear.
 */
return new class extends Migration
{
    private const STAFF_HOST = 'https://apps.e-certificate.com.my';
    private const PARTICIPANT_APP = 'https://user.e-certificate.com.my';

    public function up(): void
    {
        foreach (DB::table('pwa_settings')->orderBy('id')->get(['id', 'settings']) as $row) {
            $settings = json_decode((string) $row->settings);

            if (!$settings instanceof \stdClass || !isset($settings->pwa_app_link)) {
                continue;
            }

            if (!is_string($settings->pwa_app_link) || !$this->isStaffHost($settings->pwa_app_link)) {
                continue;
            }

            $settings->pwa_app_link = self::PARTICIPANT_APP;

            DB::table('pwa_settings')
                ->where('id', $row->id)
                ->update(['settings' => json_encode($settings, JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR)]);
        }
    }

    public function down(): void
    {
        // Deliberately not reversed. Putting the staff host back would send
        // participants to a login page their credentials cannot open.
    }

    private function isStaffHost(string $link): bool
    {
        return strtolower(rtrim(trim($link), '/')) === self::STAFF_HOST;
    }
};
