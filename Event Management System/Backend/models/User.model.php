<?php

require_once($apiPath. '/interface/User.interface.php');

class User implements UserInterface{
	protected $pdo, $gm, $md;
	
	protected $table1 = 'accounts';
	protected $table2 = 'profiles';
	protected $table3 = 'events';
	
	public function __construct(\PDO $pdo, GlobalMethods $gm, Middleware $middleware){
		$this->pdo = $pdo;
		$this->gm = $gm;
		$this->md = $middleware;
	}
	
	public function getAll(){
		if(!$this->md->isAuthenticated()) return $this->gm->responsePayload(null, "Failed", "Invalid Login, Please login first", 403);
			
		$sql = "SELECT id, email_acc FROM " . $this->table1;
		
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
	
	public function EventTime($event){
		if(!$this->md->isAuthenticated()) return $this->gm->responsePayload(null, "Failed", "Invalid Login, Please login first", 403);
		
		try{
		  $sql = "SELECT event_name, event_date, event_start_time, event_end_time FROM " . $this->table3 . " WHERE event_name=?";
		  $stmt = $this->pdo->prepare($sql);
	      if($stmt->execute([$event->event_name])){
		   	$event = $stmt->fetchAll();
			
			   if (!empty($event)){
				    return $this->gm->responsePayload($event, "Success", "Event schedule", 200);
		   	} else {
				    return $this->gm->responsePayload(null, "Failed", "No Event Found", 404);
		   	}
	       }
		} catch (\PDOException $e) {
			return $this->gm->responsePayload(null, "Failed", "An error occurred: " . $e->getMessage(), 500);
		}
		
		
		
	}
	
}