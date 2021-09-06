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
use App\TechnicianPersonalDetail;
use App\TechnicianCurrentAddress;

use App\City;
use App\Service;
use App\PhoneVarificationPartner;

use DB; 
use Redirect;
use Response;
use Auth; 
use Hash;
use Session;

use Mail;
use App\Mail\NewSellerRegisterWelcomeEmailToSeller;
use App\Mail\TechnicianWalletRechargeEmailToTechnician;

class TechnicianController extends Controller
{

	function index(){
	
	  return view('technician.index');
 	}
	

	function login_form(){
	
		return view('technician.login');
	}

	static function technician_info($tid){
	
		$technician=Technician::find($tid);
		return $technician;
	}

	function login(request $request){
		
		$email=$request->email;
 		$password=$request->password;
	 
		$this->validate($request, [
			'email' => 'required',
			'password'=>'required',
		]); 

		$results = DB::select('SELECT * from technicians where  email = :email', ['email' =>$email]);
		if(count($results)>0){
		
			if (Hash::check($password,$results[0]->password)){
				
				$request->session()->put('is_technician_logged', 'yes');
				$request->session()->put('tid',$results[0]->tid);
					
				return \Redirect::route('partner_profile')->with('message', 'Success!');

			}else{
			
				return \Redirect::route('login_from')->with('message', 'Invalid Password!');
			}
			
		}else{
			return \Redirect::route('login_from')->with('message', 'Invalid Username/Password !');
		}
	}
	
	   
	function profile(request $request){

		$tid=$request->session()->get('tid');
		
		$technician=Technician::find($tid);

		$TechnicianPersonalDetail=TechnicianPersonalDetail::where('tid',$tid)->first();

		$cities=City::orderby('title','ASC')->get();
		
		$sub_categories=Category::where('parent_cid','<>',0)->where('status',1)->orderby('title','ASC')->get();

		$technician_cid=TechnicianCategory::where('tid',$tid)->orderby('t_cid','DESC')->pluck('cid')->toArray();

		return view('technician.profile',array('technician'=>$technician, 'PersonalDetail'=>$TechnicianPersonalDetail, 'sub_categories'=>$sub_categories,'cities'=>$cities,'technician_cid'=>$technician_cid));
	
	}
	
	
	
	public function updateProfile(Request $request)
    {
		$tid=$request->session()->get('tid');
		$cid_arr=$request->cid;

		$technician_old_cid_count=TechnicianCategory::where('tid',$tid)->count();

        if($tid){
  			
			//validation not working if field disabled 	
			/*$messages=array(
				'sid.required'=>'Pls choose your primary profession !',
			);

		   	$this->validate($request, [
				'sid' => 'required', 
 			 ],$messages); */
			
			$technician = Technician::find($tid);
			$phone=$technician->phone;
 			 
			 //to send welcome to seller when email mentioned first time
 			$old_email=$technician->email;
			
			if(is_array($cid_arr)){
			
				$cid_arr=Category::whereIn('cid',$cid_arr)->pluck('cid')->toArray();
				$cid_arr=array_unique($cid_arr);
				
				$category_title_arr=Category::whereIn('cid',$cid_arr)->pluck('title')->toArray();
				
				$categories_name=implode(" , ",$category_title_arr);
				
				//Send SMS to Seller///////////
				//to send first time 
				if($technician_old_cid_count==0){
					if($phone != ''){
						$SMSController=new \App\Http\Controllers\SMSController;
						$SMSController->sms_send_on_new_seller_register_to_seller($phone,$tid,$category_title_arr);
					}
				}
				////////////
 				
				if($old_email=='' && $request->email!=''){
					//to send first time  
					Mail::to($request->email)->send(new NewSellerRegisterWelcomeEmailToSeller(['tid'=>$tid,'categories_name'=>$categories_name]));
				}
			}
			//////
			
			//to disable editing for verified technician
 			if($technician->badge==0){
				$technician->name   = $request->name;
				$technician->phone  = $request->phone;
				$technician->email    = $request->email;
				$technician->city_id   = $request->city_id;
			}
			
			$technician->accept_order = $request->accept_order;
			
			if($request->password!=''){
				$hashed_password=Hash::make($request->password);
	   	 		$technician->password=$hashed_password;
			}
			 
			if ($request->hasFile('photo')) {
				$this->validate($request, [
					'photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg',
				]); 
				
				if ($request->file('photo')->isValid()) {
					
					$fileName=$request->file('photo')->getClientOriginalName();
					$fileName =time()."_".$fileName;
					//upload
					$request->file('photo')->move('uploads/technician/photo/', $fileName);
						
					//column name 
					$technician->photo=$fileName;
				}
			}
            $technician->save();
			
			//store technician personal details
			$count=TechnicianPersonalDetail::where('tid',$tid)->count();
			if($count==0){
				$data = array(
					'tid'=>$tid,
					'permanent_address_house_no' =>$request->permanent_address_house_no,
					'permanent_address_locality' =>$request->permanent_address_locality,
					'permanent_address_pincode' =>$request->permanent_address_pincode,
					'city' =>$request->city,
					'state' =>$request->state,
				);
				TechnicianPersonalDetail::insert($data);
			}else{
				DB::table('technician_personal_details')
					->where('tid',$tid)
					->update([
						'permanent_address_house_no' =>$request->permanent_address_house_no,
						'permanent_address_locality' =>$request->permanent_address_locality,
						'permanent_address_pincode' =>$request->permanent_address_pincode,
						'city' =>$request->city,
						'state' =>$request->state,
					]);		
			}

			if($technician->badge==0){
				$data=array();
				if(is_array($cid_arr)){
					foreach($cid_arr as $cid){
						$data[]=array('tid'=>$tid,'cid'=>$cid);
					}
					
					TechnicianCategory::where('tid',$tid)->delete();
					TechnicianCategory::insert($data);
				}	
			}

			//Check Technician credit balance and store signup credit if new Technician
			/* $ctcb = TechnicianCredit::where('tid',$tid)->get()->toArray();
			if(empty($ctcb)){
				$setting=new \App\Setting;
				$signup_credit = $setting->get_setting('vendor_signup_credit');

				$userdata=[
					"tid"       => $tid,
					"total_balance" => $signup_credit,
					"payment_method" => 'cash',
					"is_recharged_by_admin" => 1,    //Credit       
					"status"  => 1,        
				];
				$tc = TechnicianCredit::create($userdata);
			} */

		 	return Redirect::back()->with('message','Successfully Updated!');
        }
    }

	
	
	function ajax_otp_send_for_login_partner(request $request){
		$send_otp=0;
		$phone=$request->phone;
		$otp=rand(1000,9999);
		
		$countv=PhoneVarificationPartner::where('phone',$phone)->count();
		$countu = Technician::where('phone',$phone)->where('email','!=','')
				->where('password','!=','')->where('badge','!=','0')->count();
		if($countv > 0){

			if($countu > 0){

				$message="Already verified vendor"; 	
				$status=2;

			}else{

				DB::table('phone_varification_partner')
					->where('phone',$phone)
					->update(['otp' =>$otp]);		

				$message="OTP sent to your mobile no"; 
				$sms_msg="Please enter the OTP ".$otp." to verify your phone.";

				$SMSController=new \App\Http\Controllers\SMSController;
				$SMSController->sms_send($phone,$sms_msg);	
				$status=1;	
			} 

		}else{

			DB::table('phone_varification_partner')->insert(
				['phone' =>$phone,'otp'=>$otp]
			);

			$message="OTP sent to your mobile no. "; 		
			$sms_msg="Please enter the OTP ".$otp." to verify your phone .";
			
			$SMSController=new \App\Http\Controllers\SMSController;
			$SMSController->sms_send($phone,$sms_msg);
			
			$status=1;	

		}
		
		return response()->json(array('status'=>$status,'message'=>$message));
	}
	
	
	
	 
	
	function check_otp_for_login_with_phone_partner(request $request){
		$phone=$request->phone;
		$otp=$request->otp;
		 
 	   	$count=PhoneVarificationPartner::where('phone',$phone)->where('otp',$otp)->count();
		if($count>0){
 				$status=1;
				$message="This phone no. successfully verified"; 
					
				DB::table('phone_varification_partner')
					->where('phone',$phone)
					->where('otp',$otp)
					->update(['is_varified' =>1]);
						
				//check the phone no. is already registred,  
				$user_exists=Technician::where('phone',$phone)->count();
				
				if($user_exists>0){
				   
					$user_active=Technician::where('phone',$phone)->where('status',1)->count();
					
					if($user_active>0){
				   
						$technician = Technician::where('phone',$phone)->where('status',1)->first();
						$tid=$technician->tid;
						//dd($technician);
					}else{
						$status=0;
						$message="Your account is Suspended Please Contact Customer Support !";
						$tid=0;
					}
					
				}else{
				
					$tid=Technician::create([
							'phone' =>$phone,
					 ])->tid;
					$user=Technician::find($tid);
		
					//Send SMS to Seller///////////
					//not sending here . Send on edit profile when cateogry selects
					//$SMSController=new \App\Http\Controllers\SMSController;
					//$SMSController->sms_send_on_new_seller_register_to_seller($phone,$tid);
					////////////
				}
				
  				//login the user
				$request->session()->put('is_technician_logged', 'yes');
				$request->session()->put('tid',$tid);
				///////////
		 }else{
				$status=0;
				$message="Incorrect OTP";
				$tid=0;
		 }
				
		 return response()->json(array('status'=>$status,'message'=>$message,'tid'=>$tid));
	}

	 
	 

	function recharge_page(request $request){
		
		$tid=$request->session()->get('tid');
		if($tid){
			$technician=Technician::find($tid);
			$email=$technician->email;
			
			$phone=$technician->phone;
			
			$redirect=url('/partner/recharge_step2?email='.$email.'&phone='.$phone);
		   return redirect($redirect);

		}
	
		return view('technician.recharge.recharge',array());
	}
	 
	 
	function recharge_step2(request $request){
		 
		$email=$request->email;
		$phone=$request->phone;
		
		$count_technician=Technician::where('phone',$phone)->count();
		
		if($count_technician==0){
		 	
			return redirect()->back()->withErrors('The phone no. '.$phone.' is not registered with us !');

		}else{
		
			$check_active=$count_technician=Technician::where('phone',$phone)->where('status',1)->count();
			
			if($check_active==0){
				 //return redirect()->back()->withErrors('This account is currently Suspended Contact Customer Care Support !');
				  return redirect()->back();

			}else{
		
				return view('technician.recharge.recharge_step2',array());

			}
		}
	}
	
	
	
	function test_recharge(request $request){

		//dd($request->all());
		\Stripe\Stripe::setApiKey ('sk_test_51HxGmYCbhZel9NwNbpZZcVWJOfJiAD5JtvTdkcwbNuVYbsdvBWVAX9SEroZrjAbS9daJQxlMnI6erCzYDN6DsnIT00bsvBrBhy');
		try {
			$ab = \Stripe\Charge::create ( array(
					"amount" => 1 * 100,
					"currency" => "INR",
					"source" => $request->input('stripeToken'), 	// obtained with Stripe.js
					"description" => "Test payment." 
			));
			//dd($ab->id);
			Session::flash ( 'success-message', 'Payment done successfully !' );
			return Redirect::back ();
		} catch ( \Exception $e ) {
			Session::flash ( 'fail-message', "Error! Please Try again." );
			return Redirect::back ();
		}
	} 


		 
	function recharge_process(request $request){
		
		$today= date("Y-m-d H:i:s");
		$email=$request->email;
		$phone=$request->phone;
		$amount=$request->amount;
		$payment_method=$request->payment_method;
		
		$transaction_id='';
		$transaction_id_arr=$request->transaction_id;
		
		if($transaction_id_arr){
			$transaction_id=implode("",$transaction_id_arr);
		}
		
		$request->session()->put('recharge_phone',$phone);
		$request->session()->put('recharge_email',$email);
		$request->session()->put('recharge_amount',$amount);

		$count_technician=Technician::where('phone',$phone)->count();
		
		if($count_technician>0){
		
			$technician=Technician::where('phone',$phone)->first();
			$tid=$technician->tid;
			
			$count=TechnicianCredit::where('tid',$tid)->where('status',1)->count();
			
			if($count>0){
				$TechnicianCredit=TechnicianCredit::where('tid',$tid)->where('status',1)->orderby('created_at','DESC')->first();
				$total_balance_old=$TechnicianCredit->total_balance;
				
			}else{
				$total_balance_old=0;
			}
			 
			$new_total_balance=$total_balance_old+$amount;
			 

			//Payment gateway integration here
			\Stripe\Stripe::setApiKey(config('services.stripe')['secret']);
			try {
				$txnobj = \Stripe\Charge::create (array(
						"amount" => $amount * 100,
						"currency" => config('services.stripe')['currency'],
						"source" => $request->input('stripeToken'), 	// obtained with Stripe.js
						"description" => "Test payment." 
				));
				$transaction_id = $txnobj->id;

				//Save data
				DB::table('technician_credits')->insert([
					['tid' => $tid, 'recharge_amount' => $amount,'total_balance' => $new_total_balance,'status'=>1,
					'payment_method'=>$payment_method,'transaction_id'=>$transaction_id,'created_at'=>$today]
				]);


				//Send Email (Currently not sendign becasue email will send when admin approve this transaction)
				if($technician->email!=''){
					Mail::to($technician->email)->send(new TechnicianWalletRechargeEmailToTechnician(
						['tid'=>$tid,
						'recharge_amount'=>$amount,
						'total_balance'=>$new_total_balance,
						'payment_method'=>$payment_method
						]));
				}
				///
			
				//return redirect()->route('recharge_done');
				Session::flash ( 'success-message', 'Payment done successfully !' );
				return Redirect::back ();

			} catch ( \Exception $e ) {
				Session::flash ('fail-message', "Error! Please Try again.");
				return Redirect::back ();
			}

			
			/* //Payment gateway integration here (previous)
			$payment_gateway_success=2;
			if($payment_gateway_success==1){
			 
				DB::table('technician_credits')->insert([
					['tid' => $tid, 'recharge_amount' =>$amount,'status'=>0,
					'payment_method'=>$payment_method,'transaction_id'=>$transaction_id,'created_at'=>$today]
				]);
				
				//Send Email (Currently not sendign becasue email will send when admin approve this transaction)
				if($technician->email!=''){
					Mail::to($technician->email)->send(new TechnicianWalletRechargeEmailToTechnician(
						['tid'=>$tid,
						'recharge_amount'=>$amount,
						'total_balance'=>$new_total_balance,
						'payment_method'=>$payment_method
						]));
				}
				///
				return redirect()->route('recharge_done');
			}else{
				return redirect()->route('recharge_failed');
			}
			*/

		}else{
			
			return redirect()->route('recharge_failed');
		
		}
	}

	

	function recharge_done(request $request){
		
		$phone=$request->session()->get('recharge_phone');
		$email=$request->session()->get('recharge_email');
		$recharge_amount=$request->session()->get('recharge_amount');
		 
		$technician=Technician::where('phone',$phone)->first();
		$tid=$technician->tid;

		$credit_history=TechnicianCredit::where('tid',$tid)->orderby('created_at','DESC')->get();

		return view('technician.recharge.recharge_done',array('technician'=>$technician,'recharge_amount'=>$recharge_amount,'credit_history'=>$credit_history));
	}
	 
	 
	 
	function recharge_failed(request $request){	
		$tid=$request->session()->get('tid');
		$technician=Technician::find($tid);	
 
		return view('technician.recharge.recharge_failed',array('technician'=>$technician));
	}
	 
	 
	function recharge_history(request $request){	
		$tid=$request->session()->get('tid');
		$technician=Technician::find($tid);	
		
 		$credit_history=TechnicianCredit::where('tid',$tid)->where('status',1)->orderby('created_at','DESC')->paginate(20);

		return view('technician.recharge.recharge_history',array('technician'=>$technician,'credit_history'=>$credit_history));
	}
	 
	 

	function check_document_submitted($document){
		$request=request();
		$tid=$request->session()->get('tid');
		
		$technician=Technician::find($tid);	
		
 		switch($document){

			//case "identity_proof":
			
			//$IdentityProofTypes_ipt_id=IdentityProofType::where('status',1)->pluck('ipt_id')->all();
		
			// $count_TechnicianIDProof=TechnicianIDProof::where('tid',$tid)->count();
			
			// if($count_TechnicianIDProof>0){
			//  	$count2_TechnicianIDProof=TechnicianIDProof::where('tid',$tid)
			// 	->where('name','!=','')
			// 	->where('number','!=','')
			// 	->where('image_front','!=','')
			// 	->where('image_back','!=','')
			// 	->count();
				
			// 	if($count2_TechnicianIDProof==1){
			// 		return 1;
			// 	}
			// }			

			// break;

			case "personal_details":
		
			$count_TechnicianPersonalDetail=TechnicianPersonalDetail::where('tid',$tid)->count();
			
			if($count_TechnicianPersonalDetail>0){
			
				$count2_TechnicianPersonalDetail=TechnicianPersonalDetail::where('tid',$tid)
				->where('father_mother_name','!=','')
				->where('gender','!=','')
				->where('dob','!=','')
				->where('permanent_address_house_no','!=','')
				->where('permanent_address_locality','!=','')
				->where('permanent_address_pincode','!=','')
				->where('city','!=','')
				->where('state','!=','')
				->count();
				
				if($count2_TechnicianPersonalDetail==1){
					return 1;
				}
			}
 			 
			break;
			
			case "current_address":

			$count_TechnicianCurrentAddress=TechnicianCurrentAddress::where('tid',$tid)->count();
			
			if($count_TechnicianCurrentAddress>0){
			
				$count2_TechnicianCurrentAddress=TechnicianCurrentAddress::where('tid',$tid)
 				->where('permanent_address_house_no','!=','')
				->where('permanent_address_locality','!=','')
				->where('permanent_address_pincode','!=','')
				->where('city','!=','')
				->where('state','!=','')
				->count();
				
				//dd($count2_TechnicianPersonalDetail);
				if($count2_TechnicianCurrentAddress==1){
					return 1;
				}
			}
 			 
			break;
			
			case "declaration":
			 
 			$count=Technician::where('tid',$tid)->count();

			if($technician->declaration_is_agree==1){
				return 1;
			} 
			
			break;
		}

	} 
	
	 
	 
	function finance_details(request $request){
		$tid=$request->session()->get('tid');
		$technician=Technician::find($tid);	

		$check_identity_proof=$this->check_document_submitted('identity_proof');
		$check_personal_details=$this->check_document_submitted('personal_details');
		//dd($check_identity_proof);
		$check_current_address=$this->check_document_submitted('current_address');
 		
		return view('technician.finance.finance_details',array(
					'technician'=>$technician,
					'check_identity_proof'=>$check_identity_proof,
					'check_personal_details'=>$check_personal_details,
					'check_current_address'=>$check_current_address,
					'declaration_is_agree' => $technician->declaration_is_agree
			));
	
	} 
	 
	 
	function identity_proof(request $request){
		$tid=$request->session()->get('tid');
		$technician=Technician::find($tid);	
 
 		$IdentityProofTypes=IdentityProofType::where('status',1)->orderby('weight','ASC')->get();
		
		$TechnicianIDProofs=TechnicianIDProof::where('tid',$tid)->get();
		
		return view('technician.finance.identity_proof',array('technician'=>$technician,'IdentityProofTypes'=>$IdentityProofTypes,'TechnicianIDProofs'=>$TechnicianIDProofs));
	} 
	
	
	
	function identity_proof_submit(request $request){
		$tid=$request->session()->get('tid');
		$technician=Technician::find($tid);	
 		
		$ipt_id=$request->ipt_id;
		$name=$request->name;
		$number=$request->number;
		
  			
		$count=TechnicianIDProof::where('tid',$tid)->where('ipt_id',$ipt_id)->count();
			
		//dd($request->all());
		if($count==0){
			DB::table('technician_id_proofs')->insert([
				['tid' =>$tid,'ipt_id' =>$ipt_id,'name'=>$name,'number'=>$number]
			]);
		}else{
			DB::table('technician_id_proofs')
			->where('tid',$tid)
			->where('ipt_id',$ipt_id)
			->update(['name' =>$name,'number' =>$number]);			
		
		}

 		return Redirect::back()->with('message','Successfully Updated!');
 	
	} 
	 
	 
	 
	function get_id_proof_ajax(request $request){
	 	$tid=$request->tid;
		$ipt_id=$request->ipt_id;
	 	
		$count=TechnicianIDProof::where('tid',$tid)->where('ipt_id',$ipt_id)->count();
		$TechnicianIDProof=TechnicianIDProof::where('tid',$tid)->where('ipt_id',$ipt_id)->first();
		if($count>0){

		    return response()->json(array('count'=>1,'TechnicianIDProof'=>$TechnicianIDProof));

		}else{
			
		    return response()->json(array('count'=>0));

		
		}
	}
	 
	 
	function logout(request $request){
	 	$tid=$request->tid;
		$ipt_id=$request->ipt_id;
		
		$request->session()->forget('is_technician_logged');
		$request->session()->forget('tid');
	 	
		  return redirect('partner/');

	}

	 
	/*function test(request $request){
		$tid=$request->session()->get('tid');
		$technician=Technician::find($tid);	

		return view('technician.finance.test',array('technician'=>$technician));
	}*/
	
	
	public function ajax_upload_id_proof_document(request $request)
    {
		$image_type=$request->image_type;
		$tid=$request->tid;
		$ipt_id=$request->ipt_id;
		
		$fileName = $request->file->getClientOriginalName();
		$fileName =time()."_".$fileName;
		$request->file->move('uploads/technician/id_proof/', $fileName);
			
		$count=TechnicianIDProof::where('tid',$tid)->where('ipt_id',$ipt_id)->count();

		if($count==0){
			DB::table('technician_id_proofs')->insert([
				['tid' =>$tid,'ipt_id' =>$ipt_id,$image_type=>$fileName,'created_at'=>date('Y-m-d H:i:s')]
			]);
		}else{
			DB::table('technician_id_proofs')
			->where('tid',$tid)
			->where('ipt_id',$ipt_id)
			->update([$image_type =>$fileName]);			
		
		}
			
		return response()->json(['uploaded' => '/uploads/technician/id_proof/'.$fileName]); 
    }
	


	function personal_details(request $request){
		$tid=$request->session()->get('tid');
		$technician=Technician::find($tid);	
 
		$TechnicianPersonalDetail=TechnicianPersonalDetail::where('tid',$tid)->first();
		
		return view('technician.finance.personal_detail',array('technician'=>$technician,'PersonalDetail'=>$TechnicianPersonalDetail));
	} 
	
	
	
	
	function personal_details_submit(request $request){

		$tid=$request->session()->get('tid');
		$technician=Technician::find($tid);	
 		
 		$count=TechnicianPersonalDetail::where('tid',$tid)->count();

		if($count==0){
			TechnicianPersonalDetail::create($request->all());
			
		}else{
			DB::table('technician_personal_details')
			->where('tid',$tid)
			->update([
					'father_mother_name' =>$request->father_mother_name,
					'gender' =>$request->gender,
					'dob' =>$request->dob,
					'permanent_address_house_no' =>$request->permanent_address_house_no,
					'permanent_address_locality' =>$request->permanent_address_locality,
					'permanent_address_pincode' =>$request->permanent_address_pincode,
					'city' =>$request->city,
					'state' =>$request->state,
					]);			
		
		}

 		return Redirect::back()->with('message','Successfully Updated!');
	} 
	
	
	
	function current_address(request $request){
		$tid=$request->session()->get('tid');
		$technician=Technician::find($tid);	
 
		$TechnicianCurrentAddress=TechnicianCurrentAddress::where('tid',$tid)->first();
		
		return view('technician.finance.current_address',array('technician'=>$technician,'CurrentAddress'=>$TechnicianCurrentAddress));
	} 
	
	
	
	
	function current_address_submit(request $request){

		$tid=$request->session()->get('tid');
		$technician=Technician::find($tid);	
 	
		$count=TechnicianCurrentAddress::where('tid',$tid)->count();

		if($count==0){
			TechnicianCurrentAddress::create($request->all());
			
		}else{
			DB::table('technician_current_address')
			->where('tid',$tid)
			->update([
					'permanent_address_house_no' =>$request->permanent_address_house_no,
					'permanent_address_locality' =>$request->permanent_address_locality,
					'permanent_address_pincode' =>$request->permanent_address_pincode,
					'city' =>$request->city,
					'state' =>$request->state,
					]);			
		
		}

 		return Redirect::back()->with('message','Successfully Updated!');
	} 

	
	

	function declaration(request $request){
		$tid=$request->session()->get('tid');
		$technician=Technician::find($tid);	
 
 		return view('technician.finance.declaration',array('technician'=>$technician));

	} 
	
	
	
	function declaration_submit(request $request){
		$tid=$request->session()->get('tid');
		$technician=Technician::find($tid);	

		DB::table('technicians')
				->where('tid',$tid)
 				->update([
						'declaration_is_agree' =>1,
 						]);	

 		//return Redirect::back()->with('message','Successfully Updated!');
 		return \Redirect::route('partner_profile');
	} 
	
	
	
}