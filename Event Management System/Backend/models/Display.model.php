<?php

require_once($apiPath . '/interface/Display.interface.php');

class Display implements DisplayInterface{
	protected $pdo, $gm;
	protected $table1 = 'events';
	protected $table2 = 'registration';
	protected $table3 = 'profiles';
	protected $table4 = 'accounts';
	
	public function __construct(\PDO $pdo, GlobalMethods $gm){
		$this->pdo = $pdo;
		$this->gm = $gm;
	}
	
	public function EventDisplay(){
		try{
			
			$updatesql = "UPDATE ".$this->table1." SET is_archived = 1 WHERE event_date < CURDATE() AND is_archived = 0";
			$updatestmt = $this->pdo->prepare($updatesql);
			$updatestmt->execute();
			
			$sql = "SELECT * FROM ".$this->table1." WHERE is_archived = 0 ORDER BY `".$this->table1."`.`event_date` ASC";
			$stmt = $this->pdo->prepare($sql);
			$stmt->execute();
			$display = $stmt->fetchAll();
			
			if (!empty($display)){
				$current_time = time();
				$upload_dir = 'http://localhost/Event Management System/eventify_backend/uploads/';
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
	
	public function ProfileDisplay(){
		try{
			$headers = apache_request_headers();
			if(isset($headers['Authorization'])){
				$jwt = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);
				$decoded = explode('.', $jwt[1]);
				$ehh = json_decode(base64_decode($decoded[1]));
				
				return $this->gm->responsePayload($ehh, "Success", "Profile", 200);
			} else {
				return $this->gm->responsePayload(null, "Failed", "Unsuccesful", 400);
			}
		} catch (\PDOException $e){
			return $this->gm->responsePayload(null, "Failed", "An error occurred: " . $e->getMessage(), 500);
		} 
	}
	
	public function AudienceReport(){
		try{
			$reportsql = "
				SELECT
					r.user_id, r.event_id,
					p.first_name, p.last_name, p.year, p.block,
					e.event_name, a.email_acc
				FROM ".$this->table2." r
				JOIN ".$this->table3." p ON r.user_id = p.id
				JOIN ".$this->table1." e ON r.event_id = e.event_id
				JOIN ".$this->table4." a ON r.user_id = a.id
			";
			$reportstmt = $this->pdo->prepare($reportsql);
			$reportstmt->execute();
			
			if($reportstmt->rowCount() === 0){
				return $this->gm->responsePayload(null, "Failed", "No registrations found", 400);
			}
			
			$registrations = $reportstmt->fetchAll();
			
			return $this->gm->responsePayload($registrations, "Success", "Audience Reports", 200);
			
		} catch (\PDOException $e){
			return $this->gm->responsePayload(null, "Failed", "An error occurred: " . $e->getMessage(), 500);
		} 
	}
}
