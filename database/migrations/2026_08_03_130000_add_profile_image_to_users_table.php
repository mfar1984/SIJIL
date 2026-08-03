<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Somewhere to keep a profile picture.
 *
 * The profile page has offered a picture upload for some time: a preview, a
 * filename box and a Browse button, all wired to a file input named
 * profile_image. None of it did anything. There was no column to store the path
 * and ProfileController never looked at the field, so the file was posted,
 * discarded, and the page returned looking successful.
 *
 * Holds the path on the public disk, matching how the branding images are stored
 * rather than introducing a second convention.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'profile_image')) {
                $table->string('profile_image')->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'profile_image')) {
                $table->dropColumn('profile_image');
            }
        });
    }
};
