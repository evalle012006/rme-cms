<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class BlogAvailableCategory extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'blog_id',
        'blog_category_id'
    ];
}
