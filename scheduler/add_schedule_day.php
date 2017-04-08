<?php # add_schedule_day.php

$page_title="Add Schedule";
include('./includes/supersessionstart.php');
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
	var x=document.Day.week_type.value;
	var y=document.Day.season.value;

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
<span class="date"><h1>Add Schedule</h1></span>

<div class="addform">
<form action="add_schedule_division" method="post" name="Day" onsubmit="return validateForm();">
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
			echo '</select></p>';
		?>
	<p><input type="submit" name="submit" value="Select" /></p>
	<input type="hidden" name="submitted" value="TRUE" />
</form></div></div></div>
<?php
include ('./includes/footer.html');
?>
</div>