<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Admin;
use App\Category;

use App\User;
use App\Order;
use App\Technician;


use DB;
use Hash;
use Route;
use Redirect;

 
class AdminController extends Controller
{
    
	function index(){
		return view('admin.index');
	}
	
	
	
	function login(request $request){
		
		$username=$request->username;
 		$password=$request->password;
		
		 		 
		 $this->validate($request, [
			'username' => 'required',
			'password'=>'required',
		  ]); 
		
		 
		//https://laravel.com/docs/5.2/hashing#basic-usage
		//to generate hased password
		// echo Hash::make($password);
		 //die();
		 
		
		 $results = DB::select('SELECT * from admin where  username = :username', ['username' =>$username]);
		
		 if(count($results)>0){
		 	
			if (Hash::check($password,$results[0]->password)){
				
				$request->session()->put('is_admin_logged', 'yes');
				$request->session()->put('aid',$results[0]->aid);
					
			    return \Redirect::route('admin_home')->with('message', 'Success!');
			}else{
			
		 	 	return \Redirect::route('admin')->with('message', 'Invalid Password!');
		 	}
			
			
		 }else{
		 	 return \Redirect::route('admin')->with('message', 'Invalid Username/Password !');
		 }
		
		
		
		 
	}
	
	
	
	function logout(request $request){
		 $request->session()->forget('is_admin_logged');
		return \Redirect::route('admin')->with('message', 'Successfully Logged Out!');
	
	}	
	
	
	 function home(request $request){
   	
		$category_count=Category::count();
		$user_count=User::count();
		$order_count=Order::count();
		$technician_count=Technician::count();	
 		 
 		
   		return view('admin.home',array('category_count'=>$category_count,'user_count'=>$user_count,'order_count'=>$order_count,'technician_count'=>$technician_count));
	
   }
   
   
   
    function profile(request $request){
   	
  		$aid=$request->session()->get('aid');
		
		$admin=Admin::find($aid);
		
		
   		return view('admin.profile',array('admin'=>$admin));
	
   }
   
   
    

   
    public function profile_update(Request $request, $aid)
    {
       
	  
	     $this->validate($request, [
				'name' => 'required',
				'email' => 'required',
				'password'=>'sometimes|nullable|min:5'
  			 ]); 
				 
			 
	 
	   
	   
	   $item = Admin::find($aid);
					
			 
			 
			 
	  if ($request->hasFile('photo')) {

	  		 $this->validate($request, [
				'photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg',
  			 ]); 


			if ($request->file('photo')->isValid()) {
				
				$fileName=$request->file('photo')->getClientOriginalName();
				$fileName =time()."_".$fileName;
				//upload
				$request->file('photo')->move('uploads/admin/photo', $fileName);
					
					//column name 
				$item->photo=$fileName;
				
 			}
		}
		
		
		 
			
			
				// store
				
				$item->name       = $request->name;
				$item->email      = $request->email;
  			 
 				
 				if($request->password!=''){
 					$hashed_password=Hash::make($request->password);
	   				 $item->password=$hashed_password;
					
				}
 
				$item->save();
				
				
				 
				
				 

            // redirect
           return Redirect::back()->with('message','Successfully Updated!');

	   
	   
	   
    }

  
   
	
}
