<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OptionMaster extends Model
{
    use SoftDeletes;
    protected $table = 'option_masters';

    protected $fillable = [
      'select_id',
      'key_text',
      'value_text'
    ];

    public function selectCategory()
    {
        return $this->belongsTo(SelectMaster::class, 'select_id', 'id');
    }

}
