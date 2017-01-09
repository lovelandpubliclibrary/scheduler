<?php # add_coverage.php

$page_title = "Schedule Coverage";
include('./includes/supersessionstart.php');
include('./includes/allsessionvariables.php');
include ('./includes/header.html');
echo '<div id="mobilehack">';
$mobilehack = 1;
include ('./includes/supersidebar.html');

?>

<div class="mobilewrapper_outer">
<div class="mobilewrapper_inner">
<span class="date"><h1>Schedule Coverage</h1></span>

<?php
	
//Get employee info for dynamic selects
foreach ($divisions as $k=>$v){
	$query = "SELECT emp_id, first_name, last_name FROM employees WHERE active = 'Active' and 
		(division like '%".$v."%') order by last_name asc";
	$result = mysql_query($query) or die(mysql_error($dbc));
	
	while ($row = mysql_fetch_array ($result, MYSQL_ASSOC)) {
		$array[$v][]=$row;
		}
	}
	
$dates = getdate();
$message_add = '';

if (isset($_POST['submitted'])){
	$empdiv = $_POST['division'];
	$emp_id = $_POST['employee'];

	$cd_div = $_POST['coverage_division'];
	list($cd_mon, $cd_day, $cd_yr) = explode('/',$_POST['coverage_date']);
	$cd_date = "$cd_yr-$cd_mon-$cd_day";
	
	$cd_onoff = $_POST['onoff'];
	if (($cd_onoff != 'On')&&(!empty($_POST['reason']))){
		$cd_reason = $_POST['reason'];
		}
	else{
		$cd_reason = NULL;
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
		and coverage_date = '$cd_date' and (('$cs_time' >= coverage_start_time and '$cs_time' < coverage_end_time) 
		or ('$ce_time' > coverage_start_time and '$ce_time' <= coverage_end_time)
		or ('$cs_time' <= coverage_start_time and '$ce_time' >= coverage_end_time))"; 
	$result = mysql_query($query);
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
			$overlap = TRUE;
			}
		$dates['mon'] = $cd_mon;
		$dates['mday'] = $cd_day;
		$dates['year'] = $cd_yr;
		}
	
	//12-Hr Times
	function twelve_hr($cs_hr, $cs_mn, $ce_hr, $ce_mn){
		if ($cs_hr > 12){
			$cs12 = $cs_hr - 12;
			if ($cs_mn != '00') {
				$cs12 .= ':'.$cs_mn;
				}
			$ce12 = $ce_hr - 12;
			if ($ce_mn != '00') {
				$ce12 .= ':'.$ce_mn;
				}
			$ce12 .= 'pm';
			}
		elseif ($cs_hr == 12){
			$cs12 = $cs_hr;
			if ($cs_mn != '00') {
				$cs12 .= ':'.$cs_mn;
				}
			if ($ce_hr > 12){
				$ce12 = $ce_hr - 12;
				}
			else{
				$ce12 = $ce_hr;
				}
			if ($ce_mn != '00') {
				$ce12 .= ':'.$ce_mn;
				}
			$ce12 .= 'pm';
			}							
		else {
			$cs12 = $cs_hr;
			if ($cs_mn != '00') {
				$cs12 .= ':'.$cs_mn;
				}
			$cs12 .= 'am';
			if ($ce_hr > 12){
				$ce12 = $ce_hr - 12;
				if ($ce_mn != '00') {
					$ce12 .= ':'.$ce_mn;
					}
				$ce12 .= 'pm';
				}
			elseif ($ce_hr == 12){
				$ce12 = $ce_hr;
				if ($ce_mn != '00') {
					$ce12 .= ':'.$ce_mn;
					}
				$ce12 .= 'pm';
				}
			else {
				$ce12 = $ce_hr;
				if ($ce_mn != '00') {
					$ce12 .= ':'.$ce_mn;
					}
				$ce12 .= 'am';
				}
			}
		return "$cs12 - $ce12";
		}
	$c_shift = twelve_hr($cs_hr, $cs_mn, $ce_hr, $ce_mn);
	
	//Remove Sub Needs, if applicable
	if (isset($_POST['confirmed'])){
		if (isset($_POST['maintain'])){
			$message_add = 'Sub Request(s) kept';
			}
		else {
			$new_req = NULL;
			if (isset($_POST['confirm'])){
				foreach ($_POST['confirm'] as $key=>$value){
					$query4 = "UPDATE sub_needs set sub_needs_covered='Y' WHERE sub_needs_id='$key'";
					$result4 = mysql_query($query4);
					}
				$overlap = FALSE;
				}
			else {
				foreach ($_POST['sub_needs_id'] as $value){
					$query4 = "UPDATE sub_needs set sub_needs_covered='Y' WHERE sub_needs_id='$value'";
					$result4 = mysql_query($query4);
					}
				}
			if (isset($_POST['new_request'])){
				foreach ($_POST['new_request'] as $value){
					$sub_needs_date = $value[0];
					$sub_needs_start_time = $value[2];
					$sub_needs_end_time = $value[3];
					$sub_needs_division = $value[1];
					$query = "INSERT into sub_needs (sub_needs_date, sub_needs_start_time, sub_needs_end_time,
						sub_needs_division) VALUES ('$sub_needs_date', '$sub_needs_start_time', '$sub_needs_end_time',
						'$sub_needs_division')";
					$result = mysql_query($query);
					}
				$new_req = ' and new request(s) created';
				}
			if (isset($_POST['new_request1'])){
				foreach ($_POST['new_request1'] as $value){
					$sub_needs_date = $value[0];
					$sub_needs_start_time = $value[2];
					$sub_needs_end_time = $value[3];
					$sub_needs_division = $value[1];
					$query = "INSERT into sub_needs (sub_needs_date, sub_needs_start_time, sub_needs_end_time,
						sub_needs_division) VALUES ('$sub_needs_date', '$sub_needs_start_time', '$sub_needs_end_time',
						'$sub_needs_division')";
					$result = mysql_query($query);
					}
				$new_req = ' and new request(s) created';
				}
			if (isset($_POST['new_request2'])){
				foreach ($_POST['new_request2'] as $value){
					$sub_needs_date = $value[0];
					$sub_needs_start_time = $value[2];
					$sub_needs_end_time = $value[3];
					$sub_needs_division = $value[1];
					$query = "INSERT into sub_needs (sub_needs_date, sub_needs_start_time, sub_needs_end_time,
						sub_needs_division) VALUES ('$sub_needs_date', '$sub_needs_start_time', '$sub_needs_end_time',
						'$sub_needs_division')";
					$result = mysql_query($query);
					}
				$new_req = ' and new request(s) created';
				}
			$message_add = 'Sub Requests confirmed'.$new_req.'.';
			}
		}
	
	if (!isset($_POST['maintain'])){
		//Sub Needs Check
		echo '<div id="sub_needs_confirm" title="Confirm"><form id="confirmForm" action="add_coverage" method="post">';
		$query = "SELECT sub_needs_id, sub_needs_date, sub_needs_start_time, 
			time_format(sub_needs_start_time,'%k') as sub_needs_start, 
			time_format(sub_needs_start_time,'%i') as sub_needs_start_minutes, sub_needs_end_time, 
			time_format(sub_needs_end_time,'%k') as sub_needs_end, 
			time_format(sub_needs_end_time,'%i') as sub_needs_end_minutes from sub_needs 
			WHERE sub_needs_division='$cd_div' and sub_needs_date='$cd_date' and sub_needs_covered='N' 
			ORDER BY sub_needs_start_time asc";
		$result = mysql_query($query);
		$num_rows = mysql_num_rows($result);
		$sub_needs = array();
		if ($num_rows != 0) {
			$sub_message = array();
			$change_message = array();
			while ($row = mysql_fetch_array ($result, MYSQL_ASSOC)){
				$sub_needs_id = $row['sub_needs_id'];
				$sns_hr = $row['sub_needs_start'];
				$sns_mn = $row['sub_needs_start_minutes'];
				$sub_needs_start_time = "$sns_hr:$sns_mn:00";
				$sne_hr = $row['sub_needs_end'];
				$sne_mn = $row['sub_needs_end_minutes'];
				$sub_needs_end_time = "$sne_hr:$sne_mn:00";
				$sub_needs[$sub_needs_id] = array('start'=>$sub_needs_start_time, 'sns_hr'=>$sns_hr, 'sns_mn'=>$sns_mn, 
					'end'=>$sub_needs_end_time, 'sne_hr'=>$sne_hr, 'sne_mn'=>$sne_mn);
				}

			foreach ($sub_needs as $id=>$hoursarray){
				$sn_shift = twelve_hr($hoursarray['sns_hr'], $hoursarray['sns_mn'], $hoursarray['sne_hr'], $hoursarray['sne_mn']);

				if((strtotime($cs_time) == strtotime($hoursarray['start']))&&(strtotime($ce_time) == strtotime($hoursarray['end']))){
					$sub_message[] = 'matches a pending Sub Request for '.$cd_div;
					$change_message[$id] = 'remove request for '.$sn_shift.'?';
					}
				elseif (((strtotime($cs_time) < strtotime($hoursarray['start']))&&(strtotime($ce_time) > strtotime($hoursarray['end'])))||
					((strtotime($cs_time) < strtotime($hoursarray['start']))&&(strtotime($ce_time) == strtotime($hoursarray['end'])))||
					((strtotime($cs_time) == strtotime($hoursarray['start']))&&(strtotime($ce_time) > strtotime($hoursarray['end'])))){
					$sub_message[] = 'encompasses a pending Sub Request for '.$cd_div.' for '.$sn_shift;
					$change_message[$id] = 'remove request for '.$sn_shift.'?';
					}
				elseif (((strtotime($cs_time) < strtotime($hoursarray['start']))&&(strtotime($ce_time) > strtotime($hoursarray['start']))
					&&(strtotime($ce_time) < strtotime($hoursarray['end'])))||((strtotime($cs_time) == strtotime($hoursarray['start']))
					&&(strtotime($ce_time) < strtotime($hoursarray['end'])))){
					$new_shift = twelve_hr($ce_hr,$ce_mn,$hoursarray['sne_hr'],$hoursarray['sne_mn']);
					$sub_message[] = 'satisfies <b>part of</b> a pending Sub Request for '.$cd_div.
						' for '.$sn_shift;
					$change_message[$id] = 'create a new request for '.$new_shift.' ?';
					$new_request = array('sub_date'=>$cd_date, 'sub_div'=>$cd_div, 'sub_start'=>$ce_time, 'sub_end'=>$sub_needs_end_time);
					}
				elseif (((strtotime($cs_time) > strtotime($hoursarray['start']))&&(strtotime($cs_time) < strtotime($hoursarray['end']))
					&&(strtotime($ce_time) > strtotime($hoursarray['end'])))||((strtotime($cs_time) > strtotime($hoursarray['start']))
					&&(strtotime($ce_time) == strtotime($hoursarray['end'])))){
					$new_shift = twelve_hr($hoursarray['sns_hr'],$hoursarray['sns_mn'],$cs_hr,$cs_mn);
					$sub_message[] = 'satisfies <b>part of</b> a pending Sub Request for '.$cd_div.
						' for '.$sn_shift;
					$change_message[$id] = 'create a new request for '.$new_shift.' ?';
					$new_request = array('sub_date'=>$cd_date, 'sub_div'=>$cd_div, 'sub_start'=>$sub_needs_start_time, 'sub_end'=>$cs_time);
					}
				elseif ((strtotime($cs_time) > strtotime($hoursarray['start']))&&(strtotime($ce_time) < strtotime($hoursarray['end']))){
					$new_shift1 = twelve_hr($hoursarray['sns_hr'],$hoursarray['sns_mn'],$cs_hr,$cs_mn);
					$new_shift2 = twelve_hr($ce_hr,$ce_mn,$hoursarray['sne_hr'],$hoursarray['sne_mn']);
					$sub_message[] = 'satisfies <b>part of</b> a pending Sub Request for '.$cd_div.
						' for '.$sn_shift;
					$change_message[$id] = 'create new requests for '.$new_shift1.' and '.$new_shift2.'?';
					$new_request1 = array('sub_date'=>$cd_date, 'sub_div'=>$cd_div, 'sub_start'=>$sub_needs_start_time, 'sub_end'=>$cs_time);
					$new_request2 = array('sub_date'=>$cd_date, 'sub_div'=>$cd_div, 'sub_start'=>$ce_time, 'sub_end'=>$sub_needs_end_time);
					}
					
				echo '<input type="hidden" name="sub_needs_id[]" value="'.$id.'"/>';
				if (isset($new_request)){
					foreach ($new_request as $v){
						echo '<input type="hidden" name="new_request['.$id.'][]" value="'.$v.'" />';
						}
					}
				if (isset($new_request1)){
					foreach ($new_request1 as $v){
						echo '<input type="hidden" name="new_request1['.$id.'][]" value="'.$v.'" />';
						}
					}
				if (isset($new_request2)){
					foreach ($new_request2 as $v){
						echo '<input type="hidden" name="new_request2['.$id.'][]" value="'.$v.'" />';
						}
					}
				}
			if (((!isset($overlap)) || ($overlap == TRUE)) && (!empty($sub_message))){
	?>
	<script>
	$(function(){
		$("#sub_needs_confirm").dialog({
			autoOpen: true,
			modal: true,
			resizable: false,
			buttons: {
			"Confirm": function(){
				$("#confirmForm").submit();
				},
			Cancel: function(){
				$(this).dialog("close");
				}
			}
		});
	});
	</script>
	<?php
				echo 'This coverage shift '.$c_shift.' on '.$cd_date.' ';
				for($i = 0; $i < sizeof($sub_message); $i++){
					if ($i > 0){
						echo ' and ';
						}
					echo $sub_message[$i];
					}
				echo '.<br/><br/>';
				echo 'Confirm coverage and ';
				if (sizeof($change_message) > 1){
					foreach ($change_message as $k=>$msg){
						echo '<br/><input type="checkbox" name="confirm['.$k.']" value="confirm_'.$k.'"/> '.$msg;
						}
					}
				else {
					foreach ($change_message as $msg){
						echo $msg.'<br/>';
						}
					}
				echo '<br/><input type="checkbox" name="maintain" value="maintain"/> OR keep current sub request(s)';
				echo '<input type="hidden" name="submitted" value="TRUE"/>';
				echo '<input type="hidden" name="confirmed" value="TRUE"/>';
				echo '<input type="hidden" name="employee" value="'.$emp_id.'"/>';
				echo '<input type="hidden" name="coverage_division" value="'.$cd_div.'"/>';
				echo '<input type="hidden" name="coverage_date" value="'.$cd_mon.'/'.$cd_day.'/'.$cd_yr.'"/>';
				echo '<input type="hidden" name="onoff" value="'.$cd_onoff.'"/>';
				echo '<input type="hidden" name="reason" value="'.$cd_reason.'"/>';
				echo '<input type="hidden" name="coverage_start[hours]" value="'.$cs_hr.'"/>';
				echo '<input type="hidden" name="coverage_start[minutes]" value="'.$cs_mn.'"/>';
				echo '<input type="hidden" name="coverage_end[hours]" value="'.$ce_hr.'"/>';
				echo '<input type="hidden" name="coverage_end[minutes]" value="'.$ce_mn.'"/>';
				$errors[] = 'Coverage not yet entered.';
				$old_emp_id = $emp_id;
				}
			}
		
		echo '</form></div>';
		}
	
	if (empty($errors)) {
	$query = "INSERT into coverage(emp_id, coverage_date, coverage_start_time, coverage_end_time,
		coverage_division, coverage_offdesk, coverage_reason) 
		values('$emp_id', '$cd_date', '$cs_time', '$ce_time', '$cd_div', '$cd_onoff', '$cd_reason')";
	$result = mysql_query($query) or die(mysql_error($dbc));
	if ($result) {		
		$query2 = "SELECT concat(first_name, ' ', last_name) as employee_name 
			FROM employees where emp_id='$emp_id'";
		$result2 = mysql_query($query2) or die(mysql_error($dbc));
		$full_name = mysql_result($result2, 0);
	
		echo "<div class=\"message\"><b>Coverage entered for</b><br/>$full_name: <a href=\"$cd_yr/";
		echo "$cd_mon/$cd_day\" title=\"See Schedule\">$cd_date</a>";
		echo ", in $cd_div, $cs_time - $ce_time<br/>$message_add</div>";
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
		mysql_close();
	}
?>
<script>
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
		//Variable
		var old_emp_id  = '<?php if(isset($old_emp_id)){echo $old_emp_id;} ?>';

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
				if (options[i].val === old_emp_id){	
					oNode.setAttribute("selected", "selected");
					}
				var txtNode = document.createTextNode(options[i].label);
				oNode.appendChild(txtNode);
				s2New.appendChild(oNode);
			}
		optionIndex = this.s1.options[this.s1.selectedIndex].value;
		document.getElementById('coverage_division').value = optionIndex;
		}
		
		// Swap out old and new select elements
		var s2 = document.getElementById(this.s2Obj.id);
		s2.parentNode.replaceChild(s2New, s2);
	};
	window.onload = function() {
		var pdaDynamicSelect = new DynamicSelect("division", "name");
	};
	
function checkDate() {
	var myOrigDate = document.DateForm.coverage_date.value.split('/');
	var myDayStr = myOrigDate[1];
	var myMonthStr = myOrigDate[0]-1;
	var myYearStr = myOrigDate[2];
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


	if (sendDate < past_date) {
		alert('The date is too far in the past. Please pick another date.');
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

function validateForm() {
	var x=document.DateForm.coverage_division.value;
	var y=document.DateForm.employee.value;
	var za=document.DateForm.elements['coverage_start[hours]'].value;
	var za_mn=document.DateForm.elements['coverage_start[minutes]'].value;
	var zb=document.DateForm.elements['coverage_end[hours]'].value;
	var zb_mn=document.DateForm.elements['coverage_end[minutes]'].value;
	if (za_mn){
		var cs=za+':'+za_mn+':00';
		}
	else {
		var cs=za+':00:00';
		}
	if (zb_mn){
		var ce=zb+':'+zb_mn+':00';
		}
	else {
		var ce=zb+':00:00';
		}
	
	if (y=="0"){
		alert("Please select an employee.");
		return false;
		}
	else if (x=="select") {
		alert("Please select the covered division.");
		return false;
		}
	else if (za==null || za=="" || za!=parseInt(za) || (za_mn && za_mn!=parseInt(za_mn))) {
		alert("Please enter a valid coverage shift start time.");
		return false;
		}
	else if (zb==null || zb=="" || zb!=parseInt(zb) || (zb_mn && zb_mn!=parseInt(zb_mn))) {
		alert("Please enter a valid coverage shift end time.");
		return false;
		}
	else { return true;}
	}

function validator() {
	if (!checkDate()) {return false;}
	else if (!validateForm()) {return false;}
	else {return true;}
}
</script>
	<div class="coverform">
		<form action="add_coverage" method="post" name="DateForm" onsubmit="return validator();">
			<select id="division" name="division">
				<option value="0">Select Division...</option>
				<?php foreach ($divisions as $k=>$v){echo '<option value="'.$v.'"';
				if ((isset($division))&&($division == $v)){echo 'selected="selected"';}
				echo '>'.$v.'</option>';} ?>
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
		<div class="label">Covered Division: </div>
			<select name="coverage_division" id="coverage_division">
				<option value="select" disabled="disabled" selected="selected">- Select -</option>
				<?php foreach ($divisions as $k=>$v){echo '<option value="'.$v.'"';
				if(isset($old_emp_id) && isset($cd_div) && ($cd_div == $v)){
					echo 'selected="selected"';
					}
				echo '>'.$v.'</option>';} ?>
			</select>
		<div class="label">Coverage Date:</div>
		<div class="cal">
			<input class="datepick" id="datepicker1" name="coverage_date" placeholder="Click to choose" 
			<?php if (isset($_POST['coverage_date']) && isset($old_emp_id)){echo 'value="'.$_POST['coverage_date'].'"';}?>/>
		</div>
		<p><div class="label time">Start Time:	</div>
			<input type="text" name="coverage_start[hours]" maxlength="2" size="1" class="hrs"
				<?php if (isset($old_emp_id) && isset($cs_hr)){
					if ($cs_hr > 12){$cs_hr -= 12;} echo 'value="'.$cs_hr.'"';}?>/><b> : </b>
			<input type="text" name="coverage_start[minutes]" maxlength="2" size="3"
				<?php if (isset($old_emp_id) && isset($cs_mn)){echo 'value="'.$cs_mn.'"';}?>/></p>
		<p><div class="label time">End Time:</div>
			<input type="text" name="coverage_end[hours]" maxlength="2" size="1" class="hrs"
				<?php if (isset($old_emp_id) && isset($ce_hr)){
					if ($ce_hr > 12){$ce_hr -= 12;} echo 'value="'.$ce_hr.'"';}?>/><b> : </b>
			<input type="text" name="coverage_end[minutes]" maxlength="2" size="3"
				<?php if (isset($old_emp_id) && isset($ce_mn)){echo 'value="'.$ce_mn.'"';}?>/></p>
		<p><div class="radio">
			<input type="radio" name="onoff" value="On" onclick="document.getElementById('coverage_note').style.display='none'" checked>On Desk
			<input type="radio" name="onoff" value="Off" onclick="document.getElementById('coverage_note').style.display='block'">Off Desk
			<input type="radio" name="onoff" value="Busy" onclick="document.getElementById('coverage_note').style.display='block'">Busy</div>
			</p>
		<div id="coverage_note" style="display:none;">
			<div class="label">Reason:</div>
			<input type="text" name="reason" size="25"/></div>
		<p><input type="submit" name="submit" value="Save" /></p>
			<input type="hidden" name="submitted" value="TRUE" />
			<input type="hidden" name="employee_full_name" value="<?php echo $employee; ?>" />
		</form>
	</div>
</div>
</div>
<?php
include ('./includes/footer.html');
?>