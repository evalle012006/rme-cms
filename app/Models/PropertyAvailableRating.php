<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyAvailableRating extends Model
{
    protected $fillable = [
        'property_category_id',
        'rating_conf_id',
        'order'
    ];

    public $timestamps = false;

}
