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
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->decimal('checkin_lat', 10, 7)->nullable()->after('participant_id');
            $table->decimal('checkin_lng', 10, 7)->nullable()->after('checkin_lat');
            $table->decimal('checkout_lat', 10, 7)->nullable()->after('checkin_lng');
            $table->decimal('checkout_lng', 10, 7)->nullable()->after('checkout_lat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropColumn(['checkin_lat', 'checkin_lng', 'checkout_lat', 'checkout_lng']);
        });
    }
};
