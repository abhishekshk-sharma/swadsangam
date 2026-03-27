<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignId('gst_slab_id')->nullable()->constrained('gst_slabs')->nullOnDelete()->after('status');
            // 'included' = GST already in price, 'excluded' = add on top at billing
            $table->enum('gst_mode', ['included', 'excluded'])->nullable()->after('gst_slab_id');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['gst_slab_id']);
            $table->dropColumn(['gst_slab_id', 'gst_mode']);
        });
    }
};
