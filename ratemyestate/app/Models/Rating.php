<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rating extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'review_id',
        'rating_conf_id',
        'rate',
        'parent_id'
    ];

    //
    public function reviewQuestion()
    {
        return $this->hasOne(RatingConfiguration::class, 'id', 'rating_conf_id');
    }
}
