<?php namespace App\Services\Validators;
 
class CustomerRegisterValidator extends Validator {
 
    public static $rules = array(
        'firstname'=>'required|alpha|min:2',
   		'lastname'=>'required|alpha|min:1',
   		'email'=>'required|email|unique:users',
   		'password'=>'required|alpha_num|between:2,12|confirmed',
   		'password_confirmation'=>'required|alpha_num|between:2,12'        
    );
 
}