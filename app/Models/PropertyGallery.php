<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

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
            return url(Storage::url($this->name));
        }
        return null;
    }


}
