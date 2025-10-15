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
        // Schema::create('certificate_templates', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('name');
        //     $table->text('description')->nullable();
        //     $table->string('pdf_file');
        //     $table->enum('orientation', ['portrait', 'landscape'])->default('landscape');
        //     $table->json('placeholders')->nullable();
        //     $table->boolean('is_active')->default(true);
        //     $table->unsignedBigInteger('created_by');
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('certificate_templates');
    }
};
