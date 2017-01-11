<?php # edit_coverage.php

include('./includes/supersessionstart.php');
$came_from = $_SESSION['came_from'];

if ($came_from == 'view_past'){
	$_SESSION['view_past'] = TRUE;
	}
	
if (($came_from != 'view_coverage') && ($came_from != 'edit_coverage') && ($came_from != 'view_past')){
	header ('Location: view_coverage');
	}

include('./includes/allsessionvariables.php');

if (isset($_POST['from_view'])){
	$_SESSION['coverage_employee_name'] = $_POST['employee_name'];
	$_SESSION['coverage_emp_id'] = $_POST['emp_id'];
	$_SESSION['coverage_date'] = $_POST['date'];
	$_SESSION['coverage_id'] = $_POST['coverage_id'];
	header('Location:edit_coverage');
	}
else{
	$employee = $_SESSION['coverage_employee_name'];
	$emp_id = $_SESSION['coverage_emp_id'];
	$date = $_SESSION['coverage_date'];
	$coverage_id = $_SESSION['coverage_id'];
	}

$query = "SELECT first_name, last_name, e.emp_id, coverage_id,
		time_format(coverage_start_time,'%k') as coverage_start, time_format(coverage_start_time,'%i') as coverage_start_minutes, 
		time_format(coverage_end_time,'%k') as coverage_end, time_format(coverage_end_time,'%i') as coverage_end_minutes, 
		coverage_date, coverage_division, coverage_offdesk, coverage_reason
		FROM employees as e, coverage as t 
		WHERE e.emp_id = t.emp_id and coverage_id = $coverage_id";
$result = mysqli_query($dbc, $query);

while ($row = mysql_fetch_array ($result, MYSQL_ASSOC)) {
	$first_name = $row['first_name'];
	$last_name = $row['last_name'];
	$emp_id = $row['emp_id'];
	$coverage_id = $row['coverage_id'];
	$coverage_start_hours = $row['coverage_start'];
	$coverage_start_minutes = $row['coverage_start_minutes'];
	$coverage_end_hours = $row['coverage_end'];
	$coverage_end_minutes = $row['coverage_end_minutes'];
	$coverage_division = $row['coverage_division'];
	$coverage_offdesk = $row['coverage_offdesk'];
	$coverage_reason = $row['coverage_reason'];
	$cd = $row['coverage_date'];
	$cd = explode("-", $cd);
	
	if ($coverage_start_hours != '00'){
		if ($coverage_start_hours > 12){
			$cs_hr = $coverage_start_hours-12;
			$cs_mn = $coverage_start_minutes;
			}
		else{
			$cs_hr = $coverage_start_hours;
			$cs_mn = $coverage_start_minutes;
			}
		}
	else {
		$cs_hr = null;
		$cs_mn = null;
		}
		
	if ($coverage_end_hours != '23'){
		if ($coverage_end_hours > 12){
			$ce_hr = $coverage_end_hours-12;
			$ce_mn = $coverage_end_minutes;
			}
		else{
			$ce_hr = $coverage_end_hours;
			$ce_mn = $coverage_end_minutes;
			}
		}
	else {
		$ce_hr = null;
		$ce_mn = null;
		}
	}
		
	//Check if the form has been submitted.
if (isset($_POST['edited'])) {
		
	$errors = array(); //Initialize error array.
	
	$cd_onoff = $_POST['onoff'];
	if (($cd_onoff != 'On')&&(!empty($_POST['reason']))){
		$cd_reason = $_POST['reason'];
		}
	else{
		$cd_reason = NULL;
		}
	
	$cd_div = $_POST['coverage_division'];
	list($cd_mon, $cd_day, $cd_yr) = explode('/',$_POST['coverage_date']);
	$cd_date = "$cd_yr-$cd_mon-$cd_day";
	
	$cmonth = date('M', strtotime($cd_date));
	$cday = date('j', strtotime($cd_date));
	if ((date('Y', strtotime($cd_date))) > date('Y')){
		$cyear = date('Y', strtotime($cd_date));
		$cyear = ', '.$cyear;
		}
	else {
		$cyear = NULL;
		}
	$cdate = $cmonth . ' ' . $cday . $cyear;
	
	if ($_SESSION['coverage_date'] != $cdate){
		$_SESSION['coverage_date'] = $cdate;
		}
	
	if (strtotime($cd_date) < strtotime('-22 days',strtotime(date('Y-m-d')))){
		$errors[] = 'The date is too far in the past. Please pick another date.';
		}
	
	$cs_hr = $_POST['coverage_start']['hours'];
	$cs_mn = $_POST['coverage_start']['minutes'];
	if (!empty($cs_hr)){
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
		$errors[] = 'Please enter a start time for this coverage shift.';
		}
	
	$ce_hr = $_POST['coverage_end']['hours'];
	$ce_mn = $_POST['coverage_end']['minutes'];
	if (!empty($ce_hr)){
		if ((!is_numeric($ce_hr)) || ((!empty($ce_mn)) && (!is_numeric($ce_mn)))){
			$errors[] = 'Please enter a valid end time.';
			}
		else {
			if (empty($ce_mn)){
				$ce_mn = '00';
				}
			if (($ce_hr < $cs_hr)||(($cs_hr == $ce_hr)&&($ce_mn <= $cs_mn))) {$ce_hr = $ce_hr+12;}
			if ($ce_hr < 7){$ce_hr = $ce_hr+12;}
			
			$ce_time = "$ce_hr:$ce_mn:00";
			}
		}
	else{
		$errors[] = 'Please enter an end time for this coverage shift.';
		}
	if (strtotime($ce_time) <= strtotime($cs_time)){
		$errors[] = 'Shift end must be after shift start.';
		}
		
	//Check for overlaps
	$query = "SELECT e.emp_id, division, concat(first_name, ' ', last_name) as employee_name, coverage_division,
		coverage_date, coverage_start_time, coverage_end_time FROM coverage as t, employees as e 
		WHERE e.emp_id = t.emp_id and e.emp_id = '$emp_id' 
		and coverage_id != '$coverage_id' and coverage_date = '$cd_date' and 
		(('$cs_time' >= coverage_start_time and '$cs_time' < coverage_end_time) 
		or ('$ce_time' > coverage_start_time and '$ce_time' <= coverage_end_time))";
	$result = mysqli_query($dbc, $query);
	$num_rows = mysql_num_rows($result);
	if ($num_rows != 0) {
		while ($row = mysql_fetch_array ($result, MYSQL_ASSOC)){
			$full_name = $row['employee_name'];
			$old_emp_id = $row['emp_id'];
			$division = $row['division'];
			$cov_date = $row['coverage_date'];
			$covs_time = $row['coverage_start_time'];
			$cove_time = $row['coverage_end_time'];
			$cov_div = $row['coverage_division'];
			$errors[] = "<b>$full_name</b> is already scheduled to cover $cov_div<br/>&nbsp;&nbsp;$cov_date, 
				$covs_time-$cove_time";
			}
		$dates['mon'] = $cd_mon;
		$dates['mday'] = $cd_day;
		$dates['year'] = $cd_yr;
		}
	
	if (empty($errors)) {
		$query = "UPDATE coverage SET coverage_division='$cd_div', coverage_date='$cd_date', coverage_start_time='$cs_time', 
			coverage_end_time='$ce_time', coverage_offdesk='$cd_onoff', coverage_reason='$cd_reason'
			WHERE coverage_id = '$coverage_id'";
		$result = mysqli_query($dbc, $query) or die(mysql_error($dbc));
		if ($result) {
			if (isset($_SESSION['view_past'])){
				$_SESSION['success'] = TRUE;
				header ('Location: view_past');
				echo '<div class="message"><b>'. $_POST['employee_name'] . '</b> has been updated.<br/>
					To edit another shift <a href="view_timeoff">click here</a></div></div>';
				include ('./includes/footer.html');
				exit();
				}
			else{
				$_SESSION['success'] = TRUE;
				header ('Location: view_coverage');
				echo '<div class="message"><b>'. $_POST['employee_name'] . '</b> has been updated.<br/>
					To edit another shift <a href="view_coverage">click here</a></div></div>';
				include ('./includes/footer.html');
				exit();
				}
			}
		else {
			$page_title = "Edit Coverage" ;
			include('./includes/supersessionstart.php');
			include ('./includes/header.html');
			include ('./includes/supersidebar.html');	
			echo '<div class="message"><b>System Error</b>
				The employee could not be added due to a system error.
				We apologize for the inconvenience.</div>';
			include ('./includes/footer.html');
			exit();
			}
		}
	}

$page_title = "Edit Coverage" ;
include ('./includes/header.html');
include ('./includes/supersidebar.html');	

?>
<script>
function checkDate() {
	var myOrigDate = document.DateForm.coverage_date.value.split('/');
	var myDayStr_start = myOrigDate[1];
	var myMonthStr_start = myOrigDate[0]-1;
	var myYearStr_start = myOrigDate[2];
	var myMonth = new Array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
	var myDateStr_start = myMonth[myMonthStr_start] + ' ' + myDayStr_start + ', ' +  myYearStr_start;

/* Using form values, create a new date object
using the setFullYear function */
	var myDate_start = new Date();
	myDate_start.setFullYear( myYearStr_start, myMonthStr_start, myDayStr_start );

//Check that date is not in the past
	var sendDate_start = myYearStr_start + '/' + myMonth[myMonthStr_start] + '/' + myDayStr_start;
	sendDate_start = new Date(Date.parse(sendDate_start.replace(/-/g,' ')));
	
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


	if (sendDate_start < past_date) {
		alert('The date is too far in the past. Please pick another date.');
		return false;
		}
	else if ( myDate_start.getMonth() != myMonthStr_start ) {
		alert( myDateStr_start + ' is not a valid start date. Please pick another.' );
		return false;
		}
	else {
		return true;
		}
	}

function validateForm() {
	var x=document.DateForm.coverage_division.value;
	var za=document.DateForm.elements['coverage_start[hours]'].value;
	var za_mn=document.DateForm.elements['coverage_start[minutes]'].value;
	var zb=document.DateForm.elements['coverage_end[hours]'].value;
	var zb_mn=document.DateForm.elements['coverage_end[minutes]'].value;
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
		
	var myOrigDate = document.DateForm.coverage_date.value.split('/');
	var myDayStr_start = myOrigDate[1];
	var myMonthStr_start = myOrigDate[0]-1;
	var myYearStr_start = myOrigDate[2];
	var myMonth = new Array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
	var myDateStr_start = myMonth[myMonthStr_start] + ' ' + myDayStr_start + ', ' +  myYearStr_start;
		
	if (x=="0"){
		alert("Please select a coverage division.");
		return false;
		}
	else if ((za && za!=parseInt(za)) || (za_mn && za_mn!=parseInt(za_mn))) {
		alert("Please enter a valid coverage start time.");
		return false;
		}
	else if ((zb && zb!=parseInt(zb)) || (zb_mn && zb_mn!=parseInt(zb_mn))) {
		alert("Please enter a valid coverage end time.");
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

<div id="edit_coverage"><span class="date"><h1>Edit Coverage</h1></span>
<?php
if (!empty($errors)) {
	echo '<div class="errormessage"><h3>Error!</h3><br/>
	The following error(s) occurred:<br/><br/>';
	foreach ($errors as $msg) { //Print each error
		echo " - $msg<br/>\n";
		}
	echo '</div>';
	}
mysql_close();
?>
<div class="coverform">
	<div class="dp">Edit coverage for</div>
	<div class="emph"><?php echo $employee; ?></div><br/>

	<form action="edit_coverage" method="post" name="DateForm" 
		onsubmit="return validator();">
	<div class="label">Covered Division: </div>
	<select name="coverage_division">
		<option value="select" disabled="disabled">- Select -</option>
		<?php foreach ($divisions as $k=>$v){echo '<option value="'.$v.'"';
			if($coverage_division == $v){
				echo 'selected="selected"';
				}
			echo '>'.$v.'</option>';} ?>
	</select>
	<div class="label">Coverage Date:</div>
	<div class="cal">
		<input class="datepick" id="datepicker1" name="coverage_date" placeholder="Click to choose" 
		value="<?php echo $cd[1].'/'.$cd[2].'/'.$cd[0];?>"/>
	</div>
		<p><div class="label time">Start Time:	</div>
		<input type="text" name="coverage_start[hours]" value="<?php if ($cs_hr > 12){$cs_hr -= 12;} echo $cs_hr;?>" maxlength="2" size="1" class="hrs"/><b> : </b>
		<input type="text" name="coverage_start[minutes]" value="<?php echo $cs_mn;?>" maxlength="2" size="3"/>
		</p>
		<p><div class="label time">End Time:</div>
		<input type="text" name="coverage_end[hours]" value="<?php if ($ce_hr > 12){$ce_hr -= 12;} echo $ce_hr;?>" maxlength="2" size="1" class="hrs"/><b> : </b>
		<input type="text" name="coverage_end[minutes]" value="<?php echo $ce_mn;?>" maxlength="2" size="3"/>
		</p>
		<p><div class="radio">
			<input type="radio" name="onoff" value="On" onclick="document.getElementById('coverage_note').style.display='none'" 
				<?php if ($coverage_offdesk == 'On') echo 'checked';?>>On Desk
			<input type="radio" name="onoff" value="Off" onclick="document.getElementById('coverage_note').style.display='block'"
				<?php if ($coverage_offdesk == 'Off') echo 'checked';?>>Off Desk
			<input type="radio" name="onoff" value="Busy" onclick="document.getElementById('coverage_note').style.display='block'"
				<?php if ($coverage_offdesk == 'Busy') echo 'checked';?>>Busy</div></p>
		<div id="coverage_note" style="display:<?php if ($coverage_offdesk == 'On'){echo 'none';} else {echo 'block';}?>;">
			<div class="label">Reason:</div>
			<input type="text" name="reason" size="25" value="<?php echo $coverage_reason;?>"/></div>
		<p><input type="submit" name="submit" value="Save" /></p>
		<input type="hidden" name="edited" value="TRUE" />
		<input type="hidden" name="employee_name" value="<?php echo $employee; ?>" />
		<input type="hidden" name="coverage_id" value="<?php echo $coverage_id; ?>" />
	</form>
</div></div>
		
<?php
include ('./includes/footer.html');
?>