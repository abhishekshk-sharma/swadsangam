<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix nulls and oversized values
        DB::statement("
            UPDATE admins
            SET phone = CONCAT('0000', LPAD(id, 10, '0'))
            WHERE phone IS NULL OR TRIM(phone) = '' OR CHAR_LENGTH(phone) > 20
        ");

        // Fix duplicates
        DB::statement("
            UPDATE admins a1
            JOIN (
                SELECT MIN(id) AS keep_id, phone
                FROM admins
                GROUP BY phone
                HAVING COUNT(*) > 1
            ) dups ON a1.phone = dups.phone AND a1.id != dups.keep_id
            SET a1.phone = CONCAT(SUBSTRING(a1.phone, 1, 13), '_', a1.id)
        ");

        Schema::table('admins', function (Blueprint $table) {
            $table->string('phone', 20)->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->string('phone', 20)->nullable()->change();
        });
    }
};
