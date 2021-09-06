<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class City extends Model
{
    protected $primaryKey = 'city_id';
	protected $table = 'city';
	
	
    protected $fillable = array('title','slug','state_id');

	
}
