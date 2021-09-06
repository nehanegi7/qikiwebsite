<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Message extends Model
{
    protected $primaryKey = 'mid';
	protected $table = 'messages';
	
    protected $fillable = array('id','member_type','message','admin_reply','parent_id','is_admin_read');
	
	
	 
	
	
	function user(){
		 return $this->belongsTo('App\User','id','id');
	
	}
	
	
	
}
