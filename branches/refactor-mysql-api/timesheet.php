<?php #timesheet.php
include('./includes/sessionstart.php');
$came_from = $_SESSION['came_from'];
include('./includes/allsessionvariables.php');

$page_title = 'Confirm Timesheets';

include ('./includes/header.html');
include ('./includes/sidebar.html');

echo '<div class="mobilewrapper_outer"><div class="mobilewrapper_inner">
	<span class="date"><h1>'.$page_title.'</h1></span>';
	
if (isset($this_emp_id)){
	if (($came_from == 'edit_my_timesheet') && (isset($_SESSION['timesheet_confirmed']))){
		$pp_start_date = $_SESSION['pp_start_date'];
		echo '<div class="message">Your timesheet starting '.$pp_start_date.' has been edited.</div>';
		unset($_SESSION['timesheet_confirmed']);
		}
	echo '<p class="divform" style="margin-bottom:-10px;font-size:14px;color:#013953;font-weight:bold;">Current Timesheets</p>';
	echo '<table class="confirming"><tr><th>Start</th><th>End</th><th></th><th></th></tr>';	
		
	$today = date('Y-m-d');
	$query = "SELECT * from pay_periods where pp_start_date<='$today' 
		ORDER BY pp_start_date desc LIMIT 2";
	$result = mysqli_query($dbc, $query);

	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
		$pp_id = $row['pp_id'];
		$pp_cycle = $row['pp_cycle'];
		$pp_start_date = $row['pp_start_date'];
		$pp_year = $row['pp_year'];
		
		$pp_end_date = strtotime('+13days', strtotime($pp_start_date));
		$pp_end_date = date('Y-m-d' , $pp_end_date );
		
		echo '<tr><td class="scheddate">'.$pp_start_date.'</td><td class="scheddate">'.$pp_end_date.'</td>';
		
		$query1 = "SELECT * from timesheet_confirm WHERE pp_id = '$pp_id' and emp_id = '$this_emp_id' 
			and assignment_id = '$this_assignment_id'";
		$result1 = mysqli_query($dbc, $query1);
		if (($result1) && (mysql_num_rows($result1)!=0)){
			echo '<td class="scheddate confirmed">Timesheet confirmed</td>';
			$row = mysql_fetch_array($result1, MYSQL_ASSOC);
			if ($row['supervisor_approve'] == 'Y'){
				echo '<td class="locked">Locked</td><td><form action="view_my_timesheet" method="post">
				<input type="hidden" name="pp_id" value="'.$pp_id.'"/>
				<input type="hidden" name="pp_start_date" value="'.$pp_start_date.'"/>
				<input type="submit" name="submit" value="View" /></form></td>';
				}
			else{
				echo '<td><form action="edit_my_timesheet" method="post">
				<input type="hidden" name="pp_id" value="'.$pp_id.'"/>
				<input type="hidden" name="pp_start_date" value="'.$pp_start_date.'"/>
				<input type="submit" name="submit" value="Edit" /></form></td><td></td>';
				}
			}
		else{
			echo '<td><form action="edit_my_timesheet" method="post">
				<input type="hidden" name="pp_id" value="'.$pp_id.'"/>
				<input type="hidden" name="pp_start_date" value="'.$pp_start_date.'"/>
				<input type="submit" name="submit" value="Confirm Timesheet" /></form></td><td></td><td></td>';
			}
		}
	echo '</table>';
	}
else{
	echo '';
	}
echo '</div></div>';
include ('./includes/footer.html');

?>