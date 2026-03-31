<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix nulls and oversized values before applying NOT NULL + UNIQUE
        DB::statement("
            UPDATE employees
            SET phone = CONCAT('0000', LPAD(id, 10, '0'))
            WHERE phone IS NULL OR TRIM(phone) = '' OR CHAR_LENGTH(phone) > 20
        ");

        // Fix any duplicates by appending the id
        DB::statement("
            UPDATE employees e1
            JOIN (
                SELECT MIN(id) AS keep_id, phone
                FROM employees
                GROUP BY phone
                HAVING COUNT(*) > 1
            ) dups ON e1.phone = dups.phone AND e1.id != dups.keep_id
            SET e1.phone = CONCAT(SUBSTRING(e1.phone, 1, 13), '_', e1.id)
        ");

        Schema::table('employees', function (Blueprint $table) {
            $table->string('phone', 20)->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->string('phone', 20)->nullable()->change();
        });
    }
};
