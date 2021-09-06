<?php

namespace App\Http\Controllers\Admin;

use View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\ContactForm;
 
class EnquiryController extends Controller
{
	
	var $type='enquiry';
 
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
		$enquiries=ContactForm::orderby('created_at','DESC')->paginate(50);
		
        return view('admin.enquiry.index',array('items'=>$enquiries));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
 		
        return view('admin.enquiry.create');
    }






    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
	 
	 
	 
    public function store(Request $request)
    {
        
	   
	   
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
        $enquiry=ContactForm::find($id);
		
 		
        return view('admin.enquiry.edit',array('item'=>$enquiry));
		
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
       
	   /*
	   
	   
	     $this->validate($request, [
				'full_name' => 'required',
				 
  			 ]); 
				 
	   
	   
	 	  $item = ContactForm::find($id);
			 $item->email       = $request->email;
				$item->phone       = $request->phone;
 				 
  
				$item->save();

            // redirect
           	return \Redirect::route('admin_enquiry_edit',array('id' => $id))->with('message', 'Successfully Updated!');*/
	   
	   
	   
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
		ContactForm::Destroy($id);
	   	   return \Redirect::route('admin_enquiry')->with('message', 'Successfully Deleted!');
		
    }
}
