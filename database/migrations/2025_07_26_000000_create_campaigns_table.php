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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('campaign_type'); // email, sms, whatsapp
            $table->string('audience_type'); // all_participants, specific_event, custom_filter
            $table->foreignId('event_id')->nullable()->constrained()->onDelete('set null');
            $table->json('filter_criteria')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status')->default('draft'); // draft, scheduled, running, completed
            $table->json('content'); // Stores email subject, body, sms message, etc.
            $table->string('schedule_type')->default('now'); // now, scheduled
            $table->dateTime('scheduled_at')->nullable();
            $table->integer('recipients_count')->default(0);
            $table->integer('delivered_count')->default(0);
            $table->integer('opened_count')->default(0);
            $table->integer('clicked_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
}; 