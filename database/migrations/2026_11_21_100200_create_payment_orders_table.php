<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per checkout. Created before CHIP is called, with a snapshot of the
 * package and its price, so the amount verified later is the amount shown.
 *
 * reference and chip_purchase_id are unique: one CHIP purchase maps to exactly
 * one order, which is what makes fulfilment idempotent. MariaDB allows any
 * number of NULLs in a unique index, so orders that never reached CHIP are fine.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_orders')) {
            return;
        }

        Schema::create('payment_orders', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id');
            $table->char('reference', 26)->unique();
            $table->enum('type', ['new', 'renewal']);
            $table->string('status', 20)->index();

            $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->string('package_code', 32);
            $table->string('package_name', 100);
            $table->string('package_category', 16);
            $table->unsignedTinyInteger('duration_months');
            $table->unsignedInteger('amount_cents');
            $table->char('currency', 3);

            $table->string('email', 255)->nullable()->index();
            $table->text('buyer_snapshot')->nullable();
            $table->char('ip_hash', 64)->nullable();
            $table->timestamp('consent_at')->nullable();

            $table->string('chip_purchase_id', 64)->nullable()->unique();
            $table->string('checkout_url', 500)->nullable();
            $table->string('chip_status', 32)->nullable();
            $table->boolean('is_test')->nullable();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('previous_expires_at')->nullable();
            $table->dateTime('package_started_at')->nullable();
            $table->dateTime('new_expires_at')->nullable();

            $table->dateTime('paid_at')->nullable();
            $table->dateTime('fulfilled_at')->nullable();
            $table->dateTime('last_checked_at')->nullable();
            $table->dateTime('set_password_sent_at')->nullable();
            $table->dateTime('purged_at')->nullable();

            $table->string('failure_reason', 255)->nullable();
            $table->string('review_reason', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_orders');
    }
};
