<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class OrderHardCopyInvoice extends Model
{
    protected $primaryKey = 'oh_id';
	protected $table = 'order_hardcopy_invoice';
	 
	 
    protected $fillable = array('oid','id','image' 
	);
	
	
	function order(){
		 return $this->belongsTo('App\Order','oid','oid');
	
	}
	
	
 
	 
	
	function user(){
		 return $this->belongsTo('App\User','id','id');
	
	}
	
	
	 
	
	   
	
	
	
}
 