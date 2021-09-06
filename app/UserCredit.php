<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class UserCredit extends Model
{
    protected $primaryKey = 'uc_id';
	protected $table = 'user_credits';
	
    protected $fillable = array('uid','points','total_points','recharge_amount','total_balance','payment_method','transaction_id','oid','tl_id','is_recharged_by_admin','is_hold','status','created_at');
	
	function user(){
		 return $this->belongsTo('App\User','uid','id');
	} 
	
	
}