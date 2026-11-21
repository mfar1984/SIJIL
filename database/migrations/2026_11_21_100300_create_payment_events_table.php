<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every inbound CHIP callback and webhook, and every server-side re-check.
 *
 * dedupe_key is deliberately not unique: CHIP delivers at least once, and a
 * retry after a 503 must be stored and processed again. The key only tells a
 * replay of an already-final delivery apart from a first delivery.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_events')) {
            return;
        }

        Schema::create('payment_events', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->bigIncrements('id');
            $table->foreignId('payment_order_id')->nullable()->constrained('payment_orders')->nullOnDelete();
            $table->enum('source', ['callback', 'webhook', 'recheck']);
            $table->string('event_type', 64)->nullable();
            $table->string('chip_purchase_id', 64)->nullable()->index();
            $table->boolean('signature_valid')->nullable();
            $table->string('result', 32)->index();
            $table->string('message', 500)->nullable();
            $table->mediumText('payload')->nullable();
            $table->char('dedupe_key', 64)->index();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_events');
    }
};
