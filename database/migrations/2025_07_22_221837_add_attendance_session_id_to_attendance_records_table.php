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
        //     $table->unsignedBigInteger('attendance_session_id')->nullable()->after('attendance_id');
        //     $table->foreign('attendance_session_id')->references('id')->on('attendance_sessions')->onDelete('set null');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('attendance_records', function (Blueprint $table) {
        //     $table->dropForeign(['attendance_session_id']);
        //     $table->dropColumn('attendance_session_id');
        // });
    }
};
