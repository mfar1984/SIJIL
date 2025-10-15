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
        Schema::table('event_pwa_participant', function (Blueprint $table) {
            $table->unsignedBigInteger('attendance_record_id')->nullable()->after('notes');
            $table->foreign('attendance_record_id')->references('id')->on('attendance_records')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_pwa_participant', function (Blueprint $table) {
            $table->dropForeign(['attendance_record_id']);
            $table->dropColumn('attendance_record_id');
        });
    }
}; 