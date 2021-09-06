<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Technician extends Authenticatable
{
    use Notifiable;

    protected $primaryKey = 'tid';
	protected $table = 'technicians';
    
    protected $fillable = [
       'name', 'email','password','phone','city_id','photo','status','declaration_is_agree','accept_order','badge','service_charge_per','fcm_reg_id'
    ];

    
	function city(){
		 return $this->belongsTo('App\City','city_id','city_id');
	
	}
}
