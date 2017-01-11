<?php #edit_schedule.php
$page_title="Division Schedule";
include('./includes/supersessionstart.php');
$came_from = $_SESSION['came_from'];
if (($came_from != 'edit_schedule')&&($came_from != 'edit_schedule_dates')&&($came_from != 'schedule_days')){
	unset($_SESSION['schedules_division']);
	}

if (isset($_POST['submitted'])) {
	$_SESSION['schedules_division'] = $_POST['division'];
	header('Location: edit_schedule');
	}
elseif (isset($_SESSION['schedules_division'])) {
	$division = $_SESSION['schedules_division'];
	}

include('./includes/allsessionvariables.php');

if (isset($_POST['edit_dates'])){
	$division = $_POST['division'];
	$schedule_id = $_POST['schedule_id'];
	
	list($ss_mon, $ss_day, $ss_yr) = explode('/',$_POST['schedstart_datepick']);
	$schedstart = "$ss_yr-$ss_mon-$ss_day";
	list($se_mon, $se_day, $se_yr) = explode('/',$_POST['schedend_datepick']);
	$schedend = "$se_yr-$se_mon-$se_day";
	
	$query = "UPDATE schedules set schedule_start_date='$schedstart', schedule_end_date='$schedend' WHERE schedule_id='$schedule_id'";
	$result = mysqli_query($dbc, $query);
	
	//Check for previous schedule overlaps
	$query = "SELECT * from schedules WHERE division='$division' and 
		(schedule_start_date < '$schedstart') and (schedule_end_date > '$schedend')";
	$result = mysqli_query($dbc, $query);
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
		$result1 = mysqli_query($dbc, $query1);
		$query2 = "INSERT into schedules (division, schedule_start_date, schedule_end_date, specific_schedule) 
			values ('$division', '$newstart', '$oldschedend', '$specific_schedule')";
		$result2 = mysqli_query($dbc, $query2);
		}
	$query = "SELECT * from schedules WHERE division='$division' and (schedule_start_date >= '$schedstart') 
		and (schedule_start_date < '$schedend') and (schedule_end_date > '$schedend')";
	$result = mysqli_query($dbc, $query);
	$num_rows = mysql_num_rows($result);
	if ($num_rows != 0){
		while ($row = mysql_fetch_array ($result, MYSQL_ASSOC)) {
			$schedule_id = $row['schedule_id'];
			}
		$newstart = date('Y-m-d', strtotime($schedend.'+1days'));
		$query1 = "UPDATE schedules set schedule_start_date='$newstart' WHERE schedule_id='$schedule_id'";
		$result1 = mysqli_query($dbc, $query1);
		}
	$query = "SELECT * from schedules WHERE division='$division' and (schedule_end_date <= '$schedend') and 
		(schedule_end_date > '$schedstart') and (schedule_start_date < '$schedstart')";
	$result = mysqli_query($dbc, $query);
	$num_rows = mysql_num_rows($result);
	if ($num_rows != 0){
		while ($row = mysql_fetch_array ($result, MYSQL_ASSOC)) {
			$schedule_id = $row['schedule_id'];
			}
		$newend = date('Y-m-d', strtotime($schedstart.'-1days'));
		$query1 = "UPDATE schedules set schedule_end_date='$newend' WHERE schedule_id='$schedule_id'";
		$result1 = mysqli_query($dbc, $query1);
		}
	$_SESSION['schedules_division'] = $division;
	header ('Location: edit_schedule');
	}
	
if(isset($_POST['init'])){
	$division = $_POST['division'];
	$specific_schedule = $_POST['specific_schedule'];
	list($ss_mon, $ss_day, $ss_yr) = explode('/',$_POST['schedstart_datepick']);
	$schedstart = "$ss_yr-$ss_mon-$ss_day";
	list($se_mon, $se_day, $se_yr) = explode('/',$_POST['schedend_datepick']);
	$schedend = "$se_yr-$se_mon-$se_day";
	
	//Check for previous schedule overlaps
	$query = "SELECT * from schedules WHERE division='$division' and (schedule_start_date >= '$schedstart') 
		and (schedule_end_date <= '$schedend')";
	$result = mysqli_query($dbc, $query);
	$num_rows = mysql_num_rows($result);
	if ($num_rows != 0){
		while ($row = mysql_fetch_array ($result, MYSQL_ASSOC)) {
			$schedule_id = $row['schedule_id'];
			}
		$query = "DELETE from schedules WHERE schedule_id='$schedule_id'";
		$result = mysqli_query($dbc, $query);
		}
	$query = "SELECT * from schedules WHERE division='$division' and 
		(schedule_start_date < '$schedstart') and (schedule_end_date > '$schedend')";
	$result = mysqli_query($dbc, $query);
	$num_rows = mysql_num_rows($result);
	if ($num_rows != 0){
		while ($row = mysql_fetch_array ($result, MYSQL_ASSOC)) {
			$schedule_id = $row['schedule_id'];
			$oldschedstart = $row['schedule_start_date'];
			$oldschedend = $row['schedule_end_date'];
			$oldspecific_schedule = $row['specific_schedule'];
			}
		$newstart = date('Y-m-d', strtotime($schedend.'+1days'));
		$newend = date('Y-m-d', strtotime($schedstart.'-1days'));
		$query1 = "UPDATE schedules set schedule_end_date='$newend' WHERE schedule_id='$schedule_id'";
		$result1 = mysqli_query($dbc, $query1);
		$query2 = "INSERT into schedules (division, schedule_start_date, schedule_end_date, specific_schedule) 
			values ('$division', '$newstart', '$oldschedend', '$oldspecific_schedule')";
		$result2 = mysqli_query($dbc, $query2);
		}
	$query = "SELECT * from schedules WHERE division='$division' and (schedule_start_date >= '$schedstart') 
		and (schedule_start_date < '$schedend') and (schedule_end_date > '$schedend')";
	$result = mysqli_query($dbc, $query);
	$num_rows = mysql_num_rows($result);
	if ($num_rows != 0){
		while ($row = mysql_fetch_array ($result, MYSQL_ASSOC)) {
			$schedule_id = $row['schedule_id'];
			}
		$newstart = date('Y-m-d', strtotime($schedend.'+1days'));
		$query1 = "UPDATE schedules set schedule_start_date='$newstart' WHERE schedule_id='$schedule_id'";
		$result1 = mysqli_query($dbc, $query1);
		}
	$query = "SELECT * from schedules WHERE division='$division' and (schedule_end_date <= '$schedend') and 
		(schedule_end_date > '$schedstart') and (schedule_start_date < '$schedstart')";
	$result = mysqli_query($dbc, $query);
	$num_rows = mysql_num_rows($result);
	if ($num_rows != 0){
		while ($row = mysql_fetch_array ($result, MYSQL_ASSOC)) {
			$schedule_id = $row['schedule_id'];
			}
		$newend = date('Y-m-d', strtotime($schedstart.'-1days'));
		$query1 = "UPDATE schedules set schedule_end_date='$newend' WHERE schedule_id='$schedule_id'";
		$result1 = mysqli_query($dbc, $query1);
		}
	
	$max = 0;
	$query = "SELECT MAX(specific_schedule) FROM schedules";
	$result = mysqli_query($dbc, $query);
	while ($row = mysql_fetch_array ($result, MYSQL_NUM)) {
		$max = $row[0];
		}
	$max += 1;
	
	$query = "INSERT into schedules (division, schedule_start_date, schedule_end_date, specific_schedule) 
		values ('$division', '$schedstart', '$schedend', '$max')";
	$result = mysqli_query($dbc, $query);
	
	$query = "INSERT into shifts (week_type, shift_day, emp_id, shift_start, shift_end, desk_start, desk_end, 
				desk_start2, desk_end2, lunch_start, lunch_end, specific_schedule) 
				SELECT week_type, shift_day, emp_id, shift_start, shift_end, desk_start, desk_end, 
				desk_start2, desk_end2, lunch_start, lunch_end, '$max' from shifts where specific_schedule='$specific_schedule'";
	$result = mysqli_query($dbc, $query);
	
	$query = "INSERT into deficiencies (def_schedule, def_week, def_day, def_division, def_start, def_end)
		SELECT '$max', def_week, def_day, def_division, def_start, def_end from deficiencies where def_schedule='$specific_schedule'";
	$result = mysqli_query($dbc, $query);
	
	$_SESSION['schedules_division'] = $division;
	header ('Location: edit_schedule');
	}

include ('./includes/header.html');
echo '<div id="mobilehack">';
$mobilehack = 1;
include ('./includes/supersidebar.html');
$today= date('Y-m-d');

?>
<script>
function deleteSchedule(){
	var agree=confirm("Are you sure you wish to delete this entire schedule?");
	if (agree){
		return true ;
		}
	else {
		return false ;
		}
	}
	
$(document).ready(function(){
	var button;
	var currentForm;
	var sub = false;
	$(function(){
		$('#dialog-confirm').dialog({
			resizable:false,
			modal:true,
			autoOpen:false,
			width:250,
			buttons:{
				'Only this schedule':function(){
					sub = true;
					currentForm.append('<input type="hidden" name="separate" value="true"/>');
					$(this).dialog('close');
					button.trigger("click");
					},
				'All similar upcoming':function(){
					sub = true;
					$(this).dialog('close');
					button.trigger("click");
					},
				'Cancel':function(){
					$(this).dialog('close');
					}
				}
			});
		$('form[data-count="true"] .editing').click(function(){
			button = $(this);
			currentForm = $(this).closest('form');
			$('#dialog-confirm').dialog('open');
			$('.ui-dialog-buttonpane button').blur();
			if(!sub){
				return false;
				}
			else{
				return true;
				}
			});
		$(".ui-dialog-buttonpane").css({"text-align":"left"});
		$(".ui-dialog-buttonpane .ui-dialog-buttonset").css({"float":"none"});
		$(".ui-dialog-buttonpane button").css({"display":"block","margin-left":"10px"});
		$('.ui-widget-overlay').live('click', function() {
			$('#dialog-confirm').dialog( "close" );
			});
		});
	});
</script>
<div id="dialog-confirm" style="display:none;"><b>Editing Multiple Schedules</b><br/><br/>This schedule is currently set for multiple date ranges. Which schedules to edit?</div>
<div class="mobilewrapper_outer">
<div class="mobilewrapper_inner">
<span class="date"><h1>Division Schedules</h1></span>
<?php
if (isset($_POST['delete'])){
	$schedule_id = $_POST['schedule_id'];
	$specific_schedule = $_POST['specific_schedule'];
	$schedstart = $_POST['schedstart'];
	$schedend = $_POST['schedend'];
	$query1 = "DELETE from schedules WHERE schedule_id='$schedule_id'";
	$result1 = mysqli_query($dbc, $query1);
	$query2 = "SELECT * from schedules WHERE specific_schedule='$specific_schedule'";
	$result2 = mysqli_query($dbc, $query2);
	if ($result2){
		$num_rows = mysql_num_rows($result2);
		if ($num_rows == 0) {
			$query3 = "DELETE from shifts WHERE specific_schedule='$specific_schedule'";
			$result3 = mysqli_query($dbc, $query3);
			}
		}
	echo '<div class="message">Schedule for '.$schedstart.' to '.$schedend.' has been deleted.</div>';
	}
?>
<form action="edit_schedule" method="post" name="select_division">
<p class="divform editsched">Division: 
		<select name="division" onchange="this.form.submit();">
			<option value="select" disabled="disabled" selected="selected">- Select -</option>
			<?php foreach ($divisions as $k=>$v){
				echo '<option value="'.$v.'"';
				if (isset($division)){
					if ($division==$v) {
						echo ' selected="selected"';
						}
					}
				echo '>'.$v.'</option>';} ?>
		</select>
		<input type="hidden" name="submitted" value="TRUE" />
	</p>
</form>

<?php
if (isset($division)){
	$past_date = date('Y-m-d', strtotime('-1 year', strtotime($today)));
	
	$_SESSION['schedules_division'] = $division;
	$query = "SELECT * from schedules as s, (SELECT specific_schedule, count(*) as count from schedules 
		WHERE schedule_end_date >= '$past_date' group by specific_schedule) as t
		WHERE division='$division' and schedule_end_date >= '$past_date' and s.specific_schedule=t.specific_schedule
		ORDER BY schedule_end_date desc";
	$result = mysqli_query($dbc, $query) or die(mysql_error());
	if ($result){
		$num_rows = mysql_num_rows($result);
		if ($num_rows != 0) {
			echo '<div class="divboxes"><table class="timeoff editschedule">
				<tr class="headrow"><th>Schedule ID</th><th>Start</th><th>End</th><th></th><th></th><th></th><th></th></tr>';
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
				$schedule_id = $row['schedule_id'];
				$schedule_start_date = $row['schedule_start_date'];
				$schedule_start = date('M', strtotime($schedule_start_date)).' '.date('j', strtotime($schedule_start_date)).', '.date('Y', strtotime($schedule_start_date));
				$schedule_end_date = $row['schedule_end_date'];
				$schedule_end = date('M', strtotime($schedule_end_date)).' '.date('j', strtotime($schedule_end_date)).', '.date('Y', strtotime($schedule_end_date));
				$specific_schedule = $row['specific_schedule'];
				$count = $row['count'];
				echo '<tr><td><span class="mobile" style="float:left;">Schedule #</span>'.$specific_schedule.'</td><td class="scheddate start">'.$schedule_start.'</td><td class="scheddate"><span class="mobile" style="float:left;padding-right:12px;">to</span>'.$schedule_end.'</td>';
				echo '<td class="editbutton"><form action="schedule_days" method="post"';
				if ($count > 1){
					echo 'data-count="true"';
					}
				echo '>
					<input type="hidden" name="schedule_id" value="'.$schedule_id.'"/>
					<input type="hidden" name="schedstart" value="'.$schedule_start_date.'"/>
					<input type="hidden" name="schedend" value="'.$schedule_end_date.'"/>
					<input type="hidden" name="specific_schedule" value="'.$specific_schedule.'"/>
					<input type="hidden" name="division" value="'.$division.'"/>
					<input type="submit" name="submit" value="Edit Schedule" class="editing" /></form></td>';
				echo '<td class="editbutton"><form action="edit_schedule_dates" method="post">
					<input type="hidden" name="schedule_id" value="'.$schedule_id.'"/>
					<input type="hidden" name="schedstart" value="'.$schedule_start_date.'"/>
					<input type="hidden" name="schedend" value="'.$schedule_end_date.'"/>
					<input type="hidden" name="division" value="'.$division.'"/>
					<input type="submit" name="submit" value="Adjust Dates" /></form></td>';
				echo '<td class="editbutton"><form action="copy_schedule" method="post">
					<input type="hidden" name="schedule_id" value="'.$schedule_id.'"/>
					<input type="hidden" name="specific_schedule" value="'.$specific_schedule.'"/>
					<input type="hidden" name="schedstart" value="'.$schedule_start_date.'"/>
					<input type="hidden" name="schedend" value="'.$schedule_end_date.'"/>
					<input type="hidden" name="division" value="'.$division.'"/>
					<input type="submit" name="submit" value="Copy" /></form></td>';
				echo '<td class="editbutton"><form action="edit_schedule" method="post" onsubmit="return deleteSchedule()">
					<input type="hidden" name="schedule_id" value="'.$schedule_id.'"/>
					<input type="hidden" name="specific_schedule" value="'.$specific_schedule.'"/>
					<input type="hidden" name="schedstart" value="'.$schedule_start_date.'"/>
					<input type="hidden" name="schedend" value="'.$schedule_end_date.'"/>
					<input type="hidden" name="delete" value="TRUE" />
					<input type="submit" name="delete" value="Delete" /></form></td></tr>';
				}
			echo '</table></div>';
			}
		else{
			echo '<div class="notimeoff"><p>No current schedules entered for that division.</p></div>';
			}
		}
	}
?>
</div></div>
<?php
include ('./includes/footer.html');
?>
</div>