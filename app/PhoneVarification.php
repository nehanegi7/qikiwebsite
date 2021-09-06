<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class PhoneVarification extends Model
{
    protected $primaryKey = 'pv_id';
	protected $table = 'phone_varification';
	
    protected $fillable = array('phone','otp','is_varified');
	
 	 public function getRouteKeyName() {
        return 'slug';
    }
	
	
}
