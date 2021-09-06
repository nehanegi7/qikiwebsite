<?php

namespace App\Http\Controllers\Admin;

use View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Category;
use App\Service;
use App\CategoryAdvantageImage;

use DB;

class CategoryController extends Controller
{
	
	var $type='category';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
	function __construct(){
	 	
		View::share('type', $this->type);
 	}
	 

    public function index() 
    {	
		$category=Category::orderby('cid','DESC')->paginate(50);

        return view('admin.category.index',array('items'=>$category));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $category=Category::orderby('cid','DESC')->paginate(50);
		
		$categories=Category::where('parent_cid',0)->orderby('title','ASC')->get();

        return view('admin.category.create',array('items'=>$category,'categories'=>$categories));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
	    $this->validate($request, [
				'title' => 'required',
   		]); 

		$records=$request->all();
		 
		if ($request->hasFile('banner')) {

		 	$this->validate($request, [
					'banner' => 'image|mimes:jpeg,png,jpg,gif,webp,svg',
	  			 ]); 

			if ($request->file('banner')->isValid ()) {
				
				$fileName=$request->file('banner')->getClientOriginalName();
				$fileName =time()."_".$fileName;
				//upload
				$request->file('banner')->move('uploads/category/banner', $fileName);
					
					//column name 
				$records['banner']=$fileName;
 			}
		}
		 
		if ($request->hasFile('icon')) {

		 	$this->validate($request, [
					'icon' => 'image|mimes:jpeg,png,jpg,gif,webp,svg',
	  			 ]); 

			if ($request->file('icon')->isValid ()) {
				
				$fileName=$request->file('icon')->getClientOriginalName();
				$fileName =time()."_".$fileName;
				//upload
				$request->file('icon')->move('uploads/category/icon', $fileName);
					
					//column name 
				$records['icon']=$fileName;
 			}
		}
		
		if ($request->hasFile('icon_for_mobile_view')) {

		 	$this->validate($request, [
					'icon_for_mobile_view' => 'image|mimes:jpeg,png,jpg,gif,webp,svg',
	  			 ]); 

			if ($request->file('icon_for_mobile_view')->isValid ()) {
				
				$fileName=$request->file('icon_for_mobile_view')->getClientOriginalName();
				$fileName =time()."_".$fileName;
				//upload
				$request->file('icon_for_mobile_view')->move('uploads/category/icon', $fileName);
					
					//column name 
				$records['icon_for_mobile_view']=$fileName;
		
 			}
		}
		
		if ($request->hasFile('image')) {

		 	$this->validate($request, [
					'image' => 'image|mimes:jpeg,png,jpg,gif,webp,svg',
	  			 ]); 

			if ($request->file('image')->isValid ()) {
				
				$fileName=$request->file('image')->getClientOriginalName();
				$fileName =time()."_".$fileName;
				//upload
				$request->file('image')->move('uploads/category/image', $fileName);
					
					//column name 
				$records['image']=$fileName;

 			}
		}
		
		$records['slug']=str_slug($request->title, '-');
 	   
	  	$cid= Category::create($records)->cid;

	    // --------- Inserting Product Images-------------- //
		// dd($request->all());
		$pop_up_advantage_images=array();
		
        $images=$request->pop_up_advantage_image;
		
		//dd($images);
		//if(count($images)>0){
		if($images){
		
			foreach($images as $k=>$image){
			
				if ($request->hasFile('pop_up_advantage_image')) {
					if ($request->file('pop_up_advantage_image')[$k]->isValid()) {

						$fileName=$request->file('pop_up_advantage_image')[$k]->getClientOriginalName();
						//dd($fileName);
						
						$fileName =time()."_".$fileName;
						//upload
						$image = $request->file('pop_up_advantage_image')[$k];
					
						$pop_up_advantage_images[]=array('cid'=>$cid,'image'=>$fileName);
					
						$request->file('pop_up_advantage_image')[$k]->move('uploads/category/advantage', $fileName);
						
					}
				}
			}

			CategoryAdvantageImage::insert($pop_up_advantage_images);
		
		} // image count if close

	  	return \Redirect::route('admin_category')->with('message', 'Category Added Successfully ! '); 
    }
	

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $category=Category::find($id);
		$categories=Category::where('parent_cid',0)->orderby('title','ASC')->get();

        return view('admin.category.edit',array('item'=>$category,'categories'=>$categories));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
	    $this->validate($request, [
				'title' => 'required',
   		]); 

	 	$item = Category::find($id);
		$item->title       = $request->title;
		$item->parent_cid       = $request->parent_cid;
		$item->min_amount_for_user       = $request->min_amount_for_user;
		$item->featured       = $request->featured;
		$item->status       = $request->status;
		$item->highlight_message       = $request->highlight_message;
		$item->advantage_message       = $request->advantage_message;
		$item->how_it_works       = $request->how_it_works;
		$item->about       = $request->about;
		$item->pop_up_advantage_message       = $request->pop_up_advantage_message;
		$item->bottom_info       = $request->bottom_info;
		$item->meta_keyword       = $request->meta_keyword;
		$item->meta_description       = $request->meta_description; 
				  
		if ($request->hasFile('banner')){
			
			$this->validate($request, [
				'banner' => 'image|mimes:jpeg,png,jpg,gif,webp,svg',
				]); 
				
			if ($request->file('banner')->isValid ()) {
					
				$fileName=$request->file('banner')->getClientOriginalName();
				$fileName =time()."_".$fileName;
				$request->file('banner')->move('uploads/category/banner', $fileName);
		
				$item->banner=$fileName;
			}
		}
					 	 
		if ($request->hasFile('icon')) {
			
			$this->validate($request, [
				'icon' => 'image|mimes:jpeg,png,jpg,gif,webp,svg',
			]); 

			if ($request->file('icon')->isValid ()) {
					
				$fileName=$request->file('icon')->getClientOriginalName();
				$fileName =time()."_".$fileName;
				$request->file('icon')->move('uploads/category/icon', $fileName);
		
				$item->icon=$fileName;
			}
		}
			
		if ($request->hasFile('icon_for_mobile_view')) {
			
			$this->validate($request, [
				'icon_for_mobile_view' => 'image|mimes:jpeg,png,jpg,gif,webp,svg',
			]); 

			if ($request->file('icon_for_mobile_view')->isValid ()) {
					
				$fileName=$request->file('icon_for_mobile_view')->getClientOriginalName();
				$fileName =time()."_".$fileName;
				$request->file('icon_for_mobile_view')->move('uploads/category/icon', $fileName);
		
				$item->icon_for_mobile_view=$fileName;
			}
		}
			
		if ($request->hasFile('image')) {
				
			$this->validate($request, [
				'image' => 'image|mimes:jpeg,png,jpg,gif,webp,svg',
				]); 
			
			if ($request->file('image')->isValid ()) {
					
				$fileName=$request->file('image')->getClientOriginalName();
				$fileName =time()."_".$fileName;
				$request->file('image')->move('uploads/category/image', $fileName);
		
				$item->image=$fileName;
			}
		}
			
		$item->slug = str_slug($request->title, '-');
		$item->save();
				

		if ($request->hasFile('pop_up_advantage_image')) {
			//dd($request->file('image'));
			//$product_images=array();
			
			foreach($request->file('pop_up_advantage_image')  as $k=>$image){
			
				$fileName=$image->getClientOriginalName();
				$fileName =time()."_".$fileName;
				//upload

				$category_advantage_images[]=array('cid'=>$id,'image'=>$fileName);
			
				$request->file('pop_up_advantage_image')[$k]->move('uploads/category/advantage', $fileName);
			}
			
			CategoryAdvantageImage::insert($category_advantage_images);
		}
		 
        // redirect
        return \Redirect::route('admin_category_edit',array('id' => $id))->with('message', 'Successfully Updated!');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
		Category::Destroy($id);
		DB::table('category_advantage_images')->where('cid',$id)->delete();
				
	   	return \Redirect::route('admin_category')->with('message', 'Successfully Deleted!');
    }
	
	

	function cat_list($p_cid=0,$space='',$n=1){

		$categories=Category::where('parent_cid',$p_cid)->orderby('title','ASC')->get(); 
		$count=count($categories);

		if($p_cid==0){
			$space='';
		}else{
			$space .=" ----- ";
		}
		if($count > 0){
			
			foreach($categories as $category){
				
				$count_service=Service::where('cid',$category->cid)->count();
				
				include('resources/views/admin/includes/category_index.loop.blade.php');
					
				$n++;
				$this->cat_list($category->cid, $space, $n);
			}
		}
	}
	

	function cat_list_dropdown($p_cid=0,$space='',$cid_selected=0){
	 	 
		$categories=Category::where('parent_cid',$p_cid)->orderby('title','ASC')->get();
		$count=count($categories);

		if($p_cid==0){
			$space='';
		}else{
			$space .=" ----- ";
		}
		if($count > 0){
			
			foreach($categories as $k=>$category){
				
				if($cid_selected==$category->cid){
					$s="selected";
				}else{
					$s='';
				}
				echo "<option ".$s." value='".$category->cid."'>".$space.$category->title."</option>";
				
				$this->cat_list_dropdown($category->cid, $space, $cid_selected);
			}
		}
	}
	

}