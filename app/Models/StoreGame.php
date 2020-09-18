<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Store.
 */
class StoreGame  extends Model
{
    protected $table = 'store_games'; 
    protected $fillable = [ 'store_id', 'game_id', 'created_at', 'updated_at'];
    public $timestamps = true; 

    // public function game_fields()
    // {
    //     return $this->hasOne('App\Models\GameFields');
    // }
	 public function store()
    {
        return $this->belongsTo('App\Models\Store', 'store_id');

    }
}
