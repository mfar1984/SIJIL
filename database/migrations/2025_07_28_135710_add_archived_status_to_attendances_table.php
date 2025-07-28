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
        Schema::table('attendances', function (Blueprint $table) {
            // Drop the existing enum column
            $table->dropColumn('status');
        });

        Schema::table('attendances', function (Blueprint $table) {
            // Recreate the enum column with 'archived' added
            $table->enum('status', ['active', 'expired', 'completed', 'archived'])->default('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Drop the column
            $table->dropColumn('status');
        });

        Schema::table('attendances', function (Blueprint $table) {
            // Recreate the original enum without 'archived'
            $table->enum('status', ['active', 'expired', 'completed'])->default('active');
        });
    }
};
