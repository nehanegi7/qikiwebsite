<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class TechnicianLead extends Model
{
    protected $primaryKey = 'tl_id';
	protected $table = 'technician_leads';
	
	
    protected $fillable = array('tid','cid','oid');
	
	 
	function technician(){
		 return $this->belongsTo('App\Technician','tid');
	
	} 
	
	function category(){
		 return $this->belongsTo('App\Category','cid');
	
	} 
	
	 
	
	
	function order(){
		 return $this->belongsTo('App\Order','oid');
	
	} 
	
	
	 
	
	
	
	
	
}
