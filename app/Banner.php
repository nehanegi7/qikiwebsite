<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Banner extends Model
{
    protected $primaryKey = 'bid';
	protected $table = 'banners';
	
    protected $fillable = array('image','order_no','link');
	
 	
	function next_banner(){
 		
		 $home_banner_total=1;
		 
		 $banners=Banner::orderby('order_no','ASC')->get();
		 if($banners->count()>0){

		
		 $home_banner_total=count($banners) ;
		 
		 
		 session_start();
		 	//	 session_destroy();
		 
		 if(isset($_SESSION['i'])){
		 	$i=$_SESSION['i'];
		 }else{
		  $_SESSION['i']=0;
		  $i=0;
		 }
		 
		 //$i = isset($_SESSION['i']) ?  : 0;
		
		 
		if($i>=$home_banner_total){
			 $_SESSION['i']=0;
		}
			 
		 
		 // dd($home_banner_no);
		 $i=$_SESSION['i'];
		 
		 $banner= $banners[$i]->image;
		  $link= $banners[$i]->link;
		   ++$i;
		
		$_SESSION['i'] = $i;
		 $banner_path=url('/uploads/banner/'.$banner);
		 
		 return ['banner_path'=>$banner_path,'link'=>$link];
		 }
	}
	

	function fetch_banner(){
 	
		$banners=Banner::orderby('order_no','ASC')->get();
		
		//$banner_path=url('/uploads/banner/'.$banner);
		
		//return ['banner_path'=>$banner_path,'link'=>$link];
		return $banners;

   	}
}
