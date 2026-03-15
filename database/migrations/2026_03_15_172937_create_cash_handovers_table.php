<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cash_handovers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('cashier_id');   // Employee id
            $table->date('handover_date');

            // Denomination counts
            $table->unsignedInteger('denom_1')->default(0);
            $table->unsignedInteger('denom_2')->default(0);
            $table->unsignedInteger('denom_5')->default(0);
            $table->unsignedInteger('denom_10')->default(0);
            $table->unsignedInteger('denom_20')->default(0);
            $table->unsignedInteger('denom_50')->default(0);
            $table->unsignedInteger('denom_100')->default(0);
            $table->unsignedInteger('denom_200')->default(0);
            $table->unsignedInteger('denom_500')->default(0);

            $table->decimal('total_cash', 10, 2)->default(0);
            $table->text('notes')->nullable();

            // Status: pending | approved
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();  // Admin id
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'cashier_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_handovers');
    }
};
