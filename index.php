<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);

header("Content-type: application/json; charset=utf-8");
setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
date_default_timezone_set('America/Sao_Paulo');

$_files = array_slice(scandir('resource'), 2);
$_class = array_map(function($n){ return substr($n, 0, -4); }, $_files);

foreach ($_files as $file)
	require_once "resource/".$file;

$_uri = $_SERVER['REQUEST_URI'];
$_uri = explode("/", trim($_uri, "/"));

$_resource   = isset($_uri[0]) ? ucfirst($_uri[0]) : null;
$_parameters = isset($_uri[1]) ? $_uri[1] : null;

if(in_array($_uri[0], $_class))
	if($_resource) {
		if($_SERVER['REQUEST_METHOD'] == 'POST')
			echo json_encode($_resource::create());
		else if($_SERVER['REQUEST_METHOD'] == 'GET')
			echo json_encode($_resource::read($_parameters));
		else if($_SERVER['REQUEST_METHOD'] == 'PUT')
			echo json_encode($_resource::update($_parameters));
		else if($_SERVER['REQUEST_METHOD'] == 'DELETE')
			echo json_encode($_resource::delete($_parameters));
	} else
		http_response_code(404);
else 
	echo json_encode("API RESTful PHP by Alvaro Marinho - alvaromarinho.com.br");
