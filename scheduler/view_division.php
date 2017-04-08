<?php #view_division.php

$page_title = 'View Division Schedules';
include('./includes/supersessionstart.php');

if (isset($_SESSION['came_from'])){
	$came_from = $_SESSION['came_from'];
	}

include('./includes/allsessionvariables.php');
include ('./includes/header.html');
include ('./includes/supersidebar.html');
include ('./display_functions.php');

date_default_timezone_set('America/Denver');
$start_year = date('Y');
$end_year = date('Y') + 10;

echo '<span class="date"><h1>View Division Schedule Details</h1></span>';

require_once ('../mysql_connect_sched.php');

if (($came_from == '/scheduler/edit_week') && (isset($_SESSION['success']))){
	$name = $_SESSION['edit_week_name'];
	$empn = $_SESSION['edit_week_number'];
	echo '<div class="message"><b>'. $name . '</b> has been updated.</div>';
	unset($_SESSION['edit_week_name']);
	unset($_SESSION['edit_week_number']);
	unset($_SESSION['success']);
	}

if (isset($_POST['submitted'])) {
	$_SESSION['view_division_submitted'] = TRUE;
	$division = $_POST['division'];
	$_SESSION['view_division_division'] = $division;
	if (isset($_POST['week_type'])){
		$week_type = $_POST['week_type'];
		}
	else {
		$week_type = 'a';
		}
	$_SESSION['view_division_week_type'] = $week_type;
	if (isset($_POST['season'])){
		$season = $_POST['season'];
		}
	else {
		$season = 'summer';
		}
	$_SESSION['view_division_season'] = $season;
	if (isset($_POST['year'])){
		$year = $_POST['year'];
		}
	else {
		$year = date('Y');
		}
	$_SESSION['view_division_year'] = $year;
	}
if (isset($_SESSION['view_division_division'])){
	$division = $_SESSION['view_division_division'];
	}
else {
	unset ($_SESSION['view_division_submitted']);
	}
if (isset($_SESSION['view_division_week_type'])){
	$week_type = $_SESSION['view_division_week_type'];
	}
if (isset($_SESSION['view_division_season'])){
	$season = $_SESSION['view_division_season'];
	}
if (isset($_SESSION['view_division_year'])){
	$year = $_SESSION['view_division_year'];
	}

$div = array('Admin', 'Adult', 'Children', 
	'Customer Service', 'LTI', 'Tech Services', 'Teen');
$div2 = array('A','B','C','D');
$div3 = array('Spring','Summer','Fall');

echo '<div class="detailform"><form action="view_division" method="post">
	<p class="divform"><div class="mobilefloat">Division: 
		<select name="division" style="margin-right:10px;">';
echo '<option value="select" disabled="disabled" selected="selected">- Select -</option>';
foreach ($div as $key => $d){
	echo '<option value="' . $d . '" ';
	if (isset($division)){
		if ($division==$d) {echo 'selected="selected"';}
		}
	echo '>' . $d . '</option>';
	}
echo '</select></div>
	Week:
		<select name="week_type" style="margin-right:10px;">
			<option value="select" disabled="disabled" selected="selected">- Select -</option>';
foreach ($div2 as $key => $d){
	echo '<option value="' . $d . '" ';
		if (isset($week_type)){
			if ($week_type==$d) {echo 'selected="selected"';}
			}
		echo '>' . $d . '</option>';
	}
echo'		</select>
	</p>
	<p><div class="mobilefloat">
	Season:
		<select name="season" style="margin-right:10px;">
			<option value="select" disabled="disabled" selected="selected">- Select -</option>';
foreach ($div3 as $key => $d){
	echo '<option value="' . $d . '" ';
		if (isset($season)){
			if ($season==$d) {echo 'selected="selected"';}
			}
		echo '>' . $d . '</option>';
	}	
echo'</select></div>
		<div class="mobilefloat">
		Year:		
		<select name="year" style="margin-right:20px;">';
for ($y = $start_year; $y<=$end_year; $y++) {
	echo "<option value =\"$y\"";
	if (isset($year)){
		if ($year==$y){
			echo ' selected="selected"';
			}
		}
	echo ">$y</option>";
	}
echo '</select>
	</div>
		<input type="submit" name="submit" value="Select" />
		<input type="hidden" name="submitted" value="TRUE" />
	</p>
</form></div>';

echo '<div class="keyd"><div class="label">Key:</div> <table class="detail">
	<tr><td class="marks" style="width:45px;padding-left:10px;">
	<span class="shift">Here</span><br/><span class="desk">Desk</span><br/><span class="lunch">Lunch</span>
	</td></tr></table></div>';

if ((isset($_POST['submitted'])) || (isset($_SESSION['view_division_submitted']))){
//Time to decimal function
	function dec_minutes($mins) {
		$dec_mins = $mins/60;
		return $dec_mins;
		}
	
	$tables = array();
	$days = array('sat','sun','mon','tue','wed','thu','fri');
	if ($division =='customerservice'){
		$division = 'Customer Service';
		}
	if ($division == 'techservices'){
		$division = 'Tech Services';
		}
	if ($division == 'lti'){
		$ucdivision = 'Library Tech & Innovation';
		}
	else {
		$ucdivision = ucwords($division);
		}
	$ucseason = ucwords($season);
	$ucweek_type = ucwords($week_type);
	
	echo '<div class="detailtable">';
	echo "<div class=\"dp\">$ucdivision, $ucseason $year &ndash; $ucweek_type</div>";
	
	foreach ($days as $day){
		$tablename = strtolower($week_type).'_'.strtolower($day).'_'.$year.'_'.strtolower($season);
		$assoc_tablename = 'employeeassoc_'.$tablename;
		$tables[] = array($tablename, $assoc_tablename);
		}
	
	echo '<div class="divboxes"><table class="detail" cellspacing="0">
		<tr class="divisions days screen"><td></td><td class="day">Saturday</td><td class="day">Sunday</td>
		<td class="day">Monday</td><td class="day">Tuesday</td><td class="day">Wednesday</td>
		<td class="day">Thursday</td><td class="day">Friday</td><td class="hrs">Hrs</td><td></td></tr>';
	echo '<tr></tr><tr class="divisions days mobile"><td></td><td class="day">Sat</td><td class="day">Sun</td>
		<td class="day">Mon</td><td class="day">Tue</td><td class="day">Wed</td>
		<td class="day">Thu</td><td class="day">Fri</td><td class="hrs">Hr</td><td></td></tr>';
	
	$query = "SELECT first_name, last_name, name_dup, employee_number FROM employees WHERE division='$division' 
		and weekly_hours != '15' and active = 'Active'
		ORDER BY division asc, exempt_status asc, weekly_hours desc, first_name asc";
	$result = mysql_query($query);
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
		$first_name = $row['first_name'];
		$last_name = $row['last_name'];
		if ($row['name_dup'] == 'Y'){
			$last_initial = substr($last_name,0,1);
			$first_name .= ' ' . $last_initial . '.';
			}	
		$empno = $row['employee_number'];
		$hr_total = 0;
		
		echo '<tr class="divisions"><td class="first_name">' . $first_name . '</td>';
		
		foreach ($tables as $tablearray) {
			$tablename = $tablearray[0];
			$assoc_tablename = $tablearray[1];

			$query2 = "SELECT time_format(shift_start,'%k') as shift_start, 
				time_format(shift_start,'%i') as shift_start_minutes, time_format(shift_end,'%k') as shift_end, 
				time_format(shift_end,'%i') as shift_end_minutes, time_format(desk_start,'%k') as desk_start, 
				time_format(desk_start,'%i') as desk_start_minutes, time_format(desk_end,'%k') as desk_end, 
				time_format(desk_end,'%i') as desk_end_minutes, time_format(desk_start2,'%k') as desk_start2, 
				time_format(desk_start2,'%i') as desk_start2_minutes, time_format(desk_end2,'%k') as desk_end2, 
				time_format(desk_end2,'%i') as desk_end2_minutes, time_format(lunch_start,'%k') as lunch_start, 
				time_format(lunch_start,'%i') as lunch_start_minutes, time_format(lunch_end,'%k') as lunch_end, 
				time_format(lunch_end,'%i') as lunch_end_minutes 
				FROM employees as e, $tablename as t, $assoc_tablename as a 
				WHERE e.employee_number = '$empno' and e.employee_number = a.employee_number and t.row_id = a.row_id";
			$result2 = mysql_query($query2);
			
				if ($result2){
				
				$row2 = mysql_fetch_array($result2, MYSQL_ASSOC);
				
				$shift_start = $row2['shift_start'];
				$shift_start_minutes = $row2['shift_start_minutes'];
				$shift_end = $row2['shift_end'];
				$shift_end_minutes = $row2['shift_end_minutes'];
				$desk_start = $row2['desk_start'];
				$desk_start_minutes = $row2['desk_start_minutes'];
				$desk_end = $row2['desk_end'];
				$desk_end_minutes = $row2['desk_end_minutes'];
				$desk_start2 = $row2['desk_start2'];
				$desk_start2_minutes = $row2['desk_start2_minutes'];
				$desk_end2 = $row2['desk_end2'];
				$desk_end2_minutes = $row2['desk_end2_minutes'];
				$lunch_start = $row2['lunch_start'];
				$lunch_start_minutes = $row2['lunch_start_minutes'];
				$lunch_end = $row2['lunch_end'];
				$lunch_end_minutes = $row2['lunch_end_minutes'];
				
				//Adjust 24-hour time.
				if ($shift_start > 12){
					$ss12 = $shift_start - 12;
					}
				elseif($shift_start == 0){
					$ss12 = NULL;
					}
				else{
					$ss12 = $shift_start;
					}
				if (($shift_start_minutes != '00') && ($shift_start_minutes != null)){
					$ss12 .= ':'.$shift_start_minutes;
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
				if (($shift_end_minutes != '00') && ($shift_end_minutes != null)){
					$se12 .= ':'.$shift_end_minutes;
					}
				if ($desk_start > 12){
					$ds12 = $desk_start - 12;
					}
				elseif($desk_start == 0){
					$ds12 = NULL;
					}
				else{
					$ds12 = $desk_start;
					}
				if (($desk_start_minutes != '00') && ($desk_start_minutes != null)){
					$ds12 .= ':'.$desk_start_minutes;
					}
			
				if ($desk_end > 12){
					$de12 = $desk_end - 12;
					}
				elseif($desk_end == 0){
					$de12 = NULL;
					}
				else{
					$de12 = $desk_end;
					}
				if (($desk_end_minutes != '00') && ($desk_end_minutes != null)){
					$de12 .= ':'.$desk_end_minutes;
					}
				if ($desk_start2 > 12){
					$ds212 = $desk_start2 - 12;
					}
				elseif($desk_start2 == 0){
					$ds212 = NULL;
					}
				else{
					$ds212 = $desk_start2;
					}
				if (($desk_start2_minutes != '00') && ($desk_start2_minutes != null)){
					$ds212 .= ':'.$desk_start2_minutes;
					}
			
				if ($desk_end2 > 12){
					$de212 = $desk_end2 - 12;
					}
				elseif($desk_end2 == 0){
					$de212 = NULL;
					}
				else{
					$de212 = $desk_end2;
					}
				if (($desk_end2_minutes != '00') && ($desk_end2_minutes != null)){
					$de212 .= ':'.$desk_end2_minutes;
					}

				if ($lunch_start > 12){
					$ls12 = $lunch_start - 12;
					}
				elseif($lunch_start == 0){
					$ls12 = NULL;
					}
				else{
					$ls12 = $lunch_start;
					}
				if (($lunch_start_minutes != '00') && ($lunch_start_minutes != null)){
					$ls12 .= ':'.$lunch_start_minutes;
					}
			
				if ($lunch_end > 12){
					$le12 = $lunch_end - 12;
					}
				elseif($lunch_end == 0){
					$le12 = NULL;
					}
				else{
					$le12 = $lunch_end;
					}
				if (($lunch_end_minutes != '00') && ($lunch_end_minutes != null)){
					$le12 .= ':'.$lunch_end_minutes;
					}					
					
				echo '<td class="shift">';
				if (isset($ss12)){
					echo $ss12 . '-' . $se12;
					}
				if (isset($ds12)){
					echo '<br/><span class="desk">'.$ds12.'-'.$de12;
					if (isset($ds212)){
						echo ', '.$ds212.'-'.$de212;
						}
					echo '</span>';
					}
				else{
					echo '<br/>';
					}
				if (isset($ls12)){
					echo '<br/><span class="lunch">'.$ls12.'-'.$le12.'</span>';
					}
				else{
					echo '<br/>&nbsp;';
					}
				echo '</td>';
					
				//Calculate Hour Totals
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
				$shift_total = $shift_end - $shift_start;
				$lunch_total = $lunch_end - $lunch_start;
				$hr_total += $shift_total;
				$hr_total -= $lunch_total;
				}
			else{
				echo '<td class="shift"></td>';
				}
			}

		echo '<td class="hr_total">'.$hr_total.'</td>';
		echo '<td class="editbutton"><form action="edit_week" method="post">
			<input type="hidden" name="employee_name" value="' . $first_name . ' ' . $last_name . '"/>
			<input type="hidden" name="employee_number" value="' . $empno . '"/>
			<input type="hidden" name="week_type" value="' . $week_type . '"/>
			<input type="hidden" name="season" value="' . $season . '"/>
			<input type="hidden" name="year" value="' . $year . '"/>
			<input type="submit" name="submit" value="Edit" /></form></td>';
		echo '</tr>';
		}
	echo '</table></div>';
	
	//Pages
	$query = "SELECT first_name, last_name, name_dup, employee_number FROM employees WHERE division='$division' 
		and weekly_hours = '15' and active = 'Active'
		ORDER BY division asc, exempt_status asc, weekly_hours desc, first_name asc";
	$result = mysql_query($query);
	$numrows = mysql_num_rows($result);
	if ($numrows != 0){
		echo '<div class="divboxes"><table class="detail" cellspacing="0">
			<tr class="divisions days"><td></td><td class="day"></td><td class="day"></td>
			<td class="day"></td><td class="day"></td><td class="day"></td>
			<td class="day"></td><td class="day"></td></tr>';

		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
			$first_name = $row['first_name'];
			$last_name = $row['last_name'];
			if ($row['name_dup'] == 'Y'){
				$last_initial = substr($last_name,0,1);
				$first_name .= ' ' . $last_initial . '.';
				}	
			$empno = $row['employee_number'];
			$hr_total = 0;
			
			echo '<tr class="divisions emps"><td class="first_name">' . $first_name . '</td>';
			
			foreach ($tables as $tablearray) {
				$tablename = $tablearray[0];
				$assoc_tablename = $tablearray[1];

				$query2 = "SELECT time_format(shift_start,'%k') as shift_start, 
					time_format(shift_start,'%i') as shift_start_minutes, time_format(shift_end,'%k') as shift_end, 
					time_format(shift_end,'%i') as shift_end_minutes, time_format(lunch_start,'%k') as lunch_start, 
					time_format(lunch_start,'%i') as lunch_start_minutes, time_format(lunch_end,'%k') as lunch_end, 
					time_format(lunch_end,'%i') as lunch_end_minutes FROM employees as e, $tablename as t, 
					$assoc_tablename as a where e.employee_number = '$empno' and e.employee_number = a.employee_number 
					and t.row_id = a.row_id";
				$result2 = mysql_query($query2);
				
				if ($result2){
				
					$row2 = mysql_fetch_array($result2, MYSQL_ASSOC);
					
					$shift_start = $row2['shift_start'];
					$shift_start_minutes = $row2['shift_start_minutes'];
					$shift_end = $row2['shift_end'];
					$shift_end_minutes = $row2['shift_end_minutes'];
					$lunch_start = $row2['lunch_start'];
					$lunch_start_minutes = $row2['lunch_start_minutes'];
					$lunch_end = $row2['lunch_end'];
					$lunch_end_minutes = $row2['lunch_end_minutes'];
					
					//Adjust 24-hour time.
					if ($shift_start > 12){
						$ss12 = $shift_start - 12;
						}
					elseif($shift_start == 0){
						$ss12 = NULL;
						}
					else{
						$ss12 = $shift_start;
						}
					if (($shift_start_minutes != '00') && ($shift_start_minutes != null)){
						$ss12 .= ':'.$shift_start_minutes;
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
					if (($shift_end_minutes != '00') && ($shift_end_minutes != null)){
						$se12 .= ':'.$shift_end_minutes;
						}

					if (isset($ss12)){
						echo '<td class="shift">' . $ss12 . '-' . $se12 . '</td>';
						}
					else{
						echo '<td class="shift"></td>';
						}
					}
				else{
					echo '<td class="shift"></td>';
					}
				//Calculate Hour Totals
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
				$shift_total = $shift_end - $shift_start;
				$lunch_total = $lunch_end - $lunch_start;
				$hr_total += $shift_total;
				$hr_total -= $lunch_total;
				}
			echo '<td class="hr_total">'.$hr_total.'</td>';
			echo '<td class="editbutton"><form action="edit_week" method="post">
				<input type="hidden" name="employee_name" value="' . $row['first_name'] . ' ' . $row['last_name'] . '"/>
				<input type="hidden" name="employee_number" value="' . $empno . '"/>
				<input type="hidden" name="week_type" value="' . $week_type . '"/>
				<input type="hidden" name="season" value="' . $season . '"/>
				<input type="hidden" name="year" value="' . $year . '"/>
				<input type="submit" name="submit" value="Edit" /></form></td>';
			echo '</tr>';
			}
		echo '</table></div>';
		}
	echo '</div>';
	}

include ('./includes/footer.html');
?>