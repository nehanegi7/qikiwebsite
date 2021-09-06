<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Order extends Model
{
    protected $primaryKey = 'oid';
	protected $table = 'orders';
	 
	 
    protected $fillable = array('cid','id','city_id',
	'shipping_same_as_billing','invoice_no','transaction_id','invoice_date','title',
	'qty','cost','tax','sub_total','min_amount_for_user','final_subtotal','gst_amount','grand_total','order_comments',
	'coupon_applied_id','coupon_applied_discount','ip','payment_method',
	'name_title','name','phone','flat_building','location_field','date_of_service','coupon','order_pass_code',
	'billing_name','billing_email','billing_address','billing_phone','billing_gst_no','billing_city','billing_state','billing_zip','billing_country_code',
    'shipping_name','shipping_email','shipping_address','shipping_phone','shipping_city','shipping_state','shipping_zip','shipping_country_code',
	);


	function min_amount_for_technician($cid){

		$min_amount_for_technician=0;

		$category=\App\Category::find($cid);

		if($category->parent_cid==0){
			$min_amount_for_technician=$category->min_amount_for_technician;	
		}else{

			$parent_category=\App\Category::find($category->parent_cid);
        
        	$min_amount_for_technician=$parent_category->min_amount_for_technician;	


		}
 		return $min_amount_for_technician;

	}
	
	
	function user(){
		 return $this->belongsTo('App\User','id','id');
	
	}
	
	
	function CouponUsed(){
		 return $this->belongsTo('App\CouponUsed','oid','oid');
	
	}
	
	
	function coupon(){
		 return $this->belongsTo('App\Coupon','coupon_applied_id','cid');
	
	}
	
	
	
	function category(){
		 return $this->belongsTo('App\Category','cid','cid');
	
	}
	
	function service(){
		 return $this->belongsTo('App\Service','sid','sid');
	
	}
	
	 
	 function OrderCompleted(){
		 return $this->belongsTo('App\OrderCompleted','oid','oid');
	
	}
	
	
	function order_status(){
		 return $this->belongsToMany('App\OrderStatus','order_status_changes','oid','os_id')->oldest();
	
	}
	
	
	function order_status_changes(){
		 return $this->hasMany('App\OrderStatusChange','os_id');
	
	}
	
	
	function TechnicianLead(){
		 return $this->belongsTo('App\TechnicianLead','tl_id');
	
	}


	
	
	
}
 