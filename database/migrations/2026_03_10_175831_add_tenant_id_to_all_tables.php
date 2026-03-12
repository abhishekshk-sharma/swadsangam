<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add tenant_id to all tables
        $tables = [
            'restaurant_tables',
            'menu_items',
            'orders',
            'order_items',
            'table_categories',
            'menu_categories'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->onDelete('cascade');
                $table->index('tenant_id');
            });
        }
    }

    public function down(): void
    {
        $tables = ['restaurant_tables', 'menu_items', 'orders', 'order_items', 'table_categories', 'menu_categories'];
        
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }
    }
};
