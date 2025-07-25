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
        Schema::table('participants', function (Blueprint $table) {
            // Drop columns
            if (Schema::hasColumn('participants', 'tags')) {
                $table->dropColumn('tags');
            }
            if (Schema::hasColumn('participants', 'source')) {
                $table->dropColumn('source');
            }
            if (Schema::hasColumn('participants', 'id_passport')) {
                $table->dropColumn('id_passport');
            }
            if (Schema::hasColumn('participants', 'role')) {
                $table->dropColumn('role');
            }
            if (Schema::hasColumn('participants', 'address')) {
                $table->dropColumn('address');
            }

            // Add new address fields
            $table->string('address1')->nullable()->after('job_title');
            $table->string('address2')->nullable()->after('address1');
            $table->string('city')->nullable()->after('address2');
            $table->string('state')->nullable()->after('city');
            $table->string('postcode')->nullable()->after('state');
            $table->string('country')->nullable()->default('Malaysia')->after('postcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            // Add dropped columns back (if needed)
            $table->string('tags')->nullable();
            $table->string('source')->nullable();
            $table->string('id_passport')->nullable();
            $table->string('role')->nullable();
            $table->text('address')->nullable();

            // Drop new address fields
            $table->dropColumn(['address1', 'address2', 'city', 'state', 'postcode', 'country']);
        });
    }
};
