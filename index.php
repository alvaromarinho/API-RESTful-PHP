<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

header('Content-type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authtoken");

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
require_once 'vendor/Resources.php';
require_once 'vendor/Route.php';
require_once 'vendor/Auth.php';

$_route = new Route();

/* Import resources from version */
$_files = array_slice(scandir($_route->getVersion()), 2);
foreach ($_files as $file)
	require_once $_route->getVersion() . '/' . $file;

$_class = array_map(function ($n) {
	return substr($n, 0, -4);
}, $_files);

if ($_route->getResource() == 'crypt')
	die(Resources::response(200, 'OK', Auth::crypt($_route->getArgs())));
if ($_route->getResource() == 'auth')
	die(Resources::response(200, 'OK', Auth::token($_POST)));
if (!in_array($_route->getResource(), $_class))
	die(Resources::response(404, 'Resource not found.'));
if (!Auth::login(getallheaders()))
	die(Resources::response(401, 'Unauthorized.'));
if ($_SERVER['REQUEST_METHOD'] == 'POST')
	die(Resources::response(200, 'OK', $_route->getResource()::create($_route->getArgs())));
else if ($_SERVER['REQUEST_METHOD'] == 'GET')
	die(Resources::response(200, 'OK', $_route->getResource()::read($_route->getArgs())));
else if ($_SERVER['REQUEST_METHOD'] == 'PUT')
	die(Resources::response(200, 'OK', $_route->getResource()::update($_route->getArgs())));
else if ($_SERVER['REQUEST_METHOD'] == 'DELETE')
	die(Resources::response(200, 'OK', $_route->getResource()::delete($_route->getArgs())));
