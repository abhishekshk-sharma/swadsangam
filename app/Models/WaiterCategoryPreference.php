<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaiterCategoryPreference extends Model
{
    protected $fillable = ['employee_id', 'menu_category_id'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function menuCategory()
    {
        return $this->belongsTo(MenuCategory::class);
    }
}
