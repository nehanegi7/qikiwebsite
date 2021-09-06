<?php

namespace App\Http\Controllers\Admin;

use View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\User;
use App\Role;
use App\UserRole;
use Hash;
use DB;
use Redirect;
use Mail; 

class UserController extends Controller
{
	
	var $type='user';

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
		 
 	 	$id=$request->id;
		$phone=$request->phone;
		$email=$request->email;
		$status=$request->status;

		 //dd($status);
 	  
	  	$users=User::
		
 			 when($id, function ($query) use ($id) {
                	  	 return $query->where('id',$id);      
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
		
		$users->appends($request->all());
		
      	return view('admin.user.index',array('items'=>$users));

		
    }
	


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
         return view('admin.user.create');
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
			'email'=>'required|unique:users',
			'password'=>'required',
		]); 
			
		$records=$request->all();
		 
		 
		$hashed_password=Hash::make($request->password);
	   	$records['password']=$hashed_password;

 	   	$id=User::create($records)->id;
		
		return Redirect::back()->with('message','Added Successfully !');
		  
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
        $user=User::find($id);

        return view('admin.user.edit',array('item'=>$user));
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
				'email'=>'required|unique:users,email,'.$id,

				]); 
					
		}
	   
	   $item = User::find($id);

		if ($request->hasFile('photo')) {


			$this->validate($request, [
				'photo' => 'image|mimes:jpeg,png,jpg,gif,webp,svg',
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
		

		// store
		$item->name       = $request->name;
		$item->email      = $request->email;
		$item->phone      = $request->phone;
		$item->update_on_whatsapp      = $request->update_on_whatsapp;
		$item->status      = $request->status;
		
		if($request->password!=''){
			$hashed_password=Hash::make($request->password);
				$item->password=$hashed_password;
		}

		$item->save();

            // redirect
        return \Redirect::route('admin_user_edit',array('id' => $id))->with('message', 'Successfully Updated!');
	
    }

  
  
  
    public function destroy($id)
    {
        
		User::Destroy($id);
	
		return Redirect::back()->with('message','Successfully deleted!');
 		
    }
}
