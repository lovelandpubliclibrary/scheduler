<?php #block5.php
date_default_timezone_set('America/Denver');
require_once ('../mysql_connect.php');
include('./includes/allsessionvariables.php');
include ('./display_functions.php');
$today = $_GET['today'];
$now = strtotime($today);
$division = $_GET['division'];
echo '<div id="weekDiv">';
division_weekly($division, $now);
echo '</div>';
?>