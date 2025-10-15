<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename organizer_id to user_id in pwa_email_templates table
        Schema::table('pwa_email_templates', function (Blueprint $table) {
            $table->renameColumn('organizer_id', 'user_id');
        });

        // Rename organizer_id to user_id in pwa_settings table
        Schema::table('pwa_settings', function (Blueprint $table) {
            $table->renameColumn('organizer_id', 'user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert pwa_email_templates table
        Schema::table('pwa_email_templates', function (Blueprint $table) {
            $table->renameColumn('user_id', 'organizer_id');
        });

        // Revert pwa_settings table
        Schema::table('pwa_settings', function (Blueprint $table) {
            $table->renameColumn('user_id', 'organizer_id');
        });
    }
};
