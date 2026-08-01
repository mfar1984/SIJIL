<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lets an app participant raise a helpdesk ticket.
 *
 * Both helpdesk tables tie their author to `users` with a non-nullable foreign key.
 * App participants live in `pwa_participants` and deliberately have no `users` row -
 * that separation is what stops an app login reaching the backend - so there was no
 * way to record one as the author of a ticket.
 *
 * Rather than make the author polymorphic, which would touch every existing query,
 * `user_id` becomes nullable and a second nullable column is added beside it. A row
 * carries exactly one of the two. Backend tickets keep using `user_id`, so every
 * `where('user_id', ...)` in the existing code stays correct, and an organizer never
 * sees app tickets because their `user_id` is null.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Drop the foreign keys first: MySQL will not let a constrained column change
        // nullability while the constraint is in place.
        Schema::table('helpdesk_tickets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('helpdesk_messages', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        DB::statement('ALTER TABLE helpdesk_tickets MODIFY user_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE helpdesk_messages MODIFY user_id BIGINT UNSIGNED NULL');

        Schema::table('helpdesk_tickets', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            $table->foreignId('pwa_participant_id')
                ->nullable()
                ->after('user_id')
                ->constrained('pwa_participants')
                ->nullOnDelete();
        });

        Schema::table('helpdesk_messages', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            $table->foreignId('pwa_participant_id')
                ->nullable()
                ->after('user_id')
                ->constrained('pwa_participants')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Tickets raised from the app cannot be represented once the column is gone,
        // so they are removed rather than left pointing at nobody.
        DB::table('helpdesk_messages')->whereNotNull('pwa_participant_id')->delete();
        DB::table('helpdesk_tickets')->whereNotNull('pwa_participant_id')->delete();

        Schema::table('helpdesk_messages', function (Blueprint $table) {
            $table->dropForeign(['pwa_participant_id']);
            $table->dropColumn('pwa_participant_id');
            $table->dropForeign(['user_id']);
        });

        Schema::table('helpdesk_tickets', function (Blueprint $table) {
            $table->dropForeign(['pwa_participant_id']);
            $table->dropColumn('pwa_participant_id');
            $table->dropForeign(['user_id']);
        });

        DB::table('helpdesk_messages')->whereNull('user_id')->delete();
        DB::table('helpdesk_tickets')->whereNull('user_id')->delete();

        DB::statement('ALTER TABLE helpdesk_tickets MODIFY user_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE helpdesk_messages MODIFY user_id BIGINT UNSIGNED NOT NULL');

        Schema::table('helpdesk_tickets', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('helpdesk_messages', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
