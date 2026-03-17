<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewStates extends Model
{
    protected $fillable = [
        'name',
        'abbreviation',
    ];
}
