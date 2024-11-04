<?php
if (!empty($req)) {
    switch ($req[0]) {
        case 'login':
            $response = $auth->login($data_input);
            echo json_encode($response);
            break;
        case 'register':
            $response = $auth->register($data_input);
            echo json_encode($response);
            break;
        case 'logout';
            $response = $auth->logout();
            echo json_encode($response);
            break;
        default:
            // Sending a JSON error response without any output before it
            http_response_code(404); // Not Found
            echo json_encode(array("error" => "No valid endpoint specified"));
            break;
    }
} else {
    http_response_code(400); // Bad Request
    echo json_encode(array("error" => "No endpoint specified"));
}
