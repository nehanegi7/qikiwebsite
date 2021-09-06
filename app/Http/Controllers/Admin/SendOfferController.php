<?php

namespace App\Http\Controllers\Admin;
use View;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\User;
  
 
use Hash;
use Redirect;
use DB;
use Mail;

use DateTime;
class SendOfferController  extends Controller
{
     
	function index(){
	  	
		$users=User::orderby('name','ASC')->get();
		
	  	return view('admin.send_offer.index',array('users'=>$users));
	  
	}
	  
	  
	function send_offer(request $request){
	  	
		$records=$request->all();

		$send_offer_type=$request->send_offer_type;
		$subject=$request->subject;
		$message=$request->message;
		$ids_arr=$request->chk_customer;
		
		$messages = [
			'chk_customer.required' => 'Pls choose atleast one user ! ',
 		];
		
		$this->validate($request, [
				'send_offer_type' => 'required',
 				'message'=>'required',
				'chk_customer'=>'required',
 			 ],$messages); 
	
	 
		if(in_array('email',$send_offer_type)){
			$emails_arr=User::whereIn('id',$ids_arr)->pluck('email')->toArray();
			$this->send_email($emails_arr,$subject,$message);
		}
		
		if(in_array('sms',$send_offer_type)){
			$phones_arr=User::whereIn('id',$ids_arr)->pluck('phone')->toArray();
			$this->send_sms($phones_arr,$subject,$message);
		}

	 	return Redirect::back()->with('message','Successfully Sent!');  

	}
	  
	  
	  
   	function send_email($emails_arr,$subject,$msg){
 	
 	 	$emails_arr=array_filter($emails_arr);
		
		$data=array(
		 'emails'=>$emails_arr,
		 'msg'=>$msg,
		 'subject'=>$subject,
 		 );
		 
		Mail::send([], ['data' => $data], function ($message) use ($data) {
		  $message->to($data['emails'])
				   ->subject($data['subject'])
				   ->setBody($data['msg'], 'text/html'); // for HTML rich messages
		});
  	}
  
  
  	function send_sms($phones_arr,$subject,$msg){
  	
		$SMSController=new \App\Http\Controllers\SMSController;
	
		foreach($phones_arr as $mobile){
			
			if($mobile!=NULL){
				//dd($msg);
				
				return $SMSController->sms_send($mobile,$msg);	
			}
		}
		
	}
  

}