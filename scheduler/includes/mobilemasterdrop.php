<?php #masterdrop.php

date_default_timezone_set('America/Denver');
require_once ('../mysql_connect_sched.php'); //Connect to the db.

$today = date('Y-m-d');
$y = date('Y');

//Get season.
$query = "SELECT memorial_day, labor_day FROM holidays where year='$y'";
$result = @mysql_query($query);

while ($row = mysql_fetch_assoc($result)) {
	$memorial_day = $row['memorial_day'];
	$labor_day = $row['labor_day'];
	}

$mem_sat = strtotime ('-2 days', strtotime ($memorial_day));
$lab_sat = strtotime ('-2 days', strtotime ($labor_day));

if ((strtotime($today) >= $mem_sat) && (strtotime($today) < $lab_sat)){ 
	$s = 'summer';
	}
elseif (strtotime($today) < $mem_sat){
	$s = 'spring';
	}
else {
	$s = 'fall';
	}

$link = 'scheduler/masters';

echo "<li><a href=\"/$link/$y/$s/a/sat\">Master A</a></li>\n";
echo "<li><a href=\"/$link/$y/$s/b/sat\">Master B</a></li>\n";
echo "<li><a href=\"/$link/$y/$s/c/sat\">Master C</a></li>\n";
echo "<li><a href=\"/$link/$y/$s/d/sat\">Master D</a></li>\n";

?>