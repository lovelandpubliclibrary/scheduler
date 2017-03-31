<?php # load_dates.php
?>
	<title>Load Dates</title>

<?php
date_default_timezone_set('America/Denver');

$today = date('Y-m-d');
$month6 = strtotime ('+2 years' , strtotime ( $today ) ) ;
$month6 = date('Y-m-d' , $month6 );

//Query database to get most recent date and week
require_once ('/home/teulberg/dev.lpl-repository.com/mysql_connect.php');
$query = "SELECT date, week_type FROM dates ORDER BY date_id desc LIMIT 1";
$result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));

while ($row = mysqli_fetch_assoc($result)) {
	$recentdate = $row['date'];
	$recentweek_type = $row['week_type'];
	}
	
if ($recentdate == $month6){
	echo "Up to date!";
	exit();
	}
else {
	$recentweekday = date('l', strtotime($recentdate));
		
	//Generate list of dates => 6 months from today
	function dates_between($start_date, $end_date = false){
		if ( !$end_date ){
			$end_date = date("Y-m-d");
			}

		$start_date = is_int($start_date) ? $start_date : strtotime($start_date);
		$end_date = is_int($end_date) ? $end_date : strtotime($end_date);
		 
		$end_date -= (60 * 60 * 24);

		global $test_date;
		global $array;
		$test_date = $start_date;
		$day_incrementer = 1;
		 
		do{
			$test_date = $start_date + ($day_incrementer * 60 * 60 * 24);
			$realdate = date('Y-m-d' , $test_date);
			$array[] = $realdate;
			} 
		while ($test_date < $end_date && ++$day_incrementer );
			}

	dates_between("$recentdate", "$month6");
	 
	//Query the database to add row.
	foreach ($array as $key=>$d){
		$dow = date('l', strtotime($d));
		
		if ($dow == 'Saturday'){
			if ($recentweek_type == 'a'){
				$recentweek_type = 'b';
				}
			elseif ($recentweek_type == 'b'){
				$recentweek_type = 'c';
				}
			elseif ($recentweek_type == 'c'){
				$recentweek_type = 'd';
				}
			elseif ($recentweek_type == 'd'){
				$recentweek_type = 'a';
				}
			}
		
		$query = "INSERT into dates values (null, '$d', '$recentweek_type')";
		$result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));
		echo $d .' added as '. ucwords($recentweek_type).' Week<br/>';
		echo "\n";
		}
	}
 ?>