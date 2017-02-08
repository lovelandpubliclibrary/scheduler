<?php #view_past.php

$page_title = "View Past Timeoff & Coverage" ;
include('./includes/supersessionstart.php');

if (isset($_SESSION['came_from'])){
	$came_from = $_SESSION['came_from'];
	}

if (!isset($_SESSION['timeoff_div'])){
	$_SESSION['timeoff_div'] = 'All';
	}
if (!isset($_SESSION['coverage_div'])){
	$_SESSION['coverage_div'] = 'All';
	}
	
if (isset($_SESSION['view_past'])){
	unset($_SESSION['view_past']);
	}

if ($came_from == 'view_past'){
	if (isset($_POST['timeoff_division'])){
		$_SESSION['timeoff_div'] = $_POST['timeoff_division'];
		header ('Location: view_past');
		}
	elseif ($_SESSION['timeoff_div'] !== 'All'){
		$timeoff_div = $_SESSION['timeoff_div'];
		}
	else{
		$timeoff_div = 'All';
		}
	}
else{
	if ($_SESSION['timeoff_div'] !== 'All'){
		$timeoff_div = $_SESSION['timeoff_div'];
		}
	else{
		$timeoff_div = 'All';
		}
	}

if ($came_from == 'view_past'){
	if (isset($_POST['coverage_division'])){
		$_SESSION['coverage_div'] = $_POST['coverage_division'];
		header ('Location: view_past');
		}
	elseif ($_SESSION['coverage_div'] !== 'All'){
		$coverage_div = $_SESSION['coverage_div'];
		}
	else{
		$coverage_div = 'All';
		}
	}
else{
	if ($_SESSION['coverage_div'] !== 'All'){
		$coverage_div = $_SESSION['coverage_div'];
		}
	else{
		$coverage_div = 'All';
		}
	}
	
include('./includes/allsessionvariables.php');
include ('./includes/header.html');
include ('./includes/supersidebar.html');

$today= date('Y-m-d');
$minus_21days = strtotime('-22 days',strtotime($today));
$minus_21days = date('Y-m-d',$minus_21days);

?>
<script>
function deleteTimeoff()
{
var agree=confirm("Are you sure you wish to delete?");
if (agree){
	return true ;}
else {
	return false ;}
}
function deletecoverage()
{
var agree=confirm("Are you sure you wish to delete?");
if (agree){
	return true ;}
else {
	return false ;}
}
</script>
<?php

//Timeoff Stuff
echo '<div class="divspec" style="margin-bottom:-20px;">Past Timeoff</div>';

if (($came_from == 'edit_timeoff') && (isset($_SESSION['success']))){
	$name = $_SESSION['timeoff_employee_name'];
	$emp_id = $_SESSION['timeoff_emp_id'];
	$tid = $_SESSION['timeoff_id'];
	$d = $_SESSION['timeoff_date'];
	echo '<div class="message"><b>Timeoff for</b> '. $name . ' starting ' . $d . ' has been updated.</div>';
	unset($_SESSION['success']);
	}
	
if (isset($_POST['timeoff_delete'])){
	$emp_id = $_POST['emp_id'];
	$name = $_POST['employee_name'];
	$timeoff_id = $_POST['timeoff_id'];
	$date = $_POST['date'];
	$ts_date = $_POST['ts_date'];
	
	$query = "SELECT division FROM employees WHERE emp_id='$emp_id'";
	$result = mysqli_query($dbc, $query);
	while ($row = mysqli_fetch_assoc($result)){
		$timeoff_div = $row['division'];
		}
	$query1 = "DELETE from timeoff WHERE timeoff_id='$timeoff_id'";
	$result1 = mysqli_query($dbc, $query1);
	echo '<div class="message"><b>Timeoff for</b> '. $name . ' starting ' . $date . ' has been deleted.</div>';
	}

echo '<form action="view_past" method="post">
	<p class="divform">Division: 
		<select name="timeoff_division" onchange="this.form.submit();">
			<option value="All">All</option>';
foreach ($divisions as $k=>$v){
	echo '<option value="'.$v.'"';
	if (isset($timeoff_div)){
		if ($timeoff_div==$v) {echo 'selected="selected"';}
		}
	echo '>'.$v.'</option>';
	}
echo '</select>
		<input type="hidden" name="submitted" value="TRUE" />
		<input type="hidden" name="timeoff" value="TRUE" />
	</p>
</form>';

if (((isset($_POST['submitted'])) && (isset($_POST['timeoff'])) && ($_POST['timeoff_division'] !== 'All'))
	|| (($_SESSION['timeoff_div'] !== 'All') && (!isset($_POST['timeoff'])))){
	if (isset($_POST['timeoff_division'])){
		$_SESSION['timeoff_div'] = $_POST['timeoff_division'];
		}
	$timeoff_div = $_SESSION['timeoff_div'];

	$query = "SELECT first_name, last_name, e.emp_id, division, timeoff_id,
		time_format(timeoff_start_time,'%k') as timeoff_start, 	time_format(timeoff_start_time,'%i') as timeoff_start_minutes, 
		time_format(timeoff_end_time,'%k') as timeoff_end, time_format(timeoff_end_time,'%i') as timeoff_end_minutes, 
		timeoff_start_date, timeoff_end_date
		FROM employees as e, timeoff as t
		WHERE e.division = '$timeoff_div' and e.emp_id = t.emp_id and e.active = 'Active' 
		and timeoff_start_date > '$minus_21days' and timeoff_start_date <= '$today'
		ORDER by timeoff_start_date desc, first_name asc";
	$result = mysqli_query($dbc, $query);
	if ($result){
		$num_rows = mysqli_num_rows($result);
		if ($num_rows != 0) {
			echo '<div class="divboxes"><table class="timeoff">';
			while ($row = mysqli_fetch_assoc($result)){
				$first_name = $row['first_name'];
				$last_name = $row['last_name'];
				$emp_id = $row['emp_id'];
				$division = $row['division'];
				$timeoff_id = $row['timeoff_id'];
				$timeoff_start_hours = $row['timeoff_start'];
				$timeoff_start_minutes = $row['timeoff_start_minutes'];
				$timeoff_end_hours = $row['timeoff_end'];
				$timeoff_end_minutes = $row['timeoff_end_minutes'];
				$timeoff_start_date = $row['timeoff_start_date'];
				$timeoff_end_date = $row['timeoff_end_date'];
				$ts12 = NULL;
				$te12 = NULL;
				
				//Date specifics
				$tosmonth = date('M', strtotime($timeoff_start_date));
				$tosday = date('j', strtotime($timeoff_start_date));
				if ((date('Y', strtotime($timeoff_start_date))) > date('Y')){
					$tosyear = date('Y', strtotime($timeoff_start_date));
					$tosyear = ', '.$tosyear;
					}
				else {
					$tosyear = NULL;
					}
				$toemonth = date('M', strtotime($timeoff_end_date));
				$toeday = date('j', strtotime($timeoff_end_date));
				if ((date('Y', strtotime($timeoff_start_date))) > date('Y')){
					$toeyear = date('Y', strtotime($timeoff_start_date));
					$toeyear = ', '.$toeyear;
					}
				else {
					$toeyear = NULL;
					}
				
				echo "<tr><td class=\"first_name\">$first_name</td>";
				echo '<td class="division">'.$division.'</td>';
				if ($timeoff_start_date == $timeoff_end_date){
					echo "<td class=\"datetime\"><span class=\"todate\">$tosmonth $tosday$tosyear</span>";
					if (($timeoff_start_hours != '00') && ($timeoff_end_hours != 23)){
						if ($timeoff_start_hours > 12){
							$ts12 = $timeoff_start_hours - 12;
							if ($timeoff_start_minutes != '00') {
								$ts12 .= ':'.$timeoff_start_minutes;
								}
							$te12 = $timeoff_end_hours - 12;
							if ($timeoff_end_minutes != '00') {
								$te12 .= ':'.$timeoff_end_minutes;
								}
							$te12 .= 'pm';
							}
						elseif ($timeoff_start_hours == 12){
							$ts12 = $timeoff_start_hours;
							if ($timeoff_start_minutes != '00') {
								$ts12 .= ':'.$timeoff_start_minutes;
								}
							if ($timeoff_end_hours > 12){
								$te12 = $timeoff_end_hours - 12;
								}
							else{
								$te12 = $timeoff_end_hours;
								}
							if ($timeoff_end_minutes != '00') {
								$te12 .= ':'.$timeoff_end_minutes;
								}
							$te12 .= 'pm';
							}							
						else {
							$ts12 = $timeoff_start_hours;
							if ($timeoff_start_minutes != '00') {
								$ts12 .= ':'.$timeoff_start_minutes;
								}
							$ts12 .= 'am';
							if ($timeoff_end_hours > 12){
								$te12 = $timeoff_end_hours - 12;
								if ($timeoff_end_minutes != '00') {
									$te12 .= ':'.$timeoff_end_minutes;
									}
								$te12 .= 'pm';
								}
							elseif ($timeoff_end_hours == 12){
								$te12 = $timeoff_end_hours;
								if ($timeoff_end_minutes != '00') {
									$te12 .= ':'.$timeoff_end_minutes;
									}
								$te12 .= 'pm';
								}
							else {
								$te12 = $timeoff_end_hours;
								if ($timeoff_end_minutes != '00') {
									$te12 .= ':'.$timeoff_end_minutes;
									}
								$te12 .= 'am';
								}
							}
						echo ", $ts12 - $te12";
						}
					echo "</td>";
					}
				else {
					if ($timeoff_start_hours > 12){
						$ts12 = $timeoff_start_hours - 12;
						if ($timeoff_start_minutes != '00') {
							$ts12 .= ':'.$timeoff_start_minutes;
							}
						$ts12 .= 'pm';
						}
					elseif($timeoff_start_hours == 12){
						$ts12 = $timeoff_start_hours;
						if ($timeoff_start_minutes != '00') {
							$ts12 .= ':'.$timeoff_start_minutes;
							}
						$ts12 .= 'pm';
						}
					elseif($timeoff_start_hours == 0){
						$ts12 = NULL;
						}
					else{
						$ts12 = $timeoff_start_hours;
						if ($timeoff_start_minutes != '00') {
							$ts12 .= ':'.$timeoff_start_minutes;
							}
						$ts12 .= 'am';
						}
					if ($timeoff_end_hours > 12){
						$te12 = $timeoff_end_hours - 12;
						if ($timeoff_end_minutes != '00') {
							$te12 .= ':'.$timeoff_end_minutes;
							}
						$te12 .= 'pm';
						}
					elseif($timeoff_end_hours == 12){
						$te12 = $timeoff_end_hours;
						if ($timeoff_end_minutes != '00') {
							$te12 .= ':'.$timeoff_end_minutes;
							}
						$te12 .= 'pm';
						}
					elseif($timeoff_end_hours == 0){
						$te12 = NULL;
						}
					else{
						$te12 = $timeoff_end_hours;
						if ($timeoff_end_minutes != '00') {
							$te12 .= ':'.$timeoff_end_minutes;
							}
						$te12 .= 'am';
						}
					echo "<td class=\"datetime\"><span class=\"todate\">$tosmonth $tosday$tosyear</span>";
					if ($timeoff_start_hours != '00'){
						echo ", $ts12";
						}
					echo " &ndash; <span class=\"todate\">$toemonth $toeday$toeyear</span>";
					if ($timeoff_end_hours != '23'){
						echo ", $te12";
						}
					}
				echo '<td><form action="edit_timeoff" method="post">
					<input type="hidden" name="emp_id" value="' . $emp_id . '"/>
					<input type="hidden" name="employee_name" value="' . $first_name . ' ' . $last_name . '"/>
					<input type="hidden" name="timeoff_id" value="' . $timeoff_id . '"/>
					<input type="hidden" name="came_from" value="' . $_SERVER['REQUEST_URI'] . '" />
					<input type="hidden" name="date" value="' . $tosmonth . ' ' . $tosday . $tosyear . '"/>
					<input type="hidden" name="from_view" value="TRUE"/>
					<input type="submit" name="submit" value="Edit" /></form></td>
					<td><form action="view_past" method="post" onsubmit="return deleteTimeoff()">
					<input type="hidden" name="emp_id" value="' . $emp_id . '"/>
					<input type="hidden" name="employee_name" value="' . $first_name . ' ' . $last_name . '"/>
					<input type="hidden" name="timeoff_id" value="' . $timeoff_id . '"/>
					<input type="hidden" name="date" value="' . $tosmonth . ' ' . $tosday . $tosyear . '"/>
					<input type="hidden" name="ts_date" value="' . $timeoff_start_date . '"/>
					<input type="hidden" name="timeoff_delete" value="TRUE" />
					<input type="submit" name="delete" value="Delete" /></form>
					</td>';
				echo '</tr>';
				}
			echo '</table></div>';
			}
		else {
			echo '<div class="notimeoff"><p>No timeoff scheduled for that division.</p></div>';
			}
		}
	}
else {
	$_SESSION['timeoff_div'] = 'All';
	$query = "SELECT first_name, last_name, e.emp_id, division, timeoff_id, 
		time_format(timeoff_start_time,'%k') as timeoff_start, time_format(timeoff_start_time,'%i') as timeoff_start_minutes, 
		time_format(timeoff_end_time,'%k') as timeoff_end, time_format(timeoff_end_time,'%i') as timeoff_end_minutes, 
		timeoff_start_date, timeoff_end_date
		FROM employees as e, timeoff as t 
		WHERE e.emp_id = t.emp_id and e.active = 'Active' 
		and timeoff_start_date between '$minus_21days' and '$today'
		ORDER by timeoff_start_date desc, first_name asc";
	$result = mysqli_query($dbc, $query);
	if ($result){
		$num_rows = mysqli_num_rows($result);
		if ($num_rows != 0) {
			echo '<div class="divboxes"><table class="timeoff">';
			while ($row = mysqli_fetch_assoc($result)){
				$first_name = $row['first_name'];
				$last_name = $row['last_name'];
				$emp_id = $row['emp_id'];
				$division = $row['division'];
				$timeoff_id = $row['timeoff_id'];
				$timeoff_start_hours = $row['timeoff_start'];
				$timeoff_start_minutes = $row['timeoff_start_minutes'];
				$timeoff_end_hours = $row['timeoff_end'];
				$timeoff_end_minutes = $row['timeoff_end_minutes'];
				$timeoff_start_date = $row['timeoff_start_date'];
				$timeoff_end_date = $row['timeoff_end_date'];
				$ts12 = NULL;
				$te12 = NULL;
				
				//Date specifics
				$tosmonth = date('M', strtotime($timeoff_start_date));
				$tosday = date('j', strtotime($timeoff_start_date));
				if ((date('Y', strtotime($timeoff_start_date))) > date('Y')){
					$tosyear = date('Y', strtotime($timeoff_start_date));
					$tosyear = ', '.$tosyear;
					}
				else {
					$tosyear = NULL;
					}
				$toemonth = date('M', strtotime($timeoff_end_date));
				$toeday = date('j', strtotime($timeoff_end_date));
				if ((date('Y', strtotime($timeoff_start_date))) > date('Y')){
					$toeyear = date('Y', strtotime($timeoff_start_date));
					$toeyear = ', '.$toeyear;
					}
				else {
					$toeyear = NULL;
					}
				
				echo "<tr><td class=\"first_name\">$first_name</td>";
				echo '<td class="division">'.$division.'</td>';
				if ($timeoff_start_date == $timeoff_end_date){
					echo "<td class=\"datetime\"><span class=\"todate\">$tosmonth $tosday$tosyear</span>";
					if (($timeoff_start_hours != '00') && ($timeoff_end_hours != 23)){
						if ($timeoff_start_hours > 12){
							$ts12 = $timeoff_start_hours - 12;
							if ($timeoff_start_minutes != '00') {
								$ts12 .= ':'.$timeoff_start_minutes;
								}
							$te12 = $timeoff_end_hours - 12;
							if ($timeoff_end_minutes != '00') {
								$te12 .= ':'.$timeoff_end_minutes;
								}
							$te12 .= 'pm';
							}
						elseif ($timeoff_start_hours == 12){
							$ts12 = $timeoff_start_hours;
							if ($timeoff_start_minutes != '00') {
								$ts12 .= ':'.$timeoff_start_minutes;
								}
							if ($timeoff_end_hours > 12){
								$te12 = $timeoff_end_hours - 12;
								}
							else{
								$te12 = $timeoff_end_hours;
								}
							if ($timeoff_end_minutes != '00') {
								$te12 .= ':'.$timeoff_end_minutes;
								}
							$te12 .= 'pm';
							}							
						else {
							$ts12 = $timeoff_start_hours;
							if ($timeoff_start_minutes != '00') {
								$ts12 .= ':'.$timeoff_start_minutes;
								}
							$ts12 .= 'am';
							if ($timeoff_end_hours > 12){
								$te12 = $timeoff_end_hours - 12;
								if ($timeoff_end_minutes != '00') {
									$te12 .= ':'.$timeoff_end_minutes;
									}
								$te12 .= 'pm';
								}
							elseif ($timeoff_end_hours == 12){
								$te12 = $timeoff_end_hours;
								if ($timeoff_end_minutes != '00') {
									$te12 .= ':'.$timeoff_end_minutes;
									}
								$te12 .= 'pm';
								}
							else {
								$te12 = $timeoff_end_hours;
								if ($timeoff_end_minutes != '00') {
									$te12 .= ':'.$timeoff_end_minutes;
									}
								$te12 .= 'am';
								}
							}
						echo ", $ts12 - $te12";
						}
					echo "</td>";
					}
				else {
					if ($timeoff_start_hours > 12){
						$ts12 = $timeoff_start_hours - 12;
						if ($timeoff_start_minutes != '00') {
							$ts12 .= ':'.$timeoff_start_minutes;
							}
						$ts12 .= 'pm';
						}
					elseif($timeoff_start_hours == 12){
						$ts12 = $timeoff_start_hours;
						if ($timeoff_start_minutes != '00') {
							$ts12 .= ':'.$timeoff_start_minutes;
							}
						$ts12 .= 'pm';
						}
					elseif($timeoff_start_hours == 0){
						$ts12 = NULL;
						}
					else{
						$ts12 = $timeoff_start_hours;
						if ($timeoff_start_minutes != '00') {
							$ts12 .= ':'.$timeoff_start_minutes;
							}
						$ts12 .= 'am';
						}
					if ($timeoff_end_hours > 12){
						$te12 = $timeoff_end_hours - 12;
						if ($timeoff_end_minutes != '00') {
							$te12 .= ':'.$timeoff_end_minutes;
							}
						$te12 .= 'pm';
						}
					elseif($timeoff_end_hours == 12){
						$te12 = $timeoff_end_hours;
						if ($timeoff_end_minutes != '00') {
							$te12 .= ':'.$timeoff_end_minutes;
							}
						$te12 .= 'pm';
						}
					elseif($timeoff_end_hours == 0){
						$te12 = NULL;
						}
					else{
						$te12 = $timeoff_end_hours;
						if ($timeoff_end_minutes != '00') {
							$te12 .= ':'.$timeoff_end_minutes;
							}
						$te12 .= 'am';
						}
					echo "<td class=\"datetime\"><span class=\"todate\">$tosmonth $tosday$tosyear</span>";
					if ($timeoff_start_hours != '00'){
						echo ", $ts12";
						}
					echo " &ndash; <span class=\"todate\">$toemonth $toeday$toeyear</span>";
					if ($timeoff_end_hours != '23'){
						echo ", $te12";
						}
					}
				echo '<td><form action="edit_timeoff" method="post">
					<input type="hidden" name="emp_id" value="' . $emp_id . '"/>
					<input type="hidden" name="employee_name" value="' . $first_name . ' ' . $last_name . '"/>
					<input type="hidden" name="timeoff_id" value="' . $timeoff_id . '"/>
					<input type="hidden" name="came_from" value="' . $_SERVER['REQUEST_URI'] . '" />
					<input type="hidden" name="date" value="' . $tosmonth . ' ' . $tosday . $tosyear . '"/>
					<input type="hidden" name="from_view" value="TRUE"/>
					<input type="submit" name="submit" value="Edit" /></form></td>
					<td><form action="view_past" method="post" onsubmit="return deleteTimeoff()">
					<input type="hidden" name="emp_id" value="' . $emp_id . '"/>
					<input type="hidden" name="employee_name" value="' . $first_name . ' ' . $last_name . '"/>
					<input type="hidden" name="timeoff_id" value="' . $timeoff_id . '"/>
					<input type="hidden" name="date" value="' . $tosmonth . ' ' . $tosday . $tosyear . '"/>
					<input type="hidden" name="ts_date" value="' . $timeoff_start_date . '"/>
					<input type="hidden" name="timeoff_delete" value="TRUE" />
					<input type="submit" name="delete" value="Delete" /></form>
					</td>';
				echo '</tr>';
				}
			echo '</table></div>';
			}
		}
	}
	
//Coverage Stuff

echo '<div class="divspec" style="margin-top:40px;margin-bottom:-20px;">Past Coverage</div>';

if (($came_from == 'edit_coverage') && (isset($_SESSION['success']))){
	$name = $_SESSION['coverage_employee_name'];
	$emp_id = $_SESSION['coverage_emp_id'];
	$tid = $_SESSION['coverage_id'];
	$d = $_SESSION['coverage_date'];
	echo '<div class="message"><b>Coverage by</b> '. $name . ' on ' . $d . ' has been updated.</div>';
	unset($_SESSION['success']);
	}
	
if (isset($_POST['coverage_delete'])){
	$emp_id = $_POST['emp_id'];
	$name = $_POST['employee_name'];
	$coverage_id = $_POST['coverage_id'];
	$coverage_division = $_POST['coverage_division'];
	$date = $_POST['date'];
	$cd_date = $_POST['cd_date'];
	
	$query1 = "DELETE from coverage WHERE coverage_id='$coverage_id'";
	$result1 = mysqli_query($dbc, $query1);
	echo '<div class="message"><b>Coverage by</b> '. $name . ' starting ' . $date . ' has been deleted.</div>';
	}	
	
echo '<form action="view_past" method="post">
	<p class="divform">Covered Division: 
		<select name="coverage_division" onchange="this.form.submit();">
			<option value="All">All</option>';
foreach ($divisions as $k=>$v){
	echo '<option value="'.$v.'"';
	if (isset($coverage_div)){
		if ($coverage_div==$v) {echo 'selected="selected"';}
		}
	echo '>'.$v.'</option>';
	}
echo '</select>
		<input type="hidden" name="submitted" value="TRUE" />
		<input type="hidden" name="coverage" value="TRUE" />
	</p>
</form>';
	
if ((isset($_POST['submitted'])) && (isset($_POST['coverage'])) && ($_POST['coverage_division'] !== 'All') 
	|| (($_SESSION['coverage_div'] !== 'All') && (!isset($_POST['coverage'])))){
	if (isset($_POST['coverage_division'])){
		$_SESSION['coverage_div'] = $_POST['coverage_division'];
		}
	$coverage_div = $_SESSION['coverage_div'];

	$query = "SELECT first_name, last_name, e.emp_id, coverage_division, coverage_id,
		time_format(coverage_start_time,'%k') as coverage_start, time_format(coverage_start_time,'%i') as coverage_start_minutes, 
		time_format(coverage_end_time,'%k') as coverage_end, time_format(coverage_end_time,'%i') as coverage_end_minutes, 
		coverage_date, coverage_offdesk, coverage_reason
		FROM employees as e, coverage as t 
		WHERE coverage_division = '$coverage_div' and e.emp_id = t.emp_id and e.active = 'Active' 
		and coverage_date > '$minus_21days' and coverage_date <= '$today'
		ORDER by coverage_date desc, first_name asc";
	$result = mysqli_query($dbc, $query);
	if ($result){
		$num_rows = mysqli_num_rows($result);
		if ($num_rows != 0) {
			echo '<div class="divboxes"><table class="coverage">';
			while ($row = mysqli_fetch_assoc($result)){
				$first_name = $row['first_name'];
				$last_name = $row['last_name'];
				$emp_id = $row['emp_id'];
				$coverage_division = $row['coverage_division'];
				$coverage_id = $row['coverage_id'];
				$coverage_start_hours = $row['coverage_start'];
				$coverage_start_minutes = $row['coverage_start_minutes'];
				$coverage_end_hours = $row['coverage_end'];
				$coverage_end_minutes = $row['coverage_end_minutes'];
				$coverage_date = $row['coverage_date'];
				$coverage_offdesk = $row['coverage_offdesk'];
				$coverage_reason = $row['coverage_reason'];
				$cs12 = NULL;
				$ce12 = NULL;
				
				//Date specifics
				$cmonth = date('M', strtotime($coverage_date));
				$cday = date('j', strtotime($coverage_date));
				if ((date('Y', strtotime($coverage_date))) > date('Y')){
					$cyear = date('Y', strtotime($coverage_date));
					$cyear = ', '.$cyear;
					}
				else {
					$cyear = NULL;
					}
				
				echo "<tr><td class=\"first_name\">$first_name</td>";
				echo '<td class="division">'.$coverage_division.'</td>';
				echo "<td class=\"datetime\"><span class=\"todate\">$cmonth $cday$cyear</span>";
				if (($coverage_start_hours != '00') && ($coverage_end_hours != 23)){
					if ($coverage_start_hours > 12){
						$cs12 = $coverage_start_hours - 12;
						if ($coverage_start_minutes != '00') {
							$cs12 .= ':'.$coverage_start_minutes;
							}
						$ce12 = $coverage_end_hours - 12;
						if ($coverage_end_minutes != '00') {
							$ce12 .= ':'.$coverage_end_minutes;
							}
						$ce12 .= 'pm';
						}
					elseif ($coverage_start_hours == 12){
						$cs12 = $coverage_start_hours;
						if ($coverage_start_minutes != '00') {
							$cs12 .= ':'.$coverage_start_minutes;
							}
						if ($coverage_end_hours > 12){
							$ce12 = $coverage_end_hours - 12;
							}
						else{
							$ce12 = $coverage_end_hours;
							}
						if ($coverage_end_minutes != '00') {
							$ce12 .= ':'.$coverage_end_minutes;
							}
						$ce12 .= 'pm';
						}							
					else {
						$cs12 = $coverage_start_hours;
						if ($coverage_start_minutes != '00') {
							$cs12 .= ':'.$coverage_start_minutes;
							}
						$cs12 .= 'am';
						if ($coverage_end_hours > 12){
							$ce12 = $coverage_end_hours - 12;
							if ($coverage_end_minutes != '00') {
								$ce12 .= ':'.$coverage_end_minutes;
								}
							$ce12 .= 'pm';
							}
						elseif ($coverage_end_hours == 12){
							$ce12 = $coverage_end_hours;
							if ($coverage_end_minutes != '00') {
								$ce12 .= ':'.$coverage_end_minutes;
								}
							$ce12 .= 'pm';
							}
						else {
							$ce12 = $coverage_end_hours;
							if ($coverage_end_minutes != '00') {
								$ce12 .= ':'.$coverage_end_minutes;
								}
							$ce12 .= 'am';
							}
						}
					echo ", $cs12 - $ce12";
					}
				if ($coverage_offdesk == 'Off'){
					echo ', <span class="onoff">Off-Desk';
					if ($coverage_reason != NULL){
						echo ' ('.$coverage_reason.')';
						}
					echo '</span>';
					}
				elseif ($coverage_offdesk == 'Busy'){
					echo ', <span class="onoff">Busy';
					if ($coverage_reason != NULL){
						echo ' ('.$coverage_reason.')';
						}
					echo '</span>';
					}
				echo "</td>";
				
				
				echo '<td><form action="edit_coverage" method="post">
					<input type="hidden" name="emp_id" value="' . $emp_id . '"/>
					<input type="hidden" name="employee_name" value="' . $first_name . ' ' . $last_name . '"/>
					<input type="hidden" name="coverage_id" value="' . $coverage_id . '"/>
					<input type="hidden" name="came_from" value="' . $_SERVER['REQUEST_URI'] . '" />
					<input type="hidden" name="date" value="' . $cmonth . ' ' . $cday . $cyear . '"/>
					<input type="hidden" name="from_view" value="TRUE"/>
					<input type="submit" name="submit" value="Edit" /></form></td>
					<td><form action="view_past" method="post" onsubmit="return deletecoverage()">
					<input type="hidden" name="emp_id" value="' . $emp_id . '"/>
					<input type="hidden" name="employee_name" value="' . $first_name . ' ' . $last_name . '"/>
					<input type="hidden" name="coverage_id" value="' . $coverage_id . '"/>
					<input type="hidden" name="coverage_division" value="' . $coverage_division . '"/>
					<input type="hidden" name="date" value="' . $cmonth . ' ' . $cday . $cyear . '"/>
					<input type="hidden" name="cd_date" value="' . $coverage_date . '"/>
					<input type="hidden" name="coverage_delete" value="TRUE" />
					<input type="submit" name="delete" value="Delete" /></form>
					</td>';
				echo '</tr>';
				}
			echo '</table></div>';
			}
		else {
			echo '<div class="nocoverage"><p>No coverage scheduled for that division.</p></div>';
			}
		}
	}
else {
	$_SESSION['coverage_div'] = 'All';
	$query = "SELECT first_name, last_name, e.emp_id, coverage_division, coverage_id, 
		time_format(coverage_start_time,'%k') as coverage_start, time_format(coverage_start_time,'%i') as coverage_start_minutes, 
		time_format(coverage_end_time,'%k') as coverage_end, time_format(coverage_end_time,'%i') as coverage_end_minutes, 
		coverage_date, coverage_offdesk, coverage_reason
		FROM employees as e, coverage as t
		WHERE e.emp_id = t.emp_id and e.active = 'Active' 
		and coverage_date between '$minus_21days' and '$today'
		ORDER by coverage_date desc, first_name asc";
	$result = mysqli_query($dbc, $query);
	if ($result){
		$num_rows = mysqli_num_rows($result);
		if ($num_rows != 0) {
			echo '<div class="divboxes"><table class="coverage">';
			while ($row = mysqli_fetch_assoc($result)){
				$first_name = $row['first_name'];
				$last_name = $row['last_name'];
				$emp_id = $row['emp_id'];
				$coverage_division = $row['coverage_division'];
				$coverage_id = $row['coverage_id'];
				$coverage_start_hours = $row['coverage_start'];
				$coverage_start_minutes = $row['coverage_start_minutes'];
				$coverage_end_hours = $row['coverage_end'];
				$coverage_end_minutes = $row['coverage_end_minutes'];
				$coverage_date = $row['coverage_date'];
				$coverage_offdesk = $row['coverage_offdesk'];
				$coverage_reason = $row['coverage_reason'];
				$cs12 = NULL;
				$ce12 = NULL;
				
				//Date specifics
				$cmonth = date('M', strtotime($coverage_date));
				$cday = date('j', strtotime($coverage_date));
				if ((date('Y', strtotime($coverage_date))) > date('Y')){
					$cyear = date('Y', strtotime($coverage_date));
					$cyear = ', '.$cyear;
					}
				else {
					$cyear = NULL;
					}
				
				echo "<tr><td class=\"first_name\">$first_name</td>";
				echo '<td class="division">'.$coverage_division.'</td>';
				echo "<td class=\"datetime\"><span class=\"todate\">$cmonth $cday$cyear</span>";
				if (($coverage_start_hours != '00') && ($coverage_end_hours != 23)){
					if ($coverage_start_hours > 12){
						$cs12 = $coverage_start_hours - 12;
						if ($coverage_start_minutes != '00') {
							$cs12 .= ':'.$coverage_start_minutes;
							}
						$ce12 = $coverage_end_hours - 12;
						if ($coverage_end_minutes != '00') {
							$ce12 .= ':'.$coverage_end_minutes;
							}
						$ce12 .= 'pm';
						}
					elseif ($coverage_start_hours == 12){
						$cs12 = $coverage_start_hours;
						if ($coverage_start_minutes != '00') {
							$cs12 .= ':'.$coverage_start_minutes;
							}
						if ($coverage_end_hours > 12){
							$ce12 = $coverage_end_hours - 12;
							}
						else{
							$ce12 = $coverage_end_hours;
							}
						if ($coverage_end_minutes != '00') {
							$ce12 .= ':'.$coverage_end_minutes;
							}
						$ce12 .= 'pm';
						}							
					else {
						$cs12 = $coverage_start_hours;
						if ($coverage_start_minutes != '00') {
							$cs12 .= ':'.$coverage_start_minutes;
							}
						$cs12 .= 'am';
						if ($coverage_end_hours > 12){
							$ce12 = $coverage_end_hours - 12;
							if ($coverage_end_minutes != '00') {
								$ce12 .= ':'.$coverage_end_minutes;
								}
							$ce12 .= 'pm';
							}
						elseif ($coverage_end_hours == 12){
							$ce12 = $coverage_end_hours;
							if ($coverage_end_minutes != '00') {
								$ce12 .= ':'.$coverage_end_minutes;
								}
							$ce12 .= 'pm';
							}
						else {
							$ce12 = $coverage_end_hours;
							if ($coverage_end_minutes != '00') {
								$ce12 .= ':'.$coverage_end_minutes;
								}
							$ce12 .= 'am';
							}
						}
					echo ", $cs12 - $ce12";
					}
				if ($coverage_offdesk == 'Off'){
					echo ', <span class="onoff">Off-Desk';
					if ($coverage_reason != NULL){
						echo ' ('.$coverage_reason.')';
						}
					echo '</span>';
					}
				elseif ($coverage_offdesk == 'Busy'){
					echo ', <span class="onoff">Busy';
					if ($coverage_reason != NULL){
						echo ' ('.$coverage_reason.')';
						}
					echo '</span>';
					}
				echo "</td>";

				echo '<td><form action="edit_coverage" method="post">
					<input type="hidden" name="emp_id" value="' . $emp_id . '"/>
					<input type="hidden" name="employee_name" value="' . $first_name . ' ' . $last_name . '"/>
					<input type="hidden" name="coverage_id" value="' . $coverage_id . '"/>
					<input type="hidden" name="date" value="' . $cmonth . ' ' . $cday . $cyear . '"/>
					<input type="hidden" name="from_view" value="TRUE"/>
					<input type="submit" name="submit" value="Edit" /></form></td>
					<td><form action="view_past" method="post" onsubmit="return deletecoverage()">
					<input type="hidden" name="emp_id" value="' . $emp_id . '"/>
					<input type="hidden" name="employee_name" value="' . $first_name . ' ' . $last_name . '"/>
					<input type="hidden" name="coverage_id" value="' . $coverage_id . '"/>
					<input type="hidden" name="coverage_division" value="' . $coverage_division . '"/>
					<input type="hidden" name="date" value="' . $cmonth . ' ' . $cday . $cyear . '"/>
					<input type="hidden" name="cd_date" value="' . $coverage_date . '"/>
					<input type="hidden" name="coverage_delete" value="TRUE" />
					<input type="submit" name="delete" value="Delete" /></form>
					</td>';
				echo '</tr>';
				}
			echo '</table></div>';
			}
		}
	}

include ('./includes/footer.html');
?>