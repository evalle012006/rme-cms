<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class PropertyCategory extends Model
{
    use SoftDeletes, Sluggable;

    static $rules = [
        'name' => 'required',
        'image' => 'required',
        'description' => 'required',
        'seo_title' => 'nullable',
        'seo_description' => 'nullable',
        'seo_keywords' => 'nullable',
        'question' => 'nullable'
    ];

    protected $fillable = [
        'name',
        'slug',
        'image',
        'question',
        'description',
        'seo_title',
        'seo_description',
        'seo_keywords'
    ];
    protected $appends = ['full_cat_image'];

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    function getFullCatImageAttribute()
    {
        if (!empty($this->image)) {
            return url(Storage::url($this->image));
        }
        return null;
    }

    public function propertyAllowedRating()
    {
        return $this->belongsToMany(RatingConfiguration::class,
            PropertyAvailableRating::class,
            'property_category_id',
            'rating_conf_id',
            'id',
            'id')->withPivot('order');
    }
}
