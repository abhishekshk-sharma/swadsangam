<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['emp_id', 'username', 'email_verified_at', 'department', 'position']);
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('emp_id')->nullable();
            $table->string('username')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('department')->nullable();
            $table->string('position')->nullable();
        });
    }
};
