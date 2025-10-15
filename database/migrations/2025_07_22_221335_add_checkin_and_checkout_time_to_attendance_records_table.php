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
        // Column already exists, skip to avoid duplicate error
        // Schema::table('attendance_records', function (Blueprint $table) {
        //     $table->timestamp('checkin_time')->nullable()->after('participant_id');
        //     $table->timestamp('checkout_time')->nullable()->after('checkin_time');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('attendance_records', function (Blueprint $table) {
        //     $table->dropColumn('checkin_time');
        //     $table->dropColumn('checkout_time');
        // });
    }
};
