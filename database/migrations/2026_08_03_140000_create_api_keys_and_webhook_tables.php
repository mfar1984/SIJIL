<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The API & Integrations tab has always offered "Require API keys for access"
 * and "Enable webhooks". Neither had any storage behind it: there was no key to
 * issue, no endpoint to call, and nothing that ever fired. These three tables
 * are what those switches needed all along.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('api_keys')) {
            Schema::create('api_keys', function (Blueprint $table) {
                $table->id();
                $table->string('name');

                // The secret is shown once at creation and only ever stored hashed.
                // The prefix is kept in clear so a key can be recognised in a list
                // and matched quickly without hashing every row.
                $table->string('key_prefix', 16)->index();
                $table->string('key_hash', 64)->unique();

                $table->json('abilities')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

                $table->timestamp('last_used_at')->nullable();
                $table->string('last_used_ip', 45)->nullable();
                $table->unsignedBigInteger('request_count')->default(0);

                $table->timestamp('expires_at')->nullable();
                $table->timestamp('revoked_at')->nullable();

                $table->timestamps();
            });
        }

        if (! Schema::hasTable('webhook_endpoints')) {
            Schema::create('webhook_endpoints', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('url', 500);

                // Per endpoint, so revoking one subscriber does not invalidate the
                // signatures every other subscriber is verifying.
                $table->string('secret', 80);

                $table->json('events');
                $table->boolean('is_active')->default(true);

                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

                $table->timestamp('last_delivery_at')->nullable();
                $table->unsignedSmallInteger('last_status_code')->nullable();
                $table->unsignedSmallInteger('consecutive_failures')->default(0);
                $table->timestamp('disabled_at')->nullable();

                $table->timestamps();
            });
        }

        if (! Schema::hasTable('webhook_deliveries')) {
            Schema::create('webhook_deliveries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('webhook_endpoint_id')->constrained('webhook_endpoints')->cascadeOnDelete();
                $table->string('event', 64)->index();
                $table->uuid('delivery_id')->index();
                $table->json('payload');
                $table->unsignedTinyInteger('attempt')->default(1);
                $table->unsignedSmallInteger('status_code')->nullable();
                $table->text('response_excerpt')->nullable();
                $table->text('error')->nullable();
                $table->unsignedInteger('duration_ms')->nullable();
                $table->boolean('succeeded')->default(false);
                $table->timestamps();

                $table->index(['webhook_endpoint_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhook_endpoints');
        Schema::dropIfExists('api_keys');
    }
};
