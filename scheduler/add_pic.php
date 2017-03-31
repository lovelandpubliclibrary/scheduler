<?php #add_pic.php

$page_title = 'Person In Charge';
include('./includes/supersessionstart.php');
include('./includes/allsessionvariables.php');
include ('./includes/header.html');
include ('./includes/supersidebar.html');
$today= date('Y-m-d');
$weeks = array('a','b','c','d');
$days = array('sat','sun','mon','tue','wed','thu','fri');

if (isset($_POST['delete'])){
	$pic_id = $_POST['pic_id'];
	$picstart = $_POST['picstart'];
	$picend = $_POST['picend'];
	$query1 = "DELETE from pic_schedules WHERE pic_schedule_id='$pic_id'";
	$result1 = mysqli_query($dbc, $query1);
	$query2 = "DELETE from pic WHERE pic_schedule_id='$pic_id'";
	$result2 = mysqli_query($dbc, $query2);
	
	$message = 'Schedule for '.$picstart.' to '.$picend.' has been deleted.';
	}
	
if (isset($_POST['submitted'])){
	$pics = $_POST['pic'];
	list($ps_mon, $ps_day, $ps_yr) = explode('/',$_POST['picstart_datepick']);
	$pic_start_date = "$ps_yr-$ps_mon-$ps_day";
	list($pe_mon, $pe_day, $pe_yr) = explode('/',$_POST['picend_datepick']);
	$pic_end_date = "$pe_yr-$pe_mon-$pe_day";

	if(isset($_POST['pic_id'])){
		$pic_id = $_POST['pic_id'];
		
		//Check for duplicates
		foreach ($pics as $week_type=>$day_array){
			foreach($day_array as $day=>$value){
				$dup_query = "SELECT * from pic where pic_schedule_id='$pic_id' and pic_day='$day' and week_type='$week_type'";
				$dup_result = mysqli_query($dbc, $dup_query);
				if ($dup_result){
					$dup_num_rows = mysqli_num_rows($dup_result);
					if ($dup_num_rows != 0) {
						$query = "UPDATE pic SET emp_id='$value' WHERE pic_schedule_id='$pic_id' and pic_day='$day' and week_type='$week_type'";
						$query2 = "UPDATE pic_schedules SET pic_start_date='$pic_start_date', pic_end_date='$pic_end_date'
							WHERE pic_schedule_id='$pic_id'";
						}
					else{
						$query = "INSERT into pic (week_type, pic_day, emp_id, pic_schedule_id) VALUES 
								('$week_type', '$day','$value','$pic_id')";
						}
					$result = mysqli_query($dbc, $query);
					}
				}
			}
		if (isset($query2)){
			$result2 = mysqli_query($dbc, $query2);
			}
		$message = 'PIC Schedule for '.$pic_start_date.' to '.$pic_end_date.' has been updated!';
		}
	elseif(isset($_POST['old_pic_id'])){
		$old_pic_id = $_POST['old_pic_id'];
		
		//Check for date overlaps
		$dup_query = "SELECT * from pic_schedules WHERE (pic_start_date >= '$pic_start_date' and pic_end_date <= '$pic_end_date')
			or (pic_start_date < '$pic_start_date' and pic_end_date >= '$pic_start_date') or 
			(pic_start_date <= '$pic_end_date' and pic_end_date > '$pic_end_date')";
		$dup_result = mysqli_query($dbc, $dup_query);
		if ($dup_result){
			$dup_num_rows = mysqli_num_rows($dup_result);
			if ($dup_num_rows == 0) {
				$query = "INSERT into pic_schedules (pic_start_date, pic_end_date) VALUES ('$pic_start_date','$pic_end_date')";
				$result = mysqli_query($dbc, $query);
				$pic_id = mysqli_insert_id($dbc);
				
				foreach ($pics as $week_type=>$day_array){
					foreach($day_array as $day=>$value){
						if ($value != 'select'){
							$query = "INSERT into pic (week_type, pic_day, emp_id, pic_schedule_id) VALUES 
								('$week_type', '$day','$value','$pic_id')";
							$result = mysqli_query($dbc, $query);
							}
						}
					}
				}
			else{
				$error = 'Overlaps a current PIC schedule. Please adjust date ranges.';
				}
			}
		$message = 'PIC schedule has been added!';
		
		}
	else{
		//Check for date overlaps
		$dup_query = "SELECT * from pic_schedules WHERE (pic_start_date >= '$pic_start_date' and pic_end_date <= '$pic_end_date')
			or (pic_start_date < '$pic_start_date' and pic_end_date >= '$pic_start_date') or 
			(pic_start_date <= '$pic_end_date' and pic_end_date > '$pic_end_date')";
		$dup_result = mysqli_query($dbc, $dup_query);
		if ($dup_result){
			$dup_num_rows = mysqli_num_rows($dup_result);
			if ($dup_num_rows == 0) {
				$query = "INSERT into pic_schedules (pic_start_date, pic_end_date) VALUES ('$pic_start_date','$pic_end_date')";
				$result = mysqli_query($dbc, $query);
				$pic_id = mysqli_insert_id($dbc);
				
				foreach ($pics as $week_type=>$day_array){
					foreach($day_array as $day=>$value){
						if ($value != 'select'){
							$query = "INSERT into pic (week_type, pic_day, emp_id, pic_schedule_id) VALUES 
								('$week_type', '$day','$value','$pic_id')";
							$result = mysqli_query($dbc, $query);
							}
						}
					}
				}
			else{
				$error = 'Overlaps a current PIC schedule. Please adjust date ranges.';
				}
			}
		$message = 'PIC schedule has been added!';
		}
	}

$pic_poss = array();

$query = "SELECT emp_id, first_name, last_name, name_dup FROM employees WHERE active='Active' and pic_status='Y'
	ORDER BY first_name";
$result = mysqli_query($dbc, $query);
if ($result){
	$num_rows = mysqli_num_rows($result);
	if ($num_rows != 0) {
		while($row = mysqli_fetch_assoc($result)){
			$emp_id = $row['emp_id'];
			$fn = $row['first_name'];
			$ln = $row['last_name'];
			if ($row['name_dup'] == 'Y'){
				$last_initial = substr($row['last_name'],0,1);
				$fn .= ' ' . $last_initial . '.';
				}
			$pic_poss[$emp_id] = array($fn,$ln);
			}
		}
	}
else{
	$no_results = TRUE;
	}	
	
$prev_pics = array();
$query = "SELECT * from pic_schedules where pic_end_date>='$today' order by pic_start_date asc";
$result = mysqli_query($dbc, $query);
if ($result){
	$num_rows = mysqli_num_rows($result);
	if ($num_rows != 0) {
		while($row = mysqli_fetch_assoc($result)){
			$pic_schedule_id = $row['pic_schedule_id'];
			$pic_sd = $row['pic_start_date'];
			$pic_ed = $row['pic_end_date'];
			$pic_details = array();
			$pic_form = '';
			
			$query1 = "SELECT * from pic where pic_schedule_id='$pic_schedule_id'";
			$result1 = mysqli_query($dbc, $query1);
			if ($result1){
				while($row = mysqli_fetch_assoc($result1)){
					$week_type = $row['week_type'];
					$day = $row['pic_day'];
					$pic_details[$week_type][$day] = $row['emp_id'];
					}
				}
			$pic_form = '<div class="screen old" id="pic_'.$pic_schedule_id.'" style="display:none;"><table class="pic_table"><tr><th></th>';
			foreach ($days as $k=>$v){
				$pic_form .= '<th>'.ucfirst($v).'</th>';
				}
			$pic_form .= '</tr>';
			foreach ($weeks as $wk=>$wv){
				$pic_form .= '<tr><td>'.ucfirst($wv).'</td>';
				foreach ($days as $dk=>$dv){
					$pic_form .= '<td><select name="pic['.$wv.']['.$dv.']">
						<option value="select">- Select -</option>';
					foreach ($pic_poss as $pk=>$pv){
						$pic_form .= '<option value="' . $pk . '"';
						if ((isset($pic_details[$wv][$dv]))&&($pic_details[$wv][$dv]==$pk)){
							$pic_form .= 'selected="selected"';
							}
						$pic_form .= '>'.$pv[0].'</option>';
						}
					$pic_form .= '</select></td>';
					}
				$pic_form .= '</tr>';
				}
			$pic_form .= '</table></div>';

			$prev_pics[$pic_schedule_id] = array($pic_sd, $pic_ed, $pic_form);
			}
		}
	}

?>
<script>
function deleteSchedule(){
	var agree=confirm("Are you sure you wish to delete this PIC schedule?");
	if (agree){
		return true ;
		}
	else {
		return false ;
		}
	}
	
// Custom function to fill old form values
function fillPIC(){
	$(document).ready(function(){
		$('#picform').show();
		});
	}
$(document).ready(function(){
	$(".picstart_datepick").datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,   
		beforeShowDay: enableSaturdays
		});
	$(".picend_datepick").datepicker({
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
	$('.picstart_datepick').datepicker("option", "onSelect", function(){
		var value = $(this).val();
		var date = new Date(value);
		$( ".picend_datepick" ).datepicker("option", "minDate", new Date(date));
		});
		
	$('#create_pic').click(function(){
		$('.chosen').removeClass('chosen');
		$('#add_pic').trigger("reset");
		$('.addon').remove();
		$('#pic').addClass('chosen');
		$('.old').hide();
		$('#pic').show();
		$('.pic_action').text('Creating New PIC Schedule');
		$('#picform').show();
		});
	$('.edit_pic').click(function(){
		$('.chosen').removeClass('chosen');
		$('#add_pic').trigger("reset");
		$('.addon').remove();
		var picId = $(this).data("pic_id");
		var picDivId = '#pic_'+picId;
		var picStart = $(this).data("picstart");
		var picEnd = $(this).data("picend");
		var dateRange = picStart+' to '+picEnd;
		picStartArray = picStart.split('-');
		picStart = picStartArray[1]+'/'+picStartArray[2]+'/'+picStartArray[0];
		picEndArray = picEnd.split('-');
		picEnd = picEndArray[1]+'/'+picEndArray[2]+'/'+picEndArray[0];
		$('.picstart_datepick').val(picStart);
		$('.picend_datepick').val(picEnd);
		$('#pic').hide();
		$('.old').hide();
		$(picDivId).show();
		$(picDivId).addClass('chosen');
		$('form#add_pic').append('<input type="hidden" class="addon" name="pic_id" value="'+picId+'"/>');
		$('.pic_action').text('Editing PIC Schedule '+dateRange);
		$('#picform').show();
		});
		
	$('.copy_pic').click(function(){
		$('.chosen').removeClass('chosen');
		$('#add_pic').trigger("reset");
		$('.addon').remove();
		var picId = $(this).data("pic_id");
		var picDivId = '#pic_'+picId;
		$('#pic').hide();
		$('.old').hide();
		$(picDivId).show();
		$(picDivId).addClass('chosen');
		$('form#add_pic').append('<input type="hidden" class="addon" name="old_pic_id" value="'+picId+'"/>');
		$('.pic_action').text('Copying PIC Schedule');
		$('#picform').show();
		});
		
	$('input[name=submit]').click(function(){
		$('.screen').not('.chosen').remove();
		return true;
		});
	});
</script>
<div class="mobilewrapper_outer">
<div class="mobilewrapper_inner">
<span class="date"><h1>Assign Person In Charge</h1></span>

<?php
if(isset($error)){
	echo '<div class="errormessage">'.$error.'</div>';
	echo '<script>fillPIC();</script>';
	}
elseif(isset($message)){
	echo '<div class="message">'.$message.'</div>';
	}
	
if ((isset($prev_pics))&&(!empty($prev_pics))){
	echo '<p class="divform" style="margin-bottom:-10px;font-size:14px;color:#013953;font-weight:bold;">Current PIC Schedules</p><div class="picbox">
		<table class="timeoff"><tr><th>Start</th><th>End</th><th></th><th></th><th></th></tr>';
	foreach($prev_pics as $id=>$array){
		echo '<tr><td class="scheddate">'.$array[0].'</td><td class="scheddate">'.$array[1].'</td>';
		echo '<td><button type="button" class="edit_pic" data-pic_id="'.$id.'"
			data-picstart="'.$array[0].'" data-picend="'.$array[1].'">Edit Schedule</button></td>';
		echo '<td><button type="button" class="copy_pic" data-pic_id="'.$id.'"\>Copy</button></td>';
		echo '<td><form action="add_pic" method="post" onsubmit="return deleteSchedule()">
			<input type="hidden" name="pic_id" value="'.$id.'"/>
			<input type="hidden" name="picstart" value="'.$array[0].'"/>
			<input type="hidden" name="picend" value="'.$array[1].'"/>
			<input type="hidden" name="delete" value="TRUE" />
			<input type="submit" name="delete" value="Delete" /></form></td></tr>';
		}
	echo '</table></div>';
	}
?>
<div><button type="button" id="create_pic">New PIC Schedule</button></div>

<div id="picform" style="display:none;">
<div class="pic_action">Creating New PIC Schedule</div>
<form action="add_pic" method="post" name="add_pic" id="add_pic">
	<div style="float:left;margin-right:20px;"><div class="label">Starting Date:</div>
		<input class="picstart_datepick" name="picstart_datepick" placeholder="Choose Starting Sat"
		<?php if(isset($error)){ echo 'value="'.$_POST['picstart_datepick'].'"';};?>/>
	</div>
	<div><div class="label">Ending Date:</div>
		<input class="picend_datepick" name="picend_datepick" placeholder="Choose Ending Fri" 
		<?php if(isset($error)){ echo 'value="'.$_POST['picend_datepick'].'"';};?>/>
	</div>
	<p class="helptext">Leave PIC unset when needed</p> 
<?php
	echo '<div class="screen" id="pic"><table class="pic_table"><tr><th></th>';
	foreach ($days as $k=>$v){
		echo '<th>'.ucfirst($v).'</th>';
		}
	echo '</tr>';
	foreach ($weeks as $wk=>$wv){
		echo '<tr><td>'.ucfirst($wv).'</td>';
		foreach ($days as $dk=>$dv){
			echo '<td><select name="pic['.$wv.']['.$dv.']">
				<option value="select" selected="selected">- Select -</option>';
			foreach ($pic_poss as $pk=>$pv){
				echo '<option value="' . $pk . '"';
				if ((isset($error))&&($pics[$wv][$dv]==$pk)){
					echo 'selected="selected"';
					}
				echo '>'.$pv[0].'</option>';
				}
			echo '</select></td>';
			}
		echo '</tr>';
		}
	echo '</table></div>';
	
	if ((isset($prev_pics))&&(!empty($prev_pics))){
		foreach($prev_pics as $id=>$array){
			echo $array[2];
			}
		}
?>

	<p style="clear:both;"><input type="submit" name="submit" value="Save" /></p>
	<input type="hidden" name="submitted" value="TRUE" />
</form>


</div>


</div></div>

<?php
include ('./includes/footer.html');
?>