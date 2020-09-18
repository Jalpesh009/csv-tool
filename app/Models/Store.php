<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Store.
 */
class Store extends Model
{
    protected $table = 'store'; 
    protected $fillable = [ 'store_name', 'indentifier','store_description', 'city', 'postal_code', 'logo', 'contact_email', 'contact_person', 'secondary_email', 'secondary_person', 'contact_number', 'created_at', 'updated_at'  ];
    public $timestamps = true; 
	
	public function store_games()
    {
        return $this->hasMany('App\Models\StoreGame', 'store_id');
    }
}
