<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class OrderCompleted extends Model
{
    protected $primaryKey = 'oc_id';
	protected $table = 'orders_completed';
	 
	 
    protected $fillable = array('oid','tid','tl_id','cid','id','ip','payment_method','comment','is_warrenty','warrenty_msg',
	'other_items_details','other_items_total','grand_total',
	);
	
	
	function order(){
		 return $this->belongsTo('App\Order','oid','oid');
	
	}
	
	
	function technician(){
		 return $this->belongsTo('App\Technician','tid','tid');
	
	}
	
	
	function technician_lead(){
		 return $this->belongsTo('App\TechnicianLead','tl_id','tl_id');
	
	}
	
	
	function category(){
		 return $this->belongsTo('App\Category','cid','cid');
	
	}
	
	function user(){
		 return $this->belongsTo('App\User','id','id');
	
	}
	
	
	 
	
	  
	
	
	function TechnicianLead(){
		 return $this->belongsTo('App\TechnicianLead','tl_id');
	
	}
	
	
	
}
 