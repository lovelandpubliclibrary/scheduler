<?php #add_sub_needs

$page_title = "Add Sub Shift";
include('./includes/supersessionstart.php');
include('./includes/allsessionvariables.php');
include ('./includes/header.html');
echo '<div id="mobilehack">';
$mobilehack = 1;
include ('./includes/supersidebar.html');

?>

<div class="mobilewrapper_outer">
<div class="mobilewrapper_inner">
<span class="date"><h1>Add Sub Shift</h1></span>

<script>	
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
	var past_date = subtracting_days(today,0);


	if (sendDate < past_date) {
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

function validateForm() {
	var x=document.DateForm.sub_needs_division.value;
	var za=document.DateForm.elements['sub_needs_start[hours]'].value;
	var za_mn=document.DateForm.elements['sub_needs_start[minutes]'].value;
	var zb=document.DateForm.elements['sub_needs_end[hours]'].value;
	var zb_mn=document.DateForm.elements['sub_needs_end[minutes]'].value;
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
	
	if (x=="select") {
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
	<form action="sub_needs" method="post" name="DateForm" onsubmit="return validator();">
	<div class="label">Division: </div>
		<select name="sub_needs_division">
			<option value="select" disabled="disabled" selected="selected">- Select -</option>
			<?php foreach ($divisions as $k=>$v){echo '<option value="'.$v.'">'.$v.'</option>';} ?>
		</select>
	<div class="label">Shift Date:</div>
	<div class="cal">
		<input class="datepick" id="datepicker1" name="sub_needs_date" placeholder="Click to choose" />
	</div>
	<p><div class="label time">Start Time:	</div>
		<input type="text" name="sub_needs_start[hours]" maxlength="2" size="1" class="hrs"/><b> : </b>
		<input type="text" name="sub_needs_start[minutes]" maxlength="2" size="3"/></p>
	<p><div class="label time">End Time:</div>
		<input type="text" name="sub_needs_end[hours]" maxlength="2" size="1" class="hrs"/><b> : </b>
		<input type="text" name="sub_needs_end[minutes]" maxlength="2" size="3"/></p>
	<p><input type="submit" name="submit" value="Save" /></p>
		<input type="hidden" name="add_sub_needs_submitted" value="TRUE" />
	</form>
</div>
</div>
</div>
<?php
include ('./includes/footer.html');
?>