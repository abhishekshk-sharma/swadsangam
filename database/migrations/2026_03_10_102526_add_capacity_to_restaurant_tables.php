<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_tables', function (Blueprint $table) {
            $table->integer('capacity')->default(4)->after('table_number');
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_tables', function (Blueprint $table) {
            $table->dropColumn('capacity');
        });
    }
};
