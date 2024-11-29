<?php

require_once($apiPath. '/interface/User.interface.php');

class User implements UserInterface{
	protected $pdo, $gm, $md, $headers;
	
	protected $table1 = 'accounts';
	protected $table2 = 'profiles';
	protected $table3 = 'events';
	protected $table4 = 'registration';
	
	public function __construct(\PDO $pdo, GlobalMethods $gm, Middleware $middleware){
		$this->pdo = $pdo;
		$this->gm = $gm;
		$this->md = $middleware;
		//$this->headers = apache_request_headers();
		$this->headers = $_SERVER;
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
	
	public function EventTime($data){
		if(!$this->md->isAuthenticated()) return $this->gm->responsePayload(null, "Failed", "Invalid Login, Please login first", 403);
		
		try{
		  $sql = "SELECT event_name, event_date, event_start_time, event_end_time FROM " . $this->table3 . " WHERE event_name=?";
		  $stmt = $this->pdo->prepare($sql);
	      if($stmt->execute([$data->event_name])){
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
	
	public function EventRegister($data){
		if(!$this->md->Authorization()){
			try{
				$authData = explode(' ',$this->headers['HTTP_AUTHORIZATION']);
				//$authData = explode(' ',$this->headers['Authorization']);
				$decoded = explode('.', $authData[1]);
				$tokenData = json_decode(base64_decode($decoded[1]));
				
				$userID = $tokenData->tokenData->id;
				
				$eventsql = "SELECT * FROM ".$this->table3." WHERE event_name = ?";
				$eventstmt = $this->pdo->prepare($eventsql);
				$eventstmt->execute([$data->event_name]);
				
				if($eventstmt->rowCount() !== 0){
					$event = $eventstmt->fetch();
					
					if(isset($event)){
						if($event['is_archived']){
							return $this->gm->responsePayload(null, "Failed", "No event found (archived)", 403);
						}
						if($event['event_status'] == 'Cancelled' || $event['event_status'] == 'Completed'){
							return $this->gm->responsePayload(null, "Failed", "This event no longer accepts registrations", 403);
						}
					}
					$eventID = $event['event_id'];
				} else {
					return $this->gm->responsePayload(null, "Failed", "No event found", 403);
				}
				
				
				$checksql = "SELECT user_id, event_id FROM ".$this->table4." WHERE user_id = ? AND event_id = ?";
				$checkstmt = $this->pdo->prepare($checksql);
				$checkstmt->execute([$userID,$eventID]);
				
				if($checkstmt->rowCount() > 0){
					return $this->gm->responsePayload(null, "Failed", "You have already registered on this event", 400);
				}
				
				$registersql = "INSERT INTO ".$this->table4." (user_id, event_id) VALUES (?,?)";
				$registerstmt = $this->pdo->prepare($registersql);
				
				if($registerstmt->execute([$userID, $eventID])){
					return $this->gm->responsePayload(null, "Success", "Successfully Registered", 200);
				} else {
					return $this->gm->responsePayload(null, "Failed", "Registration Failed", 400);
				}
				
			} catch (\PDOException $e) {
				return $this->gm->responsePayload(null, "Failed", "An error occurred: " . $e->getMessage(), 500);
			}
		} else {
			return $this->gm->responsePayload(null,"Failed" , "You are not an authorized user", 403);
		}
	}
	
}