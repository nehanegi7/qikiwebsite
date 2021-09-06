<?php

namespace App\Http\Controllers\Admin;

use View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Setting;
use App\Advertise;
use DB;

class SettingController extends Controller
{
	
	var $type='setting';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
	 
	 function __construct(){
	 	
		View::share('type', $this->type);
 	 }
	 
	 
	 function index(){

		$advertise=Advertise::find(1);
 		return view('admin.setting.index', array('advertise'=>$advertise));
	 }
	 

	/* 
	* Update Setting
	*/
	  public function update_bulk(Request $request) 
	  {
			$records=$request->all();
			
			$meta_key_arr=$request->meta_key;
			
			// dd($records);
				//dd($meta_key_arr);
			// dd($request->meta_value[2]);
			
			foreach($meta_key_arr as $k=>$meta_key){
				$item =Setting::where('meta_key',$meta_key)->first();
				
				if(array_key_exists($k,$request->meta_value)){
					$item->meta_value       = $request->meta_value[$k];
				}
			
				$item->save();
			}
			return redirect()->back()->with('message','Successfully Updated!');	
      }
	 
	
	
	 public function record_update(Request $request) {
       	    	$meta_key=$request->meta_key;
 				
				$item =Setting::where('meta_key',$meta_key)->first();
  				$item->meta_value       = $request->meta_value;

				$item->save();
				
 			 	return redirect()->back()->with('message','Successfully Updated!');	
      }
  
	 

    public function get_setting($meta_key){
        $item=Setting::where('meta_key',$meta_key)->first();
		
		return view('admin.about_franchise.get_setting',array('item'=>$item));
	}
	


	/* 
	* Add Advertisement
	* - Currently not used 
	*/
	public function adv_store(Request $request)
	{
		$this->validate($request, [
			'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg',
		]);
		$records=$request->all();

		//File upload
		if ($request->hasFile('image')) {
			if ($request->file('image')->isValid()) {

				$fileName=$request->file('image')->getClientOriginalName();
				$fileName =time()."_".$fileName;
				//upload
				$request->file('image')->move('uploads/advertisement', $fileName);

				//column name
				$records['image']=$fileName;
			}
		}
		Advertise::create($records);

		return redirect()->back()->with('message','Successfully Updated!');	
	}


	public function adv_update(Request $request, $id)
    {
	    $item = Advertise::find($id);
				
		if ($request->hasFile('image')) {
			
			$this->validate($request, [
				'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg',
			]); 
			
			if ($request->file('image')->isValid()) {
					
				$fileName=$request->file('image')->getClientOriginalName();
				$fileName = time()."_".$fileName;
				$request->file('image')->move('uploads/advertisement', $fileName);
				$item->image=$fileName;
			}
		}
 		$item->link=$request->link; 		 
		$item->save();

		// redirect
		return redirect()->back()->with('message','Successfully Updated!');	
    }
	

}
