<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

header('Content-type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    if (isset($_SERVER["HTTP_ACCESS_CONTROL_REQUEST_METHOD"]))
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
    if (isset($_SERVER["HTTP_ACCESS_CONTROL_REQUEST_HEADERS"]))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    exit(0);
}

setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
date_default_timezone_set('America/Sao_Paulo');

require_once 'vendor/Connection.php';
require_once 'vendor/Utils.php';
require_once 'vendor/Route.php';
require_once 'vendor/Auth.php';
require_once 'vendor/Access.php';

$_route = new Route();
$_access = Access::hasAccess($_route);

if ($_access) {
	if(is_object($_access) && Auth::login(getallheaders(), $_access)) {
		require_once $_route->getVersion() . '/resource/' . $_route->getResource().'Resource.php';
	} else if ($_route->getResource() == 'auth') {
		die(Utils::response(200, 'OK', Auth::token($_POST)));
	} else {
		die(Utils::response(200, 'OK', ['PHP-API']));
	}
} else {
    die(Utils::response(404, 'Resource for method HTTP not found.'));
}
