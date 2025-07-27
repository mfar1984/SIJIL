<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add a comment to document the new ticket_id format
        DB::statement("ALTER TABLE helpdesk_tickets MODIFY COLUMN ticket_id VARCHAR(20) COMMENT 'Format: HD-XXXX for tickets up to 9999, then HD-DDMMYY-XXXX where XXXX is a unique alphanumeric code'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the comment
        DB::statement("ALTER TABLE helpdesk_tickets MODIFY COLUMN ticket_id VARCHAR(20) COMMENT ''");
    }
};
