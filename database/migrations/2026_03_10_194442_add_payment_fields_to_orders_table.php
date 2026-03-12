<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_mode')->nullable()->after('total_amount');
            $table->timestamp('paid_at')->nullable()->after('payment_mode');
        });

        // Update status enum to include 'closed'
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'preparing', 'ready', 'completed', 'processing', 'closed') DEFAULT 'pending'");
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_mode', 'paid_at']);
        });

        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'preparing', 'ready', 'completed') DEFAULT 'pending'");
    }
};
