<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per question settings. The rating type was hard coded to 1-5 with no labels,
     * which made it unusable for anything else.
     */
    public function up(): void
    {
        Schema::table('survey_questions', function (Blueprint $table) {
            $table->unsignedTinyInteger('scale_min')->default(1)->after('options');
            $table->unsignedTinyInteger('scale_max')->default(5)->after('scale_min');
            $table->string('scale_min_label')->nullable()->after('scale_max');
            $table->string('scale_max_label')->nullable()->after('scale_min_label');
        });
    }

    public function down(): void
    {
        Schema::table('survey_questions', function (Blueprint $table) {
            $table->dropColumn(['scale_min', 'scale_max', 'scale_min_label', 'scale_max_label']);
        });
    }
};
