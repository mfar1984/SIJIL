<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Packages sold through CHIP Collect and assignable in User Management.
 *
 * This table is the only source of the amount charged. Prices are integer sen
 * (MYR cents). The eight rows are inserted by code, so a re-run inserts nothing
 * and never overwrites a price an administrator has since edited.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('packages')) {
            Schema::create('packages', function (Blueprint $table) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';

                $table->bigIncrements('id');
                $table->string('code', 32)->unique();
                $table->string('name', 100);
                $table->enum('category', ['normal', 'government']);
                $table->unsignedTinyInteger('duration_months');
                $table->unsignedInteger('price_cents');
                $table->char('currency', 3)->default('MYR');
                $table->boolean('is_active')->default(true);
                $table->unsignedSmallInteger('sort_order');
                $table->timestamps();
            });
        }

        $now = now();
        $rows = [];
        $sort = 0;

        foreach ([
            'normal' => ['Normal', [1 => 4000, 3 => 6500, 6 => 12500, 12 => 25000]],
            'government' => ['Government', [1 => 3000, 3 => 5000, 6 => 10000, 12 => 20000]],
        ] as $category => [$label, $prices]) {
            foreach ($prices as $months => $cents) {
                $rows[] = [
                    'code' => sprintf('%s-%02dm', $category, $months),
                    'name' => $label.' '.match ($months) {
                        1 => '1 Month',
                        12 => '1 Year',
                        default => $months.' Months',
                    },
                    'category' => $category,
                    'duration_months' => $months,
                    'price_cents' => $cents,
                    'currency' => 'MYR',
                    'is_active' => true,
                    'sort_order' => ++$sort,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('packages')->insertOrIgnore($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
