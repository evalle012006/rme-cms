<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        if (!empty($this->rating_icon)) {
            return absoluteStorageUrl($this->rating_icon);
        }
        return null;
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'rating_conf_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($config) {
            if ($config->ratings()->exists()) {
                throw new \Exception('Cannot delete a rating category with existing reviews attached.');
            }
        });
    }
}