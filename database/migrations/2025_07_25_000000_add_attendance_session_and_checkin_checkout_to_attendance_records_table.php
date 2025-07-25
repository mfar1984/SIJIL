<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance_records', 'attendance_session_id')) {
                $table->unsignedBigInteger('attendance_session_id')->nullable()->after('attendance_id');
            }
            if (!Schema::hasColumn('attendance_records', 'checkin_time')) {
                $table->timestamp('checkin_time')->nullable()->after('participant_id');
            }
            if (!Schema::hasColumn('attendance_records', 'checkout_time')) {
                $table->timestamp('checkout_time')->nullable()->after('checkin_time');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            if (Schema::hasColumn('attendance_records', 'attendance_session_id')) {
                $table->dropColumn('attendance_session_id');
            }
            if (Schema::hasColumn('attendance_records', 'checkin_time')) {
                $table->dropColumn('checkin_time');
            }
            if (Schema::hasColumn('attendance_records', 'checkout_time')) {
                $table->dropColumn('checkout_time');
            }
        });
    }
}; 