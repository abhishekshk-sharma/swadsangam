<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class RestaurantTable extends Model
{
    use BelongsToTenant;
    protected $fillable = ['table_number', 'capacity', 'qr_code', 'is_occupied', 'category_id'];

    public function category()
    {
        return $this->belongsTo(TableCategory::class, 'category_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'table_id');
    }
}
