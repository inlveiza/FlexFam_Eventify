<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: X-Requested-With, Origin, Content-Type,");
header("Access-Control-Max-Age: 86400");
date_default_timezone_set("Asia/Manila");
set_time_limit(1000);

//directory of files
$rootPath = $_SERVER["DOCUMENT_ROOT"];
$apiPath = $rootPath ."/Backend";

//connects the database
require_once($apiPath .'/configs/dbconn.php');
//Connects models 
require_once($apiPath .'/controllers/Path.php');

//database connection
$db = new Connection();
$pdo = $db->connect();

//model instantiates
$gm = new GlobalMethods();
$auth = new Auth($pdo,$gm);
//$try = new Example($pdo, $gm);

//request URL used to test API
$req = [];
if (isset($_REQUEST['request']))
    $req = explode ('/', rtrim($_REQUEST['request'], '/'));
else $req = array("errorcatcher");

switch ($_SERVER['REQUEST_METHOD']){
    case 'GET':
       //soon
        break;
    
    case 'POST':
        $data_input = json_decode(file_get_contents("php://input"));
        require_once($apiPath . '/routes/Auth.routes.php');
        break;
        
    default:
        echo "nuh uh ain't working cuh";
        http_response_code(403);
        break;
}

