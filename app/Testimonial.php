<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Testimonial extends Model
{
    protected $primaryKey = 'test_id';
	protected $table = 'testimonials';
	
	
    protected $fillable = array('name','body','image','reviewed_on','tid','rating','weight','status');
	
	public function getRouteKeyName() {
        return 'slug';
    }
	
	
	function technician(){
		 return $this->belongsTo('App\Technician','tid');
	
	}
	
	
}
