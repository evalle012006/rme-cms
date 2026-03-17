<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyImages extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'property_id',
        'image_path',
        'image_thumb'
    ];
}
