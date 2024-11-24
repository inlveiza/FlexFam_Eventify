<?php

ini_set('display_errors',1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: token, Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
    //header('Access-Control-Allow-Headers: token, Content-Type, Authorization');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Max-Age: 1728000');
    //header('Content-Length: 0');
    //header('Content-Type: text/plain');
    //set_time_limit(1000);
    //die();
    exit();
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
$display = new Display($pdo, $gm);

$middleware = new Middleware($gm, $auth);

$try = new Example($pdo, $gm, $middleware);
$user = new User($pdo,$gm,$middleware);
$admin = new Admin($pdo, $gm, $middleware);


//$try = new Example($pdo,$gm);

// Request endpoint used to test API
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
        require_once($apiPath . '/routes/Display.routes.php');
       
      if($req[0] == 'validation'){
           if(empty($req[1])) {echo json_encode($try->Authorization()); return ;}
           return;
       } //Admin functionality test
       
        http_response_code(404); // Not Found
        echo json_encode(array("error" => "No valid endpoint specified"));
        break;
      break;

    case 'POST':
        $data_input = json_decode(file_get_contents("php://input"));

        require_once($apiPath . '/routes/Auth.routes.php');
        require_once($apiPath . '/routes/User.routes.php');
        require_once($apiPath . '/routes/Admin.routes.php');
        
        http_response_code(404); // Not Found
        echo json_encode(array("error" => "No valid endpoint specified"));
        break;
        
     case 'PUT':
        $data_input = json_decode(file_get_contents("php://input"));
        
        require_once($apiPath . '/routes/Admin.routes.php');
        
        http_response_code(404);
        echo json_encode(array("error" => "No valid endpoint specified"));
        break;

    default:
        echo json_encode(array("error" => "Invalid request method"));
        http_response_code(403); // Forbidden
        break;
}
