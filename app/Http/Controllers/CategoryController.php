<?php 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

use App\Http\Requests;
use App\Page;
use App\Category;
use App\City;
use App\Service;
use App\Banner;
use App\TechnicianCategory;
use App\Review;

use DB;

use Response;

class CategoryController extends Controller
{
     
	
	
	 function search_get_categories(request $request){
		$keyword=$request->keyword;
		
		 
		 $categories = DB::table('category')
  				   
				   ->where('title', 'like', '%'.$keyword.'%')          
				 /*  ->orWhere('body', 'like', '%'.$keyword.'%')			*/	   
				   
				  ->where('parent_cid','!=',0)->where('status',1)  
				   
                 ->orderby('created_at','DESC')->paginate(10);
				 
		if(count($categories)>0){
			echo '<ul id="country-list">';		 
			foreach($categories as $category){
				echo '<li>';
				
				if($category->icon!=''){
					echo  '<span class="autosuggest_left"><img class="img cat_icon1  " src="'.url('/uploads/category/icon/'.$category->icon).'" /></span>';
				}else{
					echo '<span class="autosuggest_left"><img class="img cat_icon1  " src="'.url('/public/images/icons/category_default_image.png').'"></span>';
				}
				?>
 				
				 <span class="autosuggest_right"><a href="javascript:void(0)"  onclick="return service_page('<?php echo $category->slug;?>')" ><?php echo $category->title;?></a></span> 
				<?php
				echo '</li>';
			}
			
			echo '</ul>';
				
		}		 
		 
	}
	
	
	
	
	
	
	function get_sub_category($cid){
		
		$sub_categories=Category::where('parent_cid',$cid)->orderby('title','ASC')->get();
		return $sub_categories;
		
	}
	
	
	
	function category_step2($city,$category){
		
		$min_amount_for_user=0;

		$city=City::where('slug',$city)->first();
		
		$category=Category::where('slug',$category)->first();
		
		$reviews=Review::where('cid',$category->cid)->where('status',1)->orderby('created_at','DESC')->get();
		
		$services=Service::where('cid',$category->cid)->orderby('weight','ASC')->get();

		$technicians=TechnicianCategory::where('cid',$category->cid)->orderby('t_cid','DESC')->get();
		$technicians->load('technicians');	

		$parent_cid=$category->parent_cid;

		if($parent_cid==0){
			$min_amount_for_user=$category->min_amount_for_user;
		}else{

			$parent_category=Category::find($parent_cid);
			$min_amount_for_user=$parent_category->min_amount_for_user;

		}	
		//dd($city);
			
		return view('category_step2',array(
									'city'=>$city,
									'category'=>$category,
									'services'=>$services,
									'reviews'=>$reviews,
									'min_amount_for_user'=>$min_amount_for_user,
									'technicians'=> $technicians));
	
	}
	
	
	function  get_cities(request $request){

		$keyword=$request->keyword;
		
		$cities = DB::table('city')
				->where('title', 'like', '%'.$keyword.'%')          
				 /*  ->orWhere('body', 'like', '%'.$keyword.'%')			*/	   
                ->orderby('title','ASC')->paginate(10);
				 
		if(count($cities)>0){
			echo '<ul id="city-list">';		 
			foreach($cities as $city){
				?>
				<li onclick="selectCity('<?php echo $city->city_id; ?>','<?php echo $city->title; ?>')">
 				<?php
				echo $city->title;
				echo '</li>';
			}
			echo '</ul>';
		}		 
	}
	

	function ajax_category_review_submit(request $request){
	
		$records=$request->all();
		//dd($records);
		
		$records['ip']=$_SERVER['REMOTE_ADDR'];
		$records['status']=0;
		
		$rev_id=Review::create($records)->rev_id;
		
		if($rev_id){
			return 1;
		}else{
			return 0;
		}
		
	}
	 
}