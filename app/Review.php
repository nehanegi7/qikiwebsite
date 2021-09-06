<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use DB;

class Review extends Model
{
    protected $primaryKey = 'rev_id';
	protected $table = 'reviews';
	
	
    protected $fillable = array('cid','id','name','rating','review','ip','status');
	 
	 
	
	function category(){
		 return $this->belongsTo('App\Category','cid');
	
	}
	
	
	
	function category_average_rating($cid){
		  $all_ratings=DB::table('reviews')->where('cid',$cid)->where('status',1)->pluck('rating');
		  
		  $count_ratings=count($all_ratings);
		  
		  if($count_ratings){
		   
			  $total_ratings=0;
			  foreach($all_ratings as $rating){
				$total_ratings+=$rating;
			  }
			  
			  $average_rating=ceil($total_ratings/$count_ratings);
			  
			  return $average_rating;
			  
		  }else{
		  	return 0;
		  }
		  
	}
	
	
}
