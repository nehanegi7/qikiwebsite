<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class ContactForm extends Model
{
    protected $primaryKey = 'cid';
	protected $table = 'contact_form';
	
    protected $fillable = array('name','email','phone','subject','message');
	
 	
	
	
}
