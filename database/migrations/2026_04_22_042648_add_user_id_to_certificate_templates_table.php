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
        Schema::table('certificate_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('certificate_templates', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }
        });
        
        // Copy data from created_by to user_id for existing records
        DB::statement('UPDATE certificate_templates SET user_id = created_by WHERE user_id IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificate_templates', function (Blueprint $table) {
            if (Schema::hasColumn('certificate_templates', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
