<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth; 

use App\ContactForm;
 
use Mail;

use App\Setting;
 
class ContactController extends Controller
{
     
	 function contact(){
	 	return view('contact');
	 }
	 
	  
	function contact_submit(request $request){
		
		$email=$request->email;
		
		$records=$request->all();
		
		$Setting=new Setting;
		$admin_email=$Setting->get_setting('admin_email');
		
		
		
		
			//*******To database table***************/
		 
		
		ContactForm::create($records);


	
		//*******End To database table***************/
		
		
		$data=array(
		'details'=>$request,
		'admin_email'=>$admin_email
		);
		
		 
		
		          //view file located in resources/view/emails/contact_us.blade.php this is email template 
		Mail::send('emails.contact_us',$data, function ($message) use ($data){
 			 
			$message->to($data['admin_email']);
			 
 			$message->subject('Contact form submitted on our website');
		});

		
		
		 
		echo 1;
			
			
		
		
	}

	
	
}
