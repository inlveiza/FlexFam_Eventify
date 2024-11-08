<?php

if (!empty($req)){
	switch ($req[0]) {
        case 'decode':
            $response = $try->try($data_input);
            echo json_encode($response);
            break;
        
        default: 
            http_response_code(404); // Not Found
            echo json_encode(array("error" => "No valid endpoint specified"));
            break;
     }
} else {
	http_response_code(400); // Bad Request
    echo json_encode(array("error" => "No endpoint specified"));
}