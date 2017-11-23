<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminSession extends Model
{
    // Table Basic Info

    protected $table="admin_session";


	protected $fillable = array('id','token','ip_address','admin_agent','last_login');


	/**
	*@author:chiranjeevi
	*Function to set/update the admin session info
	*Username / password	
	*/
	public function setAdminSession($token,$ip_address,$admin_agent,$date,$adminExists){

		$date = date('Y-m-d H:i:s');
		$res = AdminSession::updateOrCreate(['id' => $adminExists->id],['id'=>$adminExists->id,'token'=>$token,'ip_address'=>$ip_address,'admin_agent'=>$admin_agent,'last_login'=>$date]);
		return $res;
	}

	/**
	*@author chiranjeevi
	*Description :matches the admin session token
	*Params :id,token
	*/

	public function checkAdminToken($id_admin,$token){

		$match = AdminSession::leftjoin('admin as a','a.id','=','admin_session.id')
				->select('a.id','admin_session.token','a.name','a.username','admin_session.last_login')
				->where(['admin_session.id'=>$id_admin,'admin_session.token'=>$token])
				->first();
		return $match;
	}
}
