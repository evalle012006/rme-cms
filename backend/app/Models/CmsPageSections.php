<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsPageSections extends Model
{
    protected $fillable = [
        'cms_page_id',
        'section_name',
        'section_slug',
        'title',
        'subtitle',
        'image',
        'body',
        'other_params'
    ];

    protected $casts = [
        'other_params' => 'array'
    ];

    static $rules = [
        'name' => 'required',
    ];
}
