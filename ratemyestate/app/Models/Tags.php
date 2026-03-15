<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tags extends Model
{
    use SoftDeletes;

    static $rules = [
        'name' => 'required|unique:tags,name',
        'type' => 'required',
        'is_active' => 'required'
    ];

    static $rulesUpdate = [
        'name' => 'required',
        'type' => 'required',
        'is_active' => 'required'
    ];
    protected $fillable = [
        'name','type', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];


}
