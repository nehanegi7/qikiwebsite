<?php

namespace App\Http\Controllers\Admin;

use View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\City;
use App\State;
 
class CityController extends Controller
{
	
	var $type='city';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
	 
	 function __construct(){
	 	
		View::share('type', $this->type);
 	 }
	 
    public function index() 
    {	
        $city=City::orderby('title','ASC')->paginate(50);
        $state=State::orderby('sid','ASC')->get();
        return view('admin.city.index',array('items'=>$city, 'state'=>$state));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.city.create');
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
            'title' => 'required',
            'state_id' => 'required',
        ]); 

		$records=$request->all();
		 
		$records['slug']=str_slug($request->title, '-');
 	   
	    City::create($records);
	  
        return \Redirect::route('admin_city')->with('message', 'City Added Successfully ! '); 
      
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
        $city=City::find($id);
        $state=State::orderby('sid','ASC')->get();
		
        return view('admin.city.edit',array('item'=>$city, 'state'=>$state));
		
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
            'title' => 'required',
            'state_id' => 'required',
  		]); 

	 	$item = City::find($id);
				$item->title  = $request->title;
				$item->state_id  = $request->state_id;
			    $item->slug = str_slug($request->title, '-');
				$item->save();
        // redirect
        return \Redirect::route('admin_city_edit',array('id' => $id))->with('message', 'Successfully Updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
		City::Destroy($id);
	   	return \Redirect::route('admin_city')->with('message', 'Successfully Deleted!');
		
    }
}
