<?php # Schedule_days.php

$page_title="Add/Edit Schedule";
include('./includes/supersessionstart.php');
$came_from = $_SESSION['came_from'];
include('./includes/allsessionvariables.php');
if ((!isset($_POST['schedstart_datepick']))&&(!isset($_POST['specific_schedule']))){
	header ('Location: add_schedule');
	}
	
if (isset($_POST['division'])){
	$division = $_POST['division'];
	}
else {
	$division = 'Admin';
	}
if (isset($_POST['specific_schedule'])){
	$specific_schedule = $_POST['specific_schedule'];
	}
if (isset($_POST['schedstart'])){
	$schedstart = $_POST['schedstart'];
	}
if (isset($_POST['schedend'])){
	$schedend = $_POST['schedend'];
	}
	
$week_types = array('a','b','c','d');

$daysofweek = array('sat','sun','mon','tue','wed','thu','fri');

$employees = array();
$employee_query = "SELECT emp_id, first_name, last_name FROM employees where (division like '%".$division."%') and active = 'Active' 
	order by exempt_status asc, weekly_hours desc, first_name asc";
$employee_result = mysql_query($employee_query);

while ($row = mysql_fetch_array ($employee_result, MYSQL_ASSOC)) {
	$employees[] = array('emp_id'=>$row['emp_id'],'first_name'=>$row['first_name'],'last_name'=>$row['last_name']);
	}
	
if(isset($_POST['init'])){
	$division = $_POST['division'];
	list($ss_mon, $ss_day, $ss_yr) = explode('/',$_POST['schedstart_datepick']);
	$schedstart = "$ss_yr-$ss_mon-$ss_day";
	list($se_mon, $se_day, $se_yr) = explode('/',$_POST['schedend_datepick']);
	$schedend = "$se_yr-$se_mon-$se_day";
	
	//Check for previous schedule overlaps
	$query = "SELECT * from schedules WHERE division='$division' and (schedule_start_date >= '$schedstart') 
		and (schedule_end_date <= '$schedend')";
	$result = mysql_query($query);
	$num_rows = mysql_num_rows($result);
	if ($num_rows != 0){
		while ($row = mysql_fetch_array ($result, MYSQL_ASSOC)) {
			$schedule_id = $row['schedule_id'];
			}
		$query = "DELETE from schedules WHERE schedule_id='$schedule_id'";
		$result = mysql_query($query);
		}
	$query = "SELECT * from schedules WHERE division='$division' and 
		(schedule_start_date < '$schedstart') and (schedule_end_date > '$schedend')";
	$result = mysql_query($query);
	$num_rows = mysql_num_rows($result);
	if ($num_rows != 0){
		while ($row = mysql_fetch_array ($result, MYSQL_ASSOC)) {
			$schedule_id = $row['schedule_id'];
			$oldschedstart = $row['schedule_start_date'];
			$oldschedend = $row['schedule_end_date'];
			$specific_schedule = $row['specific_schedule'];
			}
		$newstart = date('Y-m-d', strtotime($schedend.'+1days'));
		$newend = date('Y-m-d', strtotime($schedstart.'-1days'));
		$query1 = "UPDATE schedules set schedule_end_date='$newend' WHERE schedule_id='$schedule_id'";
		$result1 = mysql_query($query1);
		$query2 = "INSERT into schedules (division, schedule_start_date, schedule_end_date, specific_schedule) 
			values ('$division', '$newstart', '$oldschedend', '$specific_schedule')";
		$result2 = mysql_query($query2);
		}
	$query = "SELECT * from schedules WHERE division='$division' and (schedule_start_date >= '$schedstart') 
		and (schedule_start_date < '$schedend') and (schedule_end_date > '$schedend')";
	$result = mysql_query($query);
	$num_rows = mysql_num_rows($result);
	if ($num_rows != 0){
		while ($row = mysql_fetch_array ($result, MYSQL_ASSOC)) {
			$schedule_id = $row['schedule_id'];
			}
		$newstart = date('Y-m-d', strtotime($schedend.'+1days'));
		$query1 = "UPDATE schedules set schedule_start_date='$newstart' WHERE schedule_id='$schedule_id'";
		$result1 = mysql_query($query1);
		}
	$query = "SELECT * from schedules WHERE division='$division' and (schedule_end_date <= '$schedend') and 
		(schedule_end_date > '$schedstart') and (schedule_start_date < '$schedstart')";
	$result = mysql_query($query);
	$num_rows = mysql_num_rows($result);
	if ($num_rows != 0){
		while ($row = mysql_fetch_array ($result, MYSQL_ASSOC)) {
			$schedule_id = $row['schedule_id'];
			}
		$newend = date('Y-m-d', strtotime($schedstart.'-1days'));
		$query1 = "UPDATE schedules set schedule_end_date='$newend' WHERE schedule_id='$schedule_id'";
		$result1 = mysql_query($query1);
		}
	
	$max = 0;
	$query = "SELECT MAX(specific_schedule) FROM schedules";
	$result = mysql_query($query);
	while ($row = mysql_fetch_array ($result, MYSQL_NUM)) {
		$max = $row[0];
		}
	$max += 1;
	
	$query = "INSERT into schedules (division, schedule_start_date, schedule_end_date, specific_schedule) 
		values ('$division', '$schedstart', '$schedend', '$max')";
	$result = mysql_query($query);
	$specific_schedule = $max;
	}
	
if(isset($_POST['separate'])){
	$schedule_id = $_POST['schedule_id'];
	$max = 0;
	$query = "SELECT MAX(specific_schedule) FROM schedules";
	$result = mysql_query($query);
	while ($row = mysql_fetch_array ($result, MYSQL_NUM)) {
		$max = $row[0];
		}
	$max += 1;
	
	$query = "UPDATE schedules SET specific_schedule='$max' WHERE schedule_id='$schedule_id'";
	$result = mysql_query($query);
	
	$separate_query2 = "INSERT into shifts (week_type, shift_day, emp_id, shift_start, shift_end, 
		desk_start, desk_end, desk_start2, desk_end2, lunch_start, lunch_end, specific_schedule) 
		SELECT week_type, shift_day, emp_id, shift_start, shift_end, desk_start, desk_end, 
		desk_start2, desk_end2, lunch_start, lunch_end, '$max' from shifts WHERE specific_schedule='$specific_schedule'";
	$separate_result2 = mysql_query($separate_query2);
	$specific_schedule = $max;
	}

//All date ranges
$today= date('Y-m-d');
$additional_dates = '';
$date_query = "SELECT * from schedules WHERE specific_schedule='$specific_schedule' and schedule_end_date >= '$today' 
	and schedule_start_date != '$schedstart' ORDER BY schedule_end_date asc";
$date_result = mysql_query($date_query);
if ($date_result){
	$num_rows = mysql_num_rows($date_result);
	if ($num_rows != 0) {
		while ($row = mysql_fetch_array ($date_result, MYSQL_ASSOC)) {
			$start = $row['schedule_start_date'];
			$end = $row['schedule_end_date'];
			$start = date('n', strtotime($start)).'/'.date('j', strtotime($start)).'/'.date('Y', strtotime($start));
			$end = date('n', strtotime($end)).'/'.date('j', strtotime($end)).'/'.date('Y', strtotime($end));
			$additional_dates .= "; and $start to $end";
			}
		}
	}
	
//Check for previous schedules
$prev_schedules = array();
$emp_id_arr = array();
foreach ($employees as $key=>$employeearray){
	$emp_id = $employeearray['emp_id'];
	$emp_id_arr[] = $emp_id;
	$prev_schedules[$emp_id] = array();
	foreach ($week_types as $key=>$value){
		foreach ($daysofweek as $key2=>$value2){
			$prev_schedules[$emp_id][$value] = array();
			}
		}
		
	$query = "SELECT * from shifts where specific_schedule='$specific_schedule'and emp_id='$emp_id'";
	$result = mysql_query($query);
	if ($result){
		while ($row = mysql_fetch_array ($result, MYSQL_ASSOC)) {
			$wt = $row['week_type'];
			$d = $row['shift_day'];
			$ss = explode(":",$row['shift_start']);
			$se = explode(":",$row['shift_end']);
			$ds = explode(":",$row['desk_start']);
			$de = explode(":",$row['desk_end']);
			$ds2 = explode(":",$row['desk_start2']);
			$de2 = explode(":",$row['desk_end2']);
			$ls = explode(":",$row['lunch_start']);
			$le = explode(":",$row['lunch_end']);
			
			$ss_hr = (integer)$ss[0];
				if ($ss_hr > 12){
					$ss_hr = $ss_hr-12;
					}
			$ss_mn = $ss[1];
			$prev_schedules[$emp_id][$wt][$d]['shift_start'] = array('hours'=>$ss_hr, 'minutes'=>$ss_mn);
			
			$se_hr = (integer)$se[0];
				if ($se_hr > 12){
					$se_hr = $se_hr-12;
					}
			$se_mn = $se[1];
			$prev_schedules[$emp_id][$wt][$d]['shift_end'] = array('hours'=>$se_hr, 'minutes'=>$se_mn);
			
			$ds_hr = (integer)$ds[0];
				if ($ds_hr > 12){
					$ds_hr = $ds_hr-12;
					}
			$ds_mn = $ds[1];
			$prev_schedules[$emp_id][$wt][$d]['desk_start'] = array('hours'=>$ds_hr, 'minutes'=>$ds_mn);
			
			$de_hr = (integer)$de[0];
				if ($de_hr > 12){
					$de_hr = $de_hr-12;
					}
			$de_mn = $de[1];
			$prev_schedules[$emp_id][$wt][$d]['desk_end'] = array('hours'=>$de_hr, 'minutes'=>$de_mn);
			
			$ds2_hr = (integer)$ds2[0];
				if ($ds2_hr > 12){
					$ds2_hr = $ds2_hr-12;
					}
			$ds2_mn = $ds2[1];
			$prev_schedules[$emp_id][$wt][$d]['desk_start2'] = array('hours'=>$ds2_hr, 'minutes'=>$ds2_mn);
			
			$de2_hr = (integer)$de2[0];
				if ($de2_hr > 12){
					$de2_hr = $de2_hr-12;
					}
			$de2_mn = $de2[1];
			$prev_schedules[$emp_id][$wt][$d]['desk_end2'] = array('hours'=>$de2_hr, 'minutes'=>$de2_mn);

			$ls_hr = (integer)$ls[0];
				if ($ls_hr > 12){
					$ls_hr = $ls_hr-12;
					}
			$ls_mn = $ls[1];
			$prev_schedules[$emp_id][$wt][$d]['lunch_start'] = array('hours'=>$ls_hr, 'minutes'=>$ls_mn);
			
			$le_hr = (integer)$le[0];
				if ($le_hr > 12){
					$le_hr = $le_hr-12;
					}
			$le_mn = $le[1];
			$prev_schedules[$emp_id][$wt][$d]['lunch_end'] = array('hours'=>$le_hr, 'minutes'=>$le_mn);
			}
		}
	}
	
//Check for previous deficiency schedules
$prev_def = array();
$query = "SELECT * from deficiencies WHERE def_division='$division' and def_schedule='$specific_schedule' 
	ORDER BY def_week asc, def_day asc, def_start asc";
$result = mysql_query($query);
if ($result){
	while ($row = mysql_fetch_array ($result, MYSQL_ASSOC)) {
		$def_id = $row['def_id'];
		$wt = $row['def_week'];
		$d = $row['def_day'];
		$def_s = explode(":",$row['def_start']);
		$def_e = explode(":",$row['def_end']);
		$def_s_hr = (integer)$def_s[0];
			if ($def_s_hr > 12){
				$def_s_hr = $def_s_hr-12;
				}
		$def_s_mn = $def_s[1];
		$prev_def[$wt][$d][$def_id]['def_start'] = array('hours'=>$def_s_hr, 'minutes'=>$def_s_mn);
		
		$def_e_hr = (integer)$def_e[0];
			if ($def_e_hr > 12){
				$def_e_hr = $def_e_hr-12;
				}
		$def_e_mn = $def_e[1];
		$prev_def[$wt][$d][$def_id]['def_end'] = array('hours'=>$def_e_hr, 'minutes'=>$def_e_mn);
		}
	}

if(isset($_POST['day_submit'])){
	$schedule_array = $_POST['schedule'];
	$def_array = $_POST['def'];
	
	$emp_id_str = implode(",",$emp_id_arr);
	$deleteold_query = "DELETE from shifts WHERE emp_id not in ($emp_id_str) and specific_schedule='$specific_schedule'";
	$deleteold_result = mysql_query($deleteold_query) or die(mysql_error());
	
	foreach ($schedule_array as $emp_id=>$weekarray){
		foreach ($weekarray as $week_type=>$dayarray){
			foreach ($dayarray as $day=>$sched){
				$ss_hr = $sched['shift_start']['hours'];
					if (!empty($ss_hr) && ($ss_hr < 7)) {$ss_hr = $ss_hr+12;}
					if (empty($ss_hr)){$ss_hr = "00";}
				$ss_mn = $sched['shift_start']['minutes'];
					if (empty($ss_mn)){$ss_mn = "00";}
				$ss = "$ss_hr:$ss_mn:00";
				if (!empty($ss_hr)){
					$schedule_array[$emp_id][$week_type][$day]['shift_start']['minutes'] = $ss_mn;
					}
				
				$se_hr = $sched['shift_end']['hours'];
					if (!empty($se_hr) && ($se_hr <= $ss_hr)) {$se_hr = $se_hr+12;}
					elseif (!empty($se_hr) && ($se_hr >= $ss_hr) && ($se_hr < 9)) {$se_hr = $se_hr+12;}
					if (empty($se_hr)){$se_hr = "00";}
				$se_mn = $sched['shift_end']['minutes'];
					if (empty($se_mn)){$se_mn = "00";}
				$se = "$se_hr:$se_mn:00";
				if (!empty($se_hr)){
					$schedule_array[$emp_id][$week_type][$day]['shift_end']['minutes'] = $se_mn;
					}
				
				if (($ss_hr=='00')&&($se_hr=='00')){
					$ds = "00:00:00";
					$de = "00:00:00";
					$ds2 = "00:00:00";
					$de2 = "00:00:00";
					$ls = "00:00:00";
					$le = "00:00:00";
					
					$schedule_array[$emp_id][$week_type][$day]['desk_start']['hours'] = '';
					$schedule_array[$emp_id][$week_type][$day]['desk_start']['minutes'] = '';
					$schedule_array[$emp_id][$week_type][$day]['desk_end']['minutes'] = '';
					$schedule_array[$emp_id][$week_type][$day]['desk_end']['minutes'] = '';					
					$schedule_array[$emp_id][$week_type][$day]['desk_start2']['hours'] = '';
					$schedule_array[$emp_id][$week_type][$day]['desk_start2']['minutes'] = '';
					$schedule_array[$emp_id][$week_type][$day]['desk_end2']['minutes'] = '';
					$schedule_array[$emp_id][$week_type][$day]['desk_end2']['minutes'] = '';
					$schedule_array[$emp_id][$week_type][$day]['lunch_start']['hours'] = '';
					$schedule_array[$emp_id][$week_type][$day]['lunch_start']['minutes'] = '';
					$schedule_array[$emp_id][$week_type][$day]['lunch_end']['hours'] = '';
					$schedule_array[$emp_id][$week_type][$day]['lunch_end']['minutes'] = '';
					}
				else{
					$ds_hr = $sched['desk_start']['hours'];
						if (!empty($ds_hr) && ($ds_hr < 8)) {$ds_hr = $ds_hr+12;}
						if (empty($ds_hr)){$ds_hr = "00";}
					$ds_mn = $sched['desk_start']['minutes'];
						if (empty($ds_mn)){$ds_mn = "00";}
					$ds = "$ds_hr:$ds_mn:00";
					if (!empty($ds_hr)){
						$schedule_array[$emp_id][$week_type][$day]['desk_start']['minutes'] = $ds_mn;
						}
					
					$de_hr = $sched['desk_end']['hours'];
						if (!empty($de_hr) && ($de_hr < $ds_hr)) {$de_hr = $de_hr+12;}
						if (empty($de_hr)){$de_hr = "00";}
					$de_mn = $sched['desk_end']['minutes'];
						if (empty($de_mn)){$de_mn = "00";}
					$de = "$de_hr:$de_mn:00";
					if (!empty($de_hr)){
						$schedule_array[$emp_id][$week_type][$day]['desk_end']['minutes'] = $de_mn;
						}
					
					$ds2_hr = $sched['desk_start2']['hours'];
						if (!empty($ds2_hr) && ($ds2_hr < 8)) {$ds2_hr = $ds2_hr+12;}
						if (empty($ds2_hr)){$ds2_hr = "00";}
					$ds2_mn = $sched['desk_start2']['minutes'];
						if (empty($ds2_mn)){$ds2_mn = "00";}
					$ds2 = "$ds2_hr:$ds2_mn:00";
					if (!empty($ds2_hr)){
						$schedule_array[$emp_id][$week_type][$day]['desk_start2']['minutes'] = $ds2_mn;
						}
					
					$de2_hr = $sched['desk_end2']['hours'];
						if (!empty($de2_hr) && ($de2_hr < $ds_hr)) {$de2_hr = $de2_hr+12;}
						if (empty($de2_hr)){$de2_hr = "00";}
					$de2_mn = $sched['desk_end2']['minutes'];
						if (empty($de2_mn)){$de2_mn = "00";}
					$de2 = "$de2_hr:$de2_mn:00";
					if (!empty($de2_hr)){
						$schedule_array[$emp_id][$week_type][$day]['desk_end2']['minutes'] = $de2_mn;
						}
					
					$ls_hr = $sched['lunch_start']['hours'];
						if (!empty($ls_hr) && ($ls_hr < 7)) {$ls_hr = $ls_hr+12;}
						if (empty($ls_hr)){$ls_hr = "00";}
					$ls_mn = $sched['lunch_start']['minutes'];
						if (empty($ls_mn)){$ls_mn = "00";}
					$ls = "$ls_hr:$ls_mn:00";
					if (!empty($ls_hr)){
						$schedule_array[$emp_id][$week_type][$day]['lunch_start']['minutes'] = $ls_mn;
						}
					
					$le_hr = $sched['lunch_end']['hours'];
						if (!empty($le_hr) && ($le_hr < $ls_hr)) {$le_hr = $le_hr+12;}
						if (empty($le_hr)){$le_hr = "00";}
					$le_mn = $sched['lunch_end']['minutes'];
						if (empty($le_mn)){$le_mn = "00";}
					$le = "$le_hr:$le_mn:00";
					if (!empty($le_hr)){
						$schedule_array[$emp_id][$week_type][$day]['lunch_end']['minutes'] = $le_mn;
						}
					}
			
				if(array_key_exists($day, $prev_schedules[$emp_id][$week_type])){
					$update_query = "UPDATE shifts SET shift_start='$ss', shift_end='$se', desk_start='$ds', desk_end='$de', 
						desk_start2='$ds2', desk_end2='$de2',lunch_start='$ls', lunch_end='$le'
						WHERE week_type='$week_type' and shift_day='$day' and specific_schedule='$specific_schedule' and emp_id='$emp_id'";
					$update_result = mysql_query($update_query) or die(mysql_error());
					}
				else{
					$insert_query = "INSERT into shifts (week_type, shift_day, emp_id, shift_start, shift_end, desk_start, desk_end, 
						desk_start2, desk_end2, lunch_start, lunch_end, specific_schedule) 
						values ('$week_type','$day','$emp_id','$ss', '$se', '$ds', '$de', '$ds2', '$de2', '$ls', '$le', '$specific_schedule')";
					$insert_result = mysql_query($insert_query) or die(mysql_error());
					}
					
				$prev_schedules[$emp_id][$week_type][$day] = $schedule_array[$emp_id][$week_type][$day];
				}
			}
		}
	
	//Enter deficiency data, ITIS.
	foreach ($def_array as $week_type => $weekarray){
		foreach ($weekarray as $day => $dayarray){
			$defs_hr = $dayarray['def_start']['hours'];
				if (!empty($defs_hr) && ($defs_hr < 7)) {$defs_hr = $defs_hr+12;}
				if (empty($defs_hr)){$defs_hr = "00";}
			$defs_mn = $dayarray['def_start']['minutes'];
				if (empty($defs_mn)){$defs_mn = "00";}
			$defs = "$defs_hr:$defs_mn:00";
			if (!empty($defs_hr)){
				$dayarray['def_start']['minutes'] = $defs_mn;
				}
				
			$defe_hr = $dayarray['def_end']['hours'];
				if (!empty($defe_hr) && ($defe_hr < 7)) {$defe_hr = $defe_hr+12;}
				if (!empty($defe_hr) && ($defe_hr < $defs_hr)) {$defe_hr = $defe_hr+12;}
				if (empty($defe_hr)){$defe_hr = "00";}
			$defe_mn = $dayarray['def_end']['minutes'];
				if (empty($defe_mn)){$defe_mn = "00";}
			$defe = "$defe_hr:$defe_mn:00";
			if (!empty($defe_hr)){
				$dayarray['def_end']['minutes'] = $defe_mn;
				}
			
			$defs_hr2 = $dayarray['def_start2']['hours'];
				if (!empty($defs_hr2) && ($defs_hr2 < 7)) {$defs_hr2 = $defs_hr2+12;}
				if (empty($defs_hr2)){$defs_hr2 = "00";}
			$defs_mn2 = $dayarray['def_start2']['minutes'];
				if (empty($defs_mn2)){$defs_mn2 = "00";}
			$defs2 = "$defs_hr2:$defs_mn2:00";
			if (!empty($defs_hr2)){
				$dayarray['def_start2']['minutes'] = $defs_mn2;
				}
				
			$defe_hr2 = $dayarray['def_end2']['hours'];
				if (!empty($defe_hr2) && ($defe_hr2 < 7)) {$defe_hr2 = $defe_hr2+12;}
				if (!empty($defe_hr2) && ($defe_hr2 < $defs_hr2)) {$defe_hr2 = $defe_hr2+12;}
				if (empty($defe_hr2)){$defe_hr2 = "00";}
			$defe_mn2 = $dayarray['def_end2']['minutes'];
				if (empty($defe_mn2)){$defe_mn2 = "00";}
			$defe2 = "$defe_hr2:$defe_mn2:00";
			if (!empty($defe_hr2)){
				$dayarray['def_end2']['minutes'] = $defe_mn2;
				}
			
			if (!isset($prev_def[$week_type][$day])||(count($prev_def[$week_type][$day]) == 0)){
				if (($defs != '00:00:00') && ($defe != '00:00:00')){
					$def_query = "INSERT into deficiencies(def_schedule, def_week, def_day, def_division, def_start, def_end) values
						('$specific_schedule','$week_type','$day','$division','$defs','$defe')";
					$def_result = mysql_query($def_query) or die(mysql_error($dbc));
					$id = mysql_insert_id();
					if ($defs_hr > 12){$defs_hr = $defs_hr-12;}
					$prev_def[$week_type][$day][$id]['def_start']['hours'] = $defs_hr;
					$prev_def[$week_type][$day][$id]['def_start']['minutes'] = $defs_mn;
					if ($defe_hr > 12){$defe_hr = $defe_hr-12;}
					$prev_def[$week_type][$day][$id]['def_end']['hours'] = $defe_hr;
					$prev_def[$week_type][$day][$id]['def_end']['minutes'] = $defe_mn;
					}
				if (($defs2 != '00:00:00') && ($defe2 != '00:00:00')){
					$def_query = "INSERT into deficiencies(def_schedule, def_week, def_day, def_division, def_start, def_end) values
						('$specific_schedule','$week_type','$day','$division','$defs2','$defe2')";
					$def_result = mysql_query($def_query) or die(mysql_error($dbc));
					$id2 = mysql_insert_id();
					if ($defs_hr2 > 12){$defs_hr2 = $defs_hr2-12;}
					$prev_def[$week_type][$day][$id2]['def_start']['hours'] = $defs_hr2;
					$prev_def[$week_type][$day][$id2]['def_start']['minutes'] = $defs_mn2;
					if ($defe_hr2 > 12){$defe_hr2 = $defe_hr2-12;}
					$prev_def[$week_type][$day][$id2]['def_end']['hours'] = $defe_hr2;
					$prev_def[$week_type][$day][$id2]['def_end']['minutes'] = $defe_mn2;
					}
				}
			elseif(count($prev_def[$week_type][$day]) == 1){
				$id = key($prev_def[$week_type][$day]);
				if (($defs != '00:00:00') && ($defe != '00:00:00')){
					$def_query = "UPDATE deficiencies set def_start='$defs', def_end='$defe' WHERE def_id='$id'";
					$def_result = mysql_query($def_query) or die(mysql_error($dbc));
					if ($defs_hr > 12){$defs_hr = $defs_hr-12;}
					$prev_def[$week_type][$day][$id]['def_start']['hours'] = $defs_hr;
					$prev_def[$week_type][$day][$id]['def_start']['minutes'] = $defs_mn;
					if ($defe_hr > 12){$defe_hr = $defe_hr-12;}
					$prev_def[$week_type][$day][$id]['def_end']['hours'] = $defe_hr;
					$prev_def[$week_type][$day][$id]['def_end']['minutes'] = $defe_mn;
					}
				else{
					$def_query = "DELETE from deficiencies WHERE def_id='$id'";
					$def_result = mysql_query($def_query) or die(mysql_error($dbc));
					unset($prev_def[$week_type][$day][$id]);
					}
				if (($defs2 != '00:00:00') && ($defe2 != '00:00:00')){
					$def_query = "INSERT into deficiencies(def_schedule, def_week, def_day, def_division, def_start, def_end) values
						('$specific_schedule','$week_type','$day','$division','$defs2','$defe2')";
					$def_result = mysql_query($def_query) or die(mysql_error($dbc));
					$id2 = mysql_insert_id();
					if ($defs_hr2 > 12){$defs_hr2 = $defs_hr2-12;}
					$prev_def[$week_type][$day][$id2]['def_start']['hours'] = $defs_hr2;
					$prev_def[$week_type][$day][$id2]['def_start']['minutes'] = $defs_mn2;
					if ($defe_hr2 > 12){$defe_hr2 = $defe_hr2-12;}
					$prev_def[$week_type][$day][$id2]['def_end']['hours'] = $defe_hr2;
					$prev_def[$week_type][$day][$id2]['def_end']['minutes'] = $defe_mn2;
					}
				}
			else{
				while ($row = current($prev_def[$week_type][$day])){
					$keys[] = key($prev_def[$week_type][$day]);
					next($prev_def[$week_type][$day]);
					}
				if (($defs != '00:00:00') && ($defe != '00:00:00')){
					$def_query = "UPDATE deficiencies set def_start='$defs', def_end='$defe' WHERE def_id='$keys[0]'";
					$def_result = mysql_query($def_query) or die(mysql_error($dbc));
					if ($defs_hr > 12){$defs_hr = $defs_hr-12;}
					$prev_def[$week_type][$day][$keys[0]]['def_start']['hours'] = $defs_hr;
					$prev_def[$week_type][$day][$keys[0]]['def_start']['minutes'] = $defs_mn;
					if ($defe_hr > 12){$defe_hr = $defe_hr-12;}
					$prev_def[$week_type][$day][$keys[0]]['def_end']['hours'] = $defe_hr;
					$prev_def[$week_type][$day][$keys[0]]['def_end']['minutes'] = $defe_mn;
					}
				else{
					$def_query = "DELETE from deficiencies WHERE def_id='$keys[0]'";
					$def_result = mysql_query($def_query) or die(mysql_error($dbc));
					unset($prev_def[$week_type][$day][$keys[0]]);
					}
				if (($defs2 != '00:00:00') && ($defe2 != '00:00:00')){
					$def_query = "UPDATE deficiencies set def_start='$defs2', def_end='$defe2' WHERE def_id='$keys[1]'";
					$def_result = mysql_query($def_query) or die(mysql_error($dbc));
					if ($defs_hr2 > 12){$defs_hr2 = $defs_hr2-12;}
					$prev_def[$week_type][$day][$keys[1]]['def_start']['hours'] = $defs_hr2;
					$prev_def[$week_type][$day][$keys[1]]['def_start']['minutes'] = $defs_mn2;
					if ($defe_hr2 > 12){$defe_hr2 = $defe_hr2-12;}
					$prev_def[$week_type][$day][$keys[1]]['def_end']['hours'] = $defe_hr2;
					$prev_def[$week_type][$day][$keys[1]]['def_end']['minutes'] = $defe_mn2;
					}
				else{
					$def_query = "DELETE from deficiencies WHERE def_id='$keys[1]'";
					$def_result = mysql_query($def_query) or die(mysql_error($dbc));
					unset($prev_def[$week_type][$day][$keys[1]]);
					}
				}
			}
		}
	}

function schedule_form($day, $week_type, $employees, $prev_schedules, $prev_def){
	$dow = date('l', strtotime($day));
	$schedule_form = '';
	foreach ($employees as $key=>$employeearray){
		$empid = $employeearray['emp_id'];
		if (isset($prev_schedules[$empid][$week_type][$day])){
			$data = $prev_schedules[$empid][$week_type][$day];
			}
		$schedule_form .= '<tr><td>
			<div class="editemp"><b>' . $employeearray['first_name'] . ' ' . $employeearray['last_name'] . '</b><br/>
				<div class="editwrapper"><div class="editinput">
					<div class="label">Shift Start:</div><input type="text" name="schedule[' . $employeearray['emp_id'] . ']['.$week_type.']['.$day.'][shift_start][hours]" 
						maxlength="2" size="1" class="hrs"';
		if ((isset($data))&&($data['shift_start']['hours'] != 0)){
			$schedule_form .= ' value="'.$data['shift_start']['hours'].'"';
			}
		$schedule_form .= '/> <b>:</b> 
					<input type="text" name="schedule[' . $employeearray['emp_id'] . ']['.$week_type.']['.$day.'][shift_start][minutes]" 
						maxlength="2" size="3"';
		if ((isset($data))&&($data['shift_start']['hours'] != 0)){
			$schedule_form .= ' value="'.$data['shift_start']['minutes'].'"';
			}
		$schedule_form .= '/></div>
					<div class="editinput end">
					<div class="label">Shift End:</div><input type="text" name="schedule[' . $employeearray['emp_id'] . ']['.$week_type.']['.$day.'][shift_end][hours]" 
						maxlength="2" size="1" class="hrs"';
		if ((isset($data))&&($data['shift_end']['hours'] != 0)){
			$schedule_form .= ' value="'.$data['shift_end']['hours'].'"';
			}
		$schedule_form .= '/> <b>:</b> 
					<input type="text" name="schedule[' . $employeearray['emp_id'] . ']['.$week_type.']['.$day.'][shift_end][minutes]" 
						maxlength="2" size="3"';
		if ((isset($data))&&($data['shift_end']['hours'] != 0)){
			$schedule_form .= ' value="'.$data['shift_end']['minutes'].'"';
			}
		$schedule_form .= '/></div>
				</div><span class="screen"><br/><br/></span>
				<input type="checkbox" name="divdesk"';
		if ((isset($data))&&!(($data['desk_start']['hours']=='')||($data['desk_start']['hours']==0))){
			$schedule_form .= ' checked/>Add Desk Shift(s)
				<div class="hidden" style="display:block;">';
			}
		else{
			$schedule_form .= ' />Add Desk Shift(s)
				<div class="hidden" style="display:none;">';
			}
		$schedule_form .= '<div class="editwrapper">
						<div class="editinput">
							<div class="label">Desk Start:</div><input type="text" name="schedule[' . $employeearray['emp_id'] . ']['.$week_type.']['.$day.'][desk_start][hours]" 
								maxlength="2" size="1" class="hrs"';
		if ((isset($data))&&($data['desk_start']['hours'] != 0)){
			$schedule_form .= ' value="'.$data['desk_start']['hours'].'"';
			}
		$schedule_form .= '/> <b>:</b> 
							<input type="text" name="schedule[' . $employeearray['emp_id'] . ']['.$week_type.']['.$day.'][desk_start][minutes]" 
								maxlength="2" size="3"';
		if ((isset($data))&&($data['desk_start']['hours'] != 0)){
			$schedule_form .= ' value="'.$data['desk_start']['minutes'].'"';
			}
		$schedule_form .= '/>
						</div>
						<div class="editinput end">
							<div class="label">Desk End:</div><input type="text" name="schedule[' . $employeearray['emp_id'] . ']['.$week_type.']['.$day.'][desk_end][hours]" 
								maxlength="2" size="1" class="hrs"';
		if ((isset($data))&&($data['desk_end']['hours'] != 0)){
			$schedule_form .= ' value="'.$data['desk_end']['hours'].'"';
			}
		$schedule_form .= '/> <b>:</b> 
							<input type="text" name="schedule[' . $employeearray['emp_id'] . ']['.$week_type.']['.$day.'][desk_end][minutes]" 
								maxlength="2" size="3"';
		if ((isset($data))&&($data['desk_end']['hours'] != 0)){
			$schedule_form .= ' value="'.$data['desk_end']['minutes'].'"';
			}
		$schedule_form .= '/>
						</div>
					</div><br/>
					<div class="editwrapper">
						<div class="editinput">
							<div class="label">Desk Start:</div><input type="text" name="schedule[' . $employeearray['emp_id'] . ']['.$week_type.']['.$day.'][desk_start2][hours]" 
								maxlength="2" size="1" class="hrs"';
		if ((isset($data))&&($data['desk_start2']['hours'] != 0)){
			$schedule_form .= ' value="'.$data['desk_start2']['hours'].'"';
			}
		$schedule_form .= '/> <b>:</b> 
							<input type="text" name="schedule[' . $employeearray['emp_id'] . ']['.$week_type.']['.$day.'][desk_start2][minutes]" 
								maxlength="2" size="3"';
		if ((isset($data))&&($data['desk_start2']['hours'] != 0)){
			$schedule_form .= ' value="'.$data['desk_start2']['minutes'].'"';
			}
		$schedule_form .= '/>
						</div>
						<div class="editinput end">
							<div class="label">Desk End:</div><input type="text" name="schedule[' . $employeearray['emp_id'] . ']['.$week_type.']['.$day.'][desk_end2][hours]" 
								maxlength="2" size="1" class="hrs"';
		if ((isset($data))&&($data['desk_end2']['hours'] != 0)){
			$schedule_form .= ' value="'.$data['desk_end2']['hours'].'"';
			}
		$schedule_form .= '/> <b>:</b> 
							<input type="text" name="schedule[' . $employeearray['emp_id'] . ']['.$week_type.']['.$day.'][desk_end2][minutes]" 
								maxlength="2" size="3"';
		if ((isset($data))&&($data['desk_end2']['hours'] != 0)){
			$schedule_form .= ' value="'.$data['desk_end2']['minutes'].'"';
			}
		$schedule_form .= '/>
						</div>
					</div><span class="screen"><br/></span>
				</div><br/>
				<input type="checkbox" name="divlunch"';
		if ((isset($data))&&!(($data['lunch_start']['hours']=='')||($data['lunch_start']['hours']==0))){
			$schedule_form .= ' checked/>Add Lunch
				<div class="hidden" style="display:block;">';
			}
		else{
			$schedule_form .= ' />Add Lunch
				<div class="hidden" style="display:none;">';
			}
		$schedule_form .= '<div class="editwrapper">
						<div class="editinput">
							<div class="label">Lunch Start:</div><input type="text" name="schedule[' . $employeearray['emp_id'] . ']['.$week_type.']['.$day.'][lunch_start][hours]" 
								maxlength="2" size="1" class="hrs"';
		if ((isset($data))&&($data['lunch_start']['hours'] != 0)){
			$schedule_form .= ' value="'.$data['lunch_start']['hours'].'"';
			}
		$schedule_form .= '/> <b>:</b> 
							<input type="text" name="schedule[' . $employeearray['emp_id'] . ']['.$week_type.']['.$day.'][lunch_start][minutes]" 
								maxlength="2" size="3"';
		if ((isset($data))&&($data['lunch_start']['hours'] != 0)){
			$schedule_form .= ' value="'.$data['lunch_start']['minutes'].'"';
			}
		$schedule_form .= '/>
						</div>
						<div class="editinput end">
							<div class="label">Lunch End:</div><input type="text" name="schedule[' . $employeearray['emp_id'] . ']['.$week_type.']['.$day.'][lunch_end][hours]" 
								maxlength="2" size="1" class="hrs"';
		if ((isset($data))&&($data['lunch_end']['hours'] != 0)){
			$schedule_form .= ' value="'.$data['lunch_end']['hours'].'"';
			}
		$schedule_form .= '/> <b>:</b> 
							<input type="text" name="schedule[' . $employeearray['emp_id'] . ']['.$week_type.']['.$day.'][lunch_end][minutes]" 
								maxlength="2" size="3"';
		if ((isset($data))&&($data['lunch_end']['hours'] != 0)){
			$schedule_form .= ' value="'.$data['lunch_end']['minutes'].'"';
			}
		$schedule_form .= '/>
						</div>
					</div>
				</div>
			</div></td></tr>';
		}
	if (isset($prev_def[$week_type][$day])){
		$defdata = $prev_def[$week_type][$day];
		while ($row = current($defdata)){
			$keys[] = key($defdata);
			next($defdata);
			}
		}
	$schedule_form .= '<tr><td>
		<div class="editdef">
			<input type="checkbox" name="deft"';
		if (isset($defdata)&&(count($defdata) > 0)){
			$schedule_form .= ' checked/>Schedule Regular Coverage Needed
				<div class="hidden" style="display:block;">';
			}
		else{
			$schedule_form .= ' />Schedule Regular Coverage Needed
				<div class="hidden" style="display:none;">';
			}
		$schedule_form .= '<div class="editwrapper">
					<div class="editinput">
						<div class="label cov">Coverage Start:</div><input type="text" name="def['.$week_type.']['.$day.'][def_start][hours]" 
							maxlength="2" size="1" class="hrs"';
		if ((isset($defdata))&&($defdata[$keys[0]]['def_start']['hours'] != 0)){
			$schedule_form .= ' value="'.$defdata[$keys[0]]['def_start']['hours'].'"';
			}
		$schedule_form .= '/> <b>:</b> 
						<input type="text" name="def['.$week_type.']['.$day.'][def_start][minutes]" maxlength="2" size="3"';
		if ((isset($defdata))&&($defdata[$keys[0]]['def_start']['hours'] != 0)){
			$schedule_form .= ' value="'.$defdata[$keys[0]]['def_start']['minutes'].'"';
			}
		$schedule_form .= '/>
					</div>
					<div class="editinput end">
						<div class="label cov">Coverage End:</div><input type="text" name="def['.$week_type.']['.$day.'][def_end][hours]" 
							maxlength="2" size="1" class="hrs"';
		if ((isset($defdata))&&($defdata[$keys[0]]['def_end']['hours'] != 0)){
			$schedule_form .= ' value="'.$defdata[$keys[0]]['def_end']['hours'].'"';
			}
		$schedule_form .= '/> <b>:</b> 
						<input type="text" name="def['.$week_type.']['.$day.'][def_end][minutes]" maxlength="2" size="3"';
		if ((isset($defdata))&&($defdata[$keys[0]]['def_end']['hours'] != 0)){
			$schedule_form .= ' value="'.$defdata[$keys[0]]['def_end']['minutes'].'"';
			}
		$schedule_form .= '/>
					</div>
				</div>
				<div class="editwrapper">
					<div class="editinput">
						<div class="label cov">Coverage Start:</div><input type="text" name="def['.$week_type.']['.$day.'][def_start2][hours]" 
							maxlength="2" size="1" class="hrs"';
		if ((isset($defdata))&&(count($defdata) > 1)&&($defdata[$keys[1]]['def_start']['hours'] != 0)){
			$schedule_form .= ' value="'.$defdata[$keys[1]]['def_start']['hours'].'"';
			}
		$schedule_form .= '/> <b>:</b> 
						<input type="text" name="def['.$week_type.']['.$day.'][def_start2][minutes]" maxlength="2" size="3"';
		if ((isset($defdata))&&(count($defdata) > 1)&&($defdata[$keys[1]]['def_start']['hours'] != 0)){
			$schedule_form .= ' value="'.$defdata[$keys[1]]['def_start']['minutes'].'"';
			}
		$schedule_form .= '/>
					</div>
					<div class="editinput end">
						<div class="label cov">Coverage End:</div><input type="text" name="def['.$week_type.']['.$day.'][def_end2][hours]" 
							maxlength="2" size="1" class="hrs"';
		if ((isset($defdata))&&(count($defdata) > 1)&&($defdata[$keys[1]]['def_end']['hours'] != 0)){
			$schedule_form .= ' value="'.$defdata[$keys[1]]['def_end']['hours'].'"';
			}
		$schedule_form .= '/> <b>:</b> 
						<input type="text" name="def['.$week_type.']['.$day.'][def_end2][minutes]" maxlength="2" size="3"';
		if ((isset($defdata))&&(count($defdata) > 1)&&($defdata[$keys[1]]['def_end']['hours'] != 0)){
			$schedule_form .= ' value="'.$defdata[$keys[1]]['def_end']['minutes'].'"';
			}
		$schedule_form .= '/>
					</div>
				</div>
			</div>
		</div>
		</td></tr>';
	echo $schedule_form;
	}
	
if (isset($_POST['week_type'])){
	$week_type = $_POST['week_type'];
	}
else{
	$week_type = 'a';
	}

if(isset($_POST['day'])){
	$day = ($_POST['day']);
	}
else{
	$day = 'sat';
	}
	
include ('./includes/header.html');
echo '<div id="mobilehack">';
$mobilehack = 1;
include ('./includes/supersidebar.html');
?>
<script>
$(document).ready(function() {
	function showDay(){
		day = $('.dayfocus').data("day");
		week = $('.weekfocus').data("week");
		$('.editform').hide();
		$('div.'+day+'.'+week).show();
		$('div.'+day+'.'+week).find('input:first').focus();
		$('input[name="week_type"]').val(week);
		$('input[name="day"]').val(day);
		}
	function changeDay(direction){
		day = $('.dayfocus').data("day");
		if (direction == 'forward'){
			if (day == 'sat'){day = 'sun';}
			else if (day == 'sun'){day = 'mon';}
			else if (day == 'mon'){day = 'tue';}
			else if (day == 'tue'){day = 'wed';}
			else if (day == 'wed'){day = 'thu';}
			else if (day == 'thu'){day = 'fri';}
			}
		if (direction == 'back'){
			if (day == 'mon'){day = 'sun';}
			else if (day == 'tue'){day = 'mon';}
			else if (day == 'wed'){day = 'tue';}
			else if (day == 'thu'){day = 'wed';}
			else if (day == 'fri'){day = 'thu';}
			else if (day == 'sun'){day = 'sat';}
			}
		$("[data-day='"+day+"']").siblings('div.daylink').removeClass('dayfocus');
		$("[data-day='"+day+"']").addClass('dayfocus');
		week = $('.weekfocus').data("week");
		$('.editform').hide();
		$('div.'+day+'.'+week).show();
		$('div.'+day+'.'+week).find('input:first').focus();
		$('input[name="week_type"]').val(week);
		$('input[name="day"]').val(day);
		}
	
	showDay();
	
	$('.daylink').click(function(){
		$(this).siblings('div.daylink').removeClass('dayfocus');
		$(this).addClass('dayfocus');
		showDay();
		});
		
	$('.weeklink').click(function(){
		$(this).siblings('div.weeklink').removeClass('weekfocus');
		$(this).addClass('weekfocus');
		showDay();
		});
	
	$(':checkbox').change(function() {
		if($(this).attr("checked")){
			$(this).next('.hidden').show();
			}
        else{
			$(this).next('.hidden').find('input').val('');
			$(this).next('.hidden').hide();
			}
		});
	$(':text').blur(function(){
		var att = $(this).val();
		if ($(this).val() == '0'){
			if(($(this).hasClass('hrs'))||($(this).prev().prev().val()=='')){
				$(this).val('');
				}
			else{
				$(this).val('00');
				}
			}
		var intRegex = /^[0-9]+$/;
		if(!att.match(intRegex)){
			$(this).val('');
			}
		});
		
	$('html').keydown(function(e){
		if (e.which == 39){
			changeDay('forward');
			}
		else if (e.which == 37){
			changeDay('back')
			}
		if (e.which == 8 && !$(e.target).is('input','textarea')){
			e.preventDefault();
			}
		});
	});
</script>

<div class="mobilewrapper_outer">
<div class="mobilewrapper_inner">
<span class="date"><h1>Add/Edit Schedule</h1></span>

<?php
$schedstart_disp = date('n', strtotime($schedstart)).'/'.date('j', strtotime($schedstart)).'/'.date('Y', strtotime($schedstart));
$schedend_disp = date('n', strtotime($schedend)).'/'.date('j', strtotime($schedend)).'/'.date('Y', strtotime($schedend));
echo '<div class="editlabel">';
echo "<b>Editing:</b> $division Schedule for $schedstart_disp to $schedend_disp$additional_dates</div>";
echo '<div class="daymenu">';
echo '<div class="weeklinks">';
foreach ($week_types as $k2=>$v2){
	echo '<div class="weeklink';
	if($v2==$week_type){
		echo ' weekfocus';
		}
	echo '" data-week="'.$v2.'">'.ucfirst($v2).'</div>';
	}
echo '</div>';

echo '<div class="daylinks">';
foreach ($daysofweek as $k=>$v){
	echo '<div class="daylink';
	if($v==$day){
		echo ' dayfocus';
		}
	echo '" data-day="'.$v.'">'.ucfirst($v).'</div>';
	}
echo '</div>';
echo '</div>';
echo '<form action="schedule_days" method="post" name="sched">';
foreach ($week_types as $k2=>$v2){
	foreach ($daysofweek as $k=>$v){
		echo '<div class="addeditsched editform '.$v.' '.$v2.'" style="display:none;">	
			<table>';
		schedule_form($v, $v2, $employees, $prev_schedules, $prev_def);
		echo '<tr><td>';
		echo '<input type="hidden" name="day_submit" value="TRUE" />
			<input type="hidden" name="division" value="'.$division.'" />
			<input type="hidden" name="specific_schedule" value="'.$specific_schedule.'"/>
			<input type="hidden" name="schedstart" value="'.$schedstart.'"/>
			<input type="hidden" name="schedend" value="'.$schedend.'"/>
			<input type="hidden" name="week_type" value=""/>
			<input type="hidden" name="day" value=""/>
			<p><input type="submit" name="submit" value="Save Schedule"/></p></td></tr>';
		echo '</table></div>';
		}
	}
echo '</form>';
?>
</div></div></div>
<?php
include ('./includes/footer.html');
?>
</div>