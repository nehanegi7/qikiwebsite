<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class TechnicianCategory  extends Model
{
    protected $primaryKey = 't_cid';
	protected $table = 'technician_categories';
	
	
    protected $fillable = array('tid','cid');
	
	 
	function technician(){
		 return $this->hasOne('App\Technician','tid');
	
	} 

	function technicians(){
		return $this->belongsTo('App\Technician','tid');
   
   	} 

	function categories(){
		 return $this->belongsTo('App\Category','cid');
	
	} 
	
}
