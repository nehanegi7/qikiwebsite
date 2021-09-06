<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class IdentityProofType extends Model
{
    protected $primaryKey = 'ipt_id';
	
	protected $table = 'identity_proof_types';
	
	
    protected $fillable = array('title','slug','weight','status');
	 
	
	
	
	
	
	
	
}
