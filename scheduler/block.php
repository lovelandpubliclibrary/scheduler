<?php #block.php
date_default_timezone_set('America/Denver');
require_once ('../mysql_connect_sched.php'); //Connect to the db.
include ('./display_functions.php');
$today = $_GET['today'];
$now = strtotime($today);
$division = $_GET['division'];
echo '<div id="myDiv">';
division_specific($division, $now);
echo '</div>';
?>