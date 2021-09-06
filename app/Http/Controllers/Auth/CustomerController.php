<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Illuminate\Http\Request;
use App\User;
use App\Category;
use App\Order;
use App\UserCredit;

use App\Service;
use App\OrderStatus;
use App\OrderStatusChange;
use Redirect;
use DB;
use Auth;
use Hash;

class CustomerController extends Controller
{
     public function __construct()
    {
        $this->middleware('auth');
    }

	 
	 public function profile(Request $request)
    {
        if ($request->user()) {
             
			 $user=$request->user();
			 
			// var_dump($user);
			return view('customer.profile',array('user'=>$user));
			
        } 
    }


 	public function updateProfile(Request $request)
    {
        if ($request->user()) {
            // $request->user() returns an instance of the authenticated user...
 			
			$user = Auth::user();
			$id=$user->id;

			 //echo $id; die();
			$item = User::find($id);
			
			$item->name  = $request->name;
			$item->phone = $request->phone;
			$item->email = $request->email;

			if (!empty($request->password)) {
				$item->password = Hash::make($request->password);
			}

			if ($request->hasFile('photo')) {
				$this->validate($request, [
					'photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg',
				]); 
	
				if ($request->file('photo')->isValid()) {
					
					$fileName=$request->file('photo')->getClientOriginalName();
					$fileName =time()."_".$fileName;
					//upload
					$request->file('photo')->move('uploads/users/photo', $fileName);
						
						//column name 
					$item->photo=$fileName;
					
				}
			}
			$item->save();

			//Check User credit balance and store signup credit if new customer
			$cucb = UserCredit::where('uid',$id)->get()->toArray();
			if(empty($cucb)){
				$setting=new \App\Setting;
				$signup_points = $setting->get_setting('customer_signup_points');

				$userdata=[
					"uid"       => $id,
					"total_points" => $signup_points,
					"is_recharged_by_admin" => 1,    //by admin 
					"is_hold" => 0,    
					"status"  => 1,        
				];
				$uc = UserCredit::create($userdata);
			}
			
		 	return Redirect::back()->with('message','Successfully Updated!');
			
        }
    }

	
	
	

function my_bookings(){
	
	$user=Auth::user();
	
	$id=$user->id;	
	
	$order_ongoing_os_id_arr=[5,6,7,8,10];		 
			
	$oid_arr_all=Order::where('id',$id)->orderby('oid','DESC')->pluck('oid')->toArray();

	$ongoing_oid_arr=array();
	$history_oid_arr=array();
	if(!empty($oid_arr_all)){
		foreach($oid_arr_all as $oid){
	
			$OrderStatusChange=OrderStatusChange::where('oid',$oid)->orderby('created_at','DESC')->first();
			if($OrderStatusChange){
				$os_id = $OrderStatusChange['os_id'];
				
				if(in_array($os_id,$order_ongoing_os_id_arr)){
					$ongoing_oid_arr[]=$oid;
				}else{
					$history_oid_arr[]=$oid;
				}
			}else{
				$ongoing_oid_arr[]=$oid;
			}
		}
	}
	
	 //dd($ongoing_oid_arr);
	
	$orders_ongoing = Order::whereIn('oid',$ongoing_oid_arr)->orderby('created_at','DESC')->get();
	$orders_history = Order::whereIn('oid',$history_oid_arr)->orderby('created_at','DESC')->get();

	return view('customer.my_bookings',array('user'=>$user,'orders_ongoing'=>$orders_ongoing,'orders_history'=>$orders_history));

}	
	
	
	
	
	
	

	function order_status_change(request $request){
		$oid=$request->oid;
		
		$order=Order::find($oid);
		 

		$id=$order->id;
		$user=User::find($id);
 		 
		
		$reschedule_date=$request->reschedule_date;
		
		$cancel_reason=$request->cancel_reason;
		
		
		$os_id = $request->os_id;
		
		DB::table('order_status_changes')->insert(
			['oid' =>$oid, 'os_id' =>$os_id,'changes_by'=>'customer','changes_by_id'=>$id,'reschedule_date'=>$reschedule_date,'cancel_reason'=>$cancel_reason,'created_at'=>date('Y-m-d H:i:s')]
		);	
		
		
		///////////Send SMS to customer///////////////////////////
		
		$order_status=OrderStatus::find($os_id);
		$phone=$user->phone;
		
		$order_status_name=str_replace('Customer',' ',$order_status->name);
		$reschedule_msg='';
		
		if($reschedule_date!=''){
			$reschedule_msg=" to ".$reschedule_date;
			
 		}
		
   		$sms_msg="Hi, ".$user->name_title.". ".$user->name." Your order # ".$oid." has been ".$order_status_name." ".$reschedule_msg.". ".config('app.name');
		 
		$SMSController=new \App\Http\Controllers\SMSController;
 		$SMSController->sms_send($phone,$sms_msg);
		//////////////////////////
		
		echo 1;
		
	}
	
	
	
	
	
	
	
	
	
}	
	