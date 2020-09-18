<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Store.
 */
class StoreField  extends Model
{
    protected $table = 'store_fields'; 
    protected $fillable = [ 'store_id', 'field_id', 'field_personal_name', 'field_order'];
    public $timestamps = true; 

    public function master_fields()
    {
        return $this->belongsTo('App\Models\Fields', 'field_id' );

    }
}
