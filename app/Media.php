<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Media extends Model
{
    protected $primaryKey = 'mid';
	protected $table = 'media';
	
    protected $fillable = array('file','alt_tag','title_tag','file_type');
	
    function get_video()
	{
  		//$media=Media::inRandomOrder()->limit(1)->get();
  		$media=Media::get();
		return $media;
	}
}
