<?php

require_once($apiPath. '/interface/Auth.php');

class Auth {
	protected $pdo, $gm;
	
	protected $table_name1 = "users";
	protected $table_name2 = "profiles";
	protected $table_name3 = "blacklisted_tokens";
	
	public function __construct(\PDO $pdo, GlobalMethods $gm)
	{
		$this->pdo = $pdo;
		$this->gm = $gm;
	}
	
	public function login($data){
		$sql = "SELECT * FROM " . $this->table_name1. " WHERE user_acc=?";
		
		try{
			$stmt = $this->pdo->prepare($sql);			
			$stmt->execute([$data->user_acc]);
			if ($stmt->rowCount() > 0){
				$res = $stmt->fetch(); 
				if ($this->checkPassword($data->password, $res['password'])){
					$token = $this->tokenGen();
					
					return $this->gm->responsePayload(array("token" => $token), "Success", "Login Successful", 200);
				} else {
					return $this->gm->responsePayload(null, "Failed", "Failed to Login, Username and Password does not match", 400);
				}
			} else {
				return $this->gm->responsePayload(null, "Failed", "Account does not Exist", 404);
			}				
		}catch(\PDOException $e){
			echo "Error: ".$e->getMessage();
			return $this->gm->responsePayload(null,"Failed", "An error occured",500);
		}
	}
	
	public function register($data){
	     $valid_programs = array("BSIT");
         $blocks_peryear = [
           1 => ["A", "B", "C", "D", "E", "F", "a", "b", "c", "d", "e", "f"],      
           2 => ["A", "B", "C", "D", "E", "a", "b", "c", "d", "e"],      
           3 => ["A", "B", "C", "a", "b", "c"],           
           4 => ["A", "B", "a", "b"]            
          ];
          $errors=[];
          
          if (empty($data->user_acc) || empty($data->password) || empty($data->first_name) || empty($data->last_name) || empty($data->program) || empty($data->year) || empty($data->block)) {
              $errors[] = "All fields are required. Please fill out the form completely. ";
          } 
          if (!in_array($data->program, $valid_programs)) { 
               $errors[] = "Invalid program. Please select a valid program.";
          } 
          if (!filter_var($data->year, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 4]])) {
               $errors[] = "Invalid year. Please enter a year between 1 and 4.";
          } 
          if (!in_array($data->block, $blocks_peryear[$data->year])) {
               $errors[] = "Invalid block for the selected year. Please choose a valid block.";
          } 
          
          if (!empty($errors)) {
               return $this->gm->responsePayload(null, "Failed", implode(" ", $errors), 400);
          }
          
		try{
			$sql="SELECT * FROM ".$this->table_name1." WHERE user_acc=?";
			$stmt=$this->pdo->prepare($sql);
			$stmt->execute([$data->user_acc]);

			if($stmt->rowCount()>0){
				return $this->gm->responsePayload(null, "Failed","Account Already Registered",400);
			} else {
				$data->password=$this->encrypt_password($data->password);
				$this->pdo->beginTransaction();
				$ins_sql1="INSERT INTO ".$this->table_name1." (user_acc,password) VALUES (?,?)";
				$ins_stmt1=$this->pdo->prepare($ins_sql1);
				$ins_stmt1->execute([$data->user_acc, $data->password]);
				
				$data->user_id=$this->pdo->lastInsertId();
				
				$ins_sql2="INSERT INTO ". $this->table_name2." (user_id, first_name, last_name, program, year, block) VALUES(?,?,?,?,?,?)";
				$ins_stmt2=$this->pdo->prepare($ins_sql2);
				$ins_stmt2->execute([$data->user_id, $data->first_name,$data->last_name,$data->program,$data->year,$data->block]);
				$this->pdo->commit();
				
				return $this->gm->responsePayload(null, "Success", "Successfully Registered", 200);
			}
		} catch (\PDOException $e) {
			$this->pdo->rollBack();
			echo "Failed to Register ".$e->getMessage();
			return $this->gm->responsePayload(null,  "Failed","Couldn't Register User",400);
		}
	}
	
	public function logout(){
		$token_check = $this->verifyToken();
		if($token_check["is_valid"]){
			$jwt = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);
			
			if($jwt[0]  !== 'Bearer'){
				return $this->gm->responsePayload(null, "Failed", "You are Not Authorized. Please login first", 403);
			} else {
				$decoded = explode(".", $jwt[1]);
				$payload = json_decode(base64_decode($decoded[1]));
				
				$sql = "INSERT INTO " . $this->table_name3 . "(token, expiry) VALUES (?,?)";
				$stmt = $this->pdo->prepare($sql);
				$expiry = date("Y-m-d H:i:s", $payload->exp);
				$stmt->execute([$jwt[1],$expiry]);
				
				return $this->gm->responsePayload(null,"Success","Successfully Logged Out",200);
			}
		} else {
			return $this->gm->responsePayload(null, "Failed", "You are Not Authorized. Please login first", 403);
		}
	}
	
	public function checkPassword($password, $db_password){
		return $db_password === crypt($password,$db_password);
	}
	
	public function saltGenerator($length){
		$str_hash = md5(uniqid(mt_rand(),true));
		$b64string = base64_encode($str_hash);
		$m64string = str_replace(['+','/','='],['.','_',''],$b64string);
		
		return substr($m64string, 0, $length);
	}
	public function encrypt_password($password){
		$hashFormat="$2y$10$";
		$saltLength = 22;
		$salt = $this->saltGenerator($saltLength);
		
		return crypt($password, $hashFormat . $salt);
	}
	
	public function tokenGen($tokenData = null){
		
		$header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
		$payload = json_encode(['tokenData' => $tokenData, 'exp' => time() + (7 * 24 * 60 * 60)]);

		
		$b64UrlHeader = str_replace(['+','/','='],['-','_',''], base64_encode($header));
		$b64UrlPayload = str_replace(['+','/','='],['-','_',''],base64_encode($payload));
		
		$signature = hash_hmac('sha256', $b64UrlHeader . "." . $b64UrlPayload, SECRET_KEY, true);
		$b64UrlSignature = str_replace(['+','/','='],['-','_',''], base64_encode($signature));
		
		 $jwt = $b64UrlHeader . "." . $b64UrlPayload . "." . $b64UrlSignature;
		
		return array("token"=>$jwt);
	}
	
	public function tokenPayload($payload,$is_valid=false){
		return array(
		"payload"=>$payload,
		"is_valid"=>$is_valid
		);
	}
	
	public function verifyToken(){
		$jwt = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);
		if ($jwt [0] != 'Bearer'){
			return $this->tokenPayload(null);
		} else {
			$decoded = explode(".", $jwt[1]);
			$payload = json_decode(str_replace(['+','/','='],['-','_',''], base64_decode($decoded[1])));
			
			$signature = hash_hmac('sha256', $decoded[0] . "." . $decoded[1], SECRET_KEY, true);
		    $b64UrlSignature = str_replace(['+','/','='],['-','_',''], base64_encode($signature));
			
			if ($b64UrlSignature === $decoded[2]){
				if($payload->exp < time()){
					return $this->tokenPayload(null);
				}
				return $this->tokenPayload($payload, true);
			} else {
				return $this->tokenPayload(null);
			}
		}
	}

}