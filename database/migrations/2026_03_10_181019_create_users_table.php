<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('role')->default('staff'); // super_admin, admin, manager, waiter, chef, staff
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
            
            $table->unique(['tenant_id', 'email']); // Email unique per tenant
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
