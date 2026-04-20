<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyAvailableCategory extends Model
{
    protected $table='property_available_categories';

    public $timestamps = false;
    protected $fillable = [
        'property_id',
        'property_category_id'
    ];
}
