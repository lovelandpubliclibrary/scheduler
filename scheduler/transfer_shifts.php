<?php #transfer_shifts.php
require_once ('../mysql_connect_sched.php');

$divisions = array();
$query = "SELECT * from lplscheduler2.divisions ORDER BY div_name";
$result = mysql_query($query);
while ($row = mysql_fetch_assoc($result)) {
	$divisions[$row['div_link']] = $row['div_name'];
	}
	
if(isset($_SESSION['this_emp_id'])){
	$this_emp_id = $_SESSION['this_emp_id'];
	$this_assignment_id = $_SESSION['assignment_id'];
	$query = "SELECT first_name, last_name, name_dup from lplscheduler2.employees WHERE emp_id = '$this_emp_id'";
	$result = mysql_query($query);
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
		$this_full_name = $row['first_name'].' '.$row['last_name'];
		}
	}

$week_types = array('a','b','c','d');
$daysofweek = array('sat','sun','mon','tue','wed','thu','fri');
//$season = 'fall';
//$year = '2014';
$count = 1;
$fall_divisions = array('admin','adult','children','lti','techservices','pages');
$spring_divisions = array('teen','customerservice');

function table_exist($db, $tablename){
	$query = "SHOW TABLES FROM $db like '$tablename'";
	$result = mysql_query($query);
	if ($result){
		return true;
		}
	else{
		return false;
		}
	}

foreach ($divisions as $div_link=>$div_name){
	if (in_array($div_link,$fall_divisions)){
		$season = 'fall';
		$year = '2014';
		}
	else{
		$season = 'spring';
		$year = '2015';
		}
	//$query = "INSERT into lplscheduler2.schedules (division, schedule_start_date, schedule_end_date, specific_schedule) 
	//	VALUES ('$div_name','2014-12-27','2015-02-27',$count)";
	//$result = mysql_query($query);
	
	echo '<h1>'.$div_name.' '.$season.' '.$year.'</h1>';

	foreach ($week_types as $key=>$week_type){
		echo $week_type.', ';
		foreach ($daysofweek as $key2=>$day){
			echo $day.'<br/>';
			$tablename = $week_type.'_'.$day.'_'.$year.'_'.$season;
			$assoc_tablename = 'employeeassoc_'.$tablename;
			
			if((table_exist('lplscheduler',$tablename))&&(table_exist('lplscheduler',$assoc_tablename))){
				$query = "SELECT * from lplscheduler.$tablename t, lplscheduler.$assoc_tablename a, lplscheduler2.employees e 
					WHERE t.row_id=a.row_id and a.employee_number=e.employee_number and a.employee_number='2014126' and (e.division like '%".$div_name."%') and active='Active'";
				$result = mysql_query($query);
				if($result){
					while($row = mysql_fetch_array ($result, MYSQL_ASSOC)) {
						$first_name = $row['first_name'];
						$emp_id = $row['emp_id'];
						$shift_start = $row['shift_start'];
						$shift_end = $row['shift_end'];
						$desk_start = $row['desk_start'];
						$desk_end = $row['desk_end'];
						$desk_start2 = $row['desk_start2'];
						$desk_end2 = $row['desk_end2'];
						$lunch_start = $row['lunch_start'];
						$lunch_end = $row['lunch_end'];
						
						$query2 = "DELETE from lplscheduler2.shifts WHERE week_type='$week_type' and shift_day='$day' 
							and emp_id='$emp_id'";
						$result2 = mysql_query($query2);
						
						$query3 = "INSERT into lplscheduler2.shifts (week_type, shift_day, emp_id, shift_start, shift_end,
							desk_start, desk_end, desk_start2, desk_end2, lunch_start, lunch_end, specific_schedule, schedule_create)
							VALUES ('$week_type','$day','$emp_id','$shift_start', '$shift_end',	'$desk_start', '$desk_end', 
							'$desk_start2', '$desk_end2', '$lunch_start', '$lunch_end',$count, CURRENT_TIMESTAMP)";
						$result3 = mysql_query($query3) or die (mysql_error());
						if ($result3){
							echo $first_name.' transferred<br/>';
							}
						}
					}
				}
			}
		}
	$count++;
	}

?>