<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Properties extends Model
{
    use SoftDeletes, Sluggable;

    static $rules = [
        'project_name' => 'nullable',
        'brand' => 'nullable',
        'developer_name' => 'nullable',
//        'developer_name' => 'required',
        'email' => 'nullable',
        'phone' => 'nullable',
        'website_url' => 'nullable',
        'description' => 'nullable',
        'address' => 'required',
        'country' => 'required',
        'state' => 'required',
        'city' => 'required',
        'lat' => 'nullable',
        'long' => 'nullable',
        'post_code' => 'nullable',
        'suburb' => 'nullable',
        'status' => 'required',
        'property_categories' => 'required',
        'logo' => 'nullable',
        'image_to_add' => 'nullable|array',
        'image_to_remove' => 'nullable|array'
    ];


    protected $fillable = [
        'developer_name',
        'brand',
        'project_name',
        'slug',
        'email',
        'phone',
        'website_url',
        'description',
        'address',
        'country',
        'state',
        'city',
        'post_code',
        'lat',
        'long',
        'year_of_completion',
        'buyer_type',
        'is_from',
        'created_by',
        'owner',
        'status',
        'suburb',
        'logo'
    ];
    protected $appends = ['full_property_logo'];

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'project_name'
            ]
        ];
    }

    function getFullPropertyLogoAttribute()
    {
        if (!empty($this->logo) && $this->logo) {
            return absoluteStorageUrl($this->logo);
        }
        return null;
    }

    public function propertyAllowedCategory()
    {

        return $this->belongsToMany(PropertyCategory::class,
            PropertyAvailableCategory::class,
            'property_id',
            'property_category_id',
            'id',
            'id'
        );
    }

    public function company()
    {
        return $this->belongsTo(CompanyDetails::class, 'developer_name', 'id');
    }

    public function gallery()
    {
        return $this->hasMany(PropertyGallery::class, 'property_id', 'id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'property_id', 'id');
    }

    public function approvedReviews()
    {
        return $this->hasMany(Review::class, 'property_id', 'id')->where('status', 1);
    }


}
