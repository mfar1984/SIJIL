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
                $table->foreign('attendance_session_id')->references('id')->on('attendance_sessions')->onDelete('set null');
            }
            if (!Schema::hasColumn('attendance_records', 'checkin_at')) {
                $table->timestamp('checkin_at')->nullable()->after('participant_id');
            }
            if (!Schema::hasColumn('attendance_records', 'checkout_at')) {
                $table->timestamp('checkout_at')->nullable()->after('checkin_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            if (Schema::hasColumn('attendance_records', 'attendance_session_id')) {
                $table->dropForeign(['attendance_session_id']);
                $table->dropColumn('attendance_session_id');
            }
            if (Schema::hasColumn('attendance_records', 'checkin_at')) {
                $table->dropColumn('checkin_at');
            }
            if (Schema::hasColumn('attendance_records', 'checkout_at')) {
                $table->dropColumn('checkout_at');
            }
        });
    }
}; 