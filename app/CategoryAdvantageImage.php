<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class CategoryAdvantageImage extends Model
{
    protected $primaryKey = 'id';
	protected $table = 'category_advantage_images';
	 
	 
    protected $fillable = array('cid','image');
	
	
	function category(){
		 return $this->belongsTo('App\Category','cid','cid');
	
	}
	
	
 
	   
	
	
	
}
 