<?php

require_once($apiPath. '/interface/Global.php');

class GlobalMethods implements GlobalInterface{
     public function responsePayload($payload,$remarks,$message,$code){
         $status = array("remarks" => $remarks, "message" => $message);
         http_response_code($code);
         return array ("status" => $status, "payload"=> $payload,"timestamp" => date_create(), "prepared_by" => "Aaron Jan Estacio");
      }
     public function notFound(){
         echo json_encode ([
            "msg" => "Your endpoint does not exist"
         ]);
         http_response_code(403);
      }
}
