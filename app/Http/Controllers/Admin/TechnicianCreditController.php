<?php

namespace App\Http\Controllers\Admin;

use View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Technician;
use App\TechnicianCategory;
use App\TechnicianCredit;
use App\TechnicianLead;
use App\Order;

  

use Hash;
use DB;
use Redirect;
use Mail; 

use App\Mail\TechnicianWalletRechargeEmailToTechnician;

class TechnicianCreditController extends Controller
{
	
	var $type='technician_wallet';

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
		 
		 
		$technicians=Technician::where('status',1)->where('accept_order',1)->orderby('name','ASC')->get();
 
 
 
 
 	$tid=$request->tid;
	 
		 $status=$request->status; 
 	  
	  $items=TechnicianCredit::
		
 			 when($tid, function ($query) use ($tid) {
                	  	 return $query->where('tid',$tid);      
  				 })	
										
		 							
			
			->when($status, function ($query) use ($status) {
							
							$status=str_replace("status|","",$status);
							//dd($status);
                	  	 	 return $query->where('status',$status);      
  				  })	
			
			
		->orderby('created_at','DESC')->orderby('status','ASC')->paginate(50);
 
 	  
 	   
	   
	   $items->appends($request->all());
	   
	   
		
      return view('admin.technician_credit.index',array('items'=>$items,'technicians'=>$technicians));

		
    }
	
	
	
	
	
	
	 
	

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
	 	$technicians=Technician::where('status',1)->where('accept_order',1)->orderby('name','ASC')->get();

        return view('admin.technician_credit.create',array('technicians'=>$technicians));
    }






    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
	 
	 
	 
    public function store(Request $request)
    {
 	   
	   $tid=$request->tid;
 	   $amount=$request->recharge_amount;
	   $payment_method=$request->payment_method;
	     $transaction_id=$request->transaction_id;
		 
		 $technician=Technician::find($tid);
	   
			 $this->validate($request, [
				'tid' => 'required',
				'recharge_amount' => 'required',
 				'status'=>'required',
 			 ]); 
				 
		 
		 
		 $records=$request->all();
		 
		  
		  
		  
		  $count=TechnicianCredit::where('tid',$tid)->count();
			
			if($count>0){
 				$TechnicianCredit=TechnicianCredit::where('tid',$tid)->where('status',1)->orderby('created_at','DESC')->first();

				$total_balance_old=$TechnicianCredit->total_balance;
				
			}else{
				$total_balance_old=0;
			}
			 
			 $new_total_balance=$total_balance_old+$amount;
			 
			 
			 //Payment gateway integration here
 			 
 			  $today= date("Y-m-d H:i:s");
			  
			 // dd($today);
			  
				DB::table('technician_credits')->insert([
					['tid' => $tid, 'recharge_amount' =>$amount,'total_balance'=>$new_total_balance,
					'payment_method'=>$payment_method,'transaction_id'=>$transaction_id,'is_recharged_by_admin'=>1,'status'=>1,'created_at'=>$today]
				]);
				
				
				
				//Send Email
				 if($technician->email!=''){
					Mail::to($technician->email)->send(new TechnicianWalletRechargeEmailToTechnician(
																									['tid'=>$tid,
																									'recharge_amount'=>$amount,
																									'total_balance'=>$new_total_balance,
																									'payment_method'=>$payment_method
																									]));
				}
				
				
				
				
				
 	   
  	   
 	   
	   
  		return Redirect::back()->with('message','Successfully Submitted !');
 
      //return Redirect::back()->with('message','Added Successfully !');

		 
 
	   
	   
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
        $TechnicianCredit=TechnicianCredit::find($id);
		
		
	 
		 
  		
        return view('admin.technician_credit.edit',array('item'=>$TechnicianCredit));
		
    }

    
    public function update(Request $request, $id)
    {

		$this->validate($request, [
		'status'=>'required',
		]); 

	   
	    $item = TechnicianCredit::find($id);
							
		$tid=$item->tid;
		$technician=Technician::find($tid);
		 
		$item->status = $request->status;
		$transaction_id=$item->transaction_id;

		if($item->status==1){
		 
		     	//handle wallet recharge
				if($item->is_hold!=1){
				
					$TechnicianCredit=TechnicianCredit::where('tid',$tid)->where('status',1)->orderby('created_at','DESC')->first();
					
					if($TechnicianCredit){
							
						$recharge_amount=$item->recharge_amount;
						
						$wallet_balance=$TechnicianCredit->total_balance;
						
						$new_wallet_balance1=$wallet_balance+$recharge_amount;
						
						//dd($new_wallet_balance1);
						
						//add full amount to technician wallet
						DB::table('technician_credits')->insert([
							['tid' =>$tid,'recharge_amount' =>$recharge_amount,'total_balance'=>$new_wallet_balance1,
							 'payment_method'=>$item->payment_method, 'transaction_id'=>$transaction_id, 'status'=>1,'created_at'=>date('Y-m-d H:i:s')]
						]);
				 
				 }else{  //for 1st wallet recharge
				 	
						$recharge_amount=$item->recharge_amount;
 						
						$new_wallet_balance1=$recharge_amount;
						
						//dd($new_wallet_balance1);
						
						//add full amount to technician wallet
						DB::table('technician_credits')->insert([
							['tid' =>$tid,'recharge_amount' =>$recharge_amount,'total_balance'=>$new_wallet_balance1,
							 'payment_method'=>$item->payment_method, 'transaction_id'=>$transaction_id,'status'=>1,'created_at'=>date('Y-m-d H:i:s')]
						]);

				 }
				 
				  //delete current entry and added new entry above 
				  TechnicianCredit::Destroy($id);
				 
			}
			
	
		 $OrderCompleted_count=\App\OrderCompleted::where('oid',$item->oid)->count();
		
		/* 
		if($OrderCompleted_count>0){
		  
		  	  $OrderCompleted=\App\OrderCompleted::where('oid',$item->oid)->first();
			 
				$payment_method=$OrderCompleted->payment_method;
			// when complete order 
			if($item->is_hold==1){
		
		
				$setting=new \App\Setting;
				$scp=$technician->service_charge_per;
				
				$scp2=100-$scp;
				
				$order=Order::find($item->oid);
				$net_grand_total=$order->grand_total;
				
				$service_charge=$net_grand_total*$scp/100;
				
				$service_charge2=$net_grand_total*$scp2/100;
				
					$TechnicianCredit=TechnicianCredit::where('tid',$tid)->where('status',1)->orderby('created_at','DESC')->first();
					
					$wallet_balance=$TechnicianCredit->total_balance;
					
			
				//  'cash' payment mode 
				if($payment_method=='cash'){
					$new_wallet_balance1=$wallet_balance+$net_grand_total;
				
					//add full amount to technician wallet
					DB::table('technician_credits')->insert([
						['oid'=>$order->oid,'tid' =>$tid,'tl_id'=>$item->tl_id,'recharge_amount' =>$net_grand_total,'total_balance'=>$new_wallet_balance1,
						 'payment_method'=>$item->payment_method,'status'=>1,'created_at'=>date('Y-m-d H:i:s')]
					]);
					
					
					 //deduct commison from technicain wallet
					$new_wallet_balance2=$new_wallet_balance1-$service_charge;
					
					DB::table('technician_credits')->insert([
						['oid'=>$order->oid,'tid' =>$tid,'tl_id'=>$item->tl_id,'recharge_amount' =>'-'.$service_charge,'total_balance'=>$new_wallet_balance2,
						 'payment_method'=>$item->payment_method,'status'=>1,'created_at'=>date('Y-m-d H:i:s')]
					]);
						

					
					//online payment method
				}else if($payment_method=='online'){
					
					
					$new_wallet_balance1=$wallet_balance+$service_charge2;
					
					DB::table('technician_credits')->insert([
						['oid'=>$order->oid,'tid' =>$tid,'tl_id'=>$item->tl_id,'recharge_amount' =>$service_charge2,'total_balance'=>$new_wallet_balance1,
						 'payment_method'=>$item->payment_method,'status'=>1,'created_at'=>date('Y-m-d H:i:s')]
					]);
				
				}
			
				 //delete current entry and added new entry above 
					 TechnicianCredit::Destroy($id);
					 
				
				//add commsision 
					  DB::table('order_service_charge')->insert([
							['oid'=>$order->oid,'cid'=>$order->cid,'tid' =>$tid,'tl_id'=>$item->tl_id,'id'=>$order->id,'grand_total' =>$net_grand_total,
							'commission_amount'=>$service_charge,'commission_percentage'=>$scp,'created_at'=>date('Y-m-d H:i:s')]
						]);

			}
		}
		*/

		}   
 		
		/// 
 
		 $item->save();
				

				//Send Email
				 if($technician->email!=''){
					Mail::to($technician->email)->send(new TechnicianWalletRechargeEmailToTechnician(
																									['tid'=>$tid,
																									'recharge_amount'=>$item->recharge_amount,
																									'total_balance'=>$item->total_balance,
																									'payment_method'=>$item->payment_method
																									]));
				}
					
				
			return \Redirect::route('admin_technician_wallet')->with('message', ' Successfully Updated! '); 
				  
  			//return Redirect::back()->with('message','Successfully Updated !');
	   
	   
	   
    }

  
  
  
  
  
   
  
  
  
    public function destroy($id)
    {
        
 		TechnicianCredit::where('tc_id',$id)->delete();
  		return Redirect::back()->with('message','Successfully deleted!');
 		
    }
	
	
	
	
	
	 
	
	
	 
	
	
	
}
