<?php #timesheet_marcia.php
include('./includes/supersessionstart.php');
$came_from = $_SESSION['came_from'];
include('./includes/allsessionvariables.php');

$page_title = 'Marcia Lewis Timesheet';

include ('./includes/header.html');
include ('./includes/sidebar.html');

echo '<div class="mobilewrapper_outer"><div class="mobilewrapper_inner">
	<span class="date"><h1>'.$page_title.'</h1></span>';

$today = date('Y-m-d');
$pps = array();
$query = "SELECT * from pay_periods where pp_start_date<='$today' ORDER BY pp_start_date desc LIMIT 4";
$result = mysqli_query($dbc, $query);

while ($row = mysqli_fetch_assoc($result)){
	$pp_id = $row['pp_id'];
	$pp_date = $row['pp_start_date'];
	$pps[$pp_id] = $pp_date;
	}
	
//Dates between function
function dates_between_inclusive($start_date, $end_date){
	global $array;
	$array = array();
	
	$start_date .= ' 9:00:00am';
	$array[] = date('m/d/Y',strtotime($start_date));
	$end_date .= ' 10:00:00am';
	$start_date = is_int($start_date) ? $start_date : strtotime($start_date);
	$end_date = is_int($end_date) ? $end_date : strtotime($end_date);
	 
	$end_date -= (60 * 60 * 24);

	global $test_date;
	$test_date = $start_date;
	$day_incrementer = 1;
	 
	do{
		$test_date = $start_date + ($day_incrementer * 60 * 60 * 24);
		$realdate = date('m/d/Y', $test_date);
		$array[] = $realdate;
		} 
	while ($test_date <= $end_date && ++$day_incrementer );
	}
	
$fn = 'Marcia';
$ln = 'Lewis';
$emp_id = 27;
$empno = 7019;
	
if (isset($_POST['submitted'])){
	$pp_id = $_POST['payperiod'];
	
	$query = "SELECT pp_year, pp_cycle, pp_start_date FROM pay_periods WHERE pp_id='$pp_id'";
	$result = mysqli_query($dbc, $query);
	$row = mysqli_fetch_assoc($result);
	$cycle = $row['pp_cycle'];
	$year = $row['pp_year'];
	$pp_start_date = $row['pp_start_date'];
	$pp_end_date = strtotime('+13days', strtotime($pp_start_date));
	$pp_end_date = date('Y-m-d', $pp_end_date );
	dates_between_inclusive("$pp_start_date","$pp_end_date");
	$pay_date = strtotime('+11days', strtotime($pp_end_date));
	$pay_date = date('m/d/Y', $pay_date);
	
	$entries = array();
	
	$query = "SELECT * from time_entry t, pay_periods p, employees e WHERE p.pp_id = t.pp_id and p.pp_id='$pp_id'
		and e.emp_id = t.emp_id and t.emp_id='$emp_id' order by entry_date asc, hour_code asc";
	$result = mysqli_query($dbc, $query);
	while($row = mysqli_fetch_assoc($result)){
		$date = $row['entry_date'];
		$hour_code = $row['hour_code'];
		$hours = $row['hours'];
		
		$entries[] = array($date, $hour_code, $hours);
		}

	$file = 'timesheets/marcia_timesheet.csv';	
	$handle = fopen($file, "w");
	$csv = '';
	foreach ($array as $k=>$v){
		$csv .= $v.',';
		}
	$csv .= "\r\n$pay_date\r\n$year,Pay Period #$cycle,$pp_start_date\r\nDate*YYYY-MM-DD,Hour Code,Number of Hours##.##\r\n";
	foreach ($entries as $k=>$v){
		$csv .= $v[0].','.$v[1].','.$v[2]."\r\n";
		}
	fwrite($handle, $csv);
	fclose($handle);
	
	if (file_exists($file)) {
		echo '<div class="message">Timesheet data has been generated for Marcia for:<br/>'.$year.' Pay Period #'.$cycle.', starting <b>'.$pp_start_date.'</b></div>';
		}
	else{
		echo '<div class="errormessage"><h3>Error!</h3><br/>There was an error generating the report. Please see your system administrator.</div>';
		}
	}

echo '<div class="coverform">';
echo '<form action="timesheet_marcia" method="post" name="payperiod_select" id="payperiod_select">
	<div class="label">Choose Pay Period Start Date:</div>
	<select name="payperiod">';
foreach ($pps as $pp_id=>$pp_start_date){
	echo '<option value="'.$pp_id.'">'.$pp_start_date.'</option>';
	}
echo '</select>
	<p><input type="submit" name="submit" value="Submit" /></p>
	<input type="hidden" name="submitted" value="TRUE" />';
echo '</form></div></div></div>';
include ('./includes/footer.html');