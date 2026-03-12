<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('emp_id')->unique();
            $table->string('fullname');
            $table->string("username");            
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role',['admin','employee'])->default('employee');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string("department");
            $table->string("position");
            // $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
