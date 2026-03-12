<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreignId('menu_category_id')->nullable()->constrained('menu_categories')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropForeign(['menu_category_id']);
            $table->dropColumn('menu_category_id');
        });
    }
};
