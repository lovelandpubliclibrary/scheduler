<?php # add_closures.php

$page_title = "Enter Library Closures";
include('./includes/supersessionstart.php');
include('./includes/allsessionvariables.php');
include ('./includes/header.html');
echo '<div id="mobilehack">';
$mobilehack = 1;
include ('./includes/supersidebar.html');

?>

<script>
$(function(){
	//initially hide the textbox
	$("#other_reason").hide();
	$('#reason').change(function() {
		if($(this).find('option:selected').val() == "Other"){
			$("#other_reason").show();
			}
		else{
			$("#other_reason").hide();
			}
		});
	$('#closures').submit(function() {
		var othersOption = $('#reason').find('option:selected');
		if(othersOption.val() == "Other"){
			// replace select value with text field value
			othersOption.val($("#other_reason").val());
			}
		});
	});
	
$(function(){
	$('input[type="checkbox"]').click(function(){
		$("#closure_hours").toggle();
		});
	});
		
function checkDate() {
	var myDayStr = document.DateForm.closure_day.value;
	var myMonthStr = document.DateForm.closure_month.value-1;
	var myYearStr = document.DateForm.closure_year.value;
	var myMonth = new Array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
	var myDateStr = myMonth[myMonthStr] + ' ' + myDayStr + ', ' +  myYearStr;

/* Using form values, create a new date object
using the setFullYear function */
	var myDate = new Date();
	myDate.setFullYear( myYearStr, myMonthStr, myDayStr );

//Check that date is not too far in the past
	var sendDate = myYearStr + '/' + myMonth[myMonthStr] + '/' + myDayStr;
	sendDate = new Date(Date.parse(sendDate.replace(/-/g,' ')));
	
	today = new Date();
	today.setHours(0,0,0,0);

	if (sendDate < today) {
		alert('The date is in the past. Please pick another date.');
		return false;
		}
	else if ( myDate.getMonth() != myMonthStr ) {
		alert( myDateStr + ' is not a valid date. Please pick another.' );
		return false;
		}
	else {
		return true;
		}
	}

function validator() {
	if (!checkDate()) {return false;}
	else {return true;}
}
</script>
<div class="mobilewrapper_outer">
<div class="mobilewrapper_inner">
<span class="date"><h1>Enter Library Closures</h1></span>

<?php 
$upcoming_closures = array();
$now = date('Y-m-d');
$query = "SELECT * FROM closures WHERE closure_date >= '$now' ORDER BY closure_date ASC";
$result = mysqli_query($dbc, $query);
while ($row = mysqli_fetch_assoc($result)){
	$cdate = $row['closure_date'];
	$cstime = $row['closure_start_time'];
	$cetime = $row['closure_end_time'];
	$creason = $row['closure_reason'];
	$upcoming_closures[] = array($cdate, $cstime, $cetime, $creason);
	}
	
if (isset($_POST['submitted'])){
	list($cd_mon, $cd_day, $cd_yr) = explode('/',$_POST['closure_date']);
	$cd_date = "$cd_yr-$cd_mon-$cd_day";
	
	if (!empty($_POST['closure_start']['hours'])){
		$cs_hr = $_POST['closure_start']['hours'];
		$cs_mn = $_POST['closure_start']['minutes'];
		if ((!is_numeric($cs_hr)) || ((!empty($cs_mn)) && (!is_numeric($cs_mn)))){
			$errors[] = 'Please enter a valid start time.';
			}
		else {
			if ($cs_hr < 7){$cs_hr = $cs_hr+12;}
			if (empty($cs_mn)){
				$cs_mn = '00';
				}
			$cs_time = "$cs_hr:$cs_mn:00";
			}
		}
	else{
		$cs_time = '00:01:00';
		}

	if (!empty($_POST['closure_end']['hours'])){
		$ce_hr = $_POST['closure_end']['hours'];
		$ce_mn = $_POST['closure_end']['minutes'];
		if ((!is_numeric($ce_hr)) || ((!empty($ce_mn)) && (!is_numeric($ce_mn)))){
			$errors[] = 'Please enter a valid end time.';
			}
		else {
			if (empty($ce_mn)){
				$ce_mn = '00';
				}
			if (($ce_hr < $cs_hr)||(($ce_hr == $cs_hr)&&($ce_mn <= $cs_mn))) {
				$ce_hr = $ce_hr+12;
				}
			if ($ce_hr < 7){
				$ce_hr = $ce_hr+12;
				}
			$ce_time = "$ce_hr:$ce_mn:00";
			}
		}
	else {
		$ce_time = '23:59:00';
		}
	if (!empty($_POST['reason'])){
		$cd_reason = escape_data($_POST['reason']);
		}
	else {
		$cd_reason = NULL;
		}
	
	if (empty($errors)) {
		$query = "SELECT * from closures WHERE closure_date='$cd_date'";
		$result = mysqli_query($dbc, $query);
		$num_rows = mysqli_num_rows($result);

		//Prevent duplicate data.
		if ($num_rows == 0){
			$query = "INSERT into closures(closure_date, closure_start_time, closure_end_time, closure_reason) 
				values('$cd_date', '$cs_time', '$ce_time', '$cd_reason')";
			$result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));
			if ($result) {
				echo "<div class=\"message\"><b>Closure entered:</b><br/>$cd_date";
				if ($cd_reason != NULL){
					echo ', '.stripslashes($cd_reason);
					}
				echo "</div>";
				}
			}
		else {
			echo "<div class=\"errormessage\">The closure on $cd_date is already in the database.
				Please enter another.</div>";
			}
		}
	else{
		echo '<div class=\"errormessage\"><b>Error!</div>
		<p class="error">The following error(s) occurred:<br/>';
		foreach ($errors as $msg) {
			echo " - $msg<br/>\n";
			}
		echo '</div>';
		}
	}

?>
<div class="coverform">
	<form action="add_closures" method="post" name="DateForm" id="closures" onsubmit="return validator();">
		<div class="label">Closure Date:</div>
		<div style="margin-bottom:5px;">
			<input class="datepick" id="datepicker1" name="closure_date" placeholder="Click to choose" />
		</div>
		<div>
			<input type="checkbox" name="allday" value="all" checked>All Day
		</div>
		<div id="closure_hours" style="display:none;margin-top:-5px;">
			<p><div class="label time">Start Time:	</div>
			<input type="text" name="closure_start[hours]" maxlength="2" size="1" class="hrs"/><b> : </b>
			<input type="text" name="closure_start[minutes]" maxlength="2" size="3"/></p>
			<p><div class="label time">End Time:</div>
			<input type="text" name="closure_end[hours]" maxlength="2" size="1" class="hrs"/><b> : </b>
			<input type="text" name="closure_end[minutes]" maxlength="2" size="3"/></p>
		</div>
		<div class="label" style="margin-top:15px;">Reason / Holiday:</div>
			<select id="reason" name="reason">
				<option value="New Year's Day">New Year's Day</option>
				<option value="Easter">Easter</option>
				<option value="Memorial Day">Memorial Day</option>
				<option value="Independence Day">Independence Day</option>
				<option value="Labor Day">Labor Day</option>
				<option value="Thanksgiving">Thanksgiving</option>
				<option value="Christmas Day">Christmas Day</option>
				<option value="Other">Other...</option>
			</select><br/>
			
			<input type="text" id="other_reason" name="other_reason" size="25"/>
		<p><input type="submit" name="submit" value="Save" /></p>
			<input type="hidden" name="submitted" value="TRUE" />
	</form>
</div>
<?php if (count($upcoming_closures) > 0){
echo '<div id="timeoff">
	<div class="divspec">Upcoming Closures</div>
	<div class="divboxes">
		<table class="timeoff extras">
			<tr><th>Date</th><th>Times</th><th>Reason</th></tr>';

	foreach ($upcoming_closures as $k=>$v){
		$friendly_date = date('j M Y', strtotime($v[0]));
		echo '<tr><td>'.$friendly_date.'</td>';
		if (($v[1] != '00:01:00')&&($v[2] != '23:59:00')){
			$start = explode(':',$v[1]);
			$start_hr = (int)$start[0];
			$start_mn = (int)$start[1];
			$end = explode(':',$v[2]);
			$end_hr = (int)$end[0];
			$end_mn = (int)$end[1];
			if ($start_hr > 12){
				$ss12 = $start_hr - 12;
				}
			elseif($start_hr == 0){
				$ss12 = NULL;
				}
			else{
				$ss12 = $start_hr;
				}
			if ($start_mn != '00') {
				$ss12 .= ':'.$start_mn;
				}
			
			if ($end_hr > 12){
				$se12 = $end_hr - 12;
				}
			elseif($end_hr == 0){
				$se12 = NULL;
				}
			else{
				$se12 = $end_hr;
				}
			if ($end_mn != '00') {
				$se12 .= ':'.$end_mn;
				}
			echo '<td class="times">'.$ss12.' - '.$se12.'</td>';
			}
		elseif ($v[1] != '00:01:00'){
			$start = explode(':',$v[1]);
			$start_hr = $start[0];
			$start_mn = $start[1];
			if ($start_hr > 12){
				$ss12 = $start_hr - 12;
				}
			elseif($start_hr == 0){
				$ss12 = NULL;
				}
			else{
				$ss12 = $start_hr;
				}
			if ($start_mn != '00') {
				$ss12 .= ':'.$start_mn;
				}
			echo '<td class="times">After '.$ss12.'</td>';
			}
		elseif ($v[2] != '23:59:00'){
			$end = explode(':',$v[2]);
			$end_hr = $end[0];
			$end_mn = $end[1];
			if ($end_hr > 12){
				$se12 = $end_hr - 12;
				}
			elseif($end_hr == 0){
				$se12 = NULL;
				}
			else{
				$se12 = $end_hr;
				}
			if ($end_mn != '00') {
				$se12 .= ':'.$end_mn;
				}
			echo '<td class="times">Until '.$se12.'</td>';
			}
		else {
			echo '<td class="times">All Day</td>';
			}
		echo '<td>'.$v[3].'</td></tr>';
		}

	echo '</table></div></div>';
	}
?>
</div>
</div>
<?php
include ('./includes/footer.html');
?>