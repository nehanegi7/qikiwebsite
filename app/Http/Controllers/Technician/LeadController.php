<?php 
namespace App\Http\Controllers\Technician;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

use App\Http\Requests;
use App\Page;
use App\Category;
use App\Technician;
use App\TechnicianCategory;
use App\TechnicianCredit;
use App\IdentityProofType;
use App\TechnicianIDProof;
use App\TechnicianLead;

use App\City;
use App\Service;
use App\PhoneVarificationPartner;

use App\Order;
use App\OrderItem;
use App\OrderCompleted;
use App\OrderStatus;

use App\User;
use App\Setting;

use DB; 
use Redirect;
use Response;
use Auth; 
use Hash;
use Mail;
use PDF;

use App\Mail\TechnicianOrderCompleteWalletDeductEmailToTechnician;
use App\Mail\OrderCompletedEmailToCustomer;

class LeadController extends Controller
{
     
	function leads(request $request){
		$tid=$request->session()->get('tid');
		$technician=Technician::find($tid);
		
 		$check=Technician::where('tid',$tid)->where('status',1)->where('accept_order',1)->orderby('created_at','DESC')->count();
 
 		if($check==1){
			$leads=TechnicianLead::where('tid',$tid)->orderby('created_at','DESC')->get();

 			return view('technician.leads.leads',array('technician'=>$technician,'leads'=>$leads));
		
		}else{
			//dd('Access Denied for this account ');
			
			return redirect()->route('partner_profile');
		}
	}
	

	function lead_details(request $request,$tl_id){
		$tid=$request->session()->get('tid');
		
		$technician=Technician::find($tid);
		
		$lead=TechnicianLead::find($tl_id);
		
		 //dd($lead->tid);
		
		if($lead->tid!=$tid){
			return redirect()->route('partner_leads');
		}
		
		$uid=$lead->order->id;
		$oid=$lead->oid;
		
		$order=Order::find($oid);
		$category=Category::find($order->cid);
		$user=User::find($uid);
		
		$count_OrderCompleted=OrderCompleted::where('oid',$oid)->count();
		$OrderCompleted=OrderCompleted::where('oid',$oid)->first();
		
		$order_items=OrderItem::where('oid',$oid)->orderby('created_at','DESC')->pluck('title')->all();

		return view('technician.leads.lead_details',array('technician'=>$technician,'lead'=>$lead,'user'=>$user,'order'=>$order,
													'category'=>$category,
													'order_items'=>$order_items,
													'count_OrderCompleted'=>$count_OrderCompleted,
													'OrderCompleted'=>$OrderCompleted
													));
	
	}
	
	
	
	function order_status_change(request $request){
		$tid=$request->session()->get('tid');
		$technician=Technician::find($tid);
		
		$order_status_reschedule=$request->order_status_reschedule;
		
		$tl_id=$request->tl_id;
		$oid=$request->oid;
		
 		
		$reschedule_date=$request->reschedule_date;
		$cancel_reason=$request->cancel_reason;
		
		$private_complaint_msg=$request->private_complaint_msg;
		
		
		$os_id       = $request->os_id;
		
		DB::table('order_status_changes')->insert(
			['oid' =>$oid, 'os_id' =>$os_id,'changes_by'=>'technician','changes_by_id'=>$tid,'reschedule_date'=>$reschedule_date,
			'cancel_reason'=>$cancel_reason,'private_complaint_msg'=>$private_complaint_msg,'created_at'=>date('Y-m-d H:i:s')]
		);	
		
		
		///////////Send SMS to customer///////////////////////////
		
		$order=Order::find($oid);
		$order_status=OrderStatus::find($os_id);
	
		$id=$order->id;
		$user=User::find($id);
		$phone=$user->phone;
		
		$order_status_name= !empty($order_status->name) ? str_replace('Customer',' ',$order_status->name):'';
		$reschedule_msg='';
		
		if($reschedule_date!=''){
			$reschedule_msg=" to ".$reschedule_date;
			
 		}
		
		if($os_id==10){ //for unreachable
			 $sms_msg="Hi, ".$user->name_title.". ".$user->name." You are unreachable for your order # ".$oid." ".config('app.name');	
		}else{
   		 	$sms_msg="Hi, ".$user->name_title.". ".$user->name." Your order # ".$oid." has been ".$order_status_name." ".$reschedule_msg.". ".config('app.name');
		} 
		 
		$SMSController=new \App\Http\Controllers\SMSController;
 		$SMSController->sms_send($phone,$sms_msg);
		//////////////////////////

		echo 1;
		
	}
	
	
	
	function order_bill_now(request $request){

		$tid=$request->session()->get('tid');
		
		$oid=$request->oid;
		$tl_id=$request->tl_id;
		
		$technician=Technician::find($tid);
		$lead=TechnicianLead::find($tl_id);
		
		$order=Order::find($oid);
		$category=Category::find($order->cid);
		$user=User::find($order->id);
		
		$order_items=OrderItem::where('oid',$oid)->orderby('created_at','DESC')->get();
		
		$count_OrderCompleted=OrderCompleted::where('oid',$oid)->count();
		
		return view('technician.leads.order_bill_now',array('technician'=>$technician,'lead'=>$lead,'user'=>$user,'order'=>$order,
														'category'=>$category,'order_items'=>$order_items,'count_OrderCompleted'=>$count_OrderCompleted));
		
	
	}
	
	
	
	
	
	function upload_order_case_picture(request $request){
		
		$case_image_type=$request->case_image_type;
		$oid=$request->oid;
		$tl_id=$request->tl_id;
		
		$tid=$request->session()->get('tid');
				
		$order=Order::find($oid);			
		
		$id=$order->id;		 
		
		//dd($records);
		
		$fileName = $request->file->getClientOriginalName();
		$fileName =time()."_".$fileName;
		$request->file->move('uploads/technician/case_image/', $fileName);
				
		DB::table('order_case_image')->insert([
			['oid'=>$oid,'tid' =>$tid,'tl_id'=>$tl_id,'id'=>$id,'case_image_type' =>$case_image_type,'image'=>$fileName,'created_at'=>date('Y-m-d H:i:s')]
		]);
			
				
		return response()->json(['uploaded' => '/uploads/technician/case_image/'.$fileName]);
	
	}
	
	
	
	function order_bill_now_submit(request $request){
		$tid=$request->session()->get('tid');
		
		$oid=$request->oid;
		$tl_id=$request->tl_id;
		
		$grand_total=$request->grand_total;
		
		$net_grand_total=$request->net_grand_total;

 		$payment_method=$request->payment_method;
		
		$technician=Technician::find($tid);
		$lead=TechnicianLead::find($tl_id);

		//service charge //

		$service_charge = '';
		$new_wallet_balance = '';

		$setting=new \App\Setting;
		$minimum_wallet_balance=$setting->get_setting('minimum_wallet_balance');

		// $scp=$technician->service_charge_per;
		// $scp2=100-$scp;
		// $service_charge=$net_grand_total*$scp/100;
		// $service_charge2=$net_grand_total*$scp2/100;

		//Slabs to charge vendors on every sale
		if($net_grand_total > 3000){
			$scp='5';     //5%
		}elseif($net_grand_total <= 3000 && $net_grand_total >= 751){
			$scp='6';     //6%
		}else{
			$scp='7.5';   //7.5%
		}
		$service_charge=$net_grand_total * $scp/100;
		$service_charge2=$net_grand_total - $service_charge;

		
		$TechnicianCredit=TechnicianCredit::where('tid',$tid)->where('status',1)->orderby('created_at','DESC')->first();
		
		if(!empty($TechnicianCredit)){
			$wallet_balance=$TechnicianCredit->total_balance;
			
			$new_wallet_balance=$wallet_balance-$service_charge;
			
			if($new_wallet_balance<$minimum_wallet_balance){
				return response()->json(['status' =>0,'msg'=>'Sorry You do not have sufficient balance in your wallet ']);
			} 
		}else{
			return response()->json(['status' =>0,'msg'=>'Sorry You do not have sufficient balance in your wallet ']);
		}

		///////////////////////

		$order=Order::find($oid);
		$category=Category::find($order->cid);
		$user=User::find($order->id);
		
		$records=$request->all();
		$records['tid']=$tid;
		$records['cid']=$order->cid;
		$records['id']=$order->id;
		$records['ip']=$request->ip();
		
		$oc_id=OrderCompleted::create($records)->oc_id;
		
		//add more billing item 
		if($oc_id!=''){
			
			$order_items=$request->order_items;
			$order_items_amount=$request->order_items_amount;
			
			$total_amount_for_added_items=0;	
				
			if(is_array($order_items)){
				if(!empty($order_items)){
					$data=array();
					
					foreach($order_items as $k=>$order_item){
						
						$amount=$order_items_amount[$k];
						
						$total_amount_for_added_items+=$amount;
						
						if($order_item!=''){
						
							$data[]=array('oid'=>$oid,'id'=>$order->id,'title'=>$order_item,'cost'=>$amount,'is_extra_order_items'=>1);
						}
					}
			
					OrderItem::insert($data);		
					
					//also update on grand_total value on order table
					if($total_amount_for_added_items>0){
						$order=Order::find($oid);
						
						$order->grand_total=$net_grand_total;
						$order->save();
					}
					/////////

			  	}		
			}
			
		}
		////

		if($oc_id!=''){

			// deduct service charge from technician wallet
			if($payment_method=='cash'){
				$status=1;
			
				DB::table('technician_credits')->insert([
					['oid'=>$oid,'tid' =>$tid,'tl_id'=>$tl_id,'recharge_amount' =>'-'.$service_charge,'total_balance'=>$new_wallet_balance,
					 'payment_method'=>$payment_method,'status'=>$status,'created_at'=>date('Y-m-d H:i:s')]
				]);
				
				DB::table('order_service_charge')->insert([
					['oid'=>$oid,'cid'=>$order->cid,'tid' =>$tid,'tl_id'=>$tl_id,'id'=>$order->id,'grand_total' =>$grand_total,
					'commission_amount'=>$service_charge,'commission_percentage'=>$scp,'created_at'=>date('Y-m-d H:i:s')]
				]);
			
			
			}else if($payment_method=='online'){
				$status=1;
				DB::table('technician_credits')->insert([
					['oid'=>$oid,'tid' =>$tid,'tl_id'=>$tl_id,'recharge_amount' =>'-'.$service_charge,'total_balance'=>$new_wallet_balance,
					 'payment_method'=>$payment_method,'status'=>$status,'created_at'=>date('Y-m-d H:i:s')]
				]);
				
				DB::table('order_service_charge')->insert([
					['oid'=>$oid,'cid'=>$order->cid,'tid' =>$tid,'tl_id'=>$tl_id,'id'=>$order->id,'grand_total' =>$grand_total,
					'commission_amount'=>$service_charge,'commission_percentage'=>$scp,'created_at'=>date('Y-m-d H:i:s')]
				]);

				//when get direct online 
				//$status=0;
				// DB::table('technician_credits')->insert([
				// 	['oid'=>$oid,'tid' =>$tid,'tl_id'=>$tl_id,'recharge_amount' =>$service_charge2,
				// 	 'payment_method'=>$payment_method,'is_hold'=>1,'status'=>$status]
				// ]);
			}
			/////
		
		
			//order status change
			$os_id = 4;  //for order status 'completed' 
			DB::table('order_status_changes')->insert(
				['oid' =>$oid, 'os_id' =>$os_id,'changes_by'=>'technician','changes_by_id'=>$tid,'created_at'=>date('Y-m-d H:i:s')]
			);	
			///////
		
		
			//Send Email to Technician regarding wallet amount deducted for the order completion
			if($technician->email!=''){
				// Mail::to($technician->email)->send(new TechnicianOrderCompleteWalletDeductEmailToTechnician(
				// 																				['tid'=>$tid,
				// 																				'service_charge'=>$service_charge,
				// 																				'total_balance'=>$new_wallet_balance,
				// 																				'payment_method'=>$payment_method
				// 																				]));
			}
			/////////
		
		
			//Send email to customer for order completed
			if($user->email!=''){ 
			 	//Mail::to($user->email)->send(new OrderCompletedEmailToCustomer($order));
			}
			////////
		
			//Send SMS
			if($user->phone!=''){
				$sms_msg="Hi, ".$user->name_title.". ".$user->name." Your order # ".$order->oid." has been completed. ".config('app.name');
				$SMSController=new \App\Http\Controllers\SMSController;
				$SMSController->sms_send($user->phone,$sms_msg);
			}
			
			return response()->json(['status' =>1,'msg'=>'Successfully Order  Completed !']);

		}
	
	}
	
	
	
	
}