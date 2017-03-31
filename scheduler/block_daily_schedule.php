<?php #block2.php
date_default_timezone_set('America/Denver');
require_once ('../mysql_connect.php');
include('./includes/allsessionvariables.php');
include ('./display_functions.php');
$today = $_GET['today'];
$now = strtotime($today);
daily_schedule($now, $divisions);
?>