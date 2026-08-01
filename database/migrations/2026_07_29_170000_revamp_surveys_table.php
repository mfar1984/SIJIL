<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replace the settings that were stored but never enforced with a set that the
     * application actually acts on.
     *
     * - access_type (public/private/registered) was never read anywhere, so a
     *   "private" survey was in practice open to anyone holding the link.
     * - allow_anonymous read backwards: false meant "collect name and email", and
     *   those details were never validated.
     */
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            // Who may answer: anyone holding the link, or only participants of the
            // linked event.
            $table->string('audience', 20)->default('anyone')->after('status');

            // Ask for the respondent's name and email when they are not identified
            // through the event participant list.
            $table->boolean('require_respondent_details')->default(false)->after('audience');

            // Allow the same respondent to submit more than once.
            $table->boolean('allow_multiple_responses')->default(false)->after('require_respondent_details');

            // Responses are only accepted between these two moments.
            $table->timestamp('opens_at')->nullable()->after('published_at');
        });

        // Carry over the closest equivalent of the old flags before dropping them.
        if (Schema::hasColumn('surveys', 'access_type')) {
            DB::table('surveys')
                ->where('access_type', 'registered')
                ->update(['audience' => 'participants']);
        }

        if (Schema::hasColumn('surveys', 'allow_anonymous')) {
            DB::table('surveys')
                ->where('allow_anonymous', false)
                ->update(['require_respondent_details' => true]);
        }

        Schema::table('surveys', function (Blueprint $table) {
            if (Schema::hasColumn('surveys', 'access_type')) {
                $table->dropColumn('access_type');
            }

            if (Schema::hasColumn('surveys', 'allow_anonymous')) {
                $table->dropColumn('allow_anonymous');
            }
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->enum('access_type', ['public', 'private', 'registered'])->default('public')->after('status');
            $table->boolean('allow_anonymous')->default(true)->after('access_type');
        });

        DB::table('surveys')
            ->where('audience', 'participants')
            ->update(['access_type' => 'registered']);

        DB::table('surveys')
            ->where('require_respondent_details', true)
            ->update(['allow_anonymous' => false]);

        Schema::table('surveys', function (Blueprint $table) {
            $table->dropColumn(['audience', 'require_respondent_details', 'allow_multiple_responses', 'opens_at']);
        });
    }
};
