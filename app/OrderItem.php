<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class OrderItem extends Model
{
    protected $primaryKey = 'oi_id';
	protected $table = 'order_items';
	 
	 
    protected $fillable = array('oid','sid','cid','id','title','cost');
	
	
	function order(){
		 return $this->belongsTo('App\Order','oid','oid');
	
	}
	
	function user(){
		 return $this->belongsTo('App\User','id','id');
	
	}
	
	function service(){
		 return $this->belongsTo('App\Service','sid','sid');
	
	}
	
	function category(){
		 return $this->belongsTo('App\Category','cid','cid');
	
	}
	
	 
	
	function order_status(){
		 return $this->belongsTo('App\OrderStatus','os_id');
	
	}
	
	
	
}
 