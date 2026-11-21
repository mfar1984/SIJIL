<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * CHIP Collect credentials, kept apart from global_configs on purpose.
 *
 * The global_configs row is cached, returned whole by its JSON endpoint, wiped
 * by "reset" and logged with every attribute. None of that may happen to the
 * secret key, so it gets its own one-row table, encrypted with APP_KEY through
 * the model cast and never cached.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payment_settings')) {
            Schema::create('payment_settings', function (Blueprint $table) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';

                $table->bigIncrements('id');
                $table->boolean('enabled')->default(false);
                $table->enum('mode', ['test', 'live'])->default('test');
                $table->text('secret_key')->nullable();
                $table->string('secret_key_last4', 8)->nullable();
                $table->string('brand_id', 36)->nullable();
                $table->text('webhook_public_key')->nullable();
                $table->text('company_public_key')->nullable();
                $table->timestamp('company_public_key_fetched_at')->nullable();
                $table->string('government_email_domains', 1000)->default('gov.my');
                $table->timestamp('last_tested_at')->nullable();
                $table->boolean('last_test_ok')->nullable();
                $table->string('last_test_message', 255)->nullable();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! DB::table('payment_settings')->exists()) {
            DB::table('payment_settings')->insert([
                'enabled' => false,
                'mode' => 'test',
                'government_email_domains' => 'gov.my',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};
