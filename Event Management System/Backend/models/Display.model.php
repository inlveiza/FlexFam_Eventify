<?php

require_once($apiPath . '/interface/Display.interface.php');

class Display implements DisplayInterface{
	protected $pdo, $gm;
	protected $table1 = 'events';
	protected $table2 = 'registration';
	
	public function __construct(\PDO $pdo, GlobalMethods $gm){
		$this->pdo = $pdo;
		$this->gm = $gm;
	}
	
	public function EventDisplay(){
		try{
			$sql = "SELECT event_name, event_date, event_start_time, event_end_time, event_status, event_description, resource_speaker, event_image FROM ".$this->table1." ORDER BY `".$this->table1."`.`event_date` ASC";
			$stmt = $this->pdo->prepare($sql);
			$stmt->execute();
			$display = $stmt->fetchAll();
			
			if (!empty($display)){
				foreach($display as &$event){
					$event['image_url'] = 'http://localhost:8080/FlexFam_Eventify/Event%20Management%20System/Backend/uploads/' . $event['event_image'];
				}
				return $this->gm->responsePayload($display, "Success", "Event schedule", 200);
		   } else {
				return $this->gm->responsePayload(null, "Failed", "No Event Found", 404);
		   }
			
		} catch (\PDOException $e){
			return $this->gm->responsePayload(null, "Failed", "An error occurred: " . $e->getMessage(), 500);
		}
	}
}
