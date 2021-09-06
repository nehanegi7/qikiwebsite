<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $primaryKey = 'aid';
	protected $table = 'admin';
	
    protected $fillable = array('name','email','photo','username','password');
	 
	 
}

