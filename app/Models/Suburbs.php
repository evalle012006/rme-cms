<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suburbs extends Model
{
    protected $fillable = [
        'name',
        'state',
        'postcode',
        'longitude',
        'latitude'
    ];
}
