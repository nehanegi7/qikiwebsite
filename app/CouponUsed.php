<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class CouponUsed extends Model
{
    protected $primaryKey = 'cu_id';
	protected $table = 'coupon_used';
	
	
    protected $fillable = array('cid','id','oid','discount_amount');
	 
	
	 
	
	
	function coupon(){
		 return $this->belongsTo('App\Coupon','cid','cid');
	
	}
	
	
	
	
}
