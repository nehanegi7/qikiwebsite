<?php

namespace App\Http\Controllers\Admin;

use View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Order;
use App\OrderCompleted;
use App\OrderItem;
use App\OrderStatus;
use App\OrderStatusChange;
use App\OrderServiceCharge;


use App\City;
use App\Category;
use App\Service;

use App\User;
use App\Technician;
use App\TechnicianLead;

use Redirect;
use DB;
use Hash;

class OrderController extends Controller
{
	
	var $type='order';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
	 
	 function __construct(){
	 	
		View::share('type', $this->type);
 	 }
	 
    public function index(request $request) 
    {	

		$oid=$request->oid;
		$id=$request->id;
		
		$tid=$request->tid;
		
		$date_from=$request->date_from;
		$date_to=$request->date_to;
		$technicians=Technician::where('status',1)->where('badge','<>',0)->where('accept_order',1)->orderby('name','ASC')->get();
 		 
		$users=user::where('status',1)->orderby('name','ASC')->get();

	  	$orders=Order::
			
 			 when($oid, function ($query) use ($oid) {
                	  	 return $query->where('oid',$oid);      
  				 })	
			
			 ->when($id, function ($query) use ($id) {
                	  	 return $query->where('id',$id);      
  				 })	
			
			
			   ->when($tid, function ($query) use ($tid) {
                    	return $query->join('technician_leads', 'technician_leads.oid', '=', 'orders.oid')
									 ->where('technician_leads.tid',$tid);
                	})
			
								
			->when($date_from, function ($query) use ($date_from,$date_to) {
								$date_from=date('Y-m-d',strtotime($date_from));
								$date_to=date('Y-m-d',strtotime($date_to));
								
								//dd($date_from);
                	  	 	 return $query->whereBetween('orders.created_at',[$date_from,$date_to]);      
  				  })
			 
		
		->orderby('orders.created_at','DESC')->paginate(100);
		
		$orders->appends($request->all());
		
        return view('admin.order.index',array('items'=>$orders,'users'=>$users,'technicians'=>$technicians));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
	
		$cities=City::orderby('title','ASC')->get();
		
		$categories=Category::orderby('title','ASC')->get();
		
        return view('admin.order.create',array('cities'=>$cities,'categories'=>$categories));
    }


	
	function ajax_get_services(request $request){
		$cid=$request->cid;
			
		$services=Service::where('cid',$cid)->orderby('weight','ASC')->get();
	     
		 return view('admin.includes.services_loop',array('services'=>$services));

	}



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
	 
	 
	 
    public function store(Request $request)
    {
	
		
		$messages=array(
			'cid.required'=>'Pls choose a category',
			'city_id.required'=>'Pls choose a city',
		);
	
		
		   $this->validate($request, [
				'name' => 'required',
				'phone' => 'required',
				/*'email'=>'required|unique:technicians',*/
				'services'=>'required',
				'cid'=>'required',
				'city_id'=>'required',
				'date_of_service'=>'required'
 			 ],$messages); 
			 
		
		 $name_title=$request->name_title;	
		$name=$request->name;
		$phone=$request->phone;
		$services=$request->services;
		
		$records=$request->all();
		
		// dd($records);
		
		$count =User::where('phone',$phone)->count();	
 		
		 if ($count>0) {
			// The user already have account...
			$user=User::where('phone',$phone)->first();	
			 $id=$user->id;
		}else{
			//register user
			 $id=User::create([
			 		'name_title' =>$name_title,
					'name' =>$name,
					'password' =>Hash::make($phone),
					'phone' =>$phone,
			 ])->id;
			 
 			 
			 
				
		}
		
		if($id!=''){
			
			
			
			//order table
			$records['order_pass_code']=rand(1000,9999);
			$records['id']=$id;
			$records['invoice_date']=date('Y-m-d');
			$records['name_title']=$name_title;
			$records['grand_total']=$request->sub_total;
			$records['order_comments']=$request->order_comments;
			
			
			$records['cid']=$request->cid;
			
			$records['ip']=$request->ip();
			
			
		   $records['is_order_completed']=0;

				
			//to generate Invoice no. 
			$last_invoice_no = Order::orderBy('oid','DESC')->pluck('invoice_no')->first();
			 $nextInvoiceNumber = $last_invoice_no + 1;
 
			
	    	 //to generate Invoice no.  

			 $records['invoice_no']=$nextInvoiceNumber;

			
			
			
			$oid=Order::create($records)->oid;
			
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
		//Current can't sent email because still no email provided by user till this step
		//Mail::to($email)->send(new NewOrderPlaceEmailToCustomer($order));
		////////////
		
		
		//Send SMS to Customer///////////
		 $SMSController=new \App\Http\Controllers\SMSController;
		 $SMSController->sms_send_on_new_order_place_to_customer($phone,$oid);
		////////////
		
		
			/*if($oid!=''){
				return response()->json(array('status'=>1,'oid'=>$oid));
			}else{
				return response()->json(array('status'=>0));
			}*/
		
	 
	
			 // return \Redirect::route('admin_order_create')->with('message', 'Order #'.$oid.' created'); 
		
		    	return \Redirect::route('admin_order_edit',array('id' => $oid))->with('message', 'Order #'.$oid.' created');

	
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
        $order=Order::find($id);
		
		$user=User::find($order->id);
		
		$technicians=Technician::where('status',1)->where('badge','<>',0)->where('accept_order',1)->orderby('name','ASC')->get();
		
		$count_TechnicianLead=TechnicianLead::where('oid',$id)->count();
 		
		$orders_status=OrderStatus::orderby('created_at','DESC')->get();
		
		$order_status_changes=OrderStatusChange::where('oid',$id)->orderby('created_at','DESC')->get();
		
        return view('admin.order.edit',array(
										'order'=>$order,
										'user'=>$user,
										'technicians'=>$technicians,
										'count_TechnicianLead'=>$count_TechnicianLead,
										'orders_status'=>$orders_status,
										'order_status_changes'=>$order_status_changes
										));
		
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
       	$aid=$request->session()->get('aid');

	   
	    
 		$os_id       = $request->os_id;
		
		DB::table('order_status_changes')->insert(
			['oid' =>$id, 'os_id' =>$os_id,'changes_by'=>'admin','changes_by_id'=>$aid,'created_at'=>date('Y-m-d H:i:s')]
		);	 
  		 

            // redirect
           	return \Redirect::route('admin_order_edit',array('id' => $id))->with('message', 'Successfully Updated!');
	   
	   
	   
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
		Order::Destroy($id);
		OrderItem::where('oid',$id)->delete();
	   	   return \Redirect::route('admin_order')->with('message', 'Successfully Deleted!');
		
    }
	
	
	
	function order_asign_to_technician(request $request){
		
		
		$records=$request->all();		
		$tid=$request->tid;
		$oid=$request->oid;
		
		//dd($records);
		
		//delete existing record
		$TechnicianLeads=TechnicianLead::where('oid',$oid)->delete();
 		 
		///
		
 

		TechnicianLead::create($records);
		
		
		 //send FCM notification
		$fcm_notification_title="New complaint assign for you."; 
		$fcm_notification_message="You have a new complaint #".$oid." assiged by admin on ".config('app.name');
		$this->send_fcm_notification('\App\Technician',$tid,$fcm_notification_title,$fcm_notification_message);
		//Emd FCM notification




		//Send SMS to Customer///////////
		 $SMSController=new \App\Http\Controllers\SMSController;
		 $SMSController->sms_send_on_new_order_place_to_technician($tid,$oid);
		////////////
		

		
		return Redirect::back()->with('message','Successfully Assigned !');
		
		
	}
	
	
	 public function order_revenue() 
    {	
		$OrderCompleted=OrderCompleted::orderby('created_at','DESC')->paginate(50);
		
		


        return view('admin.order.order_revenue_index',array('items'=>$OrderCompleted));
    }
	
	
	public function order_revenue_details(request $request,$oc_id) 
    {	
		$OrderCompleted=OrderCompleted::find($oc_id);
		
		
		
		$oid=$OrderCompleted->oid;
		
		$order=Order::find($oid);
		
		$user=User::find($OrderCompleted->id);
		
  		$technician=Technician::find($OrderCompleted->tid);
		
 		$OrderServiceCharge=OrderServiceCharge::where('oid',$oid)->orderby('created_at','DESC')->first();
 		 //dd($OrderServiceCharge);
        return view('admin.order.order_revenue_details',array(
										'order'=>$order,
										'user'=>$user,
										'technician'=>$technician,
 										'OrderCompleted'=>$OrderCompleted,
										'OrderServiceCharge'=>$OrderServiceCharge
 										));
		
		
      
    }
	 
	 
	 
	 
	 
	 
	 
	 
	function upload_hardcopy_invoice(request $request){
		
 		$oid=$request->oid;
 		
 				
		$order=Order::find($oid);			
		
		$id=$order->id;		 
		
		//dd($records);
		
			$fileName = $request->file->getClientOriginalName();
					$fileName =time()."_".$fileName;
					$request->file->move('uploads/order/', $fileName);
					
				
			DB::table('order_hardcopy_invoice')->insert([
				['oid'=>$oid,'id'=>$id,'image'=>$fileName,'created_at'=>date('Y-m-d H:i:s')]
			]);
			 
					
			return response()->json(['uploaded' => '/uploads/order/'.$fileName]);
		
	
	}
	 
	 
	 
	 
	
}
