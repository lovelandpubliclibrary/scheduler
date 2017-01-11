<?php #block3.php
date_default_timezone_set('America/Denver');
require_once ('../mysql_connect.php');
include('./includes/allsessionvariables.php');
include ('./display_functions.php');
$today = $_GET['today'];
$now = strtotime($today);
$division = 'Subs';
echo '<div id="dayDiv">';
subs_specific($division, $now);
echo '</div>';
?>