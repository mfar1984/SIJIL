<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The order deadline and who cancelled an order.
 *
 * due_at is the one stored deadline: it is written once when the order is
 * created and sent to CHIP as the purchase `due`, so a later change to
 * payments.order_deadline_hours never moves an existing deadline.
 *
 * Every column is added behind a Schema::hasColumn guard, so a re-run is a
 * no-op, and nothing in up() destroys data. No text column is added, so no
 * utf8mb4 charset/collation declaration is needed here.
 *
 * The backfill gives pre-existing unpaid orders the deadline they would have
 * had (created_at + the configured window), so none of them is left without
 * one and invisible to the expiry pass.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payment_orders')) {
            return;
        }

        Schema::table('payment_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_orders', 'due_at')) {
                $table->dateTime('due_at')->nullable()->index()->after('is_test');
            }

            if (! Schema::hasColumn('payment_orders', 'cancelled_at')) {
                $table->dateTime('cancelled_at')->nullable();
            }

            if (! Schema::hasColumn('payment_orders', 'cancelled_by')) {
                $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });

        $hours = max(1, (int) config('payments.order_deadline_hours'));

        DB::table('payment_orders')
            ->whereNull('due_at')
            ->whereIn('status', ['pending', 'awaiting_payment'])
            ->update(['due_at' => DB::raw('DATE_ADD(created_at, INTERVAL '.$hours.' HOUR)')]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('payment_orders')) {
            return;
        }

        if (Schema::hasColumn('payment_orders', 'cancelled_by')) {
            Schema::table('payment_orders', function (Blueprint $table) {
                $table->dropForeign(['cancelled_by']);
            });
        }

        if (Schema::hasColumn('payment_orders', 'due_at')) {
            Schema::table('payment_orders', function (Blueprint $table) {
                $table->dropIndex(['due_at']);
            });
        }

        foreach (['due_at', 'cancelled_at', 'cancelled_by'] as $column) {
            if (Schema::hasColumn('payment_orders', $column)) {
                Schema::table('payment_orders', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
