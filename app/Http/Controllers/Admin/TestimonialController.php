<?php

namespace App\Http\Controllers\Admin;

use View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Testimonial; 
use App\Technician;
   
class TestimonialController extends Controller
{
	
	var $type='testimonial';

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
		$testimonials=Testimonial::orderby('weight','ASC')->paginate(50);
		
		$technicians=Technician::orderby('name','ASC')->get();
	 
		
        return view('admin.testimonial.index',array('items'=>$testimonials,'technicians'=>$technicians));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
			 
        return view('admin.testimonial.create',array());
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
				'name' => 'required',
				'body'=>'required'
   			 ]); 
				 
		 
		 
		 
		 $records=$request->all();
		 
		 
	  //File upload
		if ($request->hasFile('image')) {
			if ($request->file('image')->isValid()) {
				
				$fileName=$request->file('image')->getClientOriginalName();
				$fileName =time()."_".$fileName;
				//upload
				$request->file('image')->move('uploads/testimonial', $fileName);
					
					//column name 
				$records['image']=$fileName;
		
				
 			}
		}
		 
		
  	   
	   
 	   Testimonial::create($records);
	  
	  return \Redirect::route('admin_testimonial')->with('message', 'Testimonial Added Successfully ! '); 

	   
	   
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
        $testimonial=Testimonial::find($id);
		
	    $technicians=Technician::orderby('name','ASC')->get();

 		
        return view('admin.testimonial.edit',array('item'=>$testimonial,'technicians'=>$technicians));
		
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
				'name' => 'required',
				'body'=>'required'
   			 ]); 
				 
	   
	   
	 	  $item = Testimonial::find($id);
		  	 
				$item->name       = $request->name;
				$item->body       = $request->body;
   				 $item->reviewed_on       = $request->reviewed_on;
				   $item->tid       = $request->tid;
 				  $item->rating       = $request->rating;
 				 
				  $item->weight       = $request->weight;
				   $item->status       = $request->status;
				  
			if ($request->hasFile('image')) {
			
				
				if ($request->file('image')->isValid()) {
					 
					$fileName=$request->file('image')->getClientOriginalName();
					$fileName =time()."_".$fileName;
					$request->file('image')->move('uploads/testimonial', $fileName);
			
					$item->image=$fileName;
				}
			}
			
			
			
 
				$item->save();

            // redirect
           	return \Redirect::route('admin_testimonial_edit',array('id' => $id))->with('message', 'Successfully Updated!');
	   
	   
	   
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
		Testimonial::Destroy($id);
	   	   return \Redirect::route('admin_testimonial')->with('message', 'Successfully Deleted!');
		
    }
}
