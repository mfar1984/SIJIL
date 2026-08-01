<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-event automation switches for the public registration link.
 *
 * Until now every event behaved the same way: a participant row was created and
 * a confirmation email went out. Anything else - a PWA account, a certificate,
 * attendance - had to be done by hand from the admin side. These three flags let
 * an organizer decide per event, because some events need attendance and some do
 * not.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'auto_pwa_registration')) {
                $table->boolean('auto_pwa_registration')
                    ->default(false)
                    ->after('skip_identity_verification');
            }

            if (!Schema::hasColumn('events', 'auto_generate_certificate')) {
                $table->boolean('auto_generate_certificate')
                    ->default(false)
                    ->after('auto_pwa_registration');
            }

            if (!Schema::hasColumn('events', 'attendance_required')) {
                $table->boolean('attendance_required')
                    ->default(false)
                    ->after('auto_generate_certificate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $drop = array_values(array_filter(
                ['auto_pwa_registration', 'auto_generate_certificate', 'attendance_required'],
                fn ($column) => Schema::hasColumn('events', $column)
            ));

            if ($drop) {
                $table->dropColumn($drop);
            }
        });
    }
};
