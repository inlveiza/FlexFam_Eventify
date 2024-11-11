<?php

require_once($apiPath. '/interface/User.interface.php');

class User implements UserInterface{
	protected $pdo, $gm, $md;
	
	protected $table1 = 'accounts';
	protected $table2 = 'profiles';
	
	public function __construct(\PDO $pdo, GlobalMethods $gm, Middleware $middleware){
		$this->pdo = $pdo;
		$this->gm = $gm;
		$this->md = $middleware;
	}
	
	public function getAll(){
		if(!$this->md->isAuth()) return $this->gm->responsePayload(null, "Failed", "Invalid Login, Please login first", 403);
			
		$sql = "SELECT * FROM " . $this->table1;
		
		$stmt = $this->pdo->prepare($sql);
		try{
			if($stmt->execute()){
				$data = $stmt->fetch();
				if($stmt->rowCount() > 0){
					return $this->gm->responsePayload($data, "Success", "Successfully gathered all data", 200);
				} else {
					return $this->gm->responsePayload($data, "Failed", "No Data Existing in the Database", 404);
				}
			}
			
		} catch (\PDOException $e) {
			echo $e->getMessage();
		}
		
	}
	
	public function try(){
		if(!$this->md->isAuth()) {
           return $this->gm->responsePayload(null, "Failed", "Invalid Token", 403);
        } else {
        	return $this->gm->responsePayload(null, "Success", "Valid token", 200);
        }
	}
	
}