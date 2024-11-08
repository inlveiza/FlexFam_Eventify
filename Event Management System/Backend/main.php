<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: X-Requested-With, Origin, Content-Type, Authorization");
header("Access-Control-Max-Age: 86400");
date_default_timezone_set("Asia/Manila");
set_time_limit(1000);

// Directory of files
$rootPath = $_SERVER["DOCUMENT_ROOT"];
//CHANGE ACCORDING TO WHERE THE BACKEND FOLDER IS LOCATED IN htdocs!!
$apiPath = $rootPath . "/FlexFam_Eventify/Event Management System/Backend/";


// Connects the database
require_once($apiPath . '/configs/dbconn.php');
// Connects models
require_once($apiPath . '/controllers/Path.php');

// Database connection
$db = new Connection();
$pdo = $db->connect();

// Model instantiation
$gm = new GlobalMethods();
$auth = new Auth($pdo, $gm);
//$try = new Example($pdo,$gm);

// Request URL used to test API
$req = [];
if (isset($_REQUEST['request'])) {
    $req = explode('/', rtrim($_REQUEST['request'], '/'));
} else {
    $req = array("errorcatcher");
}

// Log incoming request for debugging
error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("Request data: " . json_encode(file_get_contents("php://input")));

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Implement GET logic if needed
       // echo json_encode(array("message" => "GET method is not implemented."));
       // http_response_code(405); // Method Not Allowed
      break;

    case 'POST':
        $data_input = json_decode(file_get_contents("php://input"));

        require_once($apiPath . '/routes/Auth.routes.php');
        //require_once($apiPath . '/routes/try.routes.php');
        break;

    default:
        echo json_encode(array("error" => "Invalid request method"));
        http_response_code(403); // Forbidden
        break;
}
