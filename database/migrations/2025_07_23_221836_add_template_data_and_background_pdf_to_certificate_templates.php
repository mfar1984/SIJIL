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
            if (!Schema::hasColumn('certificate_templates', 'template_data')) {
                $table->json('template_data')->nullable()->after('placeholders');
            }
            
            if (!Schema::hasColumn('certificate_templates', 'background_pdf')) {
                $table->string('background_pdf')->nullable()->after('pdf_file');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificate_templates', function (Blueprint $table) {
            if (Schema::hasColumn('certificate_templates', 'template_data')) {
                $table->dropColumn('template_data');
            }
            
            if (Schema::hasColumn('certificate_templates', 'background_pdf')) {
                $table->dropColumn('background_pdf');
            }
        });
    }
};
