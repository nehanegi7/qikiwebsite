<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class OrderStatusChange extends Model
{
    protected $primaryKey = 'osc_id';
	
	protected $table = 'order_status_changes';
	
    protected $fillable = array('oid','os_id','changes_by','changes_by_id','reschedule_date','cancel_reason');
	
	
	function order(){
		 return $this->belongsTo('App\Order','oid');
	
	}
	
	
	function order_status(){
		 return $this->belongsTo('App\OrderStatus','os_id');
	
	}
	
	
	function technician(){
		 return $this->belongsTo('App\User','changes_by_id','tid');
	
	}
	
	function user(){
		 return $this->belongsTo('App\User','changes_by_id','id');
	
	}
	
	
}
 