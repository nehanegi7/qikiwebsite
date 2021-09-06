<?php

namespace App\Http\Controllers\Admin;

use View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Review;
use Hash;
use DB;
use Redirect;
use Mail;  

class ReviewController extends Controller
{
	
	var $type='review';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
	 
	 function __construct(){
	 	
		View::share('type', $this->type);
 	 }
	 
	 
	 
	 
	 
	  
	  
	  
	 
	 
	 
    public function index(Request $request) 
    {	
		 
 	  
	   $reviews=Review::orderby('rev_id','DESC')->paginate(50);
		
      return view('admin.review.index',array('items'=>$reviews));

		
    }
	
	
	
	
	
	
	 
	

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
	
		 
		
	
         return view('admin.review.create');
    }






    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
	 
	 
	 

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
	 
	 
	
	
	 
    public function edit($id)
    {
        $review=Review::find($id);
		
  		
        return view('admin.review.edit',array('item'=>$review));
		
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
  			 ]); 
				 
				 
				 
	 
	   
	   
			   $item = Review::find($id);
				 	// store
				
				$item->name       = $request->name;
   				$item->rating      = $request->rating;
				
				$item->review      = $request->review;
 				
				
				 
				
				$item->status      = $request->status;
 				 
 
				$item->save();
				
				
				 
				
				 

            // redirect
           	return \Redirect::route('admin_review_edit',array('id' => $id))->with('message', 'Successfully Updated!');
	   
	   
	   
    }

  
  
  
  
  
   
  
  
  
    public function destroy($id)
    {
        
		Review::Destroy($id);
		
 
		return Redirect::back()->with('message','Successfully deleted!');
 		
    }
}
