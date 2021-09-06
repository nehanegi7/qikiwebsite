<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class HomeCategory extends Model
{
    protected $primaryKey = 'hc_id';
	protected $table = 'home_categories';
	
    protected $fillable = array('cid','order_no');
	
	function category(){
		 return $this->belongsTo('App\Category','cid');
	
	}
	
	
}
