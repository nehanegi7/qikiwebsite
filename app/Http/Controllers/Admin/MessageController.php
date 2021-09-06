<?php

namespace App\Http\Controllers\Admin;
use View;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\User;
use App\Message;
 
 
use Hash;
use Redirect;
use DB;

use DateTime;
class MessageController  extends Controller
{
     
	  
	 	  
	 var $type='message';

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

		$message=Message::where('parent_id',0)->orderby('is_admin_read','ASC')->paginate(50);
		
        return view('admin.message.index',array('items'=>$message));
    }

  
  
  
  
  
  
  	   function show_query_hierarchy($mid,$id='mid'){
   		
		$query=Message::where('mid',$mid)->first();
		
		//user's id 
  		$id= $query->id;
		
   		//die('ss'.$id);
		
		$GLOBALS['id']=$id;
		$GLOBALS['parent_id']=$query->mid;
		
   		$items=Message::where('mid',$mid)
				 ->orWhere(function ($query) {
        		global $id,$parent_id;
 				 $query->where('parent_id',$parent_id);
            })
		->orderby('mid','DESC')	
		->get();		
		
			
		
		$count=count($items);
		
		
		
		if($count>0){
		
		foreach($items as $item){
 			
			
			 if($item->admin_reply==1){ 
				$sender=" You :"; 
				$class1='admin_comment';
				$depth=3;
			  }else{
				$sender= "User : ";	
				$class1='';
				$depth=1;
				
			  }
		  
			 
		?>
		
		<li class="comment even thread-even depth-<?php echo $depth; ?> parent"  >
			<div id="div-comment-220803" class="<?php echo $class1; ?> comment-body">
				<div class="comment-author vcard">
						<cite class="fn"><?php echo $sender; ?></cite>  		</div>
		
		<div class="comment-meta commentmetadata">
			
			<?php echo date('jS F Y g:i A', strtotime($item->created_at)); ?>

				 		</div>

		<p><?php echo $item->message; ?></p>

		 
				</div>


			 
			 
 



</li>
		
		<?php
		
		}
		}
		
 		  
   }

  
  
  
  
  
  
  
  
  
  
  
    public function create()
    {
 		
		 
    }





 
	 
	 
    public function store(Request $request,$id)
    {
       
	   
			

 
	   
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
	
		//////Update is_admin_read to 1 //////////////////////////
        $message1=Message::find($id);
		$message1->is_admin_read=1;
		$message1->save();
		/////////////////////////////////////////////////////
		 
		 $message=Message::find($id);
         return view('admin.message.edit',array('item'=>$message));
		
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
			 	'message' => 'required',
				 
 			 ]); 
		 
		 
		 $records=$request->all();
		 
		 
		  
	   Message::create($records);
	  
        return \Redirect::route('admin_message_edit',array('id' => $id))->with('message', ' Successfully Replied!');
	
	
	}

  
  
    public function destroy(request $request, $id)
    {
        
 		
		// echo "ID".$id; die();
		
		Message::Destroy($id);
		
		DB::table('message')->where('parent_id',$id)->delete();

		
	   	   return \Redirect::route('admin_message')->with('message', 'Successfully Deleted!');
		
    }
}

	
	
	
 
