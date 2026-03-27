<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GstSlab extends Model
{
    protected $fillable = ['name', 'total_rate', 'cgst_rate', 'sgst_rate', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
