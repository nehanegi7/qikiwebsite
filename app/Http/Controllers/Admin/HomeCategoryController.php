<?php

namespace App\Http\Controllers\Admin;

use View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\HomeCategory;
use App\Category;
use Redirect;

class HomeCategoryController extends Controller
{
	
	var $type='home_category';

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

		$home_category=HomeCategory::orderby('order_no','ASC')->get();
		 
		 $categories_parent=Category::where('parent_cid',0)->where('status',1)->orderby('title','ASC')->get();
		  
		$categories=Category::all();
		
        return view('admin.home_category.index',array('items'=>$home_category,'categories'=>$categories,'categories_parent'=>$categories_parent));
    }

     



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
	 
	 
	 
    public function store(Request $request)
    {
       
$messages = [
			'cid.unique' => 'This Category is already added ',
 		];

	  
			 $this->validate($request, [
				'cid' => 'required|unique:home_categories',
				'order_no'=>'required',
 			 ],$messages); 
				 
		 
		 
		 
		 $records=$request->all();
		 
		  
	     HomeCategory::create($records);
	  
	  
		 return Redirect::back()->with('message','Added Successfully !');
 
	   
	   
    }
	
	
	
	
	 
	 
    public function edit($id)
    {
        $home_category=HomeCategory::find($id);
		$categories=Category::all();
		
		$categories_parent=Category::where('parent_cid',0)->where('status',1)->orderby('title','ASC')->get();


        return view('admin.home_category.edit',array('item'=>$home_category,'categories'=>$categories,'categories_parent'=>$categories_parent));
		
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
       
 	   
	   
	$messages = [
			'cid.unique' => 'This Category is already added ',
 		];

	  
			 $this->validate($request, [
				'cid' => 'required|unique:home_categories,cid,'.$id.',hc_id',
				'order_no'=>'required',
 			 ],$messages); 
				
		 
	   
	   
	   $item = HomeCategory::find($id);
		
		

 		
			 
			
			
				// store
				$item->cid       = $request->cid;
				$item->order_no      = $request->order_no;
				
				 
				$item->save();

            // redirect
           	return \Redirect::route('admin_home_category')->with('message', 'Successfully Updated!');
	   
	   
	   
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
		HomeCategory::Destroy($id);
	   	   return \Redirect::route('admin_home_category')->with('message', 'Successfully Deleted!');
		
    }
}
