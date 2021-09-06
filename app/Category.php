<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Category extends Model
{
    protected $primaryKey = 'cid';
	protected $table = 'category';
	
    protected $fillable = array('title','banner','slug','icon','icon_for_mobile_view','image','featured','parent_cid','min_amount_for_user',
	'highlight_message','advantage_message','how_it_works','about','pop_up_advantage_message','bottom_info',
	'status','meta_keyword','meta_description');
	
	public function getRouteKeyName() {
        return 'slug';
    }
	
	
	function sub_categories($parent_cid,$total){
		
		$sub_categories=Category::where('parent_cid',$parent_cid)->where('status',1)->take($total)->get();
		return $sub_categories;
		
	}
	
	
	
	 function CategoryAdvantageImage(){

	   return $this->hasMany('App\CategoryAdvantageImage','cid');
  
	 }
	 
	 
	
}
