<?php

require_once($apiPath. '/interface/Auth.interface.php');

class Auth implements AuthInterface{
	protected $pdo, $gm;
	
	protected $table_name1 = "accounts";
	protected $table_name2 = "profiles";
	protected $table_name3 = "blacklisted_tokens";
	
	public function __construct(\PDO $pdo, GlobalMethods $gm)
	{
		$this->pdo = $pdo;
		$this->gm = $gm;
	}
	
	public function login($data) {
    $sql = "SELECT * FROM " . $this->table_name1 . " WHERE email_acc=?";
    
    try {
    	//Commence Login
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$data->email_acc]);
        
        //Checks if there is an existing account
        if ($stmt->rowCount() > 0) {
            $res = $stmt->fetch(); 
            //Checks if password is correct
            if ($this->checkPassword($data->password, $res['password'])) {
            	 //Checks if user is currently logged in
                 if (!empty($res['login_token'])) {
                      $decoded = explode(".", $res['login_token']);
                      $payload = json_decode(base64_decode($decoded[1]));
                      //checks if login token is already expired 
                      if (isset($payload->exp) && $payload->exp < time()) {
                             $updatesql = "UPDATE " . $this->table_name1 . " SET login_token = NULL WHERE id = ?";
                             $updatestmt = $this->pdo->prepare($updatesql);
                             $updatestmt->execute([$res['id']]);
                             
                             $expiry = date("Y-m-d H:i:s", $payload->exp);
                             $blacklistsql = "INSERT INTO " . $this->table_name3 . " (token, expiry) VALUES (?, ?)"; 
                             $blackliststmt = $this->pdo->prepare($blacklistsql);
                             $blackliststmt->execute([$res['login_token'],$expiry]);
                      } else {
                             return $this->gm->responsePayload(null, "Failed", "User is currently logged in", 403);
                      }
                  } 
                  
                  //Gather logged in account's profile
            	  $profsql = "SELECT * FROM " . $this->table_name2 . " WHERE id = ?";
                  $profstmt = $this->pdo->prepare($profsql);
                  $profstmt->execute([$res['id']]);
                  $profiles = $profstmt->fetch();
                  
                  //Credentials to be encoded in JWT
                  if($profiles) {
                    $tokenData = [
                        'id' => $res['id'],
                        'email_acc' => $res['email_acc'],
                        'is_admin' => $res['is_admin'],
                        'first_name' => $profiles['first_name'],
                        'last_name' => $profiles['last_name'],
                        'program' => $profiles['program'],
                        'year' => $profiles['year'],
                        'block' => $profiles['block']
                     ];
                     //Generate JWT
                     $token = $this->tokenGen($tokenData);
                     
                     //Input Generated token in login token
                     $updatesql = "UPDATE ". $this->table_name1 . " SET login_token = ? WHERE id = ?";
                     $updatestmt = $this->pdo->prepare($updatesql);
                     $updatestmt->execute([$token['token'], $res['id']]);
                      
                      return $this->gm->responsePayload($token, "Success", "Login Successful", 200);
                   } else {
                  	return $this->gm->responsePayload(null, "Failed", "Failed to fetch profile details",500);
                   }
               } else {
                  return $this->gm->responsePayload(null, "Failed", "Invalid username or password", 401);
               }
           } else {
               return $this->gm->responsePayload(null, "Failed", "Account does not exist", 404);
           }
       } catch (\PDOException $e) {
            return $this->gm->responsePayload(null, "Failed", "An error occurred: " . $e->getMessage(), 500);
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
          
          if (empty($data->email_acc) || empty($data->password) || empty($data->first_name) || empty($data->last_name) || empty($data->program) || empty($data->year) || empty($data->block)) {
              $errors[] = "All fields are required. Please fill out the form completely. ";
          } 
          if (!in_array($data->program, $valid_programs)) {
              $errors[] = "Invalid program. Please select a valid program.";
          }
          if (!filter_var($data->year, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 4]])) {
              $errors[] = "Invalid year. Please enter a year between 1 and 4.";
          }
          if (!isset($blocks_peryear[$data->year]) || !in_array($data->block, $blocks_peryear[$data->year])) {
             $errors[] = "Invalid block for the selected year. Please choose a valid block.";
          }         
          if (!empty($errors)) {
               return $this->gm->responsePayload(null, "Failed", implode(" ", $errors), 400);
          }
          
		try{
			$sql="SELECT * FROM ".$this->table_name1." WHERE email_acc=?";
			$stmt=$this->pdo->prepare($sql);
			$stmt->execute([$data->email_acc]);

			if($stmt->rowCount()>0){
				return $this->gm->responsePayload(null, "Failed","Account Already Registered",400);
			} else {
				$data->password=$this->encrypt_password($data->password);
				$this->pdo->beginTransaction();
				$ins_sql1="INSERT INTO ".$this->table_name1." (email_acc,password) VALUES (?,?)";
				$ins_stmt1=$this->pdo->prepare($ins_sql1);
				$ins_stmt1->execute([$data->user_acc, $data->password]);
				
				$data->id=$this->pdo->lastInsertId();
				
				$ins_sql2="INSERT INTO ". $this->table_name2." (id, first_name, last_name, program, year, block) VALUES(?,?,?,?,?,?)";
				$ins_stmt2=$this->pdo->prepare($ins_sql2);
				$ins_stmt2->execute([$data->id, $data->first_name,$data->last_name,$data->program,$data->year,$data->block]);
				$this->pdo->commit();
				
				return $this->gm->responsePayload(null, "Success", "Successfully Registered", 200);
			}
		} catch (\PDOException $e) {
			$this->pdo->rollBack();
			echo "Failed to Register ".$e->getMessage();
			return $this->gm->responsePayload(null,  "Failed","Couldn't Register User",400);
		}
	}
	
	public function logout() {
        $token_check = $this->verifyToken();

         if ($token_check["is_valid"]) {
              $jwt = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);

              if (count($jwt) !== 2 || $jwt[0] !== 'Bearer') {
                 return $this->gm->responsePayload(null, "Failed", "You are not authorized. Please log in first.", 403);
              }

              $token = $jwt[1];
              $decoded = explode(".", $token);
              $payload = json_decode(base64_decode($decoded[1]));

        // Clear login_token
              $updatesql = "UPDATE " . $this->table_name1 . " SET login_token = NULL WHERE id = ?";
              $updatestmt = $this->pdo->prepare($updatesql);
              $updatestmt->execute([$payload->tokenData->id]);

        // Check if the row was affected
              if ($updatestmt->rowCount() === 0) {
                  return $this->gm->responsePayload(null, "Failed", "Token has already been invalidated", 403);
              }

        // Blacklist the token only if it's not already blacklisted
              $blacklistsql = "SELECT * FROM " . $this->table_name3 . " WHERE token = ?";
              $blacklist_stmt = $this->pdo->prepare($blacklistsql);
              $blacklist_stmt->execute([$token]);

              if ($blacklist_stmt->rowCount() > 0) {
                   return $this->gm->responsePayload(null, "Failed", "Token has already been invalidated", 403);
              }

        // Insert the token into the blacklist
              $expiry = date("Y-m-d H:i:s", $payload->exp);
              $sql = "INSERT INTO " . $this->table_name3 . " (token, expiry) VALUES (?, ?)";
              $stmt = $this->pdo->prepare($sql);
              $stmt->execute([$token, $expiry]);

              return $this->gm->responsePayload(null, "Success", "Successfully logged out", 200);
          } else {
              return $this->gm->responsePayload(null, "Failed", "You are not authorized. Please log in first.", 403);
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
		
		$header = json_encode([
              'typ' => 'JWT', 
               'alg' => 'HS256'
               ]);
		$payload = json_encode([
             'tokenData' => $tokenData,
             'exp' => time() + (7 * 24 * 60 * 60)
              ]);
		
		$b64UrlHeader = str_replace(['+','/','='],['-','_',''], base64_encode($header));
		$b64UrlPayload = str_replace(['+','/','='],['-','_',''],base64_encode($payload));
		
		$signature = hash_hmac('sha256', $b64UrlHeader . "." . $b64UrlPayload, SECRET_KEY, true);
		$b64UrlSignature = str_replace(['+','/','='],['-','_',''], base64_encode($signature));
		
		$jwt = $b64UrlHeader . "." . $b64UrlPayload . "." . $b64UrlSignature;
		 
		return array('token'=>$jwt);
	}
	
	public function tokenPayload($payload,$is_valid=false){
		return array(
		"payload"=>$payload,
		"is_valid"=>$is_valid
		);
	}
	
	public function verifyToken(){
		$jwt = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);
		if ($jwt [0] !== 'Bearer'){
			return $this->tokenPayload(null);
		} else {
			$decoded = explode(".", $jwt[1]);
			$payload = json_decode(str_replace(['+','/','='],['-','_',''], base64_decode($decoded[1])));
			
			$signature = hash_hmac('sha256', $decoded[0] . "." . $decoded[1], SECRET_KEY, true);
		    $b64UrlSignature = str_replace(['+','/','='],['-','_',''], base64_encode($signature));
			
			if ($b64UrlSignature === $decoded[2]){
				if (!isset($payload->exp)  || $payload->exp < time()) {
                    return $this->tokenPayload(null); 
                 }
				return $this->tokenPayload($payload, true);
			} else {
				return $this->tokenPayload(null);
			}
		}
	}

}