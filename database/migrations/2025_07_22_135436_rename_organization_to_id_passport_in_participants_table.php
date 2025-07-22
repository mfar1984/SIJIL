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
            // We want to keep using the same name in the code for now, but change its semantic meaning
            // So we'll just rename the column comment but keep the same name
            $table->string('organization')->nullable()->comment('Identity Card / Passport No.')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->string('organization')->nullable()->comment('Organization')->change();
        });
    }
};
