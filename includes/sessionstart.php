<?php
session_save_path("../sess_tmp/");
session_name ('VisitID');
session_start();

if(!isset($_SESSION['role'])){
	$from = $_SERVER['REQUEST_URI'];
	$_SESSION['came_from'] = substr($from, strrpos($from,'/')+1);
	$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
	if ((substr($url, -1) == '/') OR (substr($url, -1) == '\\')){
		$url = substr($url, 0, -1);
		}
	$url .= '/login';
	header("Location: $url");
	exit();
	}

?>