<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Advertise extends Model
{
    protected $primaryKey = 'adv_id';
	protected $table = 'advertisement';
	
    protected $fillable = array('image','link');


    function get_advertise($id)
	{
  		$advertise=Advertise::where('adv_id',$id)->first();
		return $advertise;
	}

	
}
