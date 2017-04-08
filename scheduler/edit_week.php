<?php #edit_week
include('./includes/supersessionstart.php');
$came_from = $_SESSION['came_from'];

if ((!isset($came_from)) || (!isset($_POST['submit']))){
	header ('Location: view_division');
	}
	
if (($came_from != '/scheduler/view_division') && ($came_from != '/scheduler/edit_week')){
	header ('Location: view_division');
	}

include('./includes/allsessionvariables.php');

if (isset($_POST['employee_name'])){
	$employee=$_POST['employee_name'];
	}
if (isset($_POST['employee_number'])){
	$empno=$_POST['employee_number'];
	}
if (isset($_POST['week_type'])){
	$week_type = $_POST['week_type'];
	}
if (isset($_POST['season'])){
	$season = $_POST['season'];
	}
if (isset($_POST['year'])){
	$year = $_POST['year'];
	}
	
$page_title = "Edit Schedule for $employee" ;

	$_SESSION['edit_week_name'] = $employee;
	$_SESSION['edit_week_number'] = $empno;

require_once ('../mysql_connect_sched.php'); //Connect to the db.

if (isset($_POST['edits_submitted'])){
	$schedule_array = $_POST['schedule'];
	
	foreach ($schedule_array as $tablename=>$value){
		$assoc_tablename = 'employeeassoc_'.$tablename;
		$namearray = explode("_", $tablename);
		$week_type = $namearray[0];
		$day = $namearray[1];
		$year = $namearray[2];
		$season = strtolower($namearray[3]);
		
		//Establish schedule time variables
		
		$ss_hr = $value['shift_start']['hours'];
			if (!empty($ss_hr) && ($ss_hr < 7)) {$ss_hr = $ss_hr+12;}
			if (empty($ss_hr)){$ss_hr = "00";}
		$ss_mn = $value['shift_start']['minutes'];
			if (empty($ss_mn)){$ss_mn = "00";}
		$ss = "$ss_hr:$ss_mn:00";
		
		$se_hr = $value['shift_end']['hours'];
			if (!empty($se_hr) && ($se_hr < $ss_hr)) {$se_hr = $se_hr+12;}
			elseif (!empty($se_hr) && ($se_hr >= $ss_hr) && ($se_hr < 9)) {$se_hr = $se_hr+12;}
			if (empty($se_hr)){$se_hr = "00";}
		$se_mn = $value['shift_end']['minutes'];
			if (empty($se_mn)){$se_mn = "00";}
		$se = "$se_hr:$se_mn:00";
		
		$ds_hr = $value['desk_start']['hours'];
			if (!empty($ds_hr) && ($ds_hr < 8)) {$ds_hr = $ds_hr+12;}
			if (empty($ds_hr)){$ds_hr = "00";}
		$ds_mn = $value['desk_start']['minutes'];
			if (empty($ds_mn)){$ds_mn = "00";}
		$ds = "$ds_hr:$ds_mn:00";
		
		$de_hr = $value['desk_end']['hours'];
			if (!empty($de_hr) && ($de_hr < $ds_hr)) {$de_hr = $de_hr+12;}
			if (empty($de_hr)){$de_hr = "00";}
		$de_mn = $value['desk_end']['minutes'];
			if (empty($de_mn)){$de_mn = "00";}
		$de = "$de_hr:$de_mn:00";
		
		$ds2_hr = $value['desk_start2']['hours'];
			if (!empty($ds2_hr) && ($ds2_hr < 8)) {$ds2_hr = $ds2_hr+12;}
			if (empty($ds2_hr)){$ds2_hr = "00";}
		$ds2_mn = $value['desk_start2']['minutes'];
			if (empty($ds2_mn)){$ds2_mn = "00";}
		$ds2 = "$ds2_hr:$ds2_mn:00";
		
		$de2_hr = $value['desk_end2']['hours'];
			if (!empty($de2_hr) && ($de2_hr < $ds_hr)) {$de2_hr = $de2_hr+12;}
			if (empty($de2_hr)){$de2_hr = "00";}
		$de2_mn = $value['desk_end2']['minutes'];
			if (empty($de2_mn)){$de2_mn = "00";}
		$de2 = "$de2_hr:$de2_mn:00";
		
		$ls_hr = $value['lunch_start']['hours'];
			if (!empty($ls_hr) && ($ls_hr < 7)) {$ls_hr = $ls_hr+12;}
			if (empty($ls_hr)){$ls_hr = "00";}
		$ls_mn = $value['lunch_start']['minutes'];
			if (empty($ls_mn)){$ls_mn = "00";}
		$ls = "$ls_hr:$ls_mn:00";
		
		$le_hr = $value['lunch_end']['hours'];
			if (!empty($le_hr) && ($le_hr < $ls_hr)) {$le_hr = $le_hr+12;}
			if (empty($le_hr)){$le_hr = "00";}
		$le_mn = $value['lunch_end']['minutes'];
			if (empty($le_mn)){$le_mn = "00";}
		$le = "$le_hr:$le_mn:00";
		
		$query = "SELECT shift_start from $tablename as t, $assoc_tablename as a WHERE a.employee_number='$empno' and a.row_id=t.row_id";
		$result = mysql_query($query);
		$num_rows = mysql_num_rows($result);
		
		if ($num_rows == 0){
			$query2 = "INSERT into $tablename (shift_start, shift_end, desk_start, desk_end, desk_start2, desk_end2, 
				lunch_start, lunch_end, schedule_create) values ('$ss', '$se', '$ds', '$de', '$ds2', '$de2', '$ls', '$le', null)";
			$result2 = mysql_query($query2);
			if ($result2) {
				$row_id = mysql_insert_id();
				}
			$query3 = "INSERT into $assoc_tablename(employee_number, row_id) values ('$empno', '$row_id')";
			$result3 = mysql_query ($query3);
			
			if ($season=='fall'){
				$copyyear = $year+1;
				$copytablename = $week_type . '_' . $day . '_' . $copyyear . '_spring';
				$copyassoc_tablename = 'employeeassoc_' . $copytablename;
				
				$query4 = "INSERT into $copytablename (shift_start, shift_end, desk_start, desk_end, desk_start2, desk_end2, 
					lunch_start, lunch_end, schedule_create) values ('$ss', '$se', '$ds', '$de', '$ds2', '$de2', '$ls', '$le', null)";
				$result4 = mysql_query($query4);
				if ($result4) {
					$copyrow_id = mysql_insert_id();
					}
				$query5 = "INSERT into $copyassoc_tablename(employee_number, row_id) values ('$empno', '$copyrow_id')";
				$result5 = mysql_query($query5);
				}
			}
		else {
			$query = "UPDATE $tablename as t, $assoc_tablename as a SET shift_start='$ss', shift_end='$se', 
				desk_start='$ds', desk_end='$de', desk_start2='$ds2', desk_end2='$de2', lunch_start='$ls', lunch_end='$le' 
				WHERE a.employee_number='$empno' and a.row_id = t.row_id";
			$result = mysql_query ($query);
			
			if ($season=='fall'){
				$copyyear = $year+1;
				$copytablename = $week_type . '_' . $day . '_' . $copyyear . '_spring';
				$copyassoc_tablename = 'employeeassoc_' . $copytablename;
				
				$query = "UPDATE $copytablename as t, $copyassoc_tablename as a SET shift_start='$ss', shift_end='$se', 
					desk_start='$ds', desk_end='$de', desk_start2='$ds2', desk_end2='$de2',lunch_start='$ls', lunch_end='$le' 
					WHERE a.employee_number='$empno' and a.row_id = t.row_id";
				$result = mysql_query ($query);
				}
			}
		}
	$_SESSION['success'] = TRUE;
	header ('Location: view_division');
	}
else {
	include ('./includes/header.html');
	include ('./includes/supersidebar.html');
?>
<script>
function validateForm() {
	for(i=0; i<document.sched.elements.length; i++) {
		var x=document.sched.elements[i].value;
		if (x && (x!=parseInt(x))){
			alert("Please ensure that you have entered valid numeric times.");
			return false;
			}
		else return true;
		}
	}
</script>

<span class="date"><h1>Edit Weekly Schedule</h1></span>

<?php
	echo '<div class="editlabel">';
	echo "<b>Editing:</b> $employee<br/>";
	echo '<div class="indent">';
	echo ucwords($week_type).' '. ucwords($season).' '.$year.'</div></div>';
	
	$days = array('sat','sun','mon','tue','wed','thu','fri');
	
	foreach ($days as $day){
		$tablename = strtolower($week_type).'_'.strtolower($day).'_'.$year.'_'.strtolower($season);
		$assoc_tablename = 'employeeassoc_'.$tablename;
		$tables[] = array($tablename, $assoc_tablename, $day);
		}
	
	echo '<div class="editform">
		<form action="edit_week" method="post" name="sched" onsubmit="return validateForm();">
		<table>';
	
	foreach ($tables as $tablearray) {
		$schedule_array = array();
		$tablename = $tablearray[0];
		$assoc_tablename = $tablearray[1];
		$day = $tablearray[2];
		$dow = date('l', strtotime($day));

		$query = "SELECT shift_start, shift_end, desk_start, desk_end, desk_start2, desk_end2, lunch_start, lunch_end 
			FROM $tablename as t, $assoc_tablename as a 
			WHERE a.employee_number = '$empno' and t.row_id = a.row_id";
		$result = mysql_query($query);
		
		if ($result){
			$schedule_array = mysql_fetch_array ($result, MYSQL_ASSOC);
			$num_rows = mysql_num_rows($result);
			}
		if ((!$result) || (!isset($schedule_array)) || ($num_rows==0)){
			$schedule_array = array('shift_start'=>'00:00:00','shift_end'=>'00:00:00',
				'desk_start'=>'00:00:00','desk_end'=>'00:00:00','desk_start2'=>'00:00:00','desk_end2'=>'00:00:00',
				'lunch_start'=>'00:00:00','lunch_end'=>'00:00:00');
			}

		foreach ($schedule_array as &$value){
			$value = explode(":", $value);
			}
		$ss_hr = (integer)$schedule_array['shift_start'][0];
			if ($ss_hr > 12){
				$ss_hr = $ss_hr-12;
				}
		$ss_mn = $schedule_array['shift_start'][1];
		
		$se_hr = (integer)$schedule_array['shift_end'][0];
			if ($se_hr > 12){
				$se_hr = $se_hr-12;
				}
		$se_mn = $schedule_array['shift_end'][1];
		
		$ds_hr = (integer)$schedule_array['desk_start'][0];
			if ($ds_hr > 12){
				$ds_hr = $ds_hr-12;
				}
		$ds_mn = $schedule_array['desk_start'][1];
		
		$de_hr = (integer)$schedule_array['desk_end'][0];
			if ($de_hr > 12){
				$de_hr = $de_hr-12;
				}
		$de_mn = $schedule_array['desk_end'][1];
		
		$ds2_hr = (integer)$schedule_array['desk_start2'][0];
			if ($ds2_hr > 12){
				$ds2_hr = $ds2_hr-12;
				}
		$ds2_mn = $schedule_array['desk_start2'][1];
		
		$de2_hr = (integer)$schedule_array['desk_end2'][0];
			if ($de2_hr > 12){
				$de2_hr = $de2_hr-12;
				}
		$de2_mn = $schedule_array['desk_end2'][1];

		$ls_hr = (integer)$schedule_array['lunch_start'][0];
			if ($ls_hr > 12){
				$ls_hr = $ls_hr-12;
				}
		$ls_mn = $schedule_array['lunch_start'][1];
		
		$le_hr = (integer)$schedule_array['lunch_end'][0];
			if ($le_hr > 12){
				$le_hr = $le_hr-12;
				}
		$le_mn = $schedule_array['lunch_end'][1];
				
		echo '<tr><td>
			<div class="editemp"><b>'.$dow.'</b><br/>';
		echo '<div class="editwrapper"><div class="editinput">
			<div class="label">Shift Start:</div><input type="text" name="schedule[' . $tablename . '][shift_start][hours]" 
			value="' . $ss_hr . '" maxlength="2" size="1" class="hrs"/> <b>:</b> 
			<input type="text" name="schedule[' . $tablename . '][shift_start][minutes]" 
			value="' . $ss_mn . '" maxlength="2" size="3"/></div>';
		echo '<div class="editinput end">
			<div class="label">Shift End:</div><input type="text" name="schedule[' . $tablename . '][shift_end][hours]" 
			value="' . $se_hr . '" maxlength="2" size="1" class="hrs"/> <b>:</b> 
			<input type="text" name="schedule[' . $tablename . '][shift_end][minutes]" 
			value="' . $se_mn . '" maxlength="2" size="3"/></div></div><br/><br/>';
		echo '<div class="editwrapper"><div class="editinput">
			<div class="label">Desk Start:</div><input type="text" name="schedule[' . $tablename . '][desk_start][hours]" 
			value="' . $ds_hr . '" maxlength="2" size="1" class="hrs"/> <b>:</b> 
			<input type="text" name="schedule[' . $tablename . '][desk_start][minutes]" 
			value="' . $ds_mn . '" maxlength="2" size="3"/></div>';
		echo '<div class="editinput end">
			<div class="label">Desk End:</div><input type="text" name="schedule[' . $tablename . '][desk_end][hours]" 
			value="' . $de_hr . '" maxlength="2" size="1" class="hrs"/> <b>:</b> 
			<input type="text" name="schedule[' . $tablename . '][desk_end][minutes]" 
			value="' . $de_mn . '" maxlength="2" size="3"/></div></div><br/>';
		echo '<div class="editwrapper"><div class="editinput">
			<div class="label">Desk Start:</div><input type="text" name="schedule[' . $tablename . '][desk_start2][hours]" 
			value="' . $ds2_hr . '" maxlength="2" size="1" class="hrs"/> <b>:</b> 
			<input type="text" name="schedule[' . $tablename . '][desk_start2][minutes]" 
			value="' . $ds2_mn . '" maxlength="2" size="3"/></div>';
		echo '<div class="editinput end">
			<div class="label">Desk End:</div><input type="text" name="schedule[' . $tablename . '][desk_end2][hours]" 
			value="' . $de2_hr . '" maxlength="2" size="1" class="hrs"/> <b>:</b> 
			<input type="text" name="schedule[' . $tablename . '][desk_end2][minutes]" 
			value="' . $de2_mn . '" maxlength="2" size="3"/></div></div><br/><br/>';
		echo '<div class="editwrapper"><div class="editinput">
			<div class="label">Lunch Start:</div><input type="text" name="schedule[' . $tablename . '][lunch_start][hours]" 
			value="' . $ls_hr . '" maxlength="2" size="1" class="hrs"/> <b>:</b> 
			<input type="text" name="schedule[' . $tablename . '][lunch_start][minutes]" 
			value="' . $ls_mn . '" maxlength="2" size="3"/></div>';
		echo '<div class="editinput end">
			<div class="label">Lunch End:</div><input type="text" name="schedule[' . $tablename . '][lunch_end][hours]" 
			value="' . $le_hr . '" maxlength="2" size="1" class="hrs"/> <b>:</b> 
			<input type="text" name="schedule[' . $tablename . '][lunch_end][minutes]" 
			value="' . $le_mn . '" maxlength="2" size="3"/></div></div></div></td></tr>';
		}
	
	echo'<tr><td><p><input type="submit" name="submit" value="Submit" /></p>
		<input type="hidden" name="edits_submitted" value="TRUE" />
		<input type="hidden" name="employee_name" value="'.$employee.'"/>
		<input type="hidden" name="employee_number" value="'.$empno.'"/>
		</td></tr>
		</table></form></div>';
	}

include ('./includes/footer.html');
?>