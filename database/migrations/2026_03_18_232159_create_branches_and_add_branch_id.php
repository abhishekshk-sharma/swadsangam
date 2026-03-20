<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add branch_id nullable to all relevant tables
        foreach (['employees', 'restaurant_tables', 'menu_items', 'menu_categories', 'orders', 'cash_handovers'] as $tbl) {
            if (Schema::hasTable($tbl) && !Schema::hasColumn($tbl, 'branch_id')) {
                Schema::table($tbl, function (Blueprint $table) {
                    $table->unsignedBigInteger('branch_id')->nullable()->after('tenant_id');
                });
            }
        }

        // Alter employees role enum to include manager
        DB::statement("ALTER TABLE employees MODIFY COLUMN role ENUM('waiter','chef','cashier','manager') NOT NULL");
    }

    public function down(): void
    {
        foreach (['employees', 'restaurant_tables', 'menu_items', 'menu_categories', 'orders', 'cash_handovers'] as $tbl) {
            if (Schema::hasTable($tbl) && Schema::hasColumn($tbl, 'branch_id')) {
                Schema::table($tbl, fn(Blueprint $t) => $t->dropColumn('branch_id'));
            }
        }
        Schema::dropIfExists('branches');
        DB::statement("ALTER TABLE employees MODIFY COLUMN role ENUM('waiter','chef','cashier') NOT NULL");
    }
};
