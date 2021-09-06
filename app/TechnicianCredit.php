<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class TechnicianCredit extends Model
{
    protected $primaryKey = 'tc_id';
	protected $table = 'technician_credits';
	
	
    protected $fillable = array('tid','recharge_amount','total_balance','payment_method','transaction_id','oid','tl_id','is_recharged_by_admin','is_hold','status','created_at');
	
	 
	function technician(){
		 return $this->belongsTo('App\Technician','tid','tid');
	
	} 
	
	
	
	
	
}
