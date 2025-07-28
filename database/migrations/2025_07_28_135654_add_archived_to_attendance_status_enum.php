<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, we need to modify the enum to include 'archived'
        // MySQL doesn't support ALTER ENUM directly, so we need to use raw SQL
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('active', 'expired', 'completed', 'archived') DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'archived' from the enum
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('active', 'expired', 'completed') DEFAULT 'active'");
    }
};
