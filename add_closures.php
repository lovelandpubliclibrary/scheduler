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
$dates = getdate();
	
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
			if (($ce_hr < $te_hr)||(($te_hr == $ce_hr)&&($ce_mn <= $te_mn))) {
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
		$result = mysql_query($query);
		$num_rows = mysql_num_rows($result);

		//Prevent duplicate data.
		if ($num_rows == 0){
			$query = "INSERT into closures(closure_date, closure_start_time, closure_end_time, closure_reason) 
				values('$cd_date', '$cs_time', '$ce_time', '$cd_reason')";
			$result = mysql_query($query) or die(mysql_error($dbc));
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
		foreach ($errors as $msg) { //Print each error
			echo " - $msg<br/>\n";
			}
		echo '</div>';
		}
		mysql_close();
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
</div>
</div>
<?php
include ('./includes/footer.html');
?>