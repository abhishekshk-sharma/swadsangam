<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_tables', function (Blueprint $table) {
            $table->dropUnique('restaurant_tables_table_number_unique');
            $table->unique(['tenant_id', 'table_number'], 'restaurant_tables_tenant_table_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_tables', function (Blueprint $table) {
            $table->dropUnique('restaurant_tables_tenant_table_number_unique');
            $table->unique('table_number');
        });
    }
};
