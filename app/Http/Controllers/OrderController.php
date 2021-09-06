<?php 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

use App\Http\Requests;
use App\Page;
use App\Order;
use App\OrderItem;
use App\Category;
use App\Service;
use App\Coupon;
use App\CouponUsed;
use App\User;
use App\Setting;

use App\TechnicianLead;
use App\UserCredit;

use DB;
use Hash;

use Response;
use Auth;

use Mail;

use App\Mail\NewOrderPlaceEmailToCustomer;

class OrderController extends Controller
{


	function ajax_order_pace(request $request){
	
		$name_title=$request->name_title;	
		$name=$request->name;

		$dialCode=$request->dialCode;
		$number=$request->phone;
		$phone= $dialCode.$number;

		$services=$request->services;
		$min_amount_for_user=$request->min_amount_for_user;
		
		$transaction_id='';
		$transaction_id_arr=$request->transaction_id;
		if($transaction_id_arr){
			$transaction_id=implode("",$transaction_id_arr);
		}
		
		$records=$request->all();

		if (Auth::check()) {
			// The user is logged in...
			$user = Auth::user();	
			$id=$user->id;

		}else{

			//register user
			 $id=User::create([
			 		'name_title' =>$name_title,
					'name' =>$name,
					'password' =>Hash::make($phone),
					'phone' =>$phone,
			 ])->id;
			 $user=User::find($id);
			 
			 //login the user manually
			$user2 = User::where('phone',$phone)->where('status',1)->first();
 		    Auth::login($user2);
			  //
		}
		
		if($id!=''){
			
			//order table
			$records['phone']=$phone;
			$records['order_pass_code']=rand(1000,9999);
			$records['id']=$id;
			$records['invoice_date']=date('Y-m-d');
			$records['name_title']=$name_title;
	
			$records['min_amount_for_user']=$min_amount_for_user;
			 
			$records['cid']=$request->cid;
			
			$records['city_id']=$request->city_id;
			
			$records['ip']=$request->ip();
			
			//to generate Invoice no. 
			$last_invoice_no = Order::orderBy('oid','DESC')->pluck('invoice_no')->first();
			$nextInvoiceNumber = $last_invoice_no + 1;
 
	    	 //to generate Invoice no.  

			$records['invoice_no']=$nextInvoiceNumber;

			$records['transaction_id']=$transaction_id;
			
			$oid=Order::create($records)->oid;
			
			//if coupon used
			if($request->coupon_applied_id!=''){
				CouponUsed::create(['cid'=>$request->coupon_applied_id,'id'=>$id,'oid'=>$oid,'discount_amount'=>$request->coupon_applied_discount]);
			}
			////////////////
			
			
			//add Order Items
			$data=array();
			foreach($services as $sid){
				$service=Service::find($sid);
				$data[]=array('oid'=>$oid,'sid'=>$sid,'cid'=>$service->cid,'id'=>$id,'title'=>$service->title,'cost'=>$service->cost);
			}
			
			OrderItem::insert($data);			
		}
		$order=Order::find($oid);
		
		//Send Email to Customer///////////
		if($user->email!=''){ 
			//Mail::to($user->email)->send(new NewOrderPlaceEmailToCustomer($order));
		}
		////////////

		//Send SMS to Customer///////////
		$SMSController=new SMSController;
		$SMSController->sms_send_on_new_order_place_to_customer($phone,$oid);
		////////////
		
		//order status processing to main on order ongoing tab for customer
		$os_id=6; //processing
		DB::table('order_status_changes')->insert(
			['oid' =>$oid, 'os_id' =>$os_id,'changes_by'=>'auto','changes_by_id'=>0]
		);
		
		//
		if($oid!=''){
			return response()->json(array('status'=>1,'oid'=>$oid));
		}else{
			return response()->json(array('status'=>0));
		}
	
	}
	 
	
	
	function order_placed(request $request){
		
		$oid=$request->oid;

		if(Auth::user()){
			$user = Auth::user();	
			$id=$user->id;
			$count=Order::where('oid',$oid)->where('id',$id)->count();
			//dd($request->all());
			
			if($count > 0){
				$order=Order::find($oid);
				
				$cid=$order->cid;
				
			    $featured_categories=Category::where('featured',1)->where('status',1)->orderby('title','ASC')->take(6)->get();

				$TechnicianLead_count=TechnicianLead::where('oid',$oid)->count();
				
				return view('order_placed',array('order'=>$order,'featured_categories'=>$featured_categories,'TechnicianLead_count'=>$TechnicianLead_count));
			
			}else{
				
				return view('error',array('page_title'=>'Access Denied','message'=>'This is not your order !'));
 
			}
		}else{
			return view('error',array('page_title'=>'Access Denied','message'=>'Access Denied !'));
 		
		}
		
	}
	
	
	
	
	
	
	
	function ajax_coupon_apply(request $request){
		 
		$id=$request->id;  //for login user

		$coupon_code=$request->coupon;
		$sub_total=$request->sub_total;
		$grand_total=$request->grand_total;
		
		//dd($coupon_code);
	
		$count=Coupon::where('code',$coupon_code)
			->whereDate('date_start', '<=', date("Y-m-d"))
			->whereDate('date_end', '>=', date("Y-m-d"))
			->where('status',1)->count();
			
		 if($count>0){
			$coupon=Coupon::where('code',$coupon_code)
				->whereDate('date_start', '<=', date("Y-m-d"))
				->whereDate('date_end', '>=', date("Y-m-d"))
				->where('status',1)->first();
			
			//$uses_per_coupon=$coupon->uses_per_coupon;
			//$uses_per_customer=$coupon->uses_per_customer;
			
			$cid=$coupon->cid;
			
			$count_coupon_used=CouponUsed::where('cid',$cid)->count();
			
			if($count_coupon_used>0){
					return response()->json(['status' =>2]);
			}else{
			
				$coupon_type=$coupon->coupon_type;
				$discount=$coupon->discount;
				
				if($coupon_type=='F'){
					$discount=$discount;
				}else if($coupon_type=='P'){
					$discount=$sub_total*($discount/100);
				}
				
				$request->session()->put('cid',$cid); 	
				$request->session()->put('coupon_code',$coupon_code); 
				$request->session()->put('coupon_discount',$discount); 
			
				$grand_total_after_discount=$grand_total-$discount;
				
				return response()->json(['status' =>1,'cid'=>$coupon->cid,'discount'=>$discount,'grand_total_after_discount'=>$grand_total_after_discount]);
			}
			
 		 }else{
		 	 return response()->json(['status' =>0]);
		 }
 	}



	function ajax_coupon_remove(request $request){
	 	$request->session()->forget('coupon_code');
		$request->session()->forget('coupon_discount'); 
     	return 1;
  	}
	
	

	function ajax_wallet_apply(request $request){

		$sub_total=$request->sub_total;
		$grand_total=$request->grand_total;
		
		if(Auth::user()){
			$uid = Auth::user()->id;
			$userCredit=UserCredit::where('uid',$uid)->where('is_hold','<>',1)->where('status',1)->first();
			
			if($userCredit){
			
				$points = 30;  // $1 = 30 Points 
				$min_points = $points * 25;   //Min Point = 750 Points ($25)
				$total_points = $userCredit->total_points;
				if($total_points >= $min_points){

					$discount_amt = ($min_points / $points);   // $25
					$discount_points=$min_points;
					$grand_total_after_discount=$grand_total-$discount_amt;
					$total_points_after_discount = $total_points - $discount_points;
						
					//Use wallet points
					$uc_id = $userCredit->uc_id;
					$updateIshold = UserCredit::find($uc_id);
					$updateIshold->is_hold = 1;
					$updateIshold->save();

					$updateData=[
						"uid"       => $uid,
						"points" => '-'.$discount_points,
						"total_points" => $total_points_after_discount,
						"is_hold" => 0,
						"status"  => 1,        
					];
					$uc = UserCredit::create($updateData);
					/////////////////////////

					$request->session()->put('uc_id',$uc_id); 	
					$request->session()->put('prev_grand_total',$grand_total); 
					$request->session()->put('discount_amt',$discount_amt); 

					return response()->json(['status' =>1,'discount'=>$discount_amt,'grand_total_after_discount'=>$grand_total_after_discount]);
				
				}else{

					return response()->json(['status' =>2]);
				}
			}else{

				  return response()->json(['status' =>0]);
			}
		}
		
 	}
	
	
	
}