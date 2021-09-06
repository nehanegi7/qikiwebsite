<?php

namespace App\Http\Controllers\Admin;

use View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Page;
  
class PageController extends Controller
{
	
	var $type='page';

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

		$page=Page::all();
		
        return view('admin.page.index',array('items'=>$page));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
 		
		
        return view('admin.page.create');
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
				'body'=>'required',
 			 ]); 
				 
		 
		 
		 
		 $records=$request->all();
		 
		 
		 
		
		
 	   $records['slug']=str_slug($request->title, '-');
	   
	   
	   $page_id=Page::create($records)->page_id;
	  
	  
	  //return \Redirect::route('admin_page_create')->with('message', 'Page Added Successfully ! '); 

	           	return \Redirect::route('admin_page_edit',array('id' => $page_id))->with('message', 'Page Added Successfully !');

	   
	   
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
        $page=Page::find($id);
		
        return view('admin.page.edit',array('item'=>$page));
		
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
				'body'=>'required',
 			 ]); 
				 
	   
	   
	   $item = Page::find($id);
		
		

 		
			 
			
			
				// store
				$item->title       = $request->title;
				$item->body      = $request->body;
				
				$item->seo_keyword      = $request->seo_keyword;
				$item->seo_description      = $request->seo_description;
 				
				if($request->slug!=''){
 					$item->slug = str_slug($request->slug, '-');
				}else{
					$item->slug = str_slug($request->title, '-');
				}
				$item->save();

            // redirect
           	return \Redirect::route('admin_page_edit',array('id' => $id))->with('message', 'Successfully Updated!');
	   
	   
	   
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
		Page::Destroy($id);
	   	   return \Redirect::route('admin_page')->with('message', 'Successfully Deleted!');
		
    }
}
