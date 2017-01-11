<?php # edit_timeoff.php

include('./includes/supersessionstart.php');
$came_from = $_SESSION['came_from'];

if ($came_from == 'view_past'){
	$_SESSION['view_past'] = TRUE;
	}

if (($came_from != 'view_timeoff') && ($came_from != 'edit_timeoff') && ($came_from != 'view_past')){
	header ('Location: view_timeoff');
	}

include('./includes/allsessionvariables.php');

if (isset($_POST['from_view'])){
	$_SESSION['timeoff_employee_name'] = $_POST['employee_name'];
	$_SESSION['timeoff_emp_id'] = $_POST['emp_id'];
	$_SESSION['timeoff_date'] = $_POST['date'];
	$_SESSION['timeoff_id'] = $_POST['timeoff_id'];
	header('Location:edit_timeoff');
	}
else{
	$employee = $_SESSION['timeoff_employee_name'];
	$emp_id = $_SESSION['timeoff_emp_id'];
	$date = $_SESSION['timeoff_date'];
	$timeoff_id = $_SESSION['timeoff_id'] ;
	}

$query = "SELECT first_name, last_name, e.emp_id, timeoff_id,
		time_format(timeoff_start_time,'%k') as timeoff_start, 	time_format(timeoff_start_time,'%i') as timeoff_start_minutes, 
		time_format(timeoff_end_time,'%k') as timeoff_end, time_format(timeoff_end_time,'%i') as timeoff_end_minutes, 
		timeoff_start_date, timeoff_end_date, timeoff_reason
		FROM employees as e, timeoff as t
		WHERE e.emp_id = t.emp_id and timeoff_id = $timeoff_id";
$result = mysqli_query($dbc, $query);

while ($row = mysql_fetch_array ($result, MYSQL_ASSOC)) {
	$first_name = $row['first_name'];
	$last_name = $row['last_name'];
	$emp_id = $row['emp_id'];
	$timeoff_id = $row['timeoff_id'];
	$timeoff_start_hours = $row['timeoff_start'];
	$timeoff_start_minutes = $row['timeoff_start_minutes'];
	$timeoff_end_hours = $row['timeoff_end'];
	$timeoff_end_minutes = $row['timeoff_end_minutes'];
	$tsd = $row['timeoff_start_date'];
	$tsd = explode("-", $tsd);
	$ted = $row['timeoff_end_date'];
	$ted = explode("-", $ted);
	$timeoff_reason = $row['timeoff_reason'];
	
	if ($timeoff_start_hours != '00'){
		if ($timeoff_start_hours > 12){
			$ts_hr = $timeoff_start_hours-12;
			$ts_mn = $timeoff_start_minutes;
			}
		else{
			$ts_hr = $timeoff_start_hours;
			$ts_mn = $timeoff_start_minutes;
			}
		}
	else {
		$ts_hr = null;
		$ts_mn = null;
		}
		
	if ($timeoff_end_hours != '23'){
		if ($timeoff_end_hours > 12){
			$te_hr = $timeoff_end_hours-12;
			$te_mn = $timeoff_end_minutes;
			}
		else{
			$te_hr = $timeoff_end_hours;
			$te_mn = $timeoff_end_minutes;
			}
		}
	else {
		$te_hr = null;
		$te_mn = null;
		}
	}
		
	//Check if the form has been submitted.
if (isset($_POST['edited'])) {
		
	$errors = array(); //Initialize error array.
	
	list($ts_mon, $ts_day, $ts_yr) = explode('/',$_POST['timeoff_start_date']);
	$ts_date = "$ts_yr-$ts_mon-$ts_day";
	
	$tmonth = date('M', strtotime($ts_date));
	$tday = date('j', strtotime($ts_date));
	if ((date('Y', strtotime($ts_date))) > date('Y')){
		$tyear = date('Y', strtotime($ts_date));
		$tyear = ', '.$tyear;
		}
	else {
		$tyear = NULL;
		}
	$tdate = $tmonth . ' ' . $tday . $tyear;
	
	if ($_SESSION['timeoff_date'] != $tdate){
		$_SESSION['timeoff_date'] = $tdate;
		}
	
	$ts_hr = $_POST['timeoff_start']['hours'];
	$ts_mn = $_POST['timeoff_start']['minutes'];
	if (!empty($ts_hr)){
		if ((!is_numeric($ts_hr)) || ((!empty($ts_mn)) && (!is_numeric($ts_mn)))){
			$errors[] = 'Please enter a valid start time.';
			}
		else {
			if ($ts_hr < 7){$ts_hr = $ts_hr+12;}
			if (empty($ts_mn)){
				$ts_mn = '00';
				}
			$ts_time = "$ts_hr:$ts_mn:00";
			}
		}
	else{
		$ts_time = '00:01:00';
		}
	
	list($te_mon, $te_day, $te_yr) = explode('/',$_POST['timeoff_end_date']);
	$te_date = "$te_yr-$te_mon-$te_day";
	
	$te_hr = $_POST['timeoff_end']['hours'];
	$te_mn = $_POST['timeoff_end']['minutes'];
	if (!empty($te_hr)){
		if ((!is_numeric($te_hr)) || ((!empty($te_mn)) && (!is_numeric($te_mn)))){
			$errors[] = 'Please enter a valid end time.';
			}
		else {
			if (empty($te_mn)){
				$te_mn = '00';
				}
			if (strtotime($ts_date) == strtotime($te_date)){
				if (($te_hr < $ts_hr)||(($ts_hr == $te_hr)&&($te_mn <= $ts_mn))) {$te_hr = $te_hr+12;}
				}
			if ($te_hr < 7){$te_hr = $te_hr+12;}
			$te_time = "$te_hr:$te_mn:00";
			}
		}
	else{
		$te_time = '23:59:00';
		}
	
	//Check for Timeoff Reason.
	if (empty($_POST['reason'])) {
		$errors[] = 'Please enter timeoff reason.';
		}
	else {
		$reason = escape_data($_POST['reason']);
		}
	
	if (empty($errors)) {
		$query = "UPDATE timeoff SET timeoff_start_date='$ts_date', timeoff_start_time='$ts_time', 
			timeoff_end_date='$te_date', timeoff_end_time='$te_time', timeoff_reason='$reason'
			WHERE timeoff_id = '$timeoff_id'";
		$result = mysqli_query($dbc, $query) or die(mysql_error($dbc));
		if ($result){
			if (isset($_SESSION['view_past'])){
				$query = "SELECT division FROM employees WHERE emp_id='$emp_id'";
				$result = mysqli_query($dbc, $query) or die(mysql_error($dbc));
				while ($row = mysql_fetch_array ($result, MYSQL_ASSOC)){
					$timeoff_div = $row['division'];
					}

				$_SESSION['success'] = TRUE;
				header ('Location: view_past');
				echo '<div class="message"><b>'. $_POST['employee_full_name'] . '</b> has been updated.<br/>
					To edit another shift <a href="view_timeoff">click here</a></div></div>';
				include ('./includes/footer.html');
				exit();
				}
			else{
				$_SESSION['success'] = TRUE;
				header ('Location: view_timeoff');
				echo '<div class="message"><b>'. $_POST['employee_full_name'] . '</b> has been updated.<br/>
					To edit another shift <a href="view_timeoff">click here</a></div></div>';
				include ('./includes/footer.html');
				exit();
				}
			}
		else {
			$page_title = "Edit Timeoff" ;
			include('./includes/supersessionstart.php');
			include ('./includes/header.html');
			include ('./includes/supersidebar.html');	
			echo '<div class="errormessage"><h3>System Error</h3>
				The employee could not be added due to a system error.
				We apologize for the inconvenience.</div>';
			include ('./includes/footer.html');
			exit();
			}
		}
	else {
		echo '<div class="errormessage"><h3>Error!</h3><br/>
		The following error(s) occurred:<br/><br/>';
		foreach ($errors as $msg) { //Print each error
			echo " - $msg<br/>\n";
			}
		echo '</div>';
		}
	mysql_close();
	}

$page_title = "Edit Timeoff" ;
include ('./includes/header.html');
include ('./includes/supersidebar.html');	

?>
<script>
function checkDate() {
	var myOrigDate_start = document.DateForm.timeoff_start_date.value.split('/');
	var myDayStr_start = myOrigDate_start[1];
	var myMonthStr_start = myOrigDate_start[0]-1;
	var myYearStr_start = myOrigDate_start[2];
	var myMonth = new Array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
	var myDateStr_start = myMonth[myMonthStr_start] + ' ' + myDayStr_start + ', ' +  myYearStr_start;

	var myOrigDate_end = document.DateForm.timeoff_end_date.value.split('/');
	var myDayStr_end = myOrigDate_end[1];
	var myMonthStr_end = myOrigDate_end[0]-1;
	var myYearStr_end = myOrigDate_end[2];
	var myDateStr_end = myMonth[myMonthStr_end] + ' ' + myDayStr_end + ', ' +  myYearStr_end;

/* Using form values, create a new date object
using the setFullYear function */
	var myDate_start = new Date();
	myDate_start.setFullYear( myYearStr_start, myMonthStr_start, myDayStr_start );

	var myDate_end = new Date();
	myDate_end.setFullYear( myYearStr_end, myMonthStr_end, myDayStr_end );

//Check that date is not in the past
	var sendDate_start = myYearStr_start + '/' + myMonth[myMonthStr_start] + '/' + myDayStr_start;
	sendDate_start = new Date(Date.parse(sendDate_start.replace(/-/g,' ')));
	
	var sendDate_end = myYearStr_end + '/' + myMonth[myMonthStr_end] + '/' + myDayStr_end;
	sendDate_end = new Date(Date.parse(sendDate_end.replace(/-/g,' ')));
	
	today = new Date();
	today.setHours(0,0,0,0);
	
	function subtracting_days(date, days) {
		return new Date(
			date.getFullYear(), 
			date.getMonth(), 
			date.getDate() - days,
			date.getHours(),
			date.getMinutes(),
			date.getSeconds(),
			date.getMilliseconds()
			);
		}
	var past_date = subtracting_days(today,22);

	if (myOrigDate_start=="" || myOrigDate_start==null){
		alert('Please select a start date.');
		return false;
		}
	else if (myOrigDate_end=="" || myOrigDate_end==null){
		alert('Please select a end date.');
		return false;
		}
	else if (sendDate_start < past_date) {
		alert('The start date is too far in the past. Please pick another date.');
		return false;
		}
	else if (sendDate_end < past_date) {
		alert('The end date is too far in the past. Please pick another date.');
		return false;
		}
	else if (sendDate_end < sendDate_start) {
		alert('The end date must be after the start date.');
		return false;
		}
	else if ( myDate_start.getMonth() != myMonthStr_start ) {
		alert( myDateStr_start + ' is not a valid start date. Please pick another.' );
		return false;
		}
	else if ( myDate_end.getMonth() != myMonthStr_end ) {
		alert( myDateStr_end + ' is not a valid end date. Please pick another.' );
		return false;
		}
	else {
		return true;
		}
	}

function validateForm() {
	var x=document.DateForm.division.value;
	var y=document.DateForm.name.value;
	var zz=document.DateForm.reason.value;
	var za=document.DateForm.elements['timeoff_start[hours]'].value;
	var za_mn=document.DateForm.elements['timeoff_start[minutes]'].value;
	var zb=document.DateForm.elements['timeoff_end[hours]'].value;
	var zb_mn=document.DateForm.elements['timeoff_end[minutes]'].value;
	if (za){
		if (za_mn){
			var ts=za+':'+za_mn+':00';
			}
		else {
			var ts=za+':00:00';
			}
		}
	else {
		ts='00:00:00';
		}
	if (zb){
		if (zb_mn){
			var te=zb+':'+zb_mn+':00';
			}
		else {
			var te=zb+':00:00';
			}
		}
	else {
		te='00:00:00';
		}
		
	var myOrigDate_start = document.DateForm.timeoff_start_date.value.split('/');
	var myDayStr_start = myOrigDate_start[1];
	var myMonthStr_start = myOrigDate_start[0]-1;
	var myYearStr_start = myOrigDate_start[2];
	var myMonth = new Array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
	var myDateStr_start = myMonth[myMonthStr_start] + ' ' + myDayStr_start + ', ' +  myYearStr_start;

	var myOrigDate_end = document.DateForm.timeoff_end_date.value.split('/');
	var myDayStr_end = myOrigDate_end[1];
	var myMonthStr_end = myOrigDate_end[0]-1;
	var myYearStr_end = myOrigDate_end[2];
	var myDateStr_end = myMonth[myMonthStr_end] + ' ' + myDayStr_end + ', ' +  myYearStr_end;
		
	if (x=="0"){
		alert("Please select a division.");
		return false;
		}
	else if (y=="0") {
		alert("Please select an employee.");
		return false;
		}
	else if (zz==null || zz=="") {
		alert("Please add a timeoff reason.");
		return false;
		}
	else if ((za && za!=parseInt(za)) || (za_mn && za_mn!=parseInt(za_mn))) {
		alert("Please enter a valid timeoff start time.");
		return false;
		}
	else if ((zb && zb!=parseInt(zb)) || (zb_mn && zb_mn!=parseInt(zb_mn))) {
		alert("Please enter a valid timeoff end time.");
		return false;
		}
	else if ((myDateStr_start==myDateStr_end) && (te < ts)){
		alert("Shift end must be after shift start.");
		return false;
		}
	else {return true;}
	}
	
function validator() {
	if (!checkDate()) {return false;}
	else if (!validateForm()) {return false;}
	else {return true;}
}
</script>

<div id="edit_timeoff"><span class="date"><h1>Edit Timeoff</h1></span>
<div class="timeoffform">
	<div class="dp">Edit Timeoff for</div>
	<div class="emph"><?php echo $employee; ?></div><br/>

	<form action="edit_timeoff" method="post" name="DateForm" 
		onsubmit="return validator();">
	<div class="label">Time Off Start:</div>
	<div class="cal">
		<input class="datepick" id="datepicker1" name="timeoff_start_date" placeholder="Click to choose" 
		value="<?php echo $tsd[1].'/'.$tsd[2].'/'.$tsd[0];?>"/>
	</div>
		<div class="times">
		<input type="text" name="timeoff_start[hours]" value="<?php echo $ts_hr;?>" maxlength="2" size="1" class="hrs"/><b> : </b>
		<input type="text" name="timeoff_start[minutes]" value="<?php echo $ts_mn;?>" maxlength="2" size="3"/>
		</div><br/>
	<div class="label">Time Off End:</div>
	<div class="cal">
		<input class="datepick" id="datepicker2" name="timeoff_end_date" placeholder="Click to choose" 
		value="<?php echo $ted[1].'/'.$ted[2].'/'.$ted[0];?>"/>
	</div>	
		<div class="times">
		<input type="text" name="timeoff_end[hours]" value="<?php echo $te_hr;?>" maxlength="2" size="1" class="hrs"/><b> : </b>
		<input type="text" name="timeoff_end[minutes]" value="<?php echo $te_mn;?>" maxlength="2" size="3"/>
		</div><br/>
	<div class="label">Reason:</div>
		<input type="text" name="reason" value="<?php echo $timeoff_reason;?>" size="35"/><br/>
		<p><input type="submit" name="submit" value="Save" /></p>
		<input type="hidden" name="edited" value="TRUE" />
		<input type="hidden" name="employee_full_name" value="<?php echo $employee; ?>" />
		<input type="hidden" name="timeoff_id" value="<?php echo $timeoff_id; ?>" />
		<input type="hidden" name="came_from" value="<?php echo $_POST['came_from'];?>" />
	</form>
</div></div>
		
<?php
include ('./includes/footer.html');
?>