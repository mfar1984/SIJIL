<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Which certificate template an event issues.
 *
 * Certificates were only ever generated from the admin screen, where the
 * template is picked by hand each time. Issuing one automatically on
 * registration needs the choice stored against the event instead of guessed.
 *
 * Nullable and ON DELETE SET NULL: removing a template must not delete events.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'certificate_template_id')) {
                $table->unsignedBigInteger('certificate_template_id')
                    ->nullable()
                    ->after('attendance_required');

                $table->foreign('certificate_template_id')
                    ->references('id')
                    ->on('certificate_templates')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'certificate_template_id')) {
                $table->dropForeign(['certificate_template_id']);
                $table->dropColumn('certificate_template_id');
            }
        });
    }
};
