<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class TechnicianCurrentAddress extends Model
{
    protected $primaryKey = 'tca_id';
	protected $table = 'technician_current_address';
	
	
    protected $fillable = array('tid','permanent_address_house_no','permanent_address_locality','permanent_address_pincode','city','state');
	
	 
	function technician(){
		 return $this->hasOne('App\Technician','tid');
	
	} 
	
	 
	
	
	
	
	
}
