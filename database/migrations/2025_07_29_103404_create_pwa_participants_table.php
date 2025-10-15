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
        // Table already exists, skip creation to avoid duplicate error
        // Schema::create('pwa_participants', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('name');
        //     $table->string('email');
        //     $table->string('phone')->nullable();
        //     $table->string('password');
        //     $table->string('organization')->nullable();
        //     $table->text('address')->nullable();
        //     $table->boolean('is_active')->default(true);
        //     $table->timestamp('last_login_at')->nullable();
        //     $table->timestamp('password_changed_at')->nullable();
        //     $table->integer('login_attempts')->default(0);
        //     $table->timestamp('locked_until')->nullable();
        //     $table->unsignedBigInteger('created_by');
        //     $table->unsignedBigInteger('updated_by')->nullable();
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('pwa_participants');
    }
};
