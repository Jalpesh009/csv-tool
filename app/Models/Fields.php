<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Store.
 */
class Fields extends Model
{
    protected $table = 'master_fields'; 
    public $timestamps = true; 

    public function game_fields()
    {
        return $this->hasMany('App\Models\GameFields', 'field_id');
    }
    public function store_fields()
    {
        return $this->hasMany('App\Models\StoreField', 'field_id');
    }
}
