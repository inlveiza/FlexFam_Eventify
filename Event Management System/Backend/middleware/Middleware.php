<?php

class Middleware {
	
	protected $auth;
	//protected $headers;
	public function __construct (Auth $auth){
		//$this->headers = apache_request_headers();
		$this->auth = $auth;
		
	}
	
	public function isAuth(){
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
	
}