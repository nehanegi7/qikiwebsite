<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class News extends Model
{
    protected $primaryKey = 'nid';
	protected $table = 'news';
	
    protected $fillable = array('title','web_url');
	
    function get_news()
	{
  		$news=News::get();
		return $news;
	}
}
