<?php

namespace App\Http\Controllers\Admin;

use View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\News;
 
class NewsController extends Controller
{
	
	var $type='news';

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
		$news=News::orderby('created_at','DESC')->get();
		
        return view('admin.news.index',array('items'=>$news));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.news.create');
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
            'web_url' => 'required',
        ]); 
		 
		$records=$request->all();
 	   
	   $nid=News::create($records)->nid;
	  
      
	   return \Redirect::route('admin_news')->with('message', 'News Added Successfully ! '); 
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
        $news=News::find($id);
	
        return view('admin.news.edit',array('item'=>$news));
		
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
	    $item = News::find($id);
	
		$item->title=$request->title; 
		$item->web_url=$request->web_url; 
		$item->save();

		// redirect
		return \Redirect::route('admin_news_edit',array('id' => $id))->with('message', 'Successfully Updated!');
	   
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
		News::Destroy($id);
	   	return \Redirect::route('admin_news')->with('message', 'Successfully Deleted!');
		
    }
}
