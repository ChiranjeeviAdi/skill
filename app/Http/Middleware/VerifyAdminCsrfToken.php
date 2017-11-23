<?php namespace App\Http\Middleware;
use Closure;

use App\Models\Admin;

class VerifyAdminCsrfToken {

	

	public function handle($request, Closure $next){
		$token = \Request::header('X-Csrf-Token');

	    $id_admin = \Request::header('X-Id-Admin');

	    $adminObject = new \App\Models\AdminSession();

	    $adminDetails = $adminObject->checkAdminToken($id_admin,$token);
	    if($adminDetails&&count($adminDetails)>0){
	    	$adminDet = array();

	    	$adminDet['id'] = $adminDetails->id;
	    	$adminDetadminDet['name'] = $adminDetails->name;
	    	$adminDet['username'] = $adminDetails->username;
	    	if($adminDetails->last_login!='')
            $adminDet['last_login']      =   date("jS F Y H:i a", strtotime(date($adminDetails->last_login)));//;date("Y-m-d H:i:s");
            else
              $adminDet['last_login']    = NULL;
          	$request->adminDet=$adminDet;
            
            return $next($request);
	    }else{
	    	return \Response::json(array('error'=>true,'message'=>'Unauthorized Request'),401);
	    }

	}
	


}
