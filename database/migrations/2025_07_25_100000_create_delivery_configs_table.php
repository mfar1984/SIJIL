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
        Schema::create('delivery_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('config_type'); // 'email' or 'sms'
            $table->string('provider'); // 'smtp', 'mailgun', 'ses', 'sendmail', 'twilio', 'nexmo', 'aws_sns', 'infobip'
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->text('default_template')->nullable();
            $table->timestamps();

            // Add unique constraint to ensure only one active config per type per user
            $table->unique(['user_id', 'config_type', 'provider']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_configs');
    }
}; 