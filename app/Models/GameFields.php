<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Store.
 */
class GameFields  extends Model
{
    protected $table = 'game_fields'; 
    protected $fillable = [ 'game_id', 'field_id', 'field_value' ];
    public $timestamps = true;   
 
   	public function master_fields()
    {
        return $this->belongsTo('App\Models\Fields', 'field_id' );

    }
    // public function store_games()
    // {
    //     return $this->hasOne('App\Phone');
    // }
 	   
}
