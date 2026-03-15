<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class RatingConfiguration extends Model
{
    use SoftDeletes;
    protected $appends = ['full_ratingconf_icon'];
    protected $fillable = [
        'name',
        'rating_icon',
        'description'
    ];
    function getFullRatingconfIconAttribute() {
        if(!empty($this->rating_icon)){
            return url(Storage::url($this->rating_icon));
        }
        return null;
    }
}
