<?php 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

use App\Http\Requests;
use App\Page;
use App\Category;
use App\HomeCategory;
use App\City;
use App\Banner;
use App\Testimonial;
use App\Coupon;

use Response;

class CMSPageController extends Controller
{
     
	
	
	function index(request $request){
		 
		$cities=City::orderby('title','ASC')->get();

		$categories_parent=Category::where('parent_cid',0)->where('status',1)->orderby('title','ASC')->get();

		$home_categories=HomeCategory::orderby('order_no','ASC')->get();

		$featured_categories=Category::where('featured',1)->where('status',1)->orderby('title','ASC')->get();

		$categories_search_filterables=Category::where('parent_cid','!=',0)->where('status',1)->orderby('title','ASC')->get();

		$testimonials=Testimonial::where('status',1)->orderby('weight','ASC')->paginate(50);

		$coupons=Coupon::orderby('cid','ASC')->get();

		//dd($city);
	
		return view('index',array(
			'cities'=>$cities,
			'categories_parent'=>$categories_parent,
			'home_categories'=>$home_categories,
			'featured_categories'=>$featured_categories,
			'categories_search_filterables'=>$categories_search_filterables,
			'testimonials'=>$testimonials,
			'coupons' => $coupons,
 		));
	}
	
	
	
	function test(){
  		
		return view('includes.google_location_suggest_api');	 
	}	
	
 
	
	function page(Page  $page){
  		
		return view('page', array('page'=>$page));	 
	}	
	
	
	
	
	
}