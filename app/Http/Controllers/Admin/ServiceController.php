<?php

namespace App\Http\Controllers\Admin;

use View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Service;

use App\Category;
 
use App\Setting;

use Redirect; 
use DB;

class ServiceController extends Controller
{
	
	var $type='service';
 
    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response 
     */
	 
	 function __construct(){
	 	
		View::share('type', $this->type);
 	 }
	 
    public function index(request $request) 
    {	
		
		$cid=$request->cid;
		//dd($cid);
		$category=Category::find($cid);
		
		$service=Service::where('cid',$cid)->orderby('weight','ASC')->paginate(50);
		
        return view('admin.service.index',array('items'=>$service,'category'=>$category)); 
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
 		
 		 
		
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
	 
   
		 
	    
	   Service::create($records);
 	   
 		return Redirect::back()->with('message','Successfully Added!');
 
	   
	   
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
	 
	 
	 
    public function edit(request $request,$id)
    {
        $service=Service::find($id);
		 
		
		$cid=$request->cid;
		 
		$category=Category::find($cid);
	 
		
         return view('admin.service.edit',array('item'=>$service,'category'=>$category));
		
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
				 
	   
	   
	   $item = Service::find($id);
		 
	 
		  
		  $item->title=$request->title;
		  $item->body=$request->body;
		   
  		   $item->cost=$request->cost;
		     $item->weight=$request->weight;
		   $item->status=$request->status;
		     
			 
		 
		  $item->save();
		 
		 
			
           return Redirect::back()->with('message','Successfully Updated!');  //because this function also using from seller services update page 

			
           //	return \Redirect::route('admin_service_edit',array('id' => $id))->with('message', 'Successfully Updated!');
	   
	   
	   
    }

  	
	 
	
	
	 
  
  
    public function destroy($id)
    {
        
		Service::Destroy($id);
		 

            return Redirect::back()->with('message','Successfully Deleted!');  //because this function also using from seller services update page 

	   	   //return \Redirect::route('admin_service')->with('message', 'Successfully Deleted!');
		
    }
	
	
	 
	
	
	
}
