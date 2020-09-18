<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
  
class Game extends Model
{     
 	protected $table = 'games'; 
    protected $fillable = [ 'game_name', 'status', 'created_at', 'updated_at'  ];
    public $timestamps = true; 
     
}
