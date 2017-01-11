<?php #load_timesheets.php
?>
	<title>Load Timesheets</title>

<?php
date_default_timezone_set('America/Denver');

$today = date('Y-m-d');
$year5 = strtotime ('+5 years' , strtotime ($today)) ;
$year5 = date('Y-m-d' , $year5 );


require_once ('/home/teulberg/dev.lpl-repository.com/mysql_connect.php');
$query = "SELECT timesheets_start, timesheets_end from timesheet_alerts ORDER BY timesheets_id desc LIMIT 1";
$result = mysqli_query($dbc, $query);

while ($row = mysqli_fetch_assoc($result)) {
	$tss = $row['timesheets_start'];
	$tse = $row['timesheets_end'];
	}
	
for ($i = strtotime('+2 weeks', strtotime($tss)); $i <= strtotime($year5); $i = strtotime('+2 weeks', $i)){
	$i2 = strtotime('+3 days', $i);
	$array[] = array(0=>date('Y-m-d', $i), 1=>date('Y-m-d', $i2));
	}

if (isset($array)){	
	foreach ($array as $key=>$dates){
		$tss = $dates[0];
		$tse = $dates[1];
		$query2 = "INSERT into timesheet_alerts (timesheets_start, timesheets_end) values ('$tss', '$tse')";
		$result2 = mysqli_query($dbc, $query2);
		echo "Timesheet period loaded: $tss - $tse.<br/>\n";
		}
	}
else {
	echo "Up to date!";
	exit();
	}

?>