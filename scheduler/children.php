<?php #children.php

date_default_timezone_set('America/Denver');
$division = "children";
$ucdivision = "Children";

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

echo '<span class="date"><h1>'.$ucdivision.'</h1></span>';
include('./includes/mobiledivmenu.php');
echo '<div id="myDiv">';
division_specific($division, $now);
echo '</div>';
echo '<div id="timeoff">';
division_timeoff($division, $today);
echo '</div>';
include ('./includes/footer.html');

?>