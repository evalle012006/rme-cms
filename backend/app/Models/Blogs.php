<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Blogs extends Model
{
    use SoftDeletes, Sluggable;

    protected $appends = ['full_feature_image'];
    static $rules = [
        'title' => 'required',
        'feature_image' => 'nullable',
        'feature_thumb_image' => 'nullable',
        'description' => 'required',
        'blog_date' => 'required',
        'status' => 'required',
        'seo_title' => 'nullable|string',
        'seo_description' => 'nullable|string',
        'seo_keywords' => 'nullable|string',
        'category_id' => 'required',
        'author_name' => 'required'
    ];
    protected $fillable = [
        'title',
        'slug',
        'feature_image',
        'feature_thumb_image',
        'blog_date',
        'description',
        'status',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'category_id',
        'created_by',
        'author_name'
    ];

    protected $dates = [
        'blog_date'
    ];

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    function getFullFeatureImageAttribute()
    {
        if (!empty($this->feature_image)) {
            return absoluteStorageUrl($this->feature_image);
        }
        return null;
    }

    public function blogCategories()
    {
//        return $this->belongsTo(BlogCategory::class, 'category_id', 'id');
//        return $this->hasMany(BlogAvailableCategory::class, 'blog_category_id', 'id');
        return $this->belongsToMany(BlogCategory::class,
            BlogAvailableCategory::class,
            'blog_id',
            'blog_category_id',
            'id',
            'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
