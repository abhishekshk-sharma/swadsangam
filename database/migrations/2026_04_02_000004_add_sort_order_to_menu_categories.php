<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_categories', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('description');
        });

        // Initialise existing rows with sequential order
        DB::statement('SET @row := 0');
        DB::statement('UPDATE menu_categories SET sort_order = (@row := @row + 1) ORDER BY id');
    }

    public function down(): void
    {
        Schema::table('menu_categories', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
