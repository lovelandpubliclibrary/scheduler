<?php # division_weekly_template.php
date_default_timezone_set('America/Denver');

$today = date('Y-m-d');
$now = strtotime("$today");

if (isset($_GET['division'])){
	$division = $_GET['division'];
	}
else {
	header("Location: index.php");
	exit;
	}
if (isset($_GET['week_type'])){
	$week_type = $_GET['week_type'];
	}
else {
	$week_type = 'a';
	}
if (isset($_GET['season'])){
	$season = $_GET['season'];
	}
else {
	$season = 'summer';
	}
if (isset($_GET['year'])){
	$year = $_GET['year'];
	}
else {
	$year = date('Y');
	}

if ($division == 'lti'){
	$ucdivision = 'Library Tech & Innovation';
	}
elseif ($division =='customerservice'){
	$ucdivision = 'Customer Service';
	}
elseif ($division == 'techservices'){
	$ucdivision = 'Tech Services';
	}
else {
	$ucdivision = ucwords($division);
	}
$ucseason = ucwords($season);
$ucweek_type = ucwords($week_type);

require_once ('../mysql_connect_sched.php'); //Connect to the db.

$page_title = "$ucdivision, $ucweek_type $ucseason $year";
include('./includes/sessionstart.php');
include('./includes/allsessionvariables.php');
include ('./includes/header.html');
include ('./includes/divisionsidebar.html');
include ('./display_functions.php');

echo "<span class=\"date\"><h1>$ucdivision, $ucseason $year &ndash; $ucweek_type</h1></span>\n";
include('./includes/mobiledivmenu.php');

weekly_master($division, $week_type, $year, $season, $now);
	
include ('./includes/footer.html');

?>