<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Fix restaurant_tables unique: (tenant_id, table_number) → (tenant_id, branch_id, table_number)
        Schema::table('restaurant_tables', function (Blueprint $table) {
            $table->dropUnique('restaurant_tables_tenant_table_number_unique');
            $table->unique(['tenant_id', 'branch_id', 'table_number'], 'restaurant_tables_tenant_branch_table_number_unique');
        });

        // 2. Add branch_id to order_items
        if (!Schema::hasColumn('order_items', 'branch_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('tenant_id');
            });
        }

        // 3. Add branch_id to table_categories
        if (!Schema::hasColumn('table_categories', 'branch_id')) {
            Schema::table('table_categories', function (Blueprint $table) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('tenant_id');
            });
        }

        // 4. Backfill order_items.branch_id from their parent order
        \DB::statement('UPDATE order_items oi JOIN orders o ON oi.order_id = o.id SET oi.branch_id = o.branch_id WHERE oi.branch_id IS NULL');
    }

    public function down(): void
    {
        Schema::table('restaurant_tables', function (Blueprint $table) {
            $table->dropUnique('restaurant_tables_tenant_branch_table_number_unique');
            $table->unique(['tenant_id', 'table_number'], 'restaurant_tables_tenant_table_number_unique');
        });

        if (Schema::hasColumn('order_items', 'branch_id')) {
            Schema::table('order_items', fn(Blueprint $t) => $t->dropColumn('branch_id'));
        }

        if (Schema::hasColumn('table_categories', 'branch_id')) {
            Schema::table('table_categories', fn(Blueprint $t) => $t->dropColumn('branch_id'));
        }
    }
};
