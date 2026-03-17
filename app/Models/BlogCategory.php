<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class BlogCategory extends Model
{
    use SoftDeletes,Sluggable;
    static $blog_category_storage_path = 'blog-category-image/';
    protected $appends = ['full_blog_category_image'];
    static $rules = [
        'name' => 'required',
        'image'=>'nullable',
        'seo_title' => 'nullable',
        'seo_description' => 'nullable',
        'seo_keywords' => 'nullable'
    ];
    protected $fillable = [
        'name',
        'image',
        'seo_title',
        'seo_description',
        'seo_keywords'
    ];

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }
    function getFullBlogCategoryImageAttribute() {
        if(!empty($this->image)){
            return url(Storage::url($this->image));
        }
        return null;
    }
}
