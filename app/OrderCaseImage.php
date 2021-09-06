<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class OrderCaseImage extends Model
{
    protected $primaryKey = 'oci_id';
	protected $table = 'order_case_image';
	 
	 
    protected $fillable = array('oid','tid','tl_id','id','case_image_type','image' 
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
	
	 
	
	function user(){
		 return $this->belongsTo('App\User','id','id');
	
	}
	
	
	 
	
	   
	
	
	
}
 