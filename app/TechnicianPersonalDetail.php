<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class TechnicianPersonalDetail extends Model
{
    protected $primaryKey = 'tpd_id';
	protected $table = 'technician_personal_details';
	
	
    protected $fillable = array('tid','father_mother_name','gender','dob','permanent_address_house_no','permanent_address_locality','permanent_address_pincode','city','state');
	
	 
	function technician(){
		 return $this->hasOne('App\Technician','tid');
	
	} 
	
	 
	
	
	
	
	
}
