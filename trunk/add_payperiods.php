<?php # add_payperiods.php

$page_title = "Enter Yearly Pay Periods";
include('./includes/supersessionstart.php');
include('./includes/allsessionvariables.php');
include ('./includes/header.html');
echo '<div id="mobilehack">';
$mobilehack = 1;
include ('./includes/supersidebar.html');

?>

<div class="mobilewrapper_outer">
<div class="mobilewrapper_inner">
<span class="date"><h1>Enter Yearly Pay Periods</h1></span>

<?php 
$year = date('Y');
$error = '';
$m_error = '';

$periods = array();
$query = "SELECT * from pay_periods WHERE pp_year='$year'";
$result = mysql_query($query);
$num_rows = mysql_num_rows($result);
if ($num_rows != 0){
	$periods = array();
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
		$cycle = $row['pp_cycle'];
		$start_date = $row['pp_start_date'];
		$periods[$cycle] = $start_date;
		}
	}
	
if (isset($_POST['submitted'])){
	$pp_year = $_POST['pp_year'];
	mysql_query("BEGIN");
	
	//Prevent duplicate data.
	$query = "SELECT * from pay_periods WHERE pp_year='$pp_year'";
	$result = mysql_query($query);
	$num_rows = mysql_num_rows($result);
	if ($num_rows != 0){
		$query = "DELETE from pay_periods WHERE pp_year='$pp_year'";
		$result = mysql_query($query);
		if(!($result)){
			$m_error = TRUE;
			}
		}
	for($i=1; $i<=27; $i++) {
		$input = 'ppdate_'.$i;
		if ($i != 27){
			if ($_POST[$input] == ''){
				$error = TRUE;
				$error_msg = "Please enter all the pay periods dates for the year.";
				}
			else {
				list($pp_mon, $pp_day, $pp_yr) = explode('/',$_POST[$input]);
				$pp_date = "$pp_yr-$pp_mon-$pp_day";
				$query = "INSERT into pay_periods (pp_year, pp_cycle, pp_start_date) VALUES 
					('$pp_year','$i','$pp_date')";
				${'result'.$i} = mysql_query($query);
				if(!(${'result'.$i})){
					$m_error = TRUE;
					}
				}
			}
		else{
			if ($_POST[$input] != ''){
				list($pp_mon, $pp_day, $pp_yr) = explode('/',$_POST[$input]);
				$pp_date = "$pp_yr-$pp_mon-$pp_day";
				$query = "INSERT into pay_periods (pp_year, pp_cycle, pp_start_date) VALUES 
					('$pp_year','$i','$pp_date')";
				${'result'.$i} = mysql_query($query);
				if(!(${'result'.$i})){
					$m_error = TRUE;
					}
				}
			}	
		}
	if (($error != TRUE) && ($m_error != TRUE)){
		mysql_query("COMMIT");
		echo '<div class="message">Pay peroods for <b>'.$pp_year .'</b> have been added.</div>';
		}
	else {
		mysql_query("ROLLBACK");
		}
	}

if ($error == TRUE){
	echo '<div class="errormessage"><h3>Error!</h3><br/>'.$error_msg.'</div>';
	}
?>
<script>
$(document).ready(function() {
	$(".ppdatepick").datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,   
		beforeShowDay: enableSaturdays
		});
	
	// Custom function to enable Saturday only in jQuery calender
	function enableSaturdays(date) {
		var day = date.getDay();
		return [(day == 6), ''];
		}

	$('.ppdatepick').datepicker("option", "onSelect", function(){
		var value = $(this).val();
		var date = new Date(value);
		$(this).closest('tr').nextAll().each(function(){
			date.setDate(date.getDate() + 14);
			$(this).children().children('.ppdatepick').datepicker("setDate", new Date(date));
			});
		$('#ppdate_27').datepicker("setDate");
		});
	});
</script>
<div class="coverform" style="float:left;">
	<form action="add_payperiods" method="post" name="DateForm" id="pay_periods">
		<div class="label">Choose Year:</div>
		<div class="cal"><?php make_calendar_pulldowns(NULL, NULL, $year, 'pp'); ?></div>
		<table>
			<tr>
				<th>Cycle #</th>
				<th>Pay Period Start Date</th>
			</tr>
<?php for($i=1; $i<=27; $i++) {
		$input = 'ppdate_'.$i;
		echo '<tr><td>'.$i.'</td><td>
			<input class="ppdatepick" id="ppdate_'.$i.'" name="ppdate_'.$i.'" placeholder="Choose 1st Sat"';
		if (isset($_POST[$input])){
			echo 'value="'.$_POST[$input].'"';
			}
		echo '/>
			</td></tr>';
		}
?>
		</table>
		<p><input type="submit" name="submit" value="Save" /></p>
			<input type="hidden" name="submitted" value="TRUE" />
	</form>
</div>
<div class="pay_periods">
	<div class="divspec"><?php echo $year;?> Pay Periods</div>
	<div class="divboxes">
		<table class="timeoff">
		<tr><th>Cycle</th><th>Start Date</th></tr>
<?php foreach ($periods as $cycle=>$start){
	echo '<tr><td>'.$cycle.'</td><td>'.$start.'</td></tr>';
	}
?>
	</table>
	</div><br/>
<i>Re-add (right) to edit</i>
</div>
</div>
</div>
<?php
include ('./includes/footer.html');
?>