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
            // Add identity_card and passport_no fields
            $table->string('identity_card')->nullable()->after('phone');
            $table->string('passport_no')->nullable()->after('identity_card');
            
            // Keep the existing id_passport field for now for backward compatibility
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn('identity_card');
            $table->dropColumn('passport_no');
        });
    }
};
