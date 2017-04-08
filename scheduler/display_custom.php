<?php # display_custom.php
date_default_timezone_set('America/Denver');

if (isset($_POST['date'])){
	$date = $_POST['date'];
	$date_array = explode("/",$date);
	$mon = $date_array[0];
	$dom = $date_array[1];
	$year = $date_array[2];
	}

elseif (isset($_GET['dom'])){
	$dom = $_GET['dom'];
	$mon = $_GET['mon'];
	$year = $_GET['year'];
	}

else {
	$dom = date('d');
	$mon = date('m');
	$year = date('Y');
	}

$date = "$year-$mon-$dom";
$phpmon = $mon-1;

$day = date('j', mktime(0,0,0,0,$dom));
$month = date('F', mktime(0,0,0,$mon));

$page_title = "$day $month $year";
include ('./includes/header.html');
include ('./includes/sidebar.html');
include ('./display_functions.php');

require_once ('../mysql_connect_sched.php'); //Connect to the db.

$now = strtotime("$date");

admin_specific($now);
	
include ('./includes/footer.html');
?>