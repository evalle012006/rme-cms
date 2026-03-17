<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewTags extends Model
{
    protected $fillable = [
        'review_id',
        'tag_id',
        'parent_id'
    ];
}
