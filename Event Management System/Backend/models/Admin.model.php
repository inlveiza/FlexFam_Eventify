<?php

require_once($apiPath.'/interface/Admin.interface.php');

class Admin implements AdminInterface{
	protected $pdo, $gm, $md;
	protected $table1 = 'events';
	protected $table2 = 'accounts';
	
	public function __construct(\PDO $pdo, GlobalMethods $gm, Middleware $middleware){
		$this->pdo = $pdo;
		$this->gm = $gm;
		$this->md = $middleware;
	}
	
	public function AddEvent($data){
		if ($this->md->Authorization()){
			$data = (object) $_POST;
			$errors = [];
			if(empty($data->event_name) || empty($data->event_date) || empty($data->event_start_time) || empty($data->event_end_time) || empty($data->venue) || empty($data->event_description) || empty($data->resource_speaker)){
				$errors[] = "All fields required. Please fill up all fields";
			}
			if(!strtotime($data->event_start_time) || !strtotime($data->event_end_time)){
				$errors[] = "Invalid time format. Use hh:mm 24-hour format";
			}
			if(strtotime($data->event_start_time) >= strtotime($data->event_end_time)){
				$errors[] = "Invalid time schedule. Please fix your time schedule";
			}
			if(!strtotime($data->event_date)){
				$errors[] = "Invalid date format. Use YYYY-MM-DD format";
			}
			if(strtotime($data->event_date) < time()){
				$errors[] = "Invalid date. The input date has already passed.";
			}
			
			$image_name = null;
			if(isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK){
				$allowed_ext = ['jpg','jpeg','png'];
				$file_ext = pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION);
				
				if(!in_array(strtolower($file_ext), $allowed_ext)){
					$errors[] = "Invalid Image File Type. Only JPG, JPEG and PNG file types are accepted";
				} else {
					$upload_dir = __DIR__ . '/../uploads/';
					if(!is_dir($upload_dir)){
						mkdir($upload_dir, 0755, TRUE);
					}
					
					$image_name = uniqid() . '.' . $file_ext;
					$upload_path = $upload_dir . $image_name;
					
					if(!move_uploaded_file($_FILES['event_image']['tmp_name'], $upload_path)){
						$errors[]="Failed to upload image.";
						$image_name=null;
					}
				}
			} else {
				$errors[] = "Image for event is required";
			}
			
			if(!empty($errors)){
				if($image_name){
					unlink($upload_dir . $image_name);
				}
				return $this->gm->responsePayload(null, "Failed", implode(" ", $errors), 400);
			}
			  
			try {
			    $checksql = "SELECT * FROM " .$this->table1. " WHERE (event_name = ? AND event_date = ?) OR (venue = ? AND event_date = ?)";
			    $checkstmt = $this->pdo->prepare($checksql);
			    $checkstmt->execute([$data->event_name, $data->event_date, $data->venue, $data->event_date]);
			    $checkres = $checkstmt->fetch();
			    
				if($checkres){
					if($image_name){
					    unlink($upload_dir . $image_name);
				    }
				
				    if($checkres['event_name'] === $data->event_name){
				        return $this->gm->responsePayload(null, "Failed", "A similar event name has already been added.", 400); 
				    }
				
				    if($checkres['venue'] === $data->venue && strtotime($checkres['event_date']) === strtotime($data->event_date)){
			            return $this->gm->responsePayload(null, "Failed", "A venue has already been set on an event on a similar date", 400); 
				    }
				}
			     
			        $insertsql = "INSERT INTO " .$this->table1." (event_name, event_date, event_start_time, event_end_time, venue, event_description, resource_speaker, event_image) VALUES (?,?,?,?,?,?,?,?)";
			        $insertstmt = $this->pdo->prepare($insertsql);
			        $insertstmt->execute([$data->event_name, $data->event_date, $data->event_start_time, $data->event_end_time, $data->venue, $data->event_description, $data->resource_speaker, $image_name]);
			
			        return $this->gm->responsePayload(null, "Success", "Event Successfully Added",200);
			
			} catch (\PDOException $e) {
				echo "Failed to Add Event ".$e->getMessage();
				if($image_name){
					    unlink($upload_dir . $image_name);
				}
			    return $this->gm->responsePayload(null,  "Failed", "Couldn't Add Event", 400);
			}
		} else {
			return $this->gm->responsePayload(null, "Failed", "You don't have the authorization to create events", 403);
		}
	}
	
	public function UpdateEvent($id, $data) {
		if ($this->md->Authorization()) {
			$data = (object) $data; 
			$errors = [];
	
			if (isset($data->event_start_time) && !strtotime($data->event_start_time)) {
				$errors[] = "Invalid time format for event_start_time. Use hh:mm 24-hour format.";
			}
			if (isset($data->event_end_time) && !strtotime($data->event_end_time)) {
				$errors[] = "Invalid time format for event_end_time. Use hh:mm 24-hour format.";
			}
			if (isset($data->event_start_time, $data->event_end_time) &&
				strtotime($data->event_start_time) >= strtotime($data->event_end_time)) {
				$errors[] = "Invalid time schedule: start time must be before end time.";
			}
			if (isset($data->event_date) && !strtotime($data->event_date)) {
				$errors[] = "Invalid date format for event_date. Use YYYY-MM-DD format.";
			}
	
			if (isset($data->event_status) && !in_array($data->event_status, ['Active', 'Cancelled', 'Completed', 'Postponed'])) {
				$errors[] = "Invalid event status. Allowed values are 'Active', 'Cancelled', 'Completed', or 'Postponed'.";
			}
	
			if (!empty($errors)) {
				return $this->gm->responsePayload(null, "Failed", implode(" ", $errors), 400);
			}
	
			try {
				$fetchEventSql = "SELECT * FROM " . $this->table1 . " WHERE event_id = ?";
				$fetchEventStmt = $this->pdo->prepare($fetchEventSql);
				$fetchEventStmt->execute([$id]);
				$existingEvent = $fetchEventStmt->fetch(PDO::FETCH_ASSOC);
	
				if (!$existingEvent) {
					return $this->gm->responsePayload(null, "Failed", "Event not found", 404);
				}
	
				// Checking for overlapping events (same date and time range)
				if (isset($data->event_date, $data->event_start_time, $data->event_end_time)) {
					$overlapCheckSql = "SELECT COUNT(*) as count 
										FROM " . $this->table1 . " 
										WHERE event_date = ? 
										AND ((event_start_time < ? AND event_end_time > ?) 
										OR (event_start_time < ? AND event_end_time > ?)) 
										AND event_id != ?"; // Exclude the current event
					$overlapCheckStmt = $this->pdo->prepare($overlapCheckSql);
					$overlapCheckStmt->execute([
						$data->event_date,
						$data->event_start_time,
						$data->event_start_time,
						$data->event_end_time,
						$data->event_end_time,
						$id  // Exclude the event with this ID from the overlap check
					]);
					$overlapCount = $overlapCheckStmt->fetch(PDO::FETCH_ASSOC)['count'];
	
					if ($overlapCount > 0) {
						return $this->gm->responsePayload(
							null, 
							"Failed", 
							"This event is the same with another event. Please change it.", 
							400
						);
					}
				}

				$updateFields = [];
				$params = [];
	
				if (isset($data->event_name)) {
					$updateFields[] = "event_name = ?";
					$params[] = $data->event_name;
				}
				if (isset($data->event_date)) {
					$updateFields[] = "event_date = ?";
					$params[] = $data->event_date;
				}
				if (isset($data->event_start_time)) {
					$updateFields[] = "event_start_time = ?";
					$params[] = $data->event_start_time;
				}
				if (isset($data->event_end_time)) {
					$updateFields[] = "event_end_time = ?";
					$params[] = $data->event_end_time;
				}
				if (isset($data->venue)) {
					$updateFields[] = "venue = ?";
					$params[] = $data->venue;
				}
				if (isset($data->event_description)) {
					$updateFields[] = "event_description = ?";
					$params[] = $data->event_description;
				}
				if (isset($data->resource_speaker)) {
					$updateFields[] = "resource_speaker = ?";
					$params[] = $data->resource_speaker;
				}
				if (isset($data->event_status)) {
					$updateFields[] = "event_status = ?";
					$params[] = $data->event_status;
				}
	
				if (empty($updateFields)) {
					return $this->gm->responsePayload(null, "Failed", "No data to update", 400);
				}
	
				$updateFieldsSql = implode(", ", $updateFields);
				$params[] = $id;
				$updateSql = "UPDATE " . $this->table1 . " SET $updateFieldsSql WHERE event_id = ?";
				$updateStmt = $this->pdo->prepare($updateSql);
				$updateStmt->execute($params);
	
				return $this->gm->responsePayload(null, "Success", "Event Successfully Updated", 200);
			} catch (\PDOException $e) {
				return $this->gm->responsePayload(null, "Failed", "Couldn't Update Event: " . $e->getMessage(), 500);
			}
		} else {
			return $this->gm->responsePayload(null, "Failed", "You don't have the authorization to update events", 403);
		}
	}	
	
	public function GrantAdmin($id){
		if($this->md->Authorization()){
		     try{
		     	$checksql = "SELECT * FROM ".$this->table2." WHERE id = ?";
	     		$checkstmt = $this->pdo->prepare($checksql);
		     	$checkstmt->execute([$id]);
		         $checkres = $checkstmt->fetch();
			
	     		if($checkres){
		             if(!$checkres['is_admin']){
						$updatesql = "UPDATE ".$this->table2. " SET is_admin = 1 WHERE id = ?";
						$updatestmt = $this->pdo->prepare($updatesql);
						$updatestmt->execute([$id]);
						
						return $this->gm->responsePayload(null, "Success", $checkres['email_acc']." is successfully turned into admin.", 200);
					} else {
						return $this->gm->responsePayload(null, "Failed", "This account is already an admin", 400);
					}
		     	} else {
	    		 	return $this->gm->responsePayload(null, "Failed", "The account id does not exist", 404);
	     		}
		     } catch (\PDOException $e) {
			    echo "Failed to grant admin priviledge: " .$e->getMessage();
				return $this->gm->responsePayload(null, "Failed", "Failed to grant admin priviledge. Something went wrong", 403);
		     }
		} else {
			return $this->gm->responsePayload(null, "Failed", "You are not authorized to perform this function", 403);
		}
	}
	
}
