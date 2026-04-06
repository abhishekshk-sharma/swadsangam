<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waiter_category_sort_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('menu_category_id');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['employee_id', 'menu_category_id']);
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('menu_category_id')->references('id')->on('menu_categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waiter_category_sort_orders');
    }
};
