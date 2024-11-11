<?php

require_once($apiPath. '/interface/Global.interface.php');

class GlobalMethods implements GlobalInterface{
     public function responsePayload($payload,$remarks,$message,$code){
         $status = array("remarks" => $remarks, "message" => $message);
         http_response_code($code);
         
         $response = array( 
              "status" => $status, 
              "payload"=> $payload,
              "timestamp" => date('Y-m-d H:i:s'),
              "prepared_by" => "Aaron Jan" 
           );
           
         echo json_encode($response);
         exit;
      }
     public function notFound(){
         echo json_encode ([
            "msg" => "Your endpoint does not exist"
         ]);
         http_response_code(404);
      }
}
