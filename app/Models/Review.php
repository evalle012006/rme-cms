<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use CyrildeWit\EloquentViewable\Viewable;
use CyrildeWit\EloquentViewable\Contracts\Viewable as ViewableContract;
use Illuminate\Support\Facades\Storage;

class Review extends Model implements ViewableContract
{
    use SoftDeletes, Viewable, Sluggable;

    protected $appends = ['full_review_image'];

    static $rules = [
        'title' => 'required',
        'user_id' => 'required',
        'property_id' => 'required',
        'category_id' => 'required',
        'recommendation' => 'required',
        'comment' => 'nullable',
        'buyer_type' => 'required',
        'status' => 'required',
        'state' => 'required',
        'suburb' => 'required',
        'post_code' => 'required',
        'year_of_completion' => 'nullable',
        'parent_id' => 'nullable',
    ];
    static $rules_admin = [
        'title' => 'required',
//        'user_id' => 'required',
        'property_id' => 'required',
        'recommendation' => 'required',
        'comment' => 'nullable',
        'buyer_type' => 'required',
        'status' => 'required',
        'state' => 'required',
        'suburb' => 'required',
        'post_code' => 'required',
        'year_of_completion' => 'nullable',
        'parent_id' => 'nullable',
    ];
    protected $fillable = [
        'property_id',
        'user_id',
        'title',
        'comment',
        'buyer_type',
        'recommendation',
        'review_image',
        'review_docs',
        'state',
        'post_code',
        'suburb',
        'year_of_completion',
        'status',
        'parent_id',
        'category_id'
    ];

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    protected $casts = [
        'recommendation' => 'boolean'
    ];

    function getFullReviewImageAttribute()
    {
        if (!empty($this->review_image)) {
            return url(Storage::url($this->review_image));
        }
        return null;
    }

    public function property()
    {
        return $this->belongsTo(Properties::class, 'property_id', 'id');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(PropertyCategory::class, 'category_id', 'id');
    }

    public function stateName()
    {
        return $this->belongsTo(ReviewStates::class, 'state', 'id');
    }

    public function buyerTypeRow()
    {
        return $this->belongsTo(OptionMaster::class, 'buyer_type', 'id');
    }

    public function reviewTags()
    {
        return $this->belongsToMany(
            Tags::class,
            ReviewTags::class,
            'review_id',
            'tag_id',
            'id',
            'id'
        );
    }


    public function ratings()
    {
        return $this->hasMany(Rating::class, 'review_id', 'id')->with('reviewQuestion');
    }
}
