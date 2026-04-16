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
            $table->string('telegram_bot_token')->nullable()->after('webhook_events');
            $table->string('telegram_bot_username', 100)->nullable()->after('telegram_bot_token');
            $table->string('telegram_channel_id', 100)->nullable()->after('telegram_bot_username');
            $table->string('telegram_owner_user_id', 100)->nullable()->after('telegram_channel_id');
            $table->string('telegram_owner_username', 100)->nullable()->after('telegram_owner_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('global_configs', function (Blueprint $table) {
            $table->dropColumn([
                'telegram_bot_token',
                'telegram_bot_username',
                'telegram_channel_id',
                'telegram_owner_user_id',
                'telegram_owner_username',
            ]);
        });
    }
};
