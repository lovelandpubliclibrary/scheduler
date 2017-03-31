<?php # add_timeoff.php

$page_title = "Schedule Time Off";
include('./includes/supersessionstart.php');
include('./includes/allsessionvariables.php');
include ('./includes/header.html');
echo '<div id="mobilehack">';
$mobilehack = 1;
include ('./includes/supersidebar.html');

?>
<script>
	$(function(){
		$("#datepicker1").change(function(){
			var startDate = $(this).val();
			$("#datepicker2").val(startDate);
			});
		});
	
	function DynamicSelect(id1, id2) {
		// Get references
		this.s1 = document.getElementById(id1);
		this.s2 = document.getElementById(id2);
		
		// Parse the dependent select box and create an object representation
		var o = this.s2Obj = {};
		o.id = id2;
		o.options = [];
		var oNodes = this.s2.getElementsByTagName("option"),
			ol = oNodes.length;
		for (var i = 0; i < ol; i++) {
			var cNodes = oNodes[i].childNodes,
			cl = cNodes.length,
			txt;
			for (var j = 0; j < cl; j++) {
				if(cNodes[j].nodeType === 3) {
					txt = cNodes[j].nodeValue;
					break;
				}
			}
			o.options.push({
				dataParentVal: oNodes[i].getAttribute("data-parent-value"),
				val: oNodes[i].getAttribute("value"),
				label: txt
			});
		}
		//console.dir(this.s2Obj);
		
		//	Add handlers and init
		var _this = this; 
		this.s1.onchange = function() {
			_this.update();
		};
		this.update();
	}
	DynamicSelect.prototype.update = function() {
		// Recreate the select box from the object
		var s2New = document.createElement("select");
		s2New.setAttribute("id", this.s2Obj.id);
		s2New.setAttribute("name", "employee");
		var options = this.s2Obj.options,
			ol = options.length;
		for (var i = 0; i < ol; i++) {
			// Only add the relevant options
			if (options[i].dataParentVal === "0" || options[i].dataParentVal === this.s1.options[this.s1.selectedIndex].value) {
				var oNode = document.createElement("option");
				oNode.setAttribute("data-parent-value", options[i].dataParentVal);
				oNode.setAttribute("value", options[i].val);
				var txtNode = document.createTextNode(options[i].label);
				oNode.appendChild(txtNode);
				s2New.appendChild(oNode);
			}
		}
		
		// Swap out old and new select elements
		var s2 = document.getElementById(this.s2Obj.id);
		s2.parentNode.replaceChild(s2New, s2);
	};
	window.onload = function() {
		var pdaDynamicSelect = new DynamicSelect("division", "name");
	};

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
	if (ts.length === 7){
		ts = '0'+ ts;
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

<div class="mobilewrapper_outer">
<div class="mobilewrapper_inner">
<span class="date"><h1>Schedule Timeoff</h1></span>

<?php 
	
//Get employee info for dynamic selects
foreach ($divisions as $k=>$v){
	$query = "SELECT emp_id, first_name, last_name FROM employees WHERE active = 'Active' and 
		(division like '%".$v."%') order by last_name asc";
	$result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));
	
	while ($row = mysqli_fetch_assoc($result)) {
		$array[$v][]=$row;
		}
	}
	
$dates = getdate();
	
	//Create Date & Time inputs
if (isset($_POST['submitted'])){
	$emp_id = $_POST['employee'];
	$timeoff_div = $_POST['division'];

	list($ts_mon, $ts_day, $ts_yr) = explode('/',$_POST['timeoff_start_date']);
	$ts_date = "$ts_yr-$ts_mon-$ts_day";
	
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
	
	if (empty($_POST['reason'])) {
		$errors[] = 'Please enter timeoff reason.';
		}
	else {
		$reason = escape_data($_POST['reason']);
		}
	
	if (empty($errors)) {
	$query = "INSERT into timeoff(emp_id, timeoff_start_date, timeoff_start_time, timeoff_end_date, timeoff_end_time,
		timeoff_reason) values('$emp_id', '$ts_date', '$ts_time', '$te_date', '$te_time', '$reason')";
	$result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));
	if ($result) {
		$query2 = "SELECT concat(first_name, ' ', last_name) as employee_name 
			FROM employees where emp_id='$emp_id'";
		$result2 = mysqli_query($dbc, $query2) or die(mysqli_error($dbc));
		$full_name = mysqli_result($result2);
	
	//Echo change success message
		echo "<div class=\"message\"><b>Time Off entered for</b><br/>$full_name: <a href=\"$ts_yr/";
		echo "$ts_mon/$ts_day\">$ts_date</a>";
		echo " - <a href=\"$te_yr/";
		echo "$te_mon/$te_day\">$te_date</a></div>";
		}
	}
	else{
		echo '<div class="errormessage"><h3>Error!</h3><br/>
		The following error(s) occurred:<br/><br/>';
		foreach ($errors as $msg) { //Print each error
			echo " - $msg<br/>\n";
			}
		echo '</div>';
		}
	mysqli_close($dbc);
	}
		
?>
	<div class="timeoffform">
		<form action="add_timeoff" method="post" name="DateForm" onsubmit="return validator();">
			<select id="division" name="division">
				<option value="0">Select Division...</option>
				<?php foreach ($divisions as $k=>$v){echo '<option value="'.$v.'">'.$v.'</option>';} ?>
			</select><br/>
			<select id="name">
				<option data-parent-value="0" value="0">Select Employee...</option>
<?php
				foreach ($array as $div=>$arr){
					foreach ($arr as $row){
						$employee = $row['first_name'] . ' ' . $row['last_name'];
						$emp_id = $row['emp_id'];
						$division = $div;
						echo '<option data-parent-value="' . $division . '" value="' . $emp_id . '" name="test">'
							. $employee . '</option>';
						}
					}
?>
			</select>
		<div class="label">Time Off Start:</div>
		<div class="cal">
			<input class="datepick" id="datepicker1" name="timeoff_start_date" placeholder="Click to choose" />
		</div>
			<div class="times">
			<input type="text" name="timeoff_start[hours]" maxlength="2" size="1" class="hrs"/><b> : </b>
			<input type="text" name="timeoff_start[minutes]" maxlength="2" size="3"/>
			</div><br/>
		<div class="label">Time Off End:</div>
		<div class="cal">
			<input class="datepick" id="datepicker2" name="timeoff_end_date" placeholder="Click to choose" />
		</div>	
			<div class="times">
			<input type="text" name="timeoff_end[hours]" maxlength="2" size="1" class="hrs"/><b> : </b>
			<input type="text" name="timeoff_end[minutes]" maxlength="2" size="3"/>
			</div><br/>
		<div class="label">Reason:</div>
			<input type="text" name="reason" size="35"/><br/>
			<p><input type="submit" name="submit" value="Save" /></p>
			<input type="hidden" name="submitted" value="TRUE" />
		</form>
	</div>
</div>
</div>
<?php
include ('./includes/footer.html');
?>