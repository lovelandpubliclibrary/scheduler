<?php # select_schedule.php

$page_title="Edit Schedule";
include('./includes/supersessionstart.php');

if (isset($_SESSION['came_from'])){
	$came_from = $_SESSION['came_from'];
	}
	
include('./includes/allsessionvariables.php');
include ('./includes/header.html');
echo '<div id="mobilehack">';
$mobilehack = 1;
include ('./includes/supersidebar.html');

date_default_timezone_set('America/Denver');
$start_year = date('Y');
$end_year = date('Y') + 10;

?>

<script>
function validateForm() {
	var z=document.Schedule.division.value;
	var x=document.Schedule.week_type.value;
	var y=document.Schedule.season.value;

	if (z=="select"){
		alert("Please select a division.");
		return false;
		}	
	if (x=="select"){
		alert("Please select a week type.");
		return false;
		}
	if (y=="select") {
		alert("Please select a season.");
		return false;
		}
	}
</script>

<div class="mobilewrapper_outer">
<div class="mobilewrapper_inner">
<span class="date"><h1>Select Schedule</h1></span>

<?php
$parts = explode("?",$came_from); 
$came_from = $parts[0];
if (($came_from == '/scheduler/edit_schedule') && (isset($_SESSION['success']))){
	$division = $_SESSION['edit_schedule_division'];
	$dow = $_SESSION['edit_schedule_dow'];
	$week_type = $_SESSION['edit_schedule_week_type'];
	$season = $_SESSION['edit_schedule_season'];
	$year = $_SESSION['edit_schedule_year'];
	
	echo '<div class="message"><b>Edits submitted for:</b><br/>'.
		ucwords($division).', '.ucwords($dow).', '. ucwords($week_type).' '. ucwords($season).' '.$year.'</div>';
	unset($_SESSION['edit_schedule_division']);
	unset($_SESSION['edit_schedule_dow']);
	unset($_SESSION['edit_schedule_week_type']);
	unset($_SESSION['edit_schedule_season']);
	unset($_SESSION['edit_schedule_year']);
	unset($_SESSION['success']);
	}

?>

<div class="selectform">
<form action="edit_schedule" method="get" name="Schedule" onsubmit="return validateForm();">
	<p><div class="label">Division:</div>
		<select name="division">
			<option value="select" disabled="disabled" selected="selected">- Select -</option>
			<option value="Admin">Administration</option>
			<option value="Adult">Adult Services</option>
			<option value="Children">Children</option>
			<option value="Customer Service">Customer Service</option>
			<option value="LTI">Library Tech & Innovation</option>
			<option value="Tech Services">Tech Services</option>
			<option value="Teen">Teen</option>
		</select>
	</p>
	<p><div class="label">Day:</div> 
		<select name="day">
			<option value="sat">Saturday</option>
			<option value="sun">Sunday</option>
			<option value="mon">Monday</option>
			<option value="tue">Tuesday</option>
			<option value="wed">Wednesday</option>
			<option value="thu">Thursday</option>
			<option value="fri">Friday</option>
		</select>
	</p>
	<p><div class="label">Week:</div>
		<select name="week_type">
			<option value="select" disabled="disabled" selected="selected">- Select -</option>
			<option value="a">A</option>
			<option value="b">B</option>
			<option value="c">C</option>
			<option value="d">D</option>
		</select>
	</p>
	<p><div class="label">Season:</div>
		<select name="season">
			<option value="select" disabled="disabled" selected="selected">- Select -</option>
			<option value="spring">Spring</option>
			<option value="summer">Summer</option>
			<option value="fall">Fall</option>
		</select>
	</p>
	<p><div class="label">Year:</div>  
		<?php
			echo '<select name="year">';
			for ($year = $start_year; $year<=$end_year; $year++) {
				echo "<option value =\"$year\"";
				if ($year == $start_year){
					echo ' selected="selected"';}
			echo ">$year</option>\n";
				}
			echo '</select>';
		?>
	</p>
	<p><input type="submit" name="submit" value="Select" /></p>
	<input type="hidden" name="select_submitted" value="TRUE" />
</form>
</div>
</div>
</div>
<?php
include ('./includes/footer.html');
?>
</div>