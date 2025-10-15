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
        // Table already exists, skip creation to avoid duplicate error
        // Schema::create('attendance_sessions', function (Blueprint $table) {
        //     $table->id();
        //     $table->unsignedBigInteger('attendance_id');
        //     $table->date('date');
        //     $table->time('checkin_start_time');
        //     $table->time('checkin_end_time');
        //     $table->time('checkout_start_time')->nullable();
        //     $table->time('checkout_end_time')->nullable();
        //     $table->timestamps();
        //     $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('cascade');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('attendance_sessions');
    }
};
