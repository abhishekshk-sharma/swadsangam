<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, rename fullname to name
        Schema::table('employees', function (Blueprint $table) {
            $table->renameColumn('fullname', 'name');
        });
        
        // Then modify and add columns
        Schema::table('employees', function (Blueprint $table) {
            // Modify existing role column
            DB::statement("ALTER TABLE employees MODIFY COLUMN role ENUM('waiter', 'chef', 'cashier') NOT NULL");
            
            // Drop status column and add is_active
            $table->dropColumn('status');
            $table->boolean('is_active')->default(true)->after('role');
            
            // Add new columns
            $table->string('phone')->nullable()->after('email');
            $table->string('telegram_chat_id')->nullable()->after('is_active');
            $table->string('telegram_username')->nullable()->after('telegram_chat_id');
            $table->rememberToken()->after('telegram_username');
            
            // Email remains globally unique (no changes needed)
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Revert role column
            DB::statement("ALTER TABLE employees MODIFY COLUMN role ENUM('admin', 'employee') NOT NULL DEFAULT 'employee'");
            
            // Remove new columns
            $table->dropColumn(['phone', 'telegram_chat_id', 'telegram_username', 'is_active', 'remember_token']);
            
            // Add back status column
            $table->enum('status', ['active', 'inactive'])->default('active');
            
            // Rename name back to fullname
            $table->renameColumn('name', 'fullname');
            
            // Email unique constraint remains unchanged
        });
    }
};
