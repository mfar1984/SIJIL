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
            $table->string('id_passport')->nullable()->after('phone');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('address');
            $table->date('date_of_birth')->nullable()->after('gender');
            $table->string('job_title')->nullable()->after('organization');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn('id_passport');
            $table->dropColumn('gender');
            $table->dropColumn('date_of_birth');
            $table->dropColumn('job_title');
        });
    }
};
