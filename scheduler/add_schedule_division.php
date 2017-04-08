<?php # add_schedule_division.php
include('./includes/supersessionstart.php');
$came_from = $_SESSION['came_from'];
include('./includes/allsessionvariables.php');

if($came_from != '/scheduler/add_schedule_day'){
	header ('Location: add_schedule_day');
	}

$page_title = "Choose Division";
include ('./includes/header.html');
echo '<div id="mobilehack">';
$mobilehack = 1;
include ('./includes/supersidebar.html');

//Check if the form has been submitted.
if (isset($_POST['submitted'])) {

	require_once ('../mysql_connect_sched.php'); //Connect to the db.
	
	$day=$_POST['day'];
	if (isset($_POST['week_type'])){
		$week_type=$_POST['week_type'];
		}
	else {
		$week_type = 'a';
		}
	if (isset($_POST['season'])){
		$season=$_POST['season'];
		}
	else {
		$season = 'summer';
		}
	$year=$_POST['year'];
	$tablename = strtolower($week_type) . '_' . strtolower($day) . '_' . $year . '_' . strtolower($season);
	$dow = date('l', strtotime($day));
	
	$row = mysql_table_exists($tablename);
	
	if ($row!=($tablename)){
		$query = "CREATE TABLE $tablename like schedule_template";
		$result = mysql_query($query);
		}
		
	if ($season=='fall'){
		$copyyear=$year+1;
		$copytablename = strtolower($week_type) . '_' . strtolower($day) . '_' . $copyyear . '_spring';
		$row = mysql_table_exists($copytablename);
		if ($row!=($copytablename)){
			$query = "CREATE TABLE $copytablename like schedule_template";
			$result = mysql_query($query);
			}
		}		
	}
	?>

<script>
function validateForm() {
	var x=document.Division.division.value;
	if (x=="select"){
		alert("Please select a division.");
		return false;
		}
	}
</script>
<div class="mobilewrapper_outer">
<div class="mobilewrapper_inner">
<span class="date"><h1>Add Schedule</h1></span>

<?php
echo '<div class="editlabel">';
echo '<b>Adding:</b> '.ucwords($dow).', '. ucwords($week_type).' '. ucwords($season).' '.$year.'</div>';
?>

<div class="addform">
<form action="add_schedule" method="post" name="Division" onsubmit="return validateForm();">
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
	<p><input type="submit" name="submit" value="Select" /></p>
	<input type="hidden" name="submitted" value="TRUE" />
	<input type="hidden" name="div_select" value="TRUE" />
	<input type="hidden" name="tablename" value="<?php echo $tablename; ?>" />
	<input type="hidden" name="day" value="<?php echo $day; ?>" />
	<input type="hidden" name="week_type" value="<?php echo $week_type; ?>" />
	<input type="hidden" name="season" value="<?php echo $season; ?>" />
	<input type="hidden" name="year" value="<?php echo $year; ?>" />
	<input type="hidden" name="came_from" value="<?php echo $_SERVER['REQUEST_URI'];?>" />
</form>
</div>
</div>
</div>
<?php
include ('./includes/footer.html');
?>
</div>