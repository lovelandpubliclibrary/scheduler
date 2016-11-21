<?php # index.php
include('./includes/sessionstart.php');
include('./includes/allsessionvariables.php');

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

$page_title = date('j F Y',strtotime($date));

include ('./includes/header.html');
include ('./includes/sidebar.html');
include ('./display_functions.php');

$now = strtotime($date);

daily_schedule($now, $divisions);
	
include ('./includes/footer.html');
?>