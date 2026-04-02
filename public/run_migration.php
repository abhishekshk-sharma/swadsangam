<?php
// One-time fix: add daily_number column to orders table on production
// DELETE THIS FILE after running it once

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (!Schema::hasColumn('orders', 'daily_number')) {
    Schema::table('orders', function (Blueprint $table) {
        $table->unsignedInteger('daily_number')->nullable()->after('id');
    });
    echo "✅ daily_number column added successfully.\n";
} else {
    echo "ℹ️ daily_number column already exists.\n";
}
