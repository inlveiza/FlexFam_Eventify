<?php
       if($req[0] = 'login'){
    	   $response = $auth->login($data_input);
           echo json_encode($response);
       }
       
       if($req[0] = 'signup'){
       	$response = $auth->register($data_input);
           echo json_encode($response);
       }
       
       if($req[0] = 'logout'){
       	$response = $auth->logout();
           echo json_encode($response);
       }
    
