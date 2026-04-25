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
        Schema::table('participants', function (Blueprint $table) {
            $table->index('registration_type');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->index('skip_identity_verification');
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropIndex(['registration_type']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['skip_identity_verification']);
            $table->dropIndex(['user_id', 'status']);
        });
    }
};
