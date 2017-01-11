<?php #view_emp_timesheet.php
//Time to decimal function
function dec_minutes($mins) {
	$dec_mins = $mins/60;
	return $dec_mins;
	}
//Dates between function
function dates_between_inclusive($start_date, $end_date){
	global $array;
	$array = array();
	$array[] = $start_date;
	
	$start_date .= ' 9:00:00am';
	$end_date .= ' 10:00:00am';
	$start_date = is_int($start_date) ? $start_date : strtotime($start_date);
	$end_date = is_int($end_date) ? $end_date : strtotime($end_date);
	 
	$end_date -= (60 * 60 * 24);

	global $test_date;
	$test_date = $start_date;
	$day_incrementer = 1;
	 
	do{
		$test_date = $start_date + ($day_incrementer * 60 * 60 * 24);
		$realdate = date('Y-m-d' , $test_date);
		$array[] = $realdate;
		} 
	while ($test_date <= $end_date && ++$day_incrementer );
	}

include('./includes/supersessionstart.php');

if (isset($_SESSION['came_from'])){
	$came_from = $_SESSION['came_from'];
	}
	
if (($came_from != 'approve_timesheets') && ($came_from != 'view_emp_timesheet') && ($came_from != 'edit_emp_timesheet')){
	header ('Location: approve_timesheets');
	}

include('./includes/allsessionvariables.php');
if (isset($_POST['from_approve'])){
	$_SESSION['pp_id'] = $_POST['pp_id'];
	$_SESSION['pp_start_date'] = $_POST['pp_start_date'];
	$_SESSION['employee_name'] = $_POST['employee_name'];
	$_SESSION['emp_id'] = $_POST['emp_id'];
	$_SESSION['assignment_id'] = $_POST['assignment_id'];
	header('Location:view_emp_timesheet');
	}
else{
	$pp_id = $_SESSION['pp_id'];
	$pp_start_date = $_SESSION['pp_start_date'];
	$employee_name = $_SESSION['employee_name'];
	$emp_id = $_SESSION['emp_id'];
	$assignment_id = $_SESSION['assignment_id'];
	}
	
$query = "SELECT weekly_hours, division from employees WHERE emp_id='$emp_id'";
$result = mysqli_query($dbc, $query);
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
	$weekly_hours = $row['weekly_hours'];
	$emp_division = $row['division'];
	}

$page_title = "Employee Timesheet | ".$employee_name;
	
if (isset($_POST['approved'])){
	$query2 = "SELECT * from timesheet_confirm WHERE emp_id='$emp_id' and pp_id='$pp_id' and assignment_id='$assignment_id'";
	$result2 = mysqli_query($dbc, $query2);
	if (($result2)&&(mysql_num_rows($result2) == 0)){
		$query = "INSERT into timesheet_confirm (emp_id, assignment_id, pp_id, employee_confirm, supervisor_approve) 
			VALUES ('$emp_id','$assignment_id','$pp_id','N','Y')";
		}
	else{
		$query = "UPDATE timesheet_confirm set supervisor_approve='Y' WHERE emp_id='$emp_id' and pp_id='$pp_id' and assignment_id='$assignment_id'";
		}
	$result = mysqli_query($dbc, $query);
	
	$query1 = "UPDATE time_entry set locked='Yes' WHERE emp_id='$emp_id' and pp_id='$pp_id' and assignment_id='$assignment_id'";
	$result1 = mysqli_query($dbc, $query1);
	
	if(mysql_affected_rows() == 0){
		if(isset($_POST['new_entry'])){
			foreach($_POST['new_entry'] as $entry_date=>$hours){
				if((!empty($hours))&&($hours != 0)&&(is_numeric($hours))){
					$query2 = "INSERT into time_entry (pp_id, emp_id, assignment_id, entry_date, hour_code, hours, locked)
						VALUES ('$pp_id', '$emp_id','$assignment_id','$entry_date','02','$hours','Yes')";
					$result2 = mysqli_query($dbc, $query2) or die(mysql_error());
					}
				}
			}
		}
	
	$_SESSION['timesheet_approved'] = TRUE;
	header ('Location: approve_timesheets');
	}
	
include ('./includes/header.html');
include ('./includes/supersidebar.html');
	
$pp_start_friendly = date('j M Y', strtotime($pp_start_date));
$pp_midweek_end_date = strtotime('+6days', strtotime($pp_start_date));
$pp_midweek_end_date = date('Y-m-d' , $pp_midweek_end_date );
$pp_midweek_start_date = strtotime('+7days', strtotime($pp_start_date));
$pp_midweek_start_date = date('Y-m-d' , $pp_midweek_start_date );
$pp_end_date = strtotime('+13days', strtotime($pp_start_date));
$pp_end_date = date('Y-m-d' , $pp_end_date );

$other_hours = array();
$query = "SELECT * from hour_codes WHERE hour_code not in ('02','28','26','24','48')";
$result = mysqli_query($dbc, $query);
while ($row = mysql_fetch_assoc($result)) {
	$hour_code = $row['hour_code'];
	$other_hours[$hour_code] = $row['description'];
	}

//Get previous entries
$previous = array();
$query = "SELECT * from time_entry WHERE emp_id='$emp_id' and entry_date>='$pp_start_date'
	and entry_date<='$pp_end_date' and assignment_id='$assignment_id'";
$result = mysqli_query($dbc, $query);
if (($result) && (mysql_num_rows($result)!=0)){
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
		$entry_date = $row['entry_date'];
		$hour_code = $row['hour_code'];
		$hours = $row['hours'];
		$previous[$entry_date][$hour_code] = $hours;
		}
	}
$confirmed = '';
$query = "SELECT * from timesheet_confirm WHERE emp_id='$emp_id' and pp_id = '$pp_id' and (employee_confirm='Y' or supervisor_approve='Y')";
$result = mysqli_query($dbc, $query);
if (($result) && (mysql_num_rows($result)!=0)){
	while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
		$confirmed = TRUE;
		}
	}
	
echo '<div class="wideview">
	<span class="date"><h1>Employee Timesheet</h1></span>
	<p style="margin:10px 0 -10px 0;font-size:14px;color:#013953;font-weight:bold;text-align:center;" class="divform">Timesheet for '.$pp_start_friendly.', '.$employee_name.'</p>';

if (($came_from == 'edit_emp_timesheet') && (isset($_SESSION['emp_timesheet_edited']))){
	echo '<div class="message">The timesheet for '.$employee_name.' starting '.$pp_start_date.' has been edited.</div>';
	unset($_SESSION['emp_timesheet_edited']);
	}
	?>
<script>
function calcTotals(){
	$(document).ready(function(){
		$('td.total').each(function(){
			var row_total = 0;
			$(this).parent().find('td.disp_hours').each(function(){
				row_total += Number($(this).text());
				});
			$(this).text(row_total);
			});
		$('td.weekly_total').each(function(){
			var week_total = 0;
			$(this).parent().parent().find('td.total').each(function(){
				week_total += Number($(this).text());
				});
			$(this).text(week_total);
			var shift_total = Number($(this).parent().parent().parent().find('.shift_total').text());
			
			if (week_total != shift_total){
				$(this).addClass('insufficient');
				}
			else{
				$(this).removeClass('insufficient');
				}
			});
		});
	}
calcTotals();
</script>
<?php
//Timesheet Week #1
dates_between_inclusive("$pp_start_date", "$pp_midweek_end_date");

echo '<table class="timetable top" cellspacing="0">'."\n";
echo '<tr class="timetable_date"><td></td>';
foreach ($array as $k=>$v){
	$short_date = date('j M', strtotime($v));
	$day = date('D', strtotime($v));
	echo '<td class="day">'.$short_date.'<br/>'.$day.'</td>';
	}
echo '<td class="day">Total</td></tr>';
echo '<tr class="scheduled"><td class="hours_type">Scheduled</td>';
foreach ($array as $k=>$v){
	echo '<td class="shift">';	
	$query = "SELECT date, week_type FROM dates where date = '$v'";
	$result = mysqli_query($dbc, $query);
	while ($row = mysql_fetch_assoc($result)) {
		$week_type = $row['week_type'];
		}

	$day = date('D', strtotime($v));
	
	$query2 = "SELECT time_format(shift_start,'%k') as shift_start, 
		time_format(shift_start,'%i') as shift_start_minutes, time_format(shift_end,'%k') as shift_end, 
		time_format(shift_end,'%i') as shift_end_minutes from employees as e, shifts as a, schedules as s 
		WHERE e.emp_id = '$emp_id' and e.emp_id = a.emp_id and 
		schedule_start_date <= '$v' and schedule_end_date >= '$v' 
		and week_type='$week_type' and shift_day='$day' and a.specific_schedule=s.specific_schedule 
		and e.active = 'Active' and (e.employee_lastday >= '$v' or e.employee_lastday is null)";
	$result2 = mysqli_query($dbc, $query2);
	if($result2){
		$shift_array = array();
		while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)){
			$shift_start = $row2['shift_start'];
			$shift_start_minutes = $row2['shift_start_minutes'];
			$shift_end = $row2['shift_end'];
			$shift_end_minutes = $row2['shift_end_minutes'];
			$shift_display = '';
			
			if ($shift_start > 12){
				$ss12 = $shift_start - 12;
				}
			elseif($shift_start == 0){
				$ss12 = NULL;
				}
			else{
				$ss12 = $shift_start;
				}
			if ($shift_start_minutes != '00') {
				$ss12 = $ss12.':'.$shift_start_minutes;
				}
		
			if ($shift_end > 12){
				$se12 = $shift_end - 12;
				}
			elseif($shift_end == 0){
				$se12 = NULL;
				}
			else{
				$se12 = $shift_end;
				}
			if ($shift_end_minutes != '00') {
				$se12 = $se12.':'.$shift_end_minutes;
				}
			if (isset($ss12)){
				$shift_display .= $ss12 . '-' . $se12;
				}
			if (!empty($shift_display)){
				$shift_array[] = $shift_display;
				}
			}
		if (count($shift_array)==1){
			echo $shift_array[0];
			}
		else{
			$sh_display = '';
			foreach ($shift_array as $item=>$shift){
				if ($item == 0){
					$sh_display .= $shift;
					}
				else{
					$sh_display .= '<br/>'.$shift;
					}
				}
			echo $sh_display;
			}
		}
	if ($emp_division == 'Subs'){
		$sub_query = "SELECT time_format(coverage_start_time,'%k') as cov_start, 
			time_format(coverage_start_time,'%i') as cov_start_minutes, time_format(coverage_end_time,'%k') as cov_end, 
			time_format(coverage_end_time,'%i') as cov_end_minutes from coverage c, employees e
			WHERE coverage_date = '$v' and c.emp_id = '$emp_id' and c.emp_id=e.emp_id 
			ORDER BY coverage_start_time asc";
		$sub_result = mysqli_query($dbc, $sub_query);
		if ($sub_result){
			$num = mysql_num_rows($sub_result);
			if ($num>0) {
				$sub_array = array();
				while($sub_row = mysql_fetch_array($sub_result, MYSQL_ASSOC)){
					$cov_start = $sub_row['cov_start'];
					$cov_start_minutes = $sub_row['cov_start_minutes'];
					$cov_end = $sub_row['cov_end'];
					$cov_end_minutes = $sub_row['cov_end_minutes'];
					
					if ($cov_start > 12){
						$cs12 = $cov_start - 12;
						}
					elseif($cov_start == 0){
						$cs12 = NULL;
						}
					else{
						$cs12 = $cov_start;
						}
					if ($cov_start_minutes != '00'){
						$cs12 = $cs12.':'.$cov_start_minutes;
						}
				
					if ($cov_end > 12){
						$ce12 = $cov_end - 12;
						}
					elseif($cov_end == 0){
						$ce12 = NULL;
						}
					else{
						$ce12 = $cov_end;
						}
					if ($cov_end_minutes != '00'){
						$ce12 = $ce12.':'.$cov_end_minutes;
						}
					
					$sub_array[] = array($cs12, $ce12);
					}
				if (count($sub_array)==1){
					echo $sub_array[0][0].'-'.$sub_array[0][1];
					}
				else{
					$cov_display = '';
					foreach ($sub_array as $item=>$covs){
						if ($item == 0){
							$cov_display .= $covs[0].'-'.$covs[1];
							}
						else{
							$cov_display .= '<br/>'.$covs[0].'-'.$covs[1];
							}
						}
					echo $cov_display;
					}
				}
			}
		}
	echo '</td>';
	}
echo '<td class="shift_total">';
if (isset($weekly_hours)){
	echo $weekly_hours;
	}
echo '</td></tr>';

echo '<tr class="specialshaded"><td class="hours_type"><b>Regular Hours Worked</b></td>';

foreach ($array as $k=>$v){
	echo '<td class="disp_hours">';
	$reg_hours = 0;
	
	if((count($previous)>0)||($confirmed==TRUE)){
		if((isset($previous[$v]))&&(isset($previous[$v]['02']))){
			$reg_hours = $previous[$v]['02'];
			}
		else{
			$reg_hours = 0;
			}
		}
	else{
		$query = "SELECT date, week_type FROM dates where date = '$v'";
		$result = mysqli_query($dbc, $query);
		while ($row = mysql_fetch_assoc($result)) {
			$week_type = $row['week_type'];
			}

		$day = date('D', strtotime($v));
		
		$query2 = "SELECT time_format(shift_start,'%k') as shift_start, 
			time_format(shift_start,'%i') as shift_start_minutes, time_format(shift_end,'%k') as shift_end, 
			time_format(shift_end,'%i') as shift_end_minutes, time_format(lunch_start,'%k') as lunch_start, 
			time_format(lunch_start,'%i') as lunch_start_minutes, time_format(lunch_end,'%k') as lunch_end, 
			time_format(lunch_end,'%i') as lunch_end_minutes FROM employees as e, shifts as a, schedules as s 
			WHERE e.emp_id = '$emp_id' and e.emp_id = a.emp_id and 
			schedule_start_date <= '$v' and schedule_end_date >= '$v' 
			and week_type='$week_type' and shift_day='$day' and a.specific_schedule=s.specific_schedule 
			and e.active = 'Active' and (e.employee_lastday >= '$v' or e.employee_lastday is null)";
		$result2 = mysqli_query($dbc, $query2);
		if($result2){
			while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)){
				$shift_start = $row2['shift_start'];
				$shift_start_minutes = $row2['shift_start_minutes'];
				$shift_end = $row2['shift_end'];
				$shift_end_minutes = $row2['shift_end_minutes'];
				$lunch_start = $row2['lunch_start'];
				$lunch_start_minutes = $row2['lunch_start_minutes'];
				$lunch_end = $row2['lunch_end'];
				$lunch_end_minutes = $row2['lunch_end_minutes'];
				if ($shift_start_minutes != '00') {
					$shift_start += dec_minutes($shift_start_minutes);
					}
				if ($shift_end_minutes != '00') {
					$shift_end += dec_minutes($shift_end_minutes);
					}
				if ($lunch_start_minutes != '00') {
					$lunch_start += dec_minutes($lunch_start_minutes);
					}
				if ($lunch_end_minutes != '00') {
					$lunch_end += dec_minutes($lunch_end_minutes);
					}
				if(($shift_end-$shift_start)>=8){
					$reg_hours += 8;
					}
				else{
					$shift = $shift_end-$shift_start;
					$reg_hours += $shift;
					$lunch = $lunch_end-$lunch_start;
					$reg_hours -= $lunch;
					}
					
				$query3 = "SELECT * from closures where closure_date='$v'";
				$result3 = mysqli_query($dbc, $query3);
				if(mysql_num_rows($result3)!=0){
					while($row3 = mysql_fetch_assoc($result3)){
						if(($row3['closure_start_time'] == '00:01:00')&&($row3['closure_end_time'] == '23:59:00')){
							$reg_hours = 0;
							}
						else{
							list($cs_hr, $cs_mn, $cs_sec) = explode(':',$row3['closure_start_time']);
							list($ce_hr, $ce_mn, $ce_sec) = explode(':',$row3['closure_end_time']);
							if ($cs_mn != '00') {
								$cs = $cs_hr - dec_minutes($cs_mn);
								}
							else{
								$cs = $cs_hr;
								}
							if ($ce_mn != '00') {
								$ce = $ce_hr + dec_minutes($ce_mn);
								}
							else{
								$ce = $ce_hr;
								}
							if (($shift_start > 0)&&($shift_end > 0)){
								if(($cs <= $shift_start)&&($ce >= $shift_end)){
									$reg_hours = 0;
									$closure = 0;
									}
								elseif((($shift_start >= $cs)&&($shift_start<=$ce))||(($shift_end >=$cs)&&($shift_end<=$ce))){
									if(($row3['closure_start_time'] == '00:01:00')||(($cs <= $shift_start)&&($ce <= $shift_end))){
										$closure = $ce-$shift_start;
										}
									elseif(($row3['closure_end_time'] == '23:59:00')||(($ce >= $shift_end)&&($cs >= $shift_start))){
										$closure = $shift_end-$cs;
										}
									else{
										$closure = $ce-$cs;
										}
									}
								else{
									$closure = 0;
									}
								$reg_hours -= $closure;
								}
							else{
								$reg_hours = 0;
								}
							}
						}
					}
				}
			}
		if ($emp_division == 'Subs'){
			$sub_query = "SELECT time_format(coverage_start_time,'%k') as cov_start, 
				time_format(coverage_start_time,'%i') as cov_start_minutes, time_format(coverage_end_time,'%k') as cov_end, 
				time_format(coverage_end_time,'%i') as cov_end_minutes from coverage 
				WHERE coverage_date = '$v' and emp_id = '$emp_id' ORDER BY coverage_start_time asc";
			$sub_result = mysqli_query($dbc, $sub_query);
			if ($sub_result){
				$num = mysql_num_rows($sub_result);
				if ($num>0) {
					while ($sub_row = mysql_fetch_array($sub_result, MYSQL_ASSOC)){
						$cov_start = $sub_row['cov_start'];
						$cov_start_minutes = $sub_row['cov_start_minutes'];
						$cov_end = $sub_row['cov_end'];
						$cov_end_minutes = $sub_row['cov_end_minutes'];
						if ($cov_start_minutes != '00') {
							$cov_start += dec_minutes($cov_start_minutes);
							}
						if ($cov_end_minutes != '00') {
							$cov_end += dec_minutes($cov_end_minutes);
							}
						$shift = $cov_end-$cov_start;
						$reg_hours += $shift;
						}
					}
				}
			}
		$new_entry[$v] = $reg_hours;
		}
	echo $reg_hours;
	echo '</td>';
	}
echo '<td class="total"></td></tr>';
echo '<tr><td class="hours_type">Floating Holiday</td>';
foreach ($array as $k=>$v){
	echo '<td class="disp_hours">';
	if((isset($previous[$v]))&&(isset($previous[$v]['28']))){echo $previous[$v]['28'];}
	echo '</td>';
	}
echo '<td class="total"></td></tr>';
echo '<tr class="shaded"><td class="hours_type">Medical Leave</td>';
foreach ($array as $k=>$v){
	echo '<td class="disp_hours">';
	if((isset($previous[$v]))&&(isset($previous[$v]['26']))){echo $previous[$v]['26'];}
	echo '</td>';
	}
echo '<td class="total"></td></tr>';
echo '<tr><td class="hours_type">Vacation</td>';
foreach ($array as $k=>$v){
	echo '<td class="disp_hours">';
	if((isset($previous[$v]))&&(isset($previous[$v]['24']))){echo $previous[$v]['24'];}
	echo '</td>';
	}
echo '<td class="total"></td></tr>';
echo '<tr class="shaded"><td class="hours_type">Bereavement</td>';
foreach ($array as $k=>$v){
	echo '<td class="disp_hours">';
	if((isset($previous[$v]))&&(isset($previous[$v]['48']))){echo $previous[$v]['48'];}
	echo '</td>';
	}
echo '<td class="total"></td></tr>';

if(isset($previous)){
	$selects = array();
	foreach ($previous as $prev_date=>$prev_codes){
		if (($prev_date >= $pp_start_date)&&($prev_date <= $pp_midweek_end_date)){
			foreach ($prev_codes as $code=>$hours){
				if (isset($other_hours[$code])){
					if(!in_array($code, $selects)){
						$selects[] = $code;
						}
					}
				}
			}
		}
	}

echo '<tr><td class="hours_type" style="width:185px;">Other: ';
if (count($selects)>0){
	$first_code = $selects[0];
	echo '<b>'.$other_hours[$first_code].'</b>';
	}
echo '</td>';
foreach ($array as $k=>$v){
	echo '<td class="disp_hours">';
	if ((count($selects)>0)&&(isset($previous[$v][$first_code]))){echo $previous[$v][$first_code];}
	echo '</td>';
	}
echo '<td class="total"></td></tr>';
echo '<tr class="shaded"><td class="hours_type" style="width:185px;">Other : ';
if (count($selects)>1){
	$second_code = $selects[1];
	echo '<b>'.$other_hours[$second_code].'</b>';
	}
echo '</td>';
foreach ($array as $k=>$v){
	echo '<td class="disp_hours">';
	if ((count($selects)>1)&&(isset($previous[$v][$second_code]))){echo $previous[$v][$second_code];}
	echo '</td>';
	}
echo '<td class="total"></td></tr>';

echo '<tr><td></td><td></td><td></td><td></td><td></td><td></td><td class="week_style label" colspan="2">Weekly Total</td><td class="week_style weekly_total"></td></tr>';
echo '</table>';

//Timesheet Week #2
dates_between_inclusive("$pp_midweek_start_date", "$pp_end_date");

echo '<table class="timetable top" cellspacing="0">'."\n";
echo '<tr class="timetable_date"><td></td>';
foreach ($array as $k=>$v){
	$short_date = date('j M', strtotime($v));
	$day = date('D', strtotime($v));
	echo '<td class="day">'.$short_date.'<br/>'.$day.'</td>';
	}
echo '<td class="day">Total</td></tr>';
echo '<tr class="scheduled"><td class="hours_type">Scheduled</td>';
foreach ($array as $k=>$v){
	echo '<td class="shift">';	
	$query = "SELECT date, week_type FROM dates where date = '$v'";
	$result = mysqli_query($dbc, $query);
	while ($row = mysql_fetch_assoc($result)) {
		$week_type = $row['week_type'];
		}

	$day = date('D', strtotime($v));
	
	$query2 = "SELECT time_format(shift_start,'%k') as shift_start, 
		time_format(shift_start,'%i') as shift_start_minutes, time_format(shift_end,'%k') as shift_end, 
		time_format(shift_end,'%i') as shift_end_minutes from employees as e, shifts as a, schedules as s 
		WHERE e.emp_id = '$emp_id' and e.emp_id = a.emp_id and 
		schedule_start_date <= '$v' and schedule_end_date >= '$v' 
		and week_type='$week_type' and shift_day='$day' and a.specific_schedule=s.specific_schedule 
		and e.active = 'Active' and (e.employee_lastday >= '$v' or e.employee_lastday is null)";
	$result2 = mysqli_query($dbc, $query2);
	if($result2){
		$shift_array = array();
		while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)){
			$shift_start = $row2['shift_start'];
			$shift_start_minutes = $row2['shift_start_minutes'];
			$shift_end = $row2['shift_end'];
			$shift_end_minutes = $row2['shift_end_minutes'];
			$shift_display = '';
			
			if ($shift_start > 12){
				$ss12 = $shift_start - 12;
				}
			elseif($shift_start == 0){
				$ss12 = NULL;
				}
			else{
				$ss12 = $shift_start;
				}
			if ($shift_start_minutes != '00') {
				$ss12 = $ss12.':'.$shift_start_minutes;
				}
		
			if ($shift_end > 12){
				$se12 = $shift_end - 12;
				}
			elseif($shift_end == 0){
				$se12 = NULL;
				}
			else{
				$se12 = $shift_end;
				}
			if ($shift_end_minutes != '00') {
				$se12 = $se12.':'.$shift_end_minutes;
				}
			if (isset($ss12)){
				$shift_display .= $ss12 . '-' . $se12;
				}
			if (!empty($shift_display)){
				$shift_array[] = $shift_display;
				}
			}
		if (count($shift_array)==1){
			echo $shift_array[0];
			}
		else{
			$sh_display = '';
			foreach ($shift_array as $item=>$shift){
				if ($item == 0){
					$sh_display .= $shift;
					}
				else{
					$sh_display .= '<br/>'.$shift;
					}
				}
			echo $sh_display;
			}
		}
	if ($emp_division == 'Subs'){
		$sub_query = "SELECT time_format(coverage_start_time,'%k') as cov_start, 
			time_format(coverage_start_time,'%i') as cov_start_minutes, time_format(coverage_end_time,'%k') as cov_end, 
			time_format(coverage_end_time,'%i') as cov_end_minutes from coverage c, employees e
			WHERE coverage_date = '$v' and c.emp_id = '$emp_id' and c.emp_id=e.emp_id 
			ORDER BY coverage_start_time asc";
		$sub_result = mysqli_query($dbc, $sub_query);
		if ($sub_result){
			$num = mysql_num_rows($sub_result);
			if ($num>0) {
				$sub_array = array();
				while($sub_row = mysql_fetch_array($sub_result, MYSQL_ASSOC)){
					$cov_start = $sub_row['cov_start'];
					$cov_start_minutes = $sub_row['cov_start_minutes'];
					$cov_end = $sub_row['cov_end'];
					$cov_end_minutes = $sub_row['cov_end_minutes'];
					
					if ($cov_start > 12){
						$cs12 = $cov_start - 12;
						}
					elseif($cov_start == 0){
						$cs12 = NULL;
						}
					else{
						$cs12 = $cov_start;
						}
					if ($cov_start_minutes != '00'){
						$cs12 = $cs12.':'.$cov_start_minutes;
						}
				
					if ($cov_end > 12){
						$ce12 = $cov_end - 12;
						}
					elseif($cov_end == 0){
						$ce12 = NULL;
						}
					else{
						$ce12 = $cov_end;
						}
					if ($cov_end_minutes != '00'){
						$ce12 = $ce12.':'.$cov_end_minutes;
						}
					
					$sub_array[] = array($cs12, $ce12);
					}
				if (count($sub_array)==1){
					echo $sub_array[0][0].'-'.$sub_array[0][1];
					}
				else{
					$cov_display = '';
					foreach ($sub_array as $item=>$covs){
						if ($item == 0){
							$cov_display .= $covs[0].'-'.$covs[1];
							}
						else{
							$cov_display .= '<br/>'.$covs[0].'-'.$covs[1];
							}
						}
					echo $cov_display;
					}
				}
			}
		}
	echo '</td>';
	}
echo '<td class="shift_total">';
if (isset($weekly_hours)){
	echo $weekly_hours;
	}
echo '</td></tr>';

echo '<tr class="specialshaded"><td class="hours_type"><b>Regular Hours Worked</b></td>';

foreach ($array as $k=>$v){
	echo '<td class="disp_hours">';
	$reg_hours = 0;
	
	if((count($previous)>0)||($confirmed==TRUE)){
		if((isset($previous[$v]))&&(isset($previous[$v]['02']))){
			$reg_hours = $previous[$v]['02'];
			}
		else{
			$reg_hours = 0;
			}
		}
	else{
		$query = "SELECT date, week_type FROM dates where date = '$v'";
		$result = mysqli_query($dbc, $query);
		while ($row = mysql_fetch_assoc($result)) {
			$week_type = $row['week_type'];
			}

		$day = date('D', strtotime($v));
		
		$query2 = "SELECT time_format(shift_start,'%k') as shift_start, 
			time_format(shift_start,'%i') as shift_start_minutes, time_format(shift_end,'%k') as shift_end, 
			time_format(shift_end,'%i') as shift_end_minutes, time_format(lunch_start,'%k') as lunch_start, 
			time_format(lunch_start,'%i') as lunch_start_minutes, time_format(lunch_end,'%k') as lunch_end, 
			time_format(lunch_end,'%i') as lunch_end_minutes FROM employees as e, shifts as a, schedules as s 
			WHERE e.emp_id = '$emp_id' and e.emp_id = a.emp_id and 
			schedule_start_date <= '$v' and schedule_end_date >= '$v' 
			and week_type='$week_type' and shift_day='$day' and a.specific_schedule=s.specific_schedule 
			and e.active = 'Active' and (e.employee_lastday >= '$v' or e.employee_lastday is null)";
		$result2 = mysqli_query($dbc, $query2);
		if($result2){
			while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)){
				$shift_start = $row2['shift_start'];
				$shift_start_minutes = $row2['shift_start_minutes'];
				$shift_end = $row2['shift_end'];
				$shift_end_minutes = $row2['shift_end_minutes'];
				$lunch_start = $row2['lunch_start'];
				$lunch_start_minutes = $row2['lunch_start_minutes'];
				$lunch_end = $row2['lunch_end'];
				$lunch_end_minutes = $row2['lunch_end_minutes'];
				if ($shift_start_minutes != '00') {
					$shift_start += dec_minutes($shift_start_minutes);
					}
				if ($shift_end_minutes != '00') {
					$shift_end += dec_minutes($shift_end_minutes);
					}
				if ($lunch_start_minutes != '00') {
					$lunch_start += dec_minutes($lunch_start_minutes);
					}
				if ($lunch_end_minutes != '00') {
					$lunch_end += dec_minutes($lunch_end_minutes);
					}
				if(($shift_end-$shift_start)>=8){
					$reg_hours += 8;
					}
				else{
					$shift = $shift_end-$shift_start;
					$reg_hours += $shift;
					$lunch = $lunch_end-$lunch_start;
					$reg_hours -= $lunch;
					}
					
				$query3 = "SELECT * from closures where closure_date='$v'";
				$result3 = mysqli_query($dbc, $query3);
				if(mysql_num_rows($result3)!=0){
					while($row3 = mysql_fetch_assoc($result3)){
						if(($row3['closure_start_time'] == '00:01:00')&&($row3['closure_end_time'] == '23:59:00')){
							$reg_hours = 0;
							}
						else{
							list($cs_hr, $cs_mn, $cs_sec) = explode(':',$row3['closure_start_time']);
							list($ce_hr, $ce_mn, $ce_sec) = explode(':',$row3['closure_end_time']);
							if ($cs_mn != '00') {
								$cs = $cs_hr - dec_minutes($cs_mn);
								}
							else{
								$cs = $cs_hr;
								}
							if ($ce_mn != '00') {
								$ce = $ce_hr + dec_minutes($ce_mn);
								}
							else{
								$ce = $ce_hr;
								}
							
							if (($shift_start > 0)&&($shift_end > 0)){
								if(($cs <= $shift_start)&&($ce >= $shift_end)){
									$reg_hours = 0;
									$closure = 0;
									}
								elseif((($shift_start >= $cs)&&($shift_start<=$ce))||(($shift_end >=$cs)&&($shift_end<=$ce))){
									if(($row3['closure_start_time'] == '00:01:00')||(($cs <= $shift_start)&&($ce <= $shift_end))){
										$closure = $ce-$shift_start;
										}
									elseif(($row3['closure_end_time'] == '23:59:00')||(($ce >= $shift_end)&&($cs >= $shift_start))){
										$closure = $shift_end-$cs;
										}
									else{
										$closure = $ce-$cs;
										}
									}
								else{
									$closure = 0;
									}
								$reg_hours -= $closure;
								}
							else{
								$reg_hours = 0;
								}
							}
						}
					}
				}
			}
		if ($emp_division == 'Subs'){
			$sub_query = "SELECT time_format(coverage_start_time,'%k') as cov_start, 
				time_format(coverage_start_time,'%i') as cov_start_minutes, time_format(coverage_end_time,'%k') as cov_end, 
				time_format(coverage_end_time,'%i') as cov_end_minutes from coverage 
				WHERE coverage_date = '$v' and emp_id = '$emp_id' ORDER BY coverage_start_time asc";
			$sub_result = mysqli_query($dbc, $sub_query);
			if ($sub_result){
				$num = mysql_num_rows($sub_result);
				if ($num>0) {
					while ($sub_row = mysql_fetch_array($sub_result, MYSQL_ASSOC)){
						$cov_start = $sub_row['cov_start'];
						$cov_start_minutes = $sub_row['cov_start_minutes'];
						$cov_end = $sub_row['cov_end'];
						$cov_end_minutes = $sub_row['cov_end_minutes'];
						if ($cov_start_minutes != '00') {
							$cov_start += dec_minutes($cov_start_minutes);
							}
						if ($cov_end_minutes != '00') {
							$cov_end += dec_minutes($cov_end_minutes);
							}
						$shift = $cov_end-$cov_start;
						$reg_hours += $shift;
						}
					}
				}
			}
		$new_entry[$v] = $reg_hours;
		}
	echo $reg_hours;
	echo '</td>';
	}
echo '<td class="total"></td></tr>';
echo '<tr><td class="hours_type">Floating Holiday</td>';
foreach ($array as $k=>$v){
	echo '<td class="disp_hours">';
	if((isset($previous[$v]))&&(isset($previous[$v]['28']))){echo $previous[$v]['28'];}
	echo '</td>';
	}
echo '<td class="total"></td></tr>';
echo '<tr class="shaded"><td class="hours_type">Medical Leave</td>';
foreach ($array as $k=>$v){
	echo '<td class="disp_hours">';
	if((isset($previous[$v]))&&(isset($previous[$v]['26']))){echo $previous[$v]['26'];}
	echo '</td>';
	}
echo '<td class="total"></td></tr>';
echo '<tr><td class="hours_type">Vacation</td>';
foreach ($array as $k=>$v){
	echo '<td class="disp_hours">';
	if((isset($previous[$v]))&&(isset($previous[$v]['24']))){echo $previous[$v]['24'];}
	echo '</td>';
	}
echo '<td class="total"></td></tr>';
echo '<tr class="shaded"><td class="hours_type">Bereavement</td>';
foreach ($array as $k=>$v){
	echo '<td class="disp_hours">';
	if((isset($previous[$v]))&&(isset($previous[$v]['48']))){echo $previous[$v]['48'];}
	echo '</td>';
	}
echo '<td class="total"></td></tr>';

if(isset($previous)){
	$selects = array();
	foreach ($previous as $prev_date=>$prev_codes){
		if (($prev_date >= $pp_midweek_start_date)&&($prev_date <= $pp_end_date)){
			foreach ($prev_codes as $code=>$hours){
				if (isset($other_hours[$code])){
					if(!in_array($code, $selects)){
						$selects[] = $code;
						}
					}
				}
			}
		}
	}

echo '<tr><td class="hours_type" style="width:185px;">Other: ';
if (count($selects)>0){
	$first_code = $selects[0];
	echo '<b>'.$other_hours[$first_code].'</b>';
	}
echo '</td>';
foreach ($array as $k=>$v){
	echo '<td class="disp_hours">';
	if ((count($selects)>0)&&(isset($previous[$v][$first_code]))){echo $previous[$v][$first_code];}
	echo '</td>';
	}
echo '<td class="total"></td></tr>';
echo '<tr class="shaded"><td class="hours_type" style="width:185px;">Other : ';
if (count($selects)>1){
	$second_code = $selects[1];
	echo '<b>'.$other_hours[$second_code].'</b>';
	}
echo '</td>';
foreach ($array as $k=>$v){
	echo '<td class="disp_hours">';
	if ((count($selects)>1)&&(isset($previous[$v][$second_code]))){echo $previous[$v][$second_code];}
	echo '</td>';
	}
echo '<td class="total"></td></tr>';

echo '<tr><td></td><td></td><td></td><td></td><td></td><td></td><td class="week_style label" colspan="2">Weekly Total</td><td class="week_style weekly_total"></td></tr>';
echo '</table>';	
	
echo '<form action="edit_emp_timesheet" method="post" style="float:left;margin-right:10px;">
	<input type="hidden" name="pp_id" value="'.$pp_id.'"/>
	<input type="hidden" name="pp_start_date" value="'.$pp_start_date.'"/>
	<input type="hidden" name="emp_id" value="'.$emp_id.'"/>
	<input type="hidden" name="assignment_id" value="'.$assignment_id.'"/>
	<input type="hidden" name="employee_name" value="'.$employee_name.'"/>
	<input type="submit" name="submit" value="Edit" /></form>';
echo '<form action="view_emp_timesheet" method="post">
	<input type="hidden" name="pp_id" value="'.$pp_id.'"/>
	<input type="hidden" name="pp_start_date" value="'.$pp_start_date.'"/>
	<input type="hidden" name="emp_id" value="'.$emp_id.'"/>
	<input type="hidden" name="assignment_id" value="'.$assignment_id.'"/>
	<input type="hidden" name="employee_name" value="'.$employee_name.'"/>';
if (isset($new_entry)){
	foreach($new_entry as $key=>$value){
		echo '<input type="hidden" name="new_entry['.$key.']" value="'. $value. '">';
		}
	}
echo '<input type="hidden" name="approved" value="TRUE"/>
	<input type="submit" name="submit" value="Approve Timesheet" /></form>';
	
echo '</div>';
include ('./includes/footer.html');
?>