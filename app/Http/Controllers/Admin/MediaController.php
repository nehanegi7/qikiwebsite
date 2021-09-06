<?php

namespace App\Http\Controllers\Admin;

use View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Media;
 
class MediaController extends Controller
{
	
	var $type='media';

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
		$media=Media::orderby('created_at','DESC')->get();
		
        return view('admin.media.index',array('items'=>$media));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.media.create');
    }






    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
	 
	 
	 
    public function store(Request $request)
    {
		$type = $request->post('type');
		if(!empty($type) && $type == 'uf'){
			$this->validate($request, [
				'file' => 'required|mimes:mp4,ogg',
			]); 
		}
		if(!empty($type) && $type == 'yu'){
			$this->validate($request, [
				'url' => 'required',
			]); 
		}
  
		$records=$request->all();
		
		$records['file_type'] = $type;
		//Youtube url
		if(!empty($_POST['url'])){
			$video_url = $_POST['url'];
			$video_url = explode('=', $video_url);
			$video_url = !empty($video_url) ? $video_url['1']:'';
			$records['file'] = !empty($video_url) ? 'https://www.youtube.com/embed/'.$video_url:'';			
		}

		 //File upload
		if($request->hasFile('file')) {
			if ($request->file('file')->isValid()) {
				
				$fileName=$request->file('file')->getClientOriginalName();
				$fileName =time()."_".$fileName;
				//upload
				$request->file('file')->move('uploads/media', $fileName);
					
				$file_path='base_url/uploads/media/'.$fileName;
 				$records['file']=$file_path;
 			}
		}
	
	   $mid=Media::create($records)->mid;
	  
       //return \Redirect::route('admin_media_edit',array('id' => $mid))->with('message', 'Successfully Added!');
	  
	   return \Redirect::route('admin_media')->with('message', 'Media Added Successfully ! ');
	   
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
        $media=Media::find($id);
	
        return view('admin.media.edit',array('item'=>$media));
		
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
	
		$item = Media::find($id);
		
		if ($request->hasFile('file')){
			$this->validate($request, [
                //'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg',
                'file' => 'required|mimes:mp4,ogg',
  			]); 
				
			if ($request->file('file')->isValid()) {
				$fileName=$request->file('file')->getClientOriginalName();
				$fileName =time()."_".$fileName;
				$request->file('file')->move('uploads/media', $fileName);
				$file_path='base_url/uploads/media/'.$fileName;
				$item->file=$file_path;
			}
		}
		
		//Youtube url
		if(!empty($_POST['url'])){
			$video_url = $_POST['url'];
			$video_url = explode('=', $video_url);
			$video_url = !empty($video_url) ? $video_url['1']:'';
			$item->file = !empty($video_url) ? 'https://www.youtube.com/embed/'.$video_url:'';			
		}
			
		$item->alt_tag=$request->alt_tag; 
		$item->title_tag=$request->title_tag; 
		$item->save();

		// redirect
		return \Redirect::route('admin_media_edit',array('id' => $id))->with('message', 'Successfully Updated!');
   
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
  
		Media::Destroy($id);
	   	return \Redirect::route('admin_media')->with('message', 'Successfully Deleted!');
		
    }
}
