<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyGallery extends Model
{
    use SoftDeletes;
    protected $appends = ['full_property_image'];

    protected $fillable = [
      'property_id',
      'name'
    ];

    function getFullPropertyImageAttribute() {
        if(!empty($this->name)){
            return absoluteStorageUrl($this->name);
        }
        return null;
    }


}
