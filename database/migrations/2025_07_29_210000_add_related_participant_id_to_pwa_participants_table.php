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
        Schema::table('pwa_participants', function (Blueprint $table) {
            $table->unsignedBigInteger('related_participant_id')->nullable()->after('id');
            $table->foreign('related_participant_id')->references('id')->on('participants')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pwa_participants', function (Blueprint $table) {
            $table->dropForeign(['related_participant_id']);
            $table->dropColumn('related_participant_id');
        });
    }
}; 