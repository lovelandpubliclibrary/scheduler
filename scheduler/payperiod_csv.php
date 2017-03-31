<?php #payperiod_csv.php
include('./includes/supersessionstart.php');
$came_from = $_SESSION['came_from'];
include('./includes/allsessionvariables.php');

$page_title = 'Generate Pay Period CSV';

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

if (isset($_POST['submitted'])){
	$pp_id = $_POST['payperiod'];
	
	$entries = array();

	$query = "SELECT * from time_entry t, pay_periods p, employees e WHERE p.pp_id = t.pp_id and p.pp_id='$pp_id'
		and e.emp_id = t.emp_id order by e.last_name asc, entry_date asc";
	$result = mysqli_query($dbc, $query);
	while($row = mysqli_fetch_assoc($result)){
		$fn = $row['first_name'];
		$ln = $row['last_name'];
		$empno = $row['employee_number'];
		$assignment_id = $row['assignment_id'];
		$cycle = $row['pp_cycle'];
		$year = $row['pp_year'];
		$date = $row['entry_date'];
		$date = date('mdY', strtotime($date));
		$hour_code = $row['hour_code'];
		$hours = $row['hours'];
		$pp_start_date = $row['pp_start_date'];
		
		$entries[] = array($fn, $ln, $empno, $assignment_id, $cycle, $year, $date, $hour_code, $hours);
		}

	$file = 'payperiods/payperiod_'.$year.'_'.$cycle.'.csv';	
	$handle = fopen($file, "w");
	$csv = "First Name,Last Name,Employee Number,Assignment ID,Cycle Number,Cycle Year*YYYY,Entry Date*MMDDYYYY,Hour Code,Number of Hours##.##,Dollar Amount##.##,Hourly Rate##.##,Regular/SupplementalR|S,Prior FLSA Cycle(Y/N),Project Number,Work Order Number,Job Number,Acct #\r\n";
	foreach ($entries as $k=>$v){
		$csv .= $v[0].','.$v[1].','.$v[2].','.$v[3].','.$v[4].','.$v[5].','.$v[6].','.$v[7].','.$v[8].',,,R,,,,'."\r\n";
		}
	fwrite($handle, $csv);
	fclose($handle);
	
	if (file_exists($file)) {
		echo '<div class="message">Timesheet data has been generated for:<br/>'.$year.' Pay Period #'.$cycle.', starting <b>'.$pp_start_date.'</b></div>';
		}
	else{
		echo '<div class="errormessage"><h3>Error!</h3><br/>There was an error generating the report. Please see your system administrator.</div>';
		}
	
	}
echo '<div class="coverform">';
echo '<form action="/scheduler/payperiod_csv" method="post" name="payperiod_select" id="payperiod_select">
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