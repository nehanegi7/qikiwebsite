<?php 
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

use App\Http\Requests;
use App\Page;
use App\Category;
use App\Technician;
use App\TechnicianCategory;
use App\TechnicianCredit;
use App\IdentityProofType;
use App\TechnicianIDProof;
use App\TechnicianPersonalDetail;
use App\TechnicianCurrentAddress;

use App\City;
use App\Service;
 use App\PhoneVarificationPartner;

use DB; 
use Redirect;
use Response;
use Auth; 
 
 
class MiscController extends Controller
{
     
	
	function get_top_level_categories(request $request){
		$skip=0;
		$take=6;
		
		if($request->skip){
			$skip=$request->skip;
		}
		
		
		if($request->take){
			$take=$request->take;
		}
		
		$categories=Category::where('parent_cid',0)->where('status',1)->orderby('title','ASC')->skip($skip)->take($take)->get();
		
		$cat_arr=array();
		
		foreach($categories as $category){
		
			$category->image=url('uploads/category/image/'.$category->image);
			$category->icon=url('uploads/category/icon/'.$category->icon);
			$cat_arr[]=$category;
			
		}
		
		return response()->json($cat_arr);
		
		
	}
	 
	 
	function category_details($cid){
		$category=Category::find($cid);
		
		$category->banner=url('uploads/category/banner/'.$category->banner);
		$category->image=url('uploads/category/image/'.$category->image);
		$category->icon=url('uploads/category/icon/'.$category->icon);
		
		
		return response()->json($category);
	}
	
	
	
	
	
	
	
	
	function get_sub_category(request $request){
		 
		 $cid=$request->cid;
		
		$categories=Category::where('parent_cid',$cid)->where('status',1)->orderby('title','ASC')->get();
		
		$cat_arr=array();
		
		foreach($categories as $category){
		
			$category->banner=url('uploads/category/banner/'.$category->banner);
			$category->image=url('uploads/category/image/'.$category->image);
			$category->icon=url('uploads/category/icon/'.$category->icon);
			$cat_arr[]=$category;
			
		}
		
		return response()->json($cat_arr);
		
		
	}
	 
	 
	 
	 
	 
	
	function get_services_by_category(request $request){
		 
		 $cid=$request->cid;
		
		$services=Service::where('cid',$cid)->where('status',1)->orderby('weight','ASC')->get()->toArray();
  		
	 
 		return response()->json($services);
		
		
	}
	 
	
	
	
}