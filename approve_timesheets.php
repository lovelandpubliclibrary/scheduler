<?php #approve_timesheets.php

$page_title = "Approve Timesheets" ;
include('./includes/supersessionstart.php');

if (isset($_SESSION['came_from'])){
	$came_from = $_SESSION['came_from'];
	}

include('./includes/allsessionvariables.php');
include ('./includes/header.html');
include ('./includes/supersidebar.html');

$today= date('Y-m-d');
$payperiods = array();
$query = "SELECT pp_id, pp_start_date from pay_periods where pp_start_date<='$today' 
	ORDER BY pp_start_date desc LIMIT 2";
$result = mysql_query($query);

while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
	$pp_id = $row['pp_id'];
	$pp_start_date = $row['pp_start_date'];
	
	$pp_end_date = strtotime('+13days', strtotime($pp_start_date));
	$pp_end_date = date('Y-m-d' , $pp_end_date );
	$payperiods[$pp_id] = array($pp_start_date, $pp_end_date);
	}
ksort($payperiods);

if (isset($_SESSION['timesheet_view_div'])){
	$division = $_SESSION['timesheet_view_div'];
	}
if (isset($_POST['submitted'])) {
	$division = $_POST['division'];
	}

echo '<div class="wideview">
	<span class="date"><h1>'.$page_title.'</h1></span>';

if (($came_from == 'view_emp_timesheet') && (isset($_SESSION['timesheet_approved']))){
	$pp_start_date = $_SESSION['pp_start_date'];
	$employee_name = $_SESSION['employee_name'];
	echo '<div class="message">The timesheet for '.$employee_name.' starting '.$pp_start_date.' has been approved.</div>';
	unset($_SESSION['pp_id']);
	unset($_SESSION['pp_start_date']);
	unset($_SESSION['employee_name']);
	unset($_SESSION['timesheet_approved']);
	}
echo '<form action="approve_timesheets" method="post">
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
	$_SESSION['timesheet_view_div'] = $division;
	
	$query = "SELECT last_name, first_name, employee_number FROM employees WHERE division='$division' and active='Active'
		ORDER BY division ASC, last_name asc";
	$result = mysql_query($query) or die(mysql_error($dbc));
	$num = mysql_num_rows ($result);

	if ($num>0) {
		echo '<div class="divboxes"><table class="approve_timesheets"><tr><th><b>Name</b></th><th><b>Timesheet Start</b></th>
			<th><b>Timesheet End</b></th><th><b>Employee Confirmed</b></th><th><b>Supervisor Approved</b></th><th></th></tr>';
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
			$ln = $row['last_name'];
			$fn = $row['first_name'];
			$empno = $row['employee_number'];
			echo '<tr><td rowspan="2">'.$ln.', '.$fn.'</td>';
			$counter = 0;
			foreach ($payperiods as $pp_id=>$dates){
				$query1 = "SELECT * from timesheet_confirm WHERE employee_number='$empno' and pp_id='$pp_id'";
				$result1 = mysql_query($query1);
				if (($result1)&&(mysql_num_rows($result1) == 1)){
					while($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)){
						$emp_confirm = $row1['employee_confirm'];
						$sup_approve = $row1['supervisor_approve'];
						}
					}
				else{
					$emp_confirm = 'N';
					$sup_approve = 'N';
					}
				if ($counter > 0){
					echo '<tr>';
					}
				echo '<td>'.$dates[0].'</td><td>'.$dates[1].'</td>';
				if ($emp_confirm == 'Y'){
					echo '<td class="confirmed">Employee confirmed</td>';
					}
				else{
					echo '<td class="waiting">Waiting</td>';
					}
				if ($sup_approve == 'Y'){
					echo '<td class="confirmed">Approved</td>';
					}
				else{
					echo '<td class="waiting">Waiting</td>';
					}
				echo '<td><form action="view_emp_timesheet" method="post">
					<input type="hidden" name="employee_name" value="'.$fn.' '.$ln.'"/>
					<input type="hidden" name="employee_number" value="'.$empno.'"/>
					<input type="hidden" name="pp_id" value="'.$pp_id.'"/>
					<input type="hidden" name="pp_start_date" value="'.$dates[0].'"/>
					<input type="submit" name="submit" value="View" />
					</form></td></tr>';
				$counter++;
				}
			}
		echo '</table></div>';
		}
	else {
		echo '<p>No results.</p></div></div>';
		}
	}
else{
	$query = "SELECT last_name, first_name, employee_number FROM employees WHERE active='Active'
		ORDER BY last_name asc";
	$result = mysql_query($query) or die(mysql_error($dbc));
	$num = mysql_num_rows ($result);

	if ($num>0) {
		
		echo '<div class="divboxes"><table class="approve_timesheets"><tr><th><b>Name</b></th><th><b>Timesheet Start</b></th>
			<th><b>Timesheet End</b></th><th><b>Employee Confirmed</b></th><th><b>Supervisor Approved</b></th><th></th></tr>';
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
			$ln = $row['last_name'];
			$fn = $row['first_name'];
			$empno = $row['employee_number'];
			echo '<tr><td rowspan="2">'.$ln.', '.$fn.'</td>';
			$counter = 0;
			foreach ($payperiods as $pp_id=>$dates){
				$query1 = "SELECT * from timesheet_confirm WHERE employee_number='$empno' and pp_id='$pp_id'";
				$result1 = mysql_query($query1);
				if (($result1)&&(mysql_num_rows($result1) == 1)){
					while($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)){
						$emp_confirm = $row1['employee_confirm'];
						$sup_approve = $row1['supervisor_approve'];
						}
					}
				else{
					$emp_confirm = 'N';
					$sup_approve = 'N';
					}
				if ($counter > 0){
					echo '<tr>';
					}
				echo '<td>'.$dates[0].'</td><td>'.$dates[1].'</td>';
				if ($emp_confirm == 'Y'){
					echo '<td class="confirmed">Employee confirmed</td>';
					}
				else{
					echo '<td class="waiting">Waiting</td>';
					}
				if ($sup_approve == 'Y'){
					echo '<td class="confirmed">Approved</td>';
					}
				else{
					echo '<td class="waiting">Waiting</td>';
					}
				echo '<td><form action="view_emp_timesheet" method="post">
					<input type="hidden" name="employee_name" value="'.$fn.' '.$ln.'"/>
					<input type="hidden" name="employee_number" value="'.$empno.'"/>
					<input type="hidden" name="pp_id" value="'.$pp_id.'"/>
					<input type="hidden" name="pp_start_date" value="'.$dates[0].'"/>
					<input type="submit" name="submit" value="View" />
					</form></td></tr>';
				$counter++;
				}
			}
		echo '</table></div>';
		}
	else {
		echo '<p>No results.</p></div></div>';
		}
	}

echo '</div>';
include ('./includes/footer.html');
?>