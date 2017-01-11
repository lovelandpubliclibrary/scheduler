<?php # load_seasons.php
?>
	<title>Load Seasons</title>

<?php
date_default_timezone_set('America/Denver');

$thisyear = date('Y');
$year_range = strtotime ('+5 years' , strtotime ( $thisyear ) ) ;
$year_range = date('Y' , $year_range );

//Query database to get last year and holidays
require_once ('/home/teulberg/dev.lpl-repository.com/mysql_connect_sched2.php');
$query = "SELECT year, memorial_day, labor_day FROM holidays ORDER BY year desc LIMIT 1";
$result = mysqli_query($dbc, $query) or die(mysql_error($dbc));

if ($result){
	while ($row = mysql_fetch_assoc($result)) {
		$recentyear = $row['year'];
		}
	}
else{
	echo (mysql_error());
}

if ($recentyear == $year_range){
	echo "Up to date!";
	exit();
	}
else {

	for ($year = $recentyear+1; $year<=$year_range; $year++) {
	
		//Calculate Memorial Day
		$number_of_days = date('t', mktime(0, 0, 0, 5, 1, $year));
	
		do {
			$weekday = date('N', mktime(0, 0, 0, 5, $number_of_days, $year));
			$number_of_days--;
			} while ($weekday != 1);
		
		$memday = $number_of_days + 1;
		$memday = "$year-05-$memday";
	
		//Calculate Labor Day
		$labday = date("Y-m-d", strtotime("September $year Monday"));

	$array[] = array("$year", "$memday", "$labday");
	}

	foreach ($array as $key => $holidays){
		$year = $holidays[0];
		$memorial_day = $holidays[1];
		$labor_day = $holidays[2];
	
		echo "$year $memorial_day $labor_day<br/>";
		$query2 = "INSERT into holidays(year, memorial_day, labor_day) 
			values ('$year', '$memorial_day', '$labor_day')";
		$result2 = mysqli_query($dbc, $query2) or die(mysql_error($dbc));
		}
	}
?>