<?php

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
    header('Access-Control-Allow-Headers: token, Content-Type, Authorization');
    header('Access-Control-Max-Age: 1728000');
    header('Content-Length: 0');
    header('Content-Type: text/plain');
    set_time_limit(1000);
    die();
}

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

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

$middleware = new Middleware($auth);

$user = new User($pdo,$gm,$middleware);


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
       
      if($req[0] == 'try'){
           if(empty($req[1])) {echo json_encode($user->getAll()); return ;}
           return;
       } //User functionality test
        
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
