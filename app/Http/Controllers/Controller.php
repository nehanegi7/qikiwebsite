<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

use App\Http\Requests;
use App\Order;

use App\User;
use App\Technician;

use DB;
use PDF;

class Controller extends BaseController
{ 

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	
	function fcm_send_registration_id_to_server(request $request){
	
		$tid=$request->tid;
		$fcm_reg_id=$request->reg_id;
		
		 $Technician = Technician::find($tid);
 		 $Technician->fcm_reg_id=$fcm_reg_id;
  		 $Technician->save();
		 
		 echo 1;
	
	}
	
	
	function send_fcm_notification($modal,$receiver_id_arr,$title,$message){
		
		// switch($modal){
		// 	case "Technician":
		// 	$id_column='tid';
		// 	break;
			
		// 	case "User":
		// 	$id_column='id';
		// 	break;
			
		// }
		
		
	 
	
		  
 		// if($receiver_id_arr==0){
		// 	return false;
		// }
		
		// //$uids is a std class object ,so convert it to array
		// $receiver_id_arr=json_decode(json_encode($receiver_id_arr),true);
	 
		// /*echo $uids;
		// die();*/
		
		// if(is_array($receiver_id_arr)){
			
		// 	$fcm_reg_ids=$modal::whereIn($id_column,$receiver_id_arr)->pluck('fcm_reg_id');
 		// 	$fcm_reg_ids=json_decode(json_encode($fcm_reg_ids),true);
		// }else{
		// 	$user=$modal::find($receiver_id_arr);
		// 	$fcm_reg_ids=array($user->fcm_reg_id);
		// }
		 
		//  //dd($fcm_reg_ids);
		// /*echo "<pre>";
		// var_dump($fcm_reg_ids);
		// echo "</pre>";
		// die();*/
		
		// if($fcm_reg_ids==''){
		// 	return false;
		// }
		
		 
 		
		  
		
		
 				
		// // API access key from Google API's Console
 		// define( 'API_ACCESS_KEY','AIzaSyA7pPSiCQWxYOSQZxjMrl23sRG50wIXI_o');
		
		// // from 
		// //https://console.firebase.google.com/u/1/project/repairservicesindia-f37b6/settings/cloudmessaging/android:com.repairservicesindia.partner
		
		// $registrationIds = $fcm_reg_ids;
		
		// $msg = array
		// (
		// 'body' => $message,
		// 'title' => $title,
		// 'vibrate' => 1,
		// 'sound' =>'notification.mp3',
		// 'icon'=>url('/public/images/favicon.png'),
		// '//click_action'=>url('/partner/leads')
		
		// // you can also add images, additionalData
		// );
		
		// //dd($registrationIds);
		
		// $fields = array
		// (
		// 'registration_ids' => $registrationIds,
		// 'data' => $msg
		// );
		
		// $headers = array
		// (
		// 'Authorization: key='.API_ACCESS_KEY,
		// 'Content-Type: application/json'
		// );
		
		// $ch = curl_init();

		// curl_setopt( $ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		
		
		// curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
		// curl_setopt( $ch,CURLOPT_POST, true );
		// curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
		// curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
		// curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
		// curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
		// $result = curl_exec($ch );
		
		// // Handle errors
		// 	if (curl_errno($ch))
		// 	{
		// 		echo 'FCM error: ' . curl_error($ch);
				
		// 	}
		
		// curl_close( $ch );
		 
		// /* echo "<pre>";
		//   var_dump($result);
		//   echo "</pre>";
		//   die(); */
		
	
	}
	
	
	
	
	
	
	
    function download_invoice($oid){
 	   
	   $order=Order::find($oid);
	   $invoice_date = date('jS F Y', strtotime($order->invoice_date)); 
	   
	   //return view('includes.invoice_template',array('order'=>$order));
	   $pdf = PDF::loadView('includes.invoice_template',array('order'=>$order))->setOptions(['defaultFont' => 'sans-serif']);

       return $pdf->download('Invoice_'.config('app.name').'_Order_No # '.$oid.' Date_'.$invoice_date.'.pdf');
	}
	
	
	
	function install_app_send_sms(request $request){
		$phone=$request->install_app_mobile_no;
		if($phone!=''){
				 	$SMSController=new \App\Http\Controllers\SMSController;
 					
					 $setting=new \App\Setting;
					$msg=$setting->get_setting('install_app_sms_message');
  			
					$SMSController->sms_send($phone,$msg);	
		
			echo 1;
		  }
		
	}


		function update_on_whatsapp(request $request){
			
			//dd($request->all());
		
			$id=$request->id;
			
			$user=User::find($id);
			$user->update_on_whatsapp=$request->update_on_whatsapp;
			$user->save();	
			
			echo 1;
		 
		
	}



		
	 
	function remove_image(request $request)
	{
		$is_remove = 0;
		
		if($request->is_remove==1){
			$is_remove = $request->is_remove;
		}
		
		$table = $request->table;
		$id_name = $request->id_name;
		$id = $request->id;
		$column = $request->column;
		
		if($is_remove==0){
			DB::table($table)
            	->where($id_name,$id)
            	->update([$column =>'']);
		
		}else if($is_remove==1){
			DB::table($table)
            	->where($id_name,$id)
            	->delete();
		
		}
		
		echo 1;

	}


}
