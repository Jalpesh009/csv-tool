<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Store.
 */
class DefaultFields extends Model
{
    protected $table = 'default_fields'; 
    protected $fillable = [ 'field_name', 'field_slug', 'created_at', 'updated_at'  ];
    public $timestamps = true; 

     
}
