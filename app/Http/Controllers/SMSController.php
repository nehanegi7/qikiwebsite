<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;

use App\User;
use App\Order;
use App\OrderItem;
use App\Service;
use App\Technician;
use App\Category;
 
use App\PhoneVarification;
use App\Setting;

use Twilio\Rest\Client;

use DB;
 
class SMSController  extends Controller
{
	
	/**
	 * Sends sms to user using Twilio's programmable sms client
	 * @param String $message Body of sms
	 * @param Number $recipients string or array of phone number of recepient
	 */
	function sms_send($mobile,$msg,$lang='EN'){
		
		$mobile = '+'.$mobile;
		$account_sid = config('services.twilio')['accountSid'];
        $auth_token = config('services.twilio')['accountToken'];
		$twilio_number = config('services.twilio')['twilioNumber'];
		
		$client = new Client($account_sid, $auth_token);
		$response = $client->messages->create($mobile, ['from' => $twilio_number, 'body' => $msg]);

		// $setting=new \App\Setting;
	 	//$full_sms_api_url=$setting->get_setting('full_sms_api_url');
		//$full_sms_api_url=str_replace('[mobile]',$mobile,$full_sms_api_url);
		//$full_sms_api_url=str_replace('[message]',$msg,$full_sms_api_url);
 		// $response = file_get_contents($full_sms_api_url);		
	}
	
	
	//this is used on 2nd page of modal phase
	function ajax_otp_send(request $request){
		$send_otp=0;
		$phone=$request->phone;
		$otp=rand(1000,9999);
		
		$count=User::where('phone',$phone)->count();
	
		if($count>0){
			$status=2;
			$message="This phone no. is already registered"; 
			
			//login existing user
			$user = User::where('phone',$phone)->where('status',1)->first();
 		    Auth::login($user);
			
			return response()->json(array('status'=>$status,'message'=>$message,'otp'=>$otp));
		} 
		
		$count1=PhoneVarification::where('phone',$phone)->count();
		if($count1>0){
		 $PhoneVarification=PhoneVarification::where('phone',$phone)->first();

			$is_varified=$PhoneVarification->is_varified;
			if($is_varified==1){
				  $status=2;
				$message="This phone no. is already verified"; 
			}else{
				DB::table('phone_varification')
				->where('phone',$phone)
				->update(['otp' =>$otp]);
				
				$send_otp=1;
				 $status=1;
				 
				 $message="OTP sent to your mobile no. "; 
			}
			
		}else{
			 $status=1;
			$send_otp=1;
			DB::table('phone_varification')->insert(
				['phone' =>$phone,'otp'=>$otp]
			);
			
 			$message="OTP sent to your mobile no. "; 
		}	
				
		//Send SMS//
		if($send_otp==1){
			$sms_msg="Please enter the OTP ".$otp." to verify your phone .";
				$this->sms_send($phone,$sms_msg);
		}
		////
		return response()->json(array('status'=>$status,'message'=>$message));
		
	}


	function ajax_check_is_valid_otp(request $request){
		$phone=$request->phone;
		$otp=$request->otp;
		 
		//check the phone no. is already registred, if so login in
		$user_exists=User::where('phone',$phone)->where('status',1)->count();
		if($user_exists>0){
		   $status=1;
		   $message="Registered Account . Logging in ."; 
		  
		   return response()->json(array('status'=>$status,'message'=>$message));
		   
			$user = User::where('phone',$phone)->where('status',1)->first();
 		    Auth::login($user);
		}
		
		///////////

		$count1=PhoneVarification::where('phone',$phone)->count();
		if($count1>0){
			$PhoneVarification=PhoneVarification::where('phone',$phone)->first();
			$is_varified=$PhoneVarification->is_varified;
			if($is_varified==1){
				$status=1;
				$message="This phone no. is already verified"; 
			
			}else{
				 $count=PhoneVarification::where('phone',$phone)->where('otp',$otp)->count();
				 if($count>0){
				 	$status=1;
				 	$message="Phone no. successfully verified";
					
					DB::table('phone_varification')
					->where('phone',$phone)
					 ->where('otp',$otp)
					->update(['is_varified' =>1]);
						
				 }else{
				 	$status=0;
				 	$message="Incorrect OTP";
				 }
			}
			
		}else{
		 	 $status=0;
			 $message="No record for this phone no.";	
		}	
				
		return response()->json(array('status'=>$status,'message'=>$message));
		
	}

	
	function sms_send_on_new_order_place_to_customer($mobile,$oid){
		
		$order=Order::find($oid);

		$order_items=OrderItem::where('oid',$order->oid)->orderby('created_at','DESC')->get();
		
		$product_data_arr=array();
		foreach($order_items as $order_item){
			$product_data_arr[]=$order_item->title;
		}
		 
		
		$product_data=implode(" , ",$product_data_arr);
		
		$msg="Thanks for your order #".$oid. " has successfully placed";
		$msg.=" of ".$product_data;
		$msg.=". Order pass code is #".$order->order_pass_code. " to share with technician. ";

		$msg.=" Total Payable Amount is $ ".$order->grand_total;
		$msg.=" ". config("app.name");
			
		$this->sms_send($mobile,$msg);	
	}
	


	/*
	function sms_send_on_new_order_place_to_customer($mobile,$oid){
		
		$order=Order::find($oid);
		
		$visiting_charge=$order->min_amount_for_user;
		
		$order_items=OrderItem::where('oid',$order->oid)->orderby('created_at','DESC')->get();
		
		$product_data_arr=array();
		foreach($order_items as $order_item){
			$product_data_arr[]=$order_item->title;
		}
		
		$product_data=implode(" , ",$product_data_arr);
		
		//English 
		$msg1="Dear Customer,
Thanks for your Complaint No ".$oid." has successfully placed of ".$product_data.". Order pass code is #".$order->order_pass_code." to share with technician.";
		
		$msg1.="Total Payable Amount is $ ".$order->grand_total.". Our Engineer Coming Soon at your Home within 24hours & Visiting Charges @".$visiting_charge."$/ compulsory & Billing By Email For Sercurity Reason. ";
		
		// $msg1.=" Note:-
		// 		1.Collect The Receive Bill By The Engineer.
		// 		2.Don't Help To Private service.
		// 		3.Payment Mode-Paytm/UPI 
		// 		Paytm No-1111111111(Only For PayTm),UPI ID-YOUR_UPI_ID@UPI(only for UPI)
		// 		Call-1111111111,2222222222.
		// 		If You need help. ";

 		$msg1.=config("app.name");
		
		//$msg1.=" Download The App  ";	
		
		//End English	

		//Hindi 
	 	$msg2="&#2346;&#2381;&#2352;&#2367;&#2351;&#2375; &#2327;&#2381;&#2352;&#2361;&#2366;&#2325;, 
&#2310;&#2346;&#2360;&#2375; &#2309;&#2344;&#2369;&#2352;&#2379;&#2343; &#2361;&#2376;,&#2325;&#2368; 
&#2361;&#2350;&#2366;&#2352;&#2368; &#2325;&#2306;&#2346;&#2344;&#2368; &#2342;&#2381;&#2357;&#2366;&#2352;&#2366; &#2342;&#2367;&#2319; &#2327;&#2319; &#2325;&#2366;&#2350; &#2325;&#2366; &#2350;&#2370;&#2354;&#2381;&#2351;  &#8377; ".$order->grand_total." &#2325;&#2371;&#2346;&#2351;&#2366; &#2361;&#2350;&#2366;&#2352;&#2375; &#2325;&#2306;&#2346;&#2344;&#2368; &#2342;&#2381;&#2357;&#2366;&#2352;&#2366; &#2342;&#2367;&#2319; &#2327;&#2319; :-  
1.Paytm no/Whatsapp No- 8802343357 
2.UPI ID- RepairServicesIndia@UPI 
&#2346;&#2352; &#2361;&#2368; Payment &#2325;&#2352;&#2375; | &#2311;&#2360;&#2360;&#2375; &#2310;&#2346;&#2325;&#2366; &#2324;&#2352; &#2361;&#2350;&#2366;&#2352;&#2368; &#2325;&#2306;&#2346;&#2344;&#2368; &#2325;&#2366; &#2346;&#2375;&#2350;&#2375;&#2306;&#2335; / &#2348;&#2367;&#2354; &#2352;&#2367;&#2325;&#2377;&#2352;&#2381;&#2337; &#2348;&#2344;&#2366; &#2352;&#2361;&#2375;&#2327;&#2366; | &#2332;&#2367;&#2360;&#2360;&#2375; &#2310;&#2346;&#2325;&#2379; &#2310;&#2327;&#2375; &#2310;&#2344;&#2375; &#2357;&#2366;&#2354;&#2375; &#2360;&#2350;&#2351; &#2350;&#2375; &#2325;&#2379;&#2312; &#2346;&#2352;&#2375;&#2358;&#2366;&#2344;&#2368; &#2344;&#2361;&#2368;&#2306; &#2361;&#2379;&#2327;&#2368; |  
{&#2325;&#2371;&#2346;&#2351;&#2366; &#2325;&#2376;&#2358; &#2342;&#2375;&#2344;&#2375; &#2325;&#2368; &#2311;&#2330;&#2381;&#2331;&#2366; &#2346;&#2381;&#2352;&#2325;&#2335; &#2344; &#2325;&#2352;&#2375; &#2324;&#2352; &#2348;&#2367;&#2354; &#2325;&#2306;&#2346;&#2344;&#2368; &#2325;&#2379; Whatsapp &#2325;&#2352;&#2375;}
Thank You - ";
 $msg2.=config("app.name");

 
		//End Hindi	 
		
		 $this->sms_send($mobile,$msg1,"EN");	
		 $this->sms_send($mobile,$msg2,"HN");	

	}
	*/
	

	function sms_send_on_new_order_place_to_technician($tid,$oid){
		
		 $Technician=\App\Technician::find($tid);
		//English 
		$msg="You have a new complaint #".$oid." assiged by admin on ".config('app.name');

		if($Technician->phone!=''){
			 $this->sms_send($Technician->phone,$msg);	
		}

	}


	function test(){

		 $msg1="Hello how are you ";
		 
	 	$msg2="&#2346;&#2381;&#2352;&#2367;&#2351;&#2375; &#2327;&#2381;&#2352;&#2361;&#2366;&#2325;, 
&#2310;&#2346;&#2360;&#2375; &#2309;&#2344;&#2369;&#2352;&#2379;&#2343; &#2361;&#2376;,&#2325;&#2368; &#2361;&#2350;&#2366;&#2352;&#2368; &#2325;&#2306;&#2346;&#2344;&#2368; &#2342;&#2381;&#2357;&#2366;&#2352;&#2366; &#2342;&#2367;&#2319; &#2327;&#2319; &#2325;&#2366;&#2350; &#2325;&#2366; &#2350;&#2370;&#2354;&#2381;&#2351; &#2325;&#2371;&#2346;&#2351;&#2366; &#2361;&#2350;&#2366;&#2352;&#2375; &#2325;&#2306;&#2346;&#2344;&#2368; &#2342;&#2381;&#2357;&#2366;&#2352;&#2366; &#2342;&#2367;&#2319; &#2327;&#2319; :- 
1.Paytm no/Whatsapp No- 7009121295 
2.UPI ID- RepairServicesIndia@UPI 
&#2346;&#2352; &#2361;&#2368; Payment &#2325;&#2352;&#2375; | &#2311;&#2360;&#2360;&#2375; &#2310;&#2346;&#2325;&#2366; &#2324;&#2352; &#2361;&#2350;&#2366;&#2352;&#2368; &#2325;&#2306;&#2346;&#2344;&#2368; &#2325;&#2366; &#2346;&#2375;&#2350;&#2375;&#2306;&#2335; / &#2348;&#2367;&#2354; &#2352;&#2367;&#2325;&#2377;&#2352;&#2381;&#2337; &#2348;&#2344;&#2366; &#2352;&#2361;&#2375;&#2327;&#2366; | &#2332;&#2367;&#2360;&#2360;&#2375; &#2310;&#2346;&#2325;&#2379; &#2310;&#2327;&#2375; &#2310;&#2344;&#2375; &#2357;&#2366;&#2354;&#2375; &#2360;&#2350;&#2351; &#2350;&#2375; &#2325;&#2379;&#2312; &#2346;&#2352;&#2375;&#2358;&#2366;&#2344;&#2368; &#2344;&#2361;&#2368;&#2306; &#2361;&#2379;&#2327;&#2368; | 
{&#2325;&#2371;&#2346;&#2351;&#2366; &#2325;&#2376;&#2358; &#2342;&#2375;&#2344;&#2375; &#2325;&#2368; &#2311;&#2330;&#2381;&#2331;&#2366; &#2346;&#2381;&#2352;&#2325;&#2335; &#2344; &#2325;&#2352;&#2375; &#2324;&#2352; &#2348;&#2367;&#2354; &#2325;&#2306;&#2346;&#2344;&#2368; &#2325;&#2379; Whatsapp &#2325;&#2352;&#2375;}
Thank You - ";

    	// $msg2=str_replace('%u', '',$this->utf8_to_unicode($msg2_str));//it will convert the normal message to the unicode
 

		 $this->sms_send('+917009121295',$msg1);	
	}
	 

		
 	function sms_send_on_new_seller_register_to_seller($mobile,$tid,$category_title_arr){
		
		$technician=Technician::find($tid);
 		
	   	$categories_name=implode(" , ",$category_title_arr);

		$msg="Welcome to the ".config('app.name')." community  of 50000+ professionals. Just register yourself -   to expand your ".$categories_name.".
To opt out, give a missed call at 1111111111";

		$this->sms_send($mobile,$msg);	
	}
	
	
	function ajax_otp_send_for_login(request $request){
		$send_otp=0;
		$phone=$request->phone;
		$otp=rand(1000,9999);

		$countv = PhoneVarification::where('phone',$phone)->count();
		$countu = User::where('phone',$phone)->where('email','!=','')->where('password','!=','')->count();
		if($countv > 0){

			if($countu > 0){

				$message="Already verified customer"; 	
				$status=2;

			}else{

				DB::table('phone_varification')
					->where('phone',$phone)
					->update(['otp' =>$otp]);	

				$message="OTP sent to your mobile no"; 
				$sms_msg="Please enter the OTP ".$otp." to verify your phone.";
				$this->sms_send($phone,$sms_msg);	
				$status=1;	
			} 

		}else{
			
			DB::table('phone_varification')->insert(
				['phone' =>$phone,'otp'=>$otp]
			);
		
			$message="OTP sent to your mobile no. "; 		
  		 	$sms_msg="Please enter the OTP ".$otp." to verify your phone .";
			$this->sms_send($phone,$sms_msg);
			$status=1;	
		}	
		
		return response()->json(array('status'=>$status,'message'=>$message));
	}
	
	
	function check_otp_for_login_with_phone(request $request){
		$phone=$request->phone;
		$otp=$request->otp;
		 
 	    $count=PhoneVarification::where('phone',$phone)->where('otp',$otp)->count();
		if($count>0){
 				$status=1;
				$message="This phone no. successfully verified"; 
					
					DB::table('phone_varification')
					->where('phone',$phone)
					 ->where('otp',$otp)
					->update(['is_varified' =>1]);
						
		
				 //check the phone no. is already registred,  
				$user_exists=User::where('phone',$phone)->where('status',1)->count();
				if($user_exists>0){
				   
					$user = User::where('phone',$phone)->where('status',1)->first();
		
				}else{
				
					$id=User::create([
							'phone' =>$phone,
					 ])->id;
					$user=User::find($id);
		
				}
				
				
				//login the user
				Auth::login($user);
 				
		 }else{
				 	$status=0;
				 	$message="Incorrect OTP";
		 }
				
		 return response()->json(array('status'=>$status,'message'=>$message));

	}
	
}