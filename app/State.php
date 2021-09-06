<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class State extends Model
{
    protected $primaryKey = 'sid';
	protected $table = 'state';
	
	
    protected $fillable = array('name');
	 
}
