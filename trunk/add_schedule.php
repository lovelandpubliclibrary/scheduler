<?php # add_schedule.php

$page_title="Add Schedule";
include('./includes/supersessionstart.php');
include('./includes/allsessionvariables.php');
include ('./includes/header.html');
echo '<div id="mobilehack">';
$mobilehack = 1;
include ('./includes/supersidebar.html');
?>
<script>
$(document).ready(function() {
	$(".schedstart_datepick").datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,   
		beforeShowDay: enableSaturdays
		});
	$(".schedend_datepick").datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,   
		beforeShowDay: enableFridays
		});
	// Custom function to enable Saturday only in jQuery calender
	function enableSaturdays(date) {
		var day = date.getDay();
		return [(day == 6), ''];
		}
	// Custom function to enable Friday only in jQuery calender
	function enableFridays(date) {
		var day = date.getDay();
		return [(day == 5), ''];
		}
	$('.schedstart_datepick').datepicker("option", "onSelect", function(){
		var value = $(this).val();
		var date = new Date(value);
		$( ".schedend_datepick" ).datepicker("option", "minDate", new Date(date));
		});	
	
	});
function validateForm() {
	var x=document.add_schedule.division.value;
	var y=document.add_schedule.schedstart_datepick.value;
	var z=document.add_schedule.schedend_datepick.value;
	
	if (x=="select"){
		alert("Please select a division.");
		return false;
		}
	if (y==""){
		alert("Please select a start date.");
		return false;
		}
	if (z==""){
		alert("Please select an end date.");
		return false;
		}
	}
</script>

<div class="mobilewrapper_outer">
<div class="mobilewrapper_inner">
<span class="date"><h1>Add Schedule</h1></span>

<div class="addform">
<form action="schedule_days" method="post" name="add_schedule" onsubmit="return validateForm();">
	<p><div class="label">Division:</div> 
		<select name="division">
			<option value="select" disabled="disabled" selected="selected">- Select -</option>
			<?php foreach ($divisions as $k=>$v){echo '<option value="'.$v.'">'.$v.'</option>';} ?>
		</select>
	</p>
	<p><div class="label">Starting Date:</div>
		<input class="schedstart_datepick" name="schedstart_datepick" placeholder="Choose Starting Sat"/>
	</p>
	<p><div class="label">Ending Date:</div>
		<input class="schedend_datepick" name="schedend_datepick" placeholder="Choose Ending Fri"/>
	</p>

	<p><input type="submit" name="submit" value="Select" /></p>
	<input type="hidden" name="init" value="TRUE" />
</form></div></div></div>
<?php
include ('./includes/footer.html');
?>
</div>