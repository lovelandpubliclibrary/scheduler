<?php #view_timeoff.php

$page_title = "View All Timeoff" ;
include('./includes/supersessionstart.php');

if (isset($_SESSION['came_from'])){
	$came_from = $_SESSION['came_from'];
	}
if (isset($_SESSION['view_past'])){
	unset($_SESSION['view_past']);
	}
	
if (isset($_POST['division'])) {
	$_SESSION['timeoff_division'] = $_POST['division'];
	header ('Location: view_timeoff');
	}
elseif (isset($_SESSION['timeoff_division'])){
	$division = $_SESSION['timeoff_division'];
	}
else{
	}
include('./includes/allsessionvariables.php');
include ('./includes/header.html');
include ('./includes/supersidebar.html');

$today= date('Y-m-d');

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
</script>
<?php
echo '<div class="divspec">Upcoming Timeoff</div>';

if (($came_from == 'edit_timeoff') && (isset($_SESSION['success']))){
	if (isset($_SESSION['timeoff_view_div'])){
		$division = $_SESSION['timeoff_view_div'];
		}
	$name = $_SESSION['timeoff_employee_name'];
	$emp_id = $_SESSION['timeoff_emp_id'];
	$tid = $_SESSION['timeoff_id'];
	$d = $_SESSION['timeoff_date'];
	echo '<div class="message"><b>Timeoff for</b> '. $name . ' starting ' . $d . ' has been updated.</div>';
	unset($_SESSION['success']);
	}
	
if (isset($_POST['delete'])){
	$emp_id = $_POST['emp_id'];
	$name = $_POST['employee_name'];
	$timeoff_id = $_POST['timeoff_id'];
	$date = $_POST['date'];
	$query1 = "DELETE from timeoff WHERE timeoff_id='$timeoff_id'";
	$result1 = mysqli_query($dbc, $query1);
	echo '<div class="message"><b>Timeoff for</b> '. $name . ' starting ' . $date . ' has been deleted.</div>';
	}	
	
echo '<form action="view_timeoff" method="post">
	<p class="divform">Division: 
		<select id="division" name="division" onchange="this.form.submit();">
			<option value="All">All</option>';
foreach ($divisions as $k=>$v){
	echo '<option value="'.$v.'"';
	if (isset($division)){
		if ($division==$v) {echo 'selected="selected"';}
		}
	echo '>'.$v.'</option>';
	}
echo '</select>
		<input type="hidden" name="submitted" value="TRUE" />
	</p>
</form>';

if ((isset($division)) && ($division !== 'All')) {
	$_SESSION['timeoff_view_div'] = $division;
	$query = "SELECT first_name, last_name, e.emp_id, division, t.timeoff_id,
		time_format(timeoff_start_time,'%k') as timeoff_start, 	time_format(timeoff_start_time,'%i') as timeoff_start_minutes, 
		time_format(timeoff_end_time,'%k') as timeoff_end, time_format(timeoff_end_time,'%i') as timeoff_end_minutes, 
		timeoff_start_date, timeoff_end_date
		FROM employees as e, timeoff as t
		WHERE e.division = '$division' and e.emp_id = t.emp_id and e.active = 'Active' and 
		(timeoff_start_date >= '$today' OR (timeoff_start_date < '$today' AND timeoff_end_date >= '$today'))
		ORDER by timeoff_start_date asc, first_name asc";
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
					<td><form action="view_timeoff" method="post" onsubmit="return deleteTimeoff()">
					<input type="hidden" name="emp_id" value="' . $emp_id . '"/>
					<input type="hidden" name="employee_name" value="' . $first_name . ' ' . $last_name . '"/>
					<input type="hidden" name="timeoff_id" value="' . $timeoff_id . '"/>
					<input type="hidden" name="date" value="' . $tosmonth . ' ' . $tosday . $tosyear . '"/>
					<input type="hidden" name="delete" value="TRUE" />
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
	unset($_SESSION['timeoff_view_div']);
	$query = "SELECT first_name, last_name, e.emp_id, division, t.timeoff_id, 
		time_format(timeoff_start_time,'%k') as timeoff_start, time_format(timeoff_start_time,'%i') as timeoff_start_minutes, 
		time_format(timeoff_end_time,'%k') as timeoff_end, time_format(timeoff_end_time,'%i') as timeoff_end_minutes, 
		timeoff_start_date, timeoff_end_date
		FROM employees as e, timeoff as t 
		WHERE e.emp_id = t.emp_id and e.active = 'Active' and 
		(timeoff_start_date >= '$today' OR (timeoff_start_date < '$today' AND timeoff_end_date >= '$today'))
		ORDER by timeoff_start_date asc, first_name asc";
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
					<td><form action="view_timeoff" method="post" onsubmit="return deleteTimeoff()">
					<input type="hidden" name="emp_id" value="' . $emp_id . '"/>
					<input type="hidden" name="employee_name" value="' . $first_name . ' ' . $last_name . '"/>
					<input type="hidden" name="timeoff_id" value="' . $timeoff_id . '"/>
					<input type="hidden" name="date" value="' . $tosmonth . ' ' . $tosday . $tosyear . '"/>
					<input type="hidden" name="delete" value="TRUE" />
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