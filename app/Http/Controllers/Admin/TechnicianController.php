<?php

namespace App\Http\Controllers\Admin;

use View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Technician;
use App\TechnicianCategory;
use App\TechnicianCredit;
use App\TechnicianLead;


use App\IdentityProofType;
use App\TechnicianIDProof;
use App\TechnicianPersonalDetail;
use App\TechnicianCurrentAddress;

use App\City;
use App\Service;
use App\Category;

use App\PhoneVarificationPartner;

use Hash;
use DB;
use Redirect;
use Mail; 

class TechnicianController extends Controller
{
	
	var $type='technician';

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
		
		$tid=$request->tid;
		$phone=$request->phone;
		$email=$request->email;
		$status=$request->status; 
 	  
	  	$technicians=Technician::
		
 			 when($tid, function ($query) use ($tid) {
                	  	 return $query->where('tid',$tid);      
  				 })	
										
			->when($phone, function ($query) use ($phone) {
                	  	 	 return $query->where('phone', 'like', '%'.$phone.'%');      
  				  })
				  
			->when($email, function ($query) use ($email) {
                	  	 	 return $query->where('email', 'like', '%'.$email.'%');      
  				  })
				  	  										
			
			->when($status, function ($query) use ($status) {
							
							$status=str_replace("status|","",$status);
							//dd($status);
                	  	 	 return $query->where('status',$status);      
  				  })	
			
			
		->orderby('created_at','DESC')->paginate(50);
		
 		
		$technicians->appends($request->all());	
		
      	return view('admin.technician.index',array('items'=>$technicians));

		
    }
	
	

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
         return view('admin.technician.create');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
		$this->validate($request, [
			'name' => 'required',
			'phone' => 'required',
			/*'email'=>'required|unique:technicians',*/
			'password'=>'required',
		]); 
				 
		if($request->email!=''){
			$this->validate($request, [
				'email'=>'required|unique:technicians',
			]); 	 
		}
		 
		 
		 $records=$request->all();
		
		 $hashed_password=Hash::make($request->password);
	   	 $records['password']=$hashed_password;


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
				$records['photo']=$fileName;
				
			}
		}
			
 	   
 	   	$tid=Technician::create($records)->tid;
 	   
 	   
	   
  		
	  	return \Redirect::route('admin_technician_edit',array('id' => $tid))->with('message', 'Successfully Updated!');

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
        $technician=Technician::find($id);
		
		//for technician update document using same url, controller,method , if no session, it rejects update ,So created session
		$request=request();
		$request->session()->put('tid',$technician->tid);
		//
		
		$cities=City::orderby('title','ASC')->get();
		
		$sub_categories=Category::where('parent_cid','<>',0)->where('status',1)->orderby('title','ASC')->get();

		$technician_cid=TechnicianCategory::where('tid',$id)->orderby('t_cid','DESC')->pluck('cid')->toArray();
  		
        return view('admin.technician.edit',array('item'=>$technician,'sub_categories'=>$sub_categories,'cities'=>$cities,'technician_cid'=>$technician_cid));
		
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
	    $this->validate($request, [
				'name' => 'required',
				'phone' => 'required',
  			 ]); 
				 
		if($request->email!=''){
			$this->validate($request, [
				'email'=>'required|unique:technicians,email,'.$id.',tid',
			]); 		 
		}
	 
	   	$item = Technician::find($id);
					
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
				$item->photo=$fileName;
				
			}
		}
		

		// store
		$item->name = $request->name;
		$item->email = $request->email;
		$item->phone = $request->phone;
		$item->city_id = $request->city_id;

		$item->status = $request->status;
		$item->badge = $request->badge;
		$item->service_charge_per = $request->service_charge_per;

		$item->accept_order = $request->accept_order;
		if($request->password!=''){
			$hashed_password=Hash::make($request->password);
			$item->password=$hashed_password;
		}
		$item->save();
			
		//Send message to vendor when account verified
		if($request->badge != '0'){
			$sms_msg="Dear vendor, Your account has been verified successfully.";
			$SMSController=new \App\Http\Controllers\SMSController;
			$SMSController->sms_send($request->phone, $sms_msg);
		}

		//service
		$cid_arr=$request->cid;
	
		$data=array();
		if($cid_arr){
		foreach($cid_arr as $cid){
			$data[]=array('tid'=>$id,'cid'=>$cid);
		}
		
		TechnicianCategory::where('tid',$id)->delete();
		TechnicianCategory::insert($data);
		}
		///	
				

		// redirect
		return \Redirect::route('admin_technician_edit',array('id' => $id))->with('message', 'Successfully Updated!');

    }

  

  
    public function destroy($id)
    {
        
		Technician::Destroy($id);
		TechnicianCredit::where('tid',$id)->delete();
		TechnicianIDProof::where('tid',$id)->delete();
		TechnicianLead::where('tid',$id)->delete();
		TechnicianCategory::where('tid',$id)->delete();
		
		return Redirect::back()->with('message','Successfully deleted!');
 		
    }
	

	
	/*Currently not used */
	public function technician_id_proof_document($tid)
    {
        $technician=Technician::find($tid);

 		$IdentityProofTypes=IdentityProofType::where('status',1)->orderby('weight','ASC')->get();
		
		//$TechnicianIDProofs=TechnicianIDProof::where('tid',$tid)->get();
		
		$TechnicianPersonalDetail=TechnicianPersonalDetail::where('tid',$tid)->first();
		
		$TechnicianCurrentAddress=TechnicianCurrentAddress::where('tid',$tid)->first();
		
        return view('admin.technician.technician_document',array(
					'technician'=>$technician,
					'IdentityProofTypes'=>$IdentityProofTypes,
					//'TechnicianIDProofs'=>$TechnicianIDProofs,
					'TechnicianPersonalDetail'=>$TechnicianPersonalDetail,
					'TechnicianCurrentAddress'=>$TechnicianCurrentAddress,
				));
    }

	
	
	
	public function technician_personal_details($tid) 
	{
        $technician=Technician::find($tid);
		
 		$IdentityProofTypes=IdentityProofType::where('status',1)->orderby('weight','ASC')->get();
		
		$TechnicianIDProofs=TechnicianIDProof::where('tid',$tid)->get();
		
		$TechnicianPersonalDetail=TechnicianPersonalDetail::where('tid',$tid)->first();
		
		$TechnicianCurrentAddress=TechnicianCurrentAddress::where('tid',$tid)->first();
		
        return view('admin.technician.technician_personal_details',array(
					'technician'=>$technician,
					'PersonalDetail'=>$TechnicianPersonalDetail,
 			));
		
    }


	
	public function technician_current_address($tid) 
	{
        $technician=Technician::find($tid);
		
		
 		$IdentityProofTypes=IdentityProofType::where('status',1)->orderby('weight','ASC')->get();
		
		$TechnicianIDProofs=TechnicianIDProof::where('tid',$tid)->get();
		
		$TechnicianPersonalDetail=TechnicianPersonalDetail::where('tid',$tid)->first();
		
		$TechnicianCurrentAddress=TechnicianCurrentAddress::where('tid',$tid)->first();
		
  		
        return view('admin.technician.technician_current_address',array(
					'technician'=>$technician,
				 
					'CurrentAddress'=>$TechnicianCurrentAddress,
 					));
		
    }


	
	
}
