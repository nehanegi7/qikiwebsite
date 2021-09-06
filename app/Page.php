<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Page extends Model
{
    protected $primaryKey = 'page_id';
	protected $table = 'pages';
	
    protected $fillable = array('title','slug','body','seo_keyword','seo_description');
	
 	 public function getRouteKeyName() {
        return 'slug';
    }
	
	
	function content($page_id){
		
		$page=Page::find($page_id);
		
		if(!$page){
			return;
		}
 		$body=str_replace('base_url',url('/'),$page['body']);
	
		$page->body=$body;
		
		return $page;
	
	}
}
