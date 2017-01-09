<?php #block.php
date_default_timezone_set('America/Denver');
require_once ('../mysql_connect_sched2.php');
include('./includes/allsessionvariables.php');
include ('./display_functions.php');
$today = $_GET['today'];
$now = strtotime($today);
$division = $_GET['division'];
echo '<div id="dayDiv">';
division_daily($division, $now);
echo '</div>';
?>