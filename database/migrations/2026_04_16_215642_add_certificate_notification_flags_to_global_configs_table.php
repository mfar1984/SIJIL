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
        Schema::table('global_configs', function (Blueprint $table) {
            $table->boolean('sms_certificate_generated')->default(false)->after('email_certificate_generated');
            $table->boolean('telegram_certificate_generated')->default(false)->after('sms_certificate_generated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('global_configs', function (Blueprint $table) {
            $table->dropColumn(['sms_certificate_generated', 'telegram_certificate_generated']);
        });
    }
};
