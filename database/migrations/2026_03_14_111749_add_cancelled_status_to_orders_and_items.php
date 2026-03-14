<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','preparing','ready','served','paid','cancelled') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE order_items MODIFY COLUMN status ENUM('pending','prepared','cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','preparing','ready','served','paid') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE order_items MODIFY COLUMN status ENUM('pending','prepared') NOT NULL DEFAULT 'pending'");
    }
};
