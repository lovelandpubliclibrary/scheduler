<?php #report.php

date_default_timezone_set('America/Denver');
require_once ('../mysql_connect_sched.php');

$page_title = "Schedule Exceptions Report";
include('./includes/supersessionstart.php');
include('./includes/allsessionvariables.php');
include ('./includes/header.html');
include ('./includes/supersidebar.html');

function dec_minutes($mins) {
	$dec_mins = $mins/60;
	return $dec_mins;
	}

$today = date('Y-m-d');

$query = "SELECT timesheets_start FROM timesheets where timesheets_start <= '$today' ORDER BY timesheets_id desc LIMIT 1";
$result = mysql_query($query);
$row = mysql_fetch_array($result, MYSQL_ASSOC);

$enddate = $row['timesheets_start'];

$startdate = strtotime('-14 days', strtotime ($enddate));
$startdate = date('Y-m-d', $startdate);

//Generate list of dates => 14 days from start
function dates_between($start_date, $end_date = false){
	$start_date = is_int($start_date) ? $start_date : strtotime($start_date);
	$end_date = is_int($end_date) ? $end_date : strtotime($end_date);

	$test_date;
	global $array;
	$test_date = $start_date;
     
	for ($i=1; $i<=14; $i++){
		$test_date = strtotime(date('Y-m-d', $test_date)."+1 day");
		$array[] = date('Y-m-d',$test_date);
		}
	}

dates_between("$startdate", "$enddate");

$printstart = date('M d, Y',strtotime($startdate));
$printend = date('M d, Y',strtotime($enddate));
echo '<span class="date"><h1>Schedule Report</h1></span><br/>';
echo '<span class="date"><h1>'.$printstart.' &ndash; '.$printend.'</h1></span>';


foreach ($array as $key=>$date){
//Get week type.
	$query = "SELECT week_type FROM dates where date = '$date'";
	$result = @mysql_query($query);

	while ($row = mysql_fetch_assoc($result)) {
		$week_type = $row['week_type'];
		}

	$year = date('Y', strtotime($date));
	$day = date('D', strtotime($date));

//Get season.
	$query2 = "SELECT memorial_day, labor_day FROM holidays where year='$year'";
	$result2 = @mysql_query($query2);

	while ($row2 = mysql_fetch_assoc($result2)) {
		$memorial_day = $row2['memorial_day'];
		$labor_day = $row2['labor_day'];
		}

	$mem_sat = strtotime ('-2 days', strtotime ($memorial_day));
	$lab_sat = strtotime ('-2 days', strtotime ($labor_day));

	if ((strtotime($date) >= $mem_sat) && (strtotime($today) < $lab_sat)){ 
		$season = 'summer';
		}
	elseif (strtotime($date) < $mem_sat){
		$season = 'spring';
		}
	else {
		$season = 'fall';
		}
	
	$tablename = strtolower($week_type).'_'.strtolower($day).'_'.$year.'_'.strtolower($season);
	$assoc_tablename = 'employeeassoc_'.$tablename;
	$days[] = array("$date","$tablename","$assoc_tablename");
	}

//Generate list of employees with exceptions
$exceptions = array();
$query3 = "SELECT e.employee_number, last_name FROM employees as e, timeoff as t, timeoffassoc as a
	WHERE e.active='Active' and a.timeoff_id=t.timeoff_id and a.employee_number=e.employee_number 
	and(timeoff_start_date between '$startdate' and '$enddate' OR timeoff_end_date between '$startdate' and '$enddate') 
	ORDER BY last_name asc";
$result3 = mysql_query($query3);

if ($result3){
	$num_rows3 = mysql_num_rows($result3);
	if ($num_rows3 != 0) {
		while ($row3 = mysql_fetch_array($result3, MYSQL_ASSOC)){
			$empno = $row3['employee_number'];
			$last_name = $row3['last_name'];
			$exceptions["$last_name"] = $empno;
			}
		}
	}
	
$query4 = "SELECT e.employee_number, last_name FROM employees as e, coverage as t, coverageassoc as a
	WHERE e.active='Active' and a.coverage_id=t.coverage_id and a.employee_number=e.employee_number 
	and(coverage_date between '$startdate' and '$enddate') ORDER BY last_name asc";
$result4 = mysql_query($query4);
if ($result4){
	$num_rows4 = mysql_num_rows($result4);
	if ($num_rows4 != 0) {
		while ($row4 = mysql_fetch_array($result4, MYSQL_ASSOC)){
			$empno = $row4['employee_number'];
			$last_name = $row4['last_name'];
			if(!in_array($empno, $exceptions)){
				$exceptions["$last_name"]=$empno;
				}
			}
		}
	}

ksort($exceptions);

echo '<div class="report">';
foreach ($exceptions as $last_name=>$empno){
	$query = "SELECT first_name FROM employees WHERE employee_number='$empno'";
	$result = mysql_query($query);
	while ($row = mysql_fetch_array($result, MYSQL_NUM)){
		$first_name = $row[0];
		}
	
	echo '<div class="listing"><div class="listlabel"><b>'.$last_name.', '.$first_name.'</b></div><table class="list">';
	foreach ($days as $key=>$array){
		$timeoff = array();
		$coverage = array();
		$normalhours = array(0=>7,7.5,8,8.5,9,9.5,10,10.5,11,11.5,12,12.5,13,13.5,14,14.5,
							15,15.5,16,16.5,17,17.5,18,18.5,19,19.5,20,20.5);
		$worked_array = array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,7=>0,
							8=>0,9=>0,10=>0,11=>0,12=>0,13=>0,14=>0,15=>0,16=>0,17=>0,
							18=>0,19=>0,20=>0,21=>0,22=>0,23=>0,24=>0,25=>0,26=>0,27=>0);
		$date = $array[0];
		$tablename = $array[1];
		$assoc_tablename = $array[2];
		
		$query = "SELECT time_format(shift_start,'%k') as shift_start, 
			time_format(shift_start,'%i') as shift_start_minutes, time_format(shift_end,'%k') as shift_end, 
			time_format(shift_end,'%i') as shift_end_minutes, time_format(lunch_start,'%k') as lunch_start, 
			time_format(lunch_start,'%i') as lunch_start_minutes, time_format(lunch_end,'%k') as lunch_end, 
			time_format(lunch_end,'%i') as lunch_end_minutes 
			FROM employees as e, $tablename as t, $assoc_tablename as a 
			WHERE e.employee_number = '$empno' and e.employee_number = a.employee_number and t.row_id = a.row_id";
		$result = mysql_query($query);
			
		if ($result){
			$row = mysql_fetch_array($result, MYSQL_ASSOC);
				
			$shift_start = $row['shift_start'];
			$shift_start_minutes = $row['shift_start_minutes'];
			$shift_end = $row['shift_end'];
			$shift_end_minutes = $row['shift_end_minutes'];
			$lunch_start = $row['lunch_start'];
			$lunch_start_minutes = $row['lunch_start_minutes'];
			$lunch_end = $row['lunch_end'];
			$lunch_end_minutes = $row['lunch_end_minutes'];
			
			$shift_start_dec = NULL;
			$shift_end_dec = NULL;
			$lunch_start_dec = NULL;
			$lunch_end_dec = NULL;
	
			$shift_start_dec = $shift_start;
			if ($shift_start_minutes != '00') {
				$shift_start_dec += dec_minutes($shift_start_minutes);
				}
			$shift_end_dec = $shift_end;
			if ($shift_end_minutes != '00') {
				$shift_end_dec += dec_minutes($shift_end_minutes);
				}
			$lunch_start_dec = $lunch_start;
			if ($lunch_start_minutes != '00') {
				$lunch_start_dec += dec_minutes($lunch_start_minutes);
				}
			$lunch_end_dec = $lunch_end;
			if ($lunch_end_minutes != '00') {
				$lunch_end_dec = $lunch_end + dec_minutes($lunch_end_minutes);
				}
			
			foreach ($normalhours as $key=>$hr){
				if (($hr >= $shift_start_dec) && ($hr < $shift_end_dec)){
					$worked_array[$key] += .5;
					}
				if (($hr >= $lunch_start_dec) && ($hr < $lunch_end_dec)){
					$worked_array[$key] -= .5;
					}
				}
			
//Timeoff
			$query2 = "SELECT time_format(timeoff_start_time,'%k') as timeoff_start, 
				time_format(timeoff_start_time,'%i') as timeoff_start_minutes, time_format(timeoff_end_time,'%k') as timeoff_end, 
				time_format(timeoff_end_time,'%i') as timeoff_end_minutes, timeoff_start_date, timeoff_end_date, timeoff_reason
				FROM employees as e, timeoff as t, timeoffassoc as a 
				WHERE e.employee_number = '$empno' and e.employee_number = a.employee_number and t.timeoff_id = a.timeoff_id 
				and timeoff_start_date <= '$date' and timeoff_end_date >= '$date'
				ORDER BY timeoff_start_time asc";
			$result2 = mysql_query($query2);
			
			if ($result2){
				$num_rows2 = mysql_num_rows($result2);
				if ($num_rows2 != 0) {
					while($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)){
						$timeoff_start_date = $row2['timeoff_start_date'];
						$timeoff_end_date = $row2['timeoff_end_date'];
						$timeoff_reason = $row2['timeoff_reason'];
						$timeoff_start_dec = NULL;
						$timeoff_end_dec = NULL;
						
						if ($timeoff_start_date == $timeoff_end_date){
							if ($row2['timeoff_start'] == 0){
								$timeoff_start = $shift_start;
								$timeoff_start_minutes = $shift_start_minutes;
								}
							else {
								$timeoff_start = $row2['timeoff_start'];
								$timeoff_start_minutes = $row2['timeoff_start_minutes'];
								}
							if ($row2['timeoff_end'] == 23){
								$timeoff_end = $shift_end;
								$timeoff_end_minutes = $shift_end_minutes;
								}
							else {
								$timeoff_end = $row2['timeoff_end'];
								$timeoff_end_minutes = $row2['timeoff_end_minutes'];
								}
							}
						elseif ($timeoff_start_date == $date){
							if ($row2['timeoff_start'] == 0){
								$timeoff_start = $shift_start;
								$timeoff_start_minutes = $shift_start_minutes;
								}
							else {
								$timeoff_start = $row2['timeoff_start'];
								$timeoff_start_minutes = $row2['timeoff_start_minutes'];
								}
							$timeoff_end = $shift_end;
							$timeoff_end_minutes = $shift_end_minutes;
							}
						elseif ($timeoff_end_date == $date){
							$timeoff_start = $shift_start;
							$timeoff_start_minutes = $shift_start_minutes;
							if ($row2['timeoff_end'] == 23){
								$timeoff_end = $shift_end;
								$timeoff_end_minutes = $shift_end_minutes;
								}
							else {
								$timeoff_end = $row2['timeoff_end'];
								$timeoff_end_minutes = $row2['timeoff_end_minutes'];
								}
							}
						else{
							$timeoff_start = $shift_start;
							$timeoff_start_minutes = $shift_start_minutes;
							$timeoff_end = $shift_end;
							$timeoff_end_minutes = $shift_end_minutes;
							}
						
						if ($timeoff_start > 12){
							$ts12 = $timeoff_start - 12;
							}
						else{
							$ts12 = $timeoff_start;
							}
						if ($timeoff_start_minutes != '00') {
							$ts12 .= ':'.$timeoff_start_minutes;
							}	
						if ($timeoff_end > 12){
							$te12 = $timeoff_end - 12;
							}
						else{
							$te12 = $timeoff_end;
							}
						if ($timeoff_end_minutes != '00') {
							$te12 .= ':'.$timeoff_end_minutes;
							}
							
						if ($timeoff_end < 12){
							$te12 .= 'am';
							}
						else {
							$te12 .= 'pm';
							if ($timeoff_start < 12){
								$ts12 .= 'am';
								}
							}
						
						$timeoff_start_dec = $timeoff_start;
						if ($timeoff_start_minutes != '00') {
							$timeoff_start_dec += dec_minutes($timeoff_start_minutes);
							}
						$timeoff_end_dec = $timeoff_end;
						if ($timeoff_end_minutes != '00') {
							$timeoff_end_dec += dec_minutes($timeoff_end_minutes);
							}
						
						foreach ($normalhours as $key=>$hr){
							if (($hr >= $timeoff_start_dec) && ($hr < $timeoff_end_dec)){
								if ($worked_array[$key] == .5){
									$worked_array[$key] -= .5;
									}
								}
							}
						$timeoff[] = array($ts12, $te12);
						}
					}
				}

//Coverage			
			$query3 = "SELECT time_format(coverage_start_time,'%k') as coverage_start, 
				time_format(coverage_start_time,'%i') as coverage_start_minutes, time_format(coverage_end_time,'%k') as coverage_end, 
				time_format(coverage_end_time,'%i') as coverage_end_minutes, coverage_date
				FROM employees as e, coverage as t, coverageassoc as a 
				WHERE e.employee_number = '$empno' and e.employee_number = a.employee_number and t.coverage_id = a.coverage_id 
				and coverage_date = '$date' ORDER BY coverage_start_time asc";
			$result3 = mysql_query($query3);
			
			if ($result3){
				$num_rows3 = mysql_num_rows($result3);
				if ($num_rows3 != 0) {
					while($row3 = mysql_fetch_array($result3, MYSQL_ASSOC)){
						$coverage_start = $row3['coverage_start'];
						$coverage_start_minutes = $row3['coverage_start_minutes'];
						$coverage_end = $row3['coverage_end'];
						$coverage_end_minutes = $row3['coverage_end_minutes'];
						$coverage_date = $row3['coverage_date'];
						$coverage_start_dec = NULL;
						$coverage_end_dec = NULL;
						
						if ($coverage_start > 12){
							$cs12 = $coverage_start - 12;
							}
						else{
							$cs12 = $coverage_start;
							}
						if ($coverage_start_minutes != '00') {
							$cs12 .= ':'.$coverage_start_minutes;
							}
						
						if ($coverage_end > 12){
							$ce12 = $coverage_end - 12;
							}
						else{
							$ce12 = $coverage_end;
							}
						if ($coverage_end_minutes != '00') {
							$ce12 .= ':'.$coverage_end_minutes;
							}
							
						if ($coverage_end < 12){
							$ce12 .= 'am';
							}
						else {
							$ce12 .= 'pm';
							if ($coverage_start < 12){
								$cs12 .= 'am';
								}
							}
						
						$coverage_start_dec = $coverage_start;
						if ($coverage_start_minutes != '00') {
							$coverage_start_dec += dec_minutes($coverage_start_minutes);
							}
						$coverage_end_dec = $coverage_end;
						if ($coverage_end_minutes != '00') {
							$coverage_end_dec += dec_minutes($coverage_end_minutes);
							}
						
						foreach ($normalhours as $key=>$hr){
							if (($hr >= $coverage_start_dec) && ($hr < $coverage_end_dec)){
								if ($worked_array[$key] == 0){
									$worked_array[$key] += .5;
									}
								}
							}
						$coverage[] = array($cs12, $ce12);
						}
					}
				}
				
			$day_total = array_sum($worked_array);
			if (($num_rows2 != 0) || ($num_rows3 != 0)){
				$num_rows_total = $num_rows2 + $num_rows3 + 1;
				$day_print = date('M j',strtotime($array[0]));
				
				echo '<tr><td><table class="child"><tr><td rowspan='.$num_rows_total.'" valign="top">'.$day_print.'</td><td></td>';
				echo '<td align="right">Total Worked:</td><td>'.$day_total.'</td></tr>';
				if ($num_rows2 != 0){
					foreach ($timeoff as $key=>$value){
						echo '<tr><td>Timeoff</td><td>'.$value[0].' - '.$value[1].'</td></tr>';
						}
					}
				if ($num_rows3 != 0){
					foreach ($coverage as $key=>$value){
						echo '<tr><td>Coverage</td><td>'.$value[0].' - '.$value[1].'</td></tr>';
						}
					}
				echo '</table></td></tr>';
				}
			}
		}
	echo '</table></div>';
	}
echo '</div>';
?>
<script type="text/javascript">
$("table.list tr:nth-child(2n)").addClass('rowspan');
$("table.child tr").removeClass('rowspan');
</script>
<?php
	
include ('./includes/footer.html');
?>