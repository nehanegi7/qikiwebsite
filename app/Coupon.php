<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Coupon extends Model
{
    protected $primaryKey = 'cid';
	protected $table = 'coupons';
	
	
    protected $fillable = array('code','name','description','coupon_type','discount','minimum_total_amount','date_start',
								'date_end','uses_per_coupon','uses_per_customer','status','created_by');
	 
	
	
	/*	
	uses_per_customer : Same user can use : one time | multile time

	uses_per_coupon  :  This coupon can use : 30 times

	note : 0 means unlimited
	*/
		
	function coupons_available(){
		$coupons=Coupon::where('status',1)->get();
		
		return $coupons;
	}

	
}
