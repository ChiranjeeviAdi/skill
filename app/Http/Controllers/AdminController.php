<?php 

namespace App\Http\Controllers;
use Illuminate\Http\Request;
class AdminController extends Controller {

	// public function __construct(Request $request)
 //    {
 //        $this->middleware(function ($request, $next) {
 //           /*$userDet                                =   $request->userDet;*/


 //            if(($userDet))
 //            {
 //                $this->id_user                      =   $userDet['id_user'];
 //                $this->organization_id_organization =   $userDet['organization_id_organization'];
 //                $this->role_id_role                 =   $userDet['role_id_role'];
 //            }

 //            $this->curr_date                        =   date('y-m-d H:i:s'); 
 //            return $next($request);
 //        });       
 //    }

    public function login(Request $request){
    	$email = trim($request->input('email'));
    	$password = trim($request->input('password'));
    	$adminObj = new \App\Models\Admin();
    	$adminExists = $adminObj->checkAdminExists($email,$password); 
    	if($adminExists&&count($adminExists)>0){
    		$ip_address = $request->getClientIp();
    		$user_agent = $request->header('User-Agent');
    		$token = str_random(22).time();
    		$date=date('Y-m-d H:i:s');
    		$adminSessionObj = new \App\Models\AdminSession();
    		$resSession=$adminSessionObj->setAdminSession($token,$ip_address,$user_agent,$date,$adminExists);

    		return \Response::json(array('message'=>'User exists','csrf_token'=>$token,'user_data'=>$adminExists),200);
    	}else{
    		return \Response::json(array('message'=>'Invalid username or password'),403);
    	}
    }

}