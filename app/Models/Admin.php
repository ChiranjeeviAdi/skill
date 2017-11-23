<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    // Table Basic Info

    protected $table="admin";

	protected $primaryKey ="id_admin";

	protected $fillable = array('id','name','username');

	/**
	*@author:chiranjeevi
	*Function to check the admin exists with the params
	*Username / password	
	*/
	public function checkAdminExists($username,$password){

		$result = Admin::select('id','name','username')
				  ->where(array('username'=>$username,'password'=>$password))
				  ->first();
		return $result;
	}

	



}
