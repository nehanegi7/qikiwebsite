<?php

namespace App\Http\Controllers\Technician;

use View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Coupon;
use Redirect;
 
class TcouponController extends Controller
{
	
	var $type='coupon';
 
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
        $tid=$request->session()->get('tid');

		$coupon=Coupon::where('created_by', $tid)->orderby('cid','DESC')->paginate(50);
		
        return view('technician.coupon.index',array('items'=>$coupon)); 
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
         return view('technician.coupon.create');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
	 
	 
    public function store(Request $request)
    {
        $tid=$request->session()->get('tid');
		$this->validate($request, [
            'name' => 'required',
            'code' => 'required',
            'discount' => 'required',
            'code' => 'required',
            'date_start' => 'required',
            'date_end' => 'required',
            /*'minimum_total_amount' => 'required',
            'uses_per_coupon' => 'required',
            'uses_per_customer' => 'required',*/
  		]); 
				
		$records=$request->all();
        $records['code'] = strtoupper($request->code);
        $records['created_by'] = 1;

	    Coupon::create($records);
	  
	    return \Redirect::route('technician_coupon')->with('message', 'Coupon Added Successfully ! '); 
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
        $coupon=Coupon::find($id);
        return view('technician.coupon.edit',array('item'=>$coupon));
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
        $tid=$request->session()->get('tid');
        $this->validate($request, [
          'name' => 'required',
          'code' => 'required',
          'discount' => 'required',
          'code' => 'required',
        ]); 
				 
        $item = Coupon::find($id);

        $item->code=strtoupper($request->code);
        $item->name=$request->name;
        $item->description=$request->description;
        $item->coupon_type=$request->coupon_type;
        $item->discount=$request->discount;

        $item->minimum_total_amount=$request->minimum_total_amount;
        $item->date_start=$request->date_start;
        $item->date_end=$request->date_end;
        $item->uses_per_coupon=$request->uses_per_coupon;
        $item->uses_per_customer=$request->uses_per_customer;
        $item->status=$request->status;
        $item->created_by=$tid;
        $item->save();  

        // redirect
        return \Redirect::route('technician_coupon_edit',array('id' => $id))->with('message', 'Successfully Updated!');
	  
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
		Coupon::Destroy($id);
	   	   return \Redirect::route('technician_coupon')->with('message', 'Successfully Deleted!');
		
    }
	
	
	
	 
	 
	
	
	
	
}
