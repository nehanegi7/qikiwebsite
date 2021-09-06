<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class TechnicianIDProof extends Model
{
    protected $primaryKey = 'tip_id';
	protected $table = 'technician_id_proofs';
	
	
    protected $fillable = array('tid','ipt_id','name','number','image_front','image_back');
	
	 
	function technician(){
		 return $this->hasOne('App\Technician','tid');
	
	} 
	
	
	function IdentityProofType(){
		 return $this->hasOne('App\IdentityProofType','ipt_id');
	
	} 
	
	
	
	
	
}
