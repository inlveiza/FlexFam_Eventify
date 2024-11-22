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
			    $checksql = "SELECT * FROM " .$this->table1. " WHERE event_name = ? OR venue = ?";
			    $checkstmt = $this->pdo->prepare($checksql);
			    $checkstmt->execute([$data->event_name, $data->venue]);
			    $checkres = $checkstmt->fetch();
			    
				if($checkres){
					if($image_name){
					    unlink($upload_dir . $image_name);
				    }
				
				    if($checkres['event_name'] === $data->event_name){
				        return $this->gm->responsePayload(null, "Failed", "A similar event name has already been added.", 400); 
				    }
				
				    if($checkres['venue'] === $data->venue && strtotime($checkres['event_date']) === strtotime($data->event_date)){
					//&& strtotime($checkres['event_start_time'])===strtotime($data->event_start_time)
			            return $this->gm->responsePayload(null, "Failed", "A venue has already been set on an event on a similar date", 400); 
				    }
				}
			     
			        $insertsql = "INSERT INTO " .$this->table1." (event_name, event_date, event_start_time, event_end_time, venue , event_description, resource_speaker, event_image) VALUES (?,?,?,?,?,?,?,?)";
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
	
	public function UpdateEvent($id,$data){
		
	}
	
}