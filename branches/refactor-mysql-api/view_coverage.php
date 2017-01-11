<?php #view_coverage.php

$page_title = "View All Coverage" ;
include('./includes/supersessionstart.php');

if (isset($_SESSION['came_from'])){
	$came_from = $_SESSION['came_from'];
	}
if (isset($_SESSION['view_past'])){
	unset($_SESSION['view_past']);
	}

if (isset($_POST['division'])) {
	$_SESSION['cov_division'] = $_POST['division'];
	header ('Location: view_coverage');
	}
elseif (isset($_SESSION['cov_division'])){
	$division = $_SESSION['cov_division'];
	}
else{
	}
include('./includes/allsessionvariables.php');
include ('./includes/header.html');
include ('./includes/supersidebar.html');

$today= date('Y-m-d');

?>
<script>
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
echo '<div class="divspec">Upcoming Coverage</div>';

if (($came_from == 'edit_coverage') && (isset($_SESSION['success']))){
	if (isset($_SESSION['coverage_view_div'])){
		$division = $_SESSION['coverage_view_div'];
		}
	$name = $_SESSION['coverage_employee_name'];
	$emp_id = $_SESSION['coverage_emp_id'];
	$tid = $_SESSION['coverage_id'];
	$d = $_SESSION['coverage_date'];
	echo '<div class="message"><b>Coverage by</b> '. $name . ' on ' . $d . ' has been updated.</div>';
	unset($_SESSION['success']);
	}
	
if (isset($_POST['delete'])){
	$emp_id = $_POST['emp_id'];
	$name = $_POST['employee_name'];
	$coverage_id = $_POST['coverage_id'];
	$date = $_POST['date'];
	$query1 = "DELETE from coverage WHERE coverage_id='$coverage_id'";
	$result1 = mysqli_query($dbc, $query1);
	echo '<div class="message"><b>Coverage by</b> '. $name . ' on ' . $date . ' has been deleted.</div>';
	}	
	
echo '<form action="view_coverage" method="post">
	<p class="divform">Covered Division: 
		<select name="division" onchange="this.form.submit();">
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
	$_SESSION['coverage_view_div'] = $division;
	$query = "SELECT first_name, last_name, e.emp_id, coverage_division, coverage_id,
		time_format(coverage_start_time,'%k') as coverage_start, time_format(coverage_start_time,'%i') as coverage_start_minutes, 
		time_format(coverage_end_time,'%k') as coverage_end, time_format(coverage_end_time,'%i') as coverage_end_minutes, 
		coverage_date, coverage_offdesk, coverage_reason
		FROM employees as e, coverage as t
		WHERE coverage_division = '$division' and e.emp_id = t.emp_id and e.active = 'Active' 
		and coverage_date >= '$today'
		ORDER by coverage_date asc, first_name asc";
	$result = mysqli_query($dbc, $query);
	if ($result){
		$num_rows = mysql_num_rows($result);
		if ($num_rows != 0) {
			echo '<div class="divboxes"><table class="coverage">';
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
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
					<td><form action="view_coverage" method="post" onsubmit="return deletecoverage()">
					<input type="hidden" name="emp_id" value="' . $emp_id . '"/>
					<input type="hidden" name="employee_name" value="' . $first_name . ' ' . $last_name . '"/>
					<input type="hidden" name="coverage_id" value="' . $coverage_id . '"/>
					<input type="hidden" name="date" value="' . $cmonth . ' ' . $cday . $cyear . '"/>
					<input type="hidden" name="delete" value="TRUE" />
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
	unset($_SESSION['coverage_view_div']);
	$query = "SELECT first_name, last_name, e.emp_id, coverage_division, coverage_id, 
		time_format(coverage_start_time,'%k') as coverage_start, time_format(coverage_start_time,'%i') as coverage_start_minutes, 
		time_format(coverage_end_time,'%k') as coverage_end, time_format(coverage_end_time,'%i') as coverage_end_minutes, 
		coverage_date, coverage_offdesk, coverage_reason
		FROM employees as e, coverage as t
		WHERE e.emp_id = t.emp_id and e.active = 'Active' 
		and coverage_date >= '$today'
		ORDER by coverage_date asc, first_name asc";
	$result = mysqli_query($dbc, $query);
	if ($result){
		$num_rows = mysql_num_rows($result);
		if ($num_rows != 0) {
			echo '<div class="divboxes"><table class="coverage">';
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
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
					<td><form action="view_coverage" method="post" onsubmit="return deletecoverage()">
					<input type="hidden" name="emp_id" value="' . $emp_id . '"/>
					<input type="hidden" name="employee_name" value="' . $first_name . ' ' . $last_name . '"/>
					<input type="hidden" name="coverage_id" value="' . $coverage_id . '"/>
					<input type="hidden" name="date" value="' . $cmonth . ' ' . $cday . $cyear . '"/>
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