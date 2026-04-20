<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SelectMaster extends Model
{
    use SoftDeletes;
    protected $table = 'select_masters';

    protected $fillable = [
        'name',
        'title'
    ];
    public function optionSelect()
    {
        return $this->hasMany(OptionMaster::class, 'select_id', 'id');
    }
}
