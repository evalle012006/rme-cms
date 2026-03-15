<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsPages extends Model
{
    protected $fillable = [
        'name'
    ];

    static $rules = [
        'name' => 'required',
    ];

    public function pageSections()
    {
        return $this->hasMany(CmsPageSections::class, 'cms_page_id', 'id');
    }
}
