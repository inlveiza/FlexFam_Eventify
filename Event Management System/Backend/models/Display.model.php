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
			$updatesql = "UPDATE ".$this->table1." SET is_archived = 1 WHERE event_date < CURDATE() AND is_archived = 0";
			$this->pdo->prepare($updatesql)->execute();
			
			$sql = "SELECT * FROM ".$this->table1." WHERE is_archived = 0 ORDER BY `".$this->table1."`.`event_date` ASC";
			$stmt = $this->pdo->prepare($sql);
			$stmt->execute();
			$display = $stmt->fetchAll();
			
			if (!empty($display)){
				$current_time = time();
				$upload_dir = 'http://localhost:8080/FlexFam_Eventify/Event%20Management%20System/Backend/uploads/';
				foreach($display as &$event){
					$event['image_url'] = $upload_dir . $event['event_image'];
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
