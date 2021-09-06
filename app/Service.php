<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Service extends Model
{
    protected $primaryKey = 'sid';
	protected $table = 'services';
	
	
    protected $fillable = array('cid','title','body','cost','weight','status');
	 
	 public function getRouteKeyName() {
        return 'slug';
    }
	
	
	 
	
	function featured_services($total){
		$tours=Service::where('is_featured',1)->orderby('created_at','DESC')->take($total)->get();
		
 		return $tours;
	}
	
	
	
	
}
