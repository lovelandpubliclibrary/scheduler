<?php #division.php

include('./includes/sessionstart.php');
include('./includes/allsessionvariables.php');

if(isset($_GET['div'])){
	$division = $_GET['div'];
	}

$ucdivision = $divisions[$division];

$page_title = "$ucdivision";

if(isset($_GET['schedule_id'])){
	$sched_id = $_GET['schedule_id'];
	}
if(isset($_GET['weekly'])){
	$weekly = TRUE;
	}

include ('./includes/header.html');
include ('./includes/divisionsidebar.html');
include ('./display_functions.php');

if (isset($_GET['today'])){
	$today = $_GET['today'];
	}
elseif (isset($_POST['date'])){
	$date = $_POST['date'];
	$date_array = explode("/",$date);
	$today = $date_array[2].'-'.$date_array[0].'-'.$date_array[1];
	}
else {
	$today = date('Y-m-d');
	}
$now = strtotime("$today");

echo '<span class="date"><h1>'.$ucdivision.'</h1></span>'."\n";

if (isset($sched_id)){
	echo '<div class="mobile toggle"><a href="/scheduler/'.$division.'/daily">Daily</a> / <a href="/scheduler/'.$division.'/weekly">Weekly</a></div>'."\n";
	echo '<div id="masterDiv">'."\n";
	division_master($sched_id);
	echo '</div>'."\n";
	include('./includes/mobiledivmenu.php');
	}
else{
	if(isset($weekly)){
		echo '<div class="toggle"><a href="/scheduler/'.$division.'/daily">Daily</a> / <span class="toggled">Weekly</span></div>'."\n";
		echo '<div id="weekDiv">'."\n";
		division_weekly($division, $now);
		echo '</div>'."\n";
		echo '<div id="timeoff">'."\n";
		division_timeoff($division, $today);
		echo '</div>'."\n";
		include('./includes/mobiledivmenu.php');
		}
	else{
		echo '<div class="toggle"><span class="toggled">Daily</span> / <a href="/scheduler/'.$division.'/weekly">Weekly</a></div>'."\n";
		echo '<div id="dayDiv">'."\n";
		division_daily($division, $now);
		echo '</div>'."\n";
		echo '<div id="timeoff">'."\n";
		division_timeoff($division, $today);
		echo '</div>'."\n";
		include('./includes/mobiledivmenu.php');
		}
	}
include ('./includes/footer.html');

?>