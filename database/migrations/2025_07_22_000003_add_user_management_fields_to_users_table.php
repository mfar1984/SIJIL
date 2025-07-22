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
        Schema::table('users', function (Blueprint $table) {
            // Basic Information
            $table->string('phone')->nullable()->after('email');
            $table->string('organization')->nullable()->after('phone');
            $table->enum('status', ['active', 'inactive', 'banned'])->default('active')->after('role_id');
            $table->timestamp('last_login_at')->nullable()->after('status');
            
            // Address Information
            $table->string('address_line1')->nullable()->after('last_login_at');
            $table->string('address_line2')->nullable()->after('address_line1');
            $table->string('city')->nullable()->after('address_line2');
            $table->string('state')->nullable()->after('city');
            $table->string('postcode')->nullable()->after('state');
            $table->string('country')->nullable()->after('postcode');
            
            // Organization Information
            $table->enum('org_type', ['company', 'government', 'ngo', 'other'])->nullable()->after('country');
            $table->string('org_name')->nullable()->after('org_type');
            $table->string('org_address_line1')->nullable()->after('org_name');
            $table->string('org_address_line2')->nullable()->after('org_address_line1');
            $table->string('org_city')->nullable()->after('org_address_line2');
            $table->string('org_state')->nullable()->after('org_city');
            $table->string('org_postcode')->nullable()->after('org_state');
            $table->string('org_country')->nullable()->after('org_postcode');
            $table->string('org_telephone')->nullable()->after('org_country');
            $table->string('org_fax')->nullable()->after('org_telephone');
            $table->string('org_email')->nullable()->after('org_fax');
            $table->string('org_website')->nullable()->after('org_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Basic Information
            $table->dropColumn('phone');
            $table->dropColumn('organization');
            $table->dropColumn('status');
            $table->dropColumn('last_login_at');
            
            // Address Information
            $table->dropColumn('address_line1');
            $table->dropColumn('address_line2');
            $table->dropColumn('city');
            $table->dropColumn('state');
            $table->dropColumn('postcode');
            $table->dropColumn('country');
            
            // Organization Information
            $table->dropColumn('org_type');
            $table->dropColumn('org_name');
            $table->dropColumn('org_address_line1');
            $table->dropColumn('org_address_line2');
            $table->dropColumn('org_city');
            $table->dropColumn('org_state');
            $table->dropColumn('org_postcode');
            $table->dropColumn('org_country');
            $table->dropColumn('org_telephone');
            $table->dropColumn('org_fax');
            $table->dropColumn('org_email');
            $table->dropColumn('org_website');
        });
    }
};
