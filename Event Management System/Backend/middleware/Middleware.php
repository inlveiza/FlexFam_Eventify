<?php

class Middleware {
	
	protected $gm, $auth;
	//protected $headers;
	public function __construct (GlobalMethods $gm, Auth $auth){
		//$this->headers = apache_request_headers();
		$this->gm = $gm;
		$this->auth = $auth;
		
		
	}
	
	public function isAuthenticated(){
		if(isset($_SERVER['HTTP_AUTHORIZATION'])){
			$data = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);
			
			if($data[0] !== 'Bearer'){
                return false;
            } else {
            	$payload = $this->auth->verifyToken($data[1]);
                if(!$payload['is_valid']){
                     return false;
                } else {
                	return true;
                }
            }
		}
		return false;
	}
	
	public function Authorization(){
		if($this->isAuthenticated()){
			$data = explode(' ',$_SERVER['HTTP_AUTHORIZATION']);
			$decoded = explode('.', $data[1]);
			$tokenData = json_decode(base64_decode($decoded[1]));
			
			if($tokenData->tokenData->is_admin){
				return true;
			} else {
				return false;
			}
		} else {
			return $this->gm->responsePayload(null, "Failed", "You're session has already expired, Please login again", 403);
		}
	}
	
}