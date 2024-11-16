<?php

require_once($apiPath.'/interface/Admin.interface.php');

class Admin implements AdminInterface{
	protected $pdo, $gm, $md;
	protected $table1 = 'events';
	
	public function __construct(\PDO $pdo, GlobalMethods $gm, Middleware $middleware){
		$this->pdo = $pdo;
		$this->gm = $gm;
		$this->md = $middleware;
	}
	
	public function AddEvent($data){
		if ($this->md->Authorization()){
			$errors = [];
			
			if($data->event_start_time >= $data->event_end_time){
				$errors[] = "Invalid time schedule. Please fix your time schedule";
			}
			
			if(!empty($errors)){
				return $this->gm->responsePayload(null, "Failed", implode(" ", $errors), 400);
			}
			  
			try {
			    $checksql = "SELECT * FROM " .$this->table1. " WHERE event_name = ?";
			    $checkstmt = $this->pdo->prepare($checksql);
			    $checkstmt->execute([$data->event_name]);
			
			    if($checkstmt->rowCount()>0){
				    return $this->gm->responsePayload(null, "Failed", "A similar event name has already been added.", 400); 
			    }
			        $insertsql = "INSERT INTO " .$this->table1." (event_name, event_date, event_start_time, event_end_time, event_status, event_description) VALUES (?,?,?,?,?,?)";
			        $insertstmt = $this->pdo->prepare($insertsql);
			        $insertstmt->execute([$data->event_name, $data->event_date, $data->event_start_time, $data->event_end_time, $data->event_status, $data->event_description]);
			
			        return $this->gm->responsePayload(null, "Success", "Event Successfully Added",200);
			
			} catch (\PDOException $e) {
				echo "Failed to Add Event ".$e->getMessage();
			    return $this->gm->responsePayload(null,  "Failed", "Couldn't Add Event", 400);
			}
		} else {
			return $this->gm->responsePayload(null, "Failed", "You don't have the authorization to create events", 403);
		}
	}
	
}