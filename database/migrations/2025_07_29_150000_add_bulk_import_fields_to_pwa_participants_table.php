<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pwa_participants', function (Blueprint $table) {
            if (!Schema::hasColumn('pwa_participants', 'identity_card')) $table->string('identity_card')->nullable()->after('organization');
            if (!Schema::hasColumn('pwa_participants', 'passport_no')) $table->string('passport_no')->nullable()->after('identity_card');
            if (!Schema::hasColumn('pwa_participants', 'gender')) $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('passport_no');
            if (!Schema::hasColumn('pwa_participants', 'date_of_birth')) $table->date('date_of_birth')->nullable()->after('gender');
            if (!Schema::hasColumn('pwa_participants', 'job_title')) $table->string('job_title')->nullable()->after('date_of_birth');
            if (!Schema::hasColumn('pwa_participants', 'address1')) $table->string('address1')->nullable()->after('address');
            if (!Schema::hasColumn('pwa_participants', 'address2')) $table->string('address2')->nullable()->after('address1');
            if (!Schema::hasColumn('pwa_participants', 'state')) $table->string('state')->nullable()->after('address2');
            if (!Schema::hasColumn('pwa_participants', 'city')) $table->string('city')->nullable()->after('state');
            if (!Schema::hasColumn('pwa_participants', 'postcode')) $table->string('postcode')->nullable()->after('city');
            if (!Schema::hasColumn('pwa_participants', 'country')) $table->string('country')->nullable()->after('postcode');
            if (!Schema::hasColumn('pwa_participants', 'notes')) $table->text('notes')->nullable()->after('country');
        });
    }

    public function down(): void
    {
        Schema::table('pwa_participants', function (Blueprint $table) {
            if (Schema::hasColumn('pwa_participants', 'identity_card')) $table->dropColumn('identity_card');
            if (Schema::hasColumn('pwa_participants', 'passport_no')) $table->dropColumn('passport_no');
            if (Schema::hasColumn('pwa_participants', 'gender')) $table->dropColumn('gender');
            if (Schema::hasColumn('pwa_participants', 'date_of_birth')) $table->dropColumn('date_of_birth');
            if (Schema::hasColumn('pwa_participants', 'job_title')) $table->dropColumn('job_title');
            if (Schema::hasColumn('pwa_participants', 'address1')) $table->dropColumn('address1');
            if (Schema::hasColumn('pwa_participants', 'address2')) $table->dropColumn('address2');
            if (Schema::hasColumn('pwa_participants', 'state')) $table->dropColumn('state');
            if (Schema::hasColumn('pwa_participants', 'city')) $table->dropColumn('city');
            if (Schema::hasColumn('pwa_participants', 'postcode')) $table->dropColumn('postcode');
            if (Schema::hasColumn('pwa_participants', 'country')) $table->dropColumn('country');
            if (Schema::hasColumn('pwa_participants', 'notes')) $table->dropColumn('notes');
        });
    }
}; 