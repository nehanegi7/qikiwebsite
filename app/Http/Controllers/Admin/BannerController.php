<?php

namespace App\Http\Controllers\Admin;

use View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Banner;
 
class BannerController extends Controller
{
	
	var $type='banner';

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
		$banner=Banner::orderby('order_no','ASC')->get();
		
        return view('admin.banner.index',array('items'=>$banner));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
 		
        return view('admin.banner.create');
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
				'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg',
  			 ]); 
				 
		 
		 
		 
		 $records=$request->all();
		 
		 
		 
		
		 //File upload
		if ($request->hasFile('image')) {
			if ($request->file('image')->isValid()) {
				
				$fileName=$request->file('image')->getClientOriginalName();
				$fileName =time()."_".$fileName;
				//upload
				$request->file('image')->move('uploads/banner', $fileName);
					
					//column name 
				$records['image']=$fileName;
		
				
 			}
		}
		 
		
		 
		
 	   
	   Banner::create($records);
	  
	  return \Redirect::route('admin_banner_create')->with('message', 'Banner Added Successfully ! '); 

	   
	   
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
        $banner=Banner::find($id);
		
 		
        return view('admin.banner.edit',array('item'=>$banner));
		
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
       
	   
	   
	   
	      
				 
	   
	   
	   $item = Banner::find($id);
					
			
		
		if ($request->hasFile('image')) {
			
				$this->validate($request, [
					'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg',
	  			 ]); 
				
				if ($request->file('image')->isValid()) {
					 
					$fileName=$request->file('image')->getClientOriginalName();
					$fileName =time()."_".$fileName;
					$request->file('image')->move('uploads/banner', $fileName);
			
					$item->image=$fileName;
				}
			}
			

 				 $item->link=$request->link; 

				$item->order_no=$request->order_no; 
 				
 				 
				$item->save();

            // redirect
           	return \Redirect::route('admin_banner_edit',array('id' => $id))->with('message', 'Successfully Updated!');
	   
	   
	   
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
		Banner::Destroy($id);
	   	   return \Redirect::route('admin_banner')->with('message', 'Successfully Deleted!');
		
    }
}
