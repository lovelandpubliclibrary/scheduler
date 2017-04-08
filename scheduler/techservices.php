<?php #techservices.php
date_default_timezone_set('America/Denver');
$division = "techservices";
$ucdivision = "Tech Services";

$page_title = "$ucdivision";

include('./includes/sessionstart.php');
include('./includes/allsessionvariables.php');
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
else {$today = date('Y-m-d');}
$now = strtotime("$today");

echo '<span class="date"><h1>'.$ucdivision.'</h1></span><br/>'."\n";
echo '<div id="myDiv">'."\n";
division_specific($division, $now);
echo '</div>'."\n";
echo '<div id="timeoff">'."\n";
division_timeoff($ucdivision, $today);
echo '</div>'."\n";
include ('./includes/footer.html');

?>