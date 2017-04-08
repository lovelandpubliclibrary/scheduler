<?php # display_master.php

date_default_timezone_set('America/Denver');
require_once ('../mysql_connect_sched.php'); //Connect to the db.

$today = date('Y-m-d');
$now = strtotime("$today");

$year = date('Y');

//Get season.
$query2 = "SELECT memorial_day, labor_day FROM holidays where year='$year'";
$result2 = @mysql_query($query2);

while ($row2 = mysql_fetch_assoc($result2)) {
	$memorial_day = $row2['memorial_day'];
	$labor_day = $row2['labor_day'];
	}

$mem_sat = strtotime ('-2 days', strtotime ($memorial_day));
$lab_sat = strtotime ('-2 days', strtotime ($labor_day));

if ((strtotime($today) >= $mem_sat) && (strtotime($today) < $lab_sat)){ 
	$season = 'summer';
	}
elseif (strtotime($today) < $mem_sat){
	$season = 'spring';
	}
else {
	$season = 'fall';
	}

if (isset($_GET['week_type'])){
	$week_type = $_GET['week_type'];
	}
else {
	$week_type = 'a';
	}
if (isset($_GET['day'])){
	$day = $_GET['day'];
	}
else {
	$day = 'mon';
	}

$page_title = date('l', strtotime($day)).', '.ucwords($week_type).' '.ucwords($season).' '.$year;
include('./includes/sessionstart.php');
include('./includes/allsessionvariables.php');
include ('./includes/header.html');
include ('./includes/masterssidebar.html');
include ('./display_functions.php');
include('./includes/mobilemastermenu.php');

public_master($week_type, $day, $year, $season, $now);

include ('./includes/footer.html');
?>