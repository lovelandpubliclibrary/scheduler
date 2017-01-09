<?php #edit_schedule_dates

$page_title="Adjust Dates";
include('./includes/supersessionstart.php');
$came_from = $_SESSION['came_from'];
include('./includes/allsessionvariables.php');
if (($came_from != 'edit_schedule')&&($came_from != 'edit_schedule_dates')){
	header ('Location: edit_schedule');
	}
include ('./includes/header.html');
echo '<div id="mobilehack">';
$mobilehack = 1;
include ('./includes/supersidebar.html');

$division = $_POST['division'];
$schedstart = $_POST['schedstart'];
$schedstart = date('n', strtotime($schedstart)).'/'.date('j', strtotime($schedstart)).'/'.date('Y', strtotime($schedstart));
$schedend = $_POST['schedend'];
$schedend = date('n', strtotime($schedend)).'/'.date('j', strtotime($schedend)).'/'.date('Y', strtotime($schedend));
$schedule_id = $_POST['schedule_id'];
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
	var x=document.edit_schedule_dates.division.value;
	var y=document.edit_schedule_dates.schedstart_datepick.value;
	var z=document.edit_schedule_dates.schedend_datepick.value;
	
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
<span class="date"><h1>Adjust Dates</h1></span>

<div class="addform">
<form action="edit_schedule" method="post" name="edit_schedule_dates" onsubmit="return validateForm();">
	<p><div class="label">Division:</div> 
		<?php echo $division; ?>
	</p>
	<p><div class="label">Starting Date:</div>
		<input class="schedstart_datepick" name="schedstart_datepick" placeholder="Choose Starting Sat" 
			value="<?php echo $schedstart;?>"/>
	</p>
	<p><div class="label">Ending Date:</div>
		<input class="schedend_datepick" name="schedend_datepick" placeholder="Choose Ending Fri"
			value="<?php echo $schedend;?>"/>
	</p>

	<p><input type="submit" name="submit" value="Save" /></p>
	<input type="hidden" name="division" value="<?php echo $division;?>" />
	<input type="hidden" name="schedule_id" value="<?php echo $schedule_id;?>" />
	<input type="hidden" name="edit_dates" value="TRUE" />
</form></div></div></div>
<?php
include ('./includes/footer.html');
?>
</div>