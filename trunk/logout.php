<?php #logout.php

session_save_path("../sess_tmp/");
session_name ('VisitID');
session_start();
include('./includes/allsessionvariables.php');

if(!isset($_SESSION['role'])){
	$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
	if ((substr($url, -1) == '/') OR (substr($url, -1) == '\\')){
		$url = substr($url, 0, -1);
		}
	$url .= '/login';
	header("Location: $url");
	exit();
	}
else {
	$_SESSION = array();
	session_destroy();
	setcookie (session_name(), '', time()-10, '/', '', 0);
	$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
	if ((substr($url, -1) == '/') OR (substr($url, -1) == '\\')){
		$url = substr($url, 0, -1);
		}
	$url .= '/login';
	header("Location: $url");
	exit();
	}

$page_title = 'Logged Out';

include ('./includes/header.html');
echo "<h1>Logged Out</h1>\n
<p>You are now logged out.</p>\n
<p><br/><br/></p>";
include ('./includes/footer.html');

?>