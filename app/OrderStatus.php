<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class OrderStatus extends Model
{
    protected $primaryKey = 'os_id';
	protected $table = 'order_status';
	
    protected $fillable = array('name');
	
	
	
	
}
 