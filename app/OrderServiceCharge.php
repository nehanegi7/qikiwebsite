<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class OrderServiceCharge extends Model
{
    protected $primaryKey = 'osc_id';
	protected $table = 'order_service_charge';
	 
	 
    protected $fillable = array('oid','tid','tl_id','cid','id','grand_total','commission_amount','commission_percentage'
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
	
	
	 
	 
	
	
	
}
 