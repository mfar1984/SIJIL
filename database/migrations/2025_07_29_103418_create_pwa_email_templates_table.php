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
        Schema::create('pwa_email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['welcome', 'password_reset', 'event_reminder', 'custom'])->default('custom');
            $table->string('subject');
            $table->longText('content');
            $table->enum('scope', ['global', 'organizer'])->default('global');
            $table->unsignedBigInteger('organizer_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('times_used')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('organizer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pwa_email_templates');
    }
};
