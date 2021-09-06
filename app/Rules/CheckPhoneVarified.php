<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use App\PhoneVarification;
use App\User;

class CheckPhoneVarified implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
		//
		
        $count=PhoneVarification::where($attribute,$value)->where('is_varified',1)->count();
		
		$count_already_registered=User::where($attribute,$value)->count();
		
		if($count_already_registered>0){
			return false;
		}
		
		//dd($count);
		if($count>0){
			return true;
		}		
			 
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The Phone no. is not varified yet !';
    }
}
