<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gst_slabs', function (Blueprint $table) {
            $table->id();
            $table->string('name');                        // e.g. "5% GST"
            $table->decimal('total_rate', 5, 2);           // e.g. 5.00
            $table->decimal('cgst_rate', 5, 2);            // e.g. 2.50
            $table->decimal('sgst_rate', 5, 2);            // e.g. 2.50
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed the default 5% slab
        DB::table('gst_slabs')->insert([
            'name'       => '5% GST (2.5% CGST + 2.5% SGST)',
            'total_rate' => 5.00,
            'cgst_rate'  => 2.50,
            'sgst_rate'  => 2.50,
            'is_active'  => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('gst_slabs');
    }
};
