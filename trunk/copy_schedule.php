<?php #copy_schedule
$page_title="Copy Schedule";
include('./includes/supersessionstart.php');
$came_from = $_SESSION['came_from'];
include('./includes/allsessionvariables.php');
if (($came_from != 'edit_schedule')&&($came_from != 'copy_schedule')){
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
$specific_schedule = $_POST['specific_schedule'];
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
	var x=document.copy_schedule.division.value;
	var y=document.copy_schedule.schedstart_datepick.value;
	var z=document.copy_schedule.schedend_datepick.value;
	
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
<span class="date"><h1>Copy Schedule</h1></span>

<div class="addform">
<form action="edit_schedule" method="post" name="copy_schedule" onsubmit="return validateForm();">
	<p><div class="label">Division:</div> 
		<?php echo $division; ?><br/>
		Copying schedule set for <?php echo $schedstart.' to '.$schedend;?>
	</p>
	<p><div class="label">New Starting Date:</div>
		<input class="schedstart_datepick" name="schedstart_datepick" placeholder="Choose Starting Sat"/>
	</p>
	<p><div class="label">New Ending Date:</div>
		<input class="schedend_datepick" name="schedend_datepick" placeholder="Choose Ending Fri"/>
	</p>

	<p><input type="submit" name="submit" value="Save" /></p>
	<input type="hidden" name="division" value="<?php echo $division;?>" />
	<input type="hidden" name="specific_schedule" value="<?php echo $specific_schedule;?>" />
	<input type="hidden" name="init" value="TRUE" />
</form></div></div></div>
<?php
include ('./includes/footer.html');
?>
</div>