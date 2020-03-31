<?php
require "../bootstrap.php";
/*
 * intial endpoint implementing REST API architecture.
 * GET/POST/PUT/DELETE 
 * /{action}/{id}
 * 
*/
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );

$id = null;
if (isset($uri[2])) {
    $id = $uri[2];
}

$domain = ucfirst($uri[1]); 
$controllerName = 'Src\Controller\\' . $domain . 'Controller';

$requestMethod = $_SERVER["REQUEST_METHOD"];

//Controllers get "autowired" according to REST request parameters
$controller = new $controllerName($dbConnection, $requestMethod, $id, $queueWriter, $cache);
$controller->processRequest();