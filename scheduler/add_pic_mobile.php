<?php #add_pic_mobile.php

$page_title = 'Person In Charge';
include('./includes/supersessionstart.php');

if (isset($_SESSION['came_from'])){
	$came_from = $_SESSION['came_from'];
	}

include('./includes/allsessionvariables.php');
include ('./includes/header.html');
include ('./includes/supersidebar.html');
include ('./display_functions.php');

date_default_timezone_set('America/Denver');
$start_year = date('Y');
$end_year = date('Y') + 10;

$seasons = array('Spring','Summer','Fall');
$weeks = array('a','b','c','d');
$pic_poss = array();

$query = "SELECT employee_number, first_name, last_name FROM employees WHERE active='Active' and pic_status='Y'
	ORDER BY last_name";
$result = mysql_query($query);
if ($result){
	$num_rows = mysql_num_rows($result);
	if ($num_rows != 0) {
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
			$empno = $row['employee_number'];
			$fn = $row['first_name'];
			$ln = $row['last_name'];
			$pic_poss[$empno] = array($fn,$ln);
			}
		}
	}
else{
	$no_results = TRUE;
	}

if (isset($_POST['season'])){
	$season = $_POST['season'];
	}
if (isset($_POST['year'])){
	$year = $_POST['year'];
	}

if (isset($_POST['submitted'])){
	$pics = $_POST['pic'];
	foreach ($pics as $k=>$v){
		//Check for duplicates
		$dup_query = "SELECT * from pic where schedule='$k'";
		$dup_result = mysql_query($dup_query);
		if ($dup_result){
			$dup_num_rows = mysql_num_rows($dup_result);
			if ($dup_num_rows != 0) {
				$query = "UPDATE pic SET employee_number='$v' WHERE schedule='$k'";
				}
			else{
				$query = "INSERT into pic (schedule, employee_number) values ('$k','$v')";
				}
			$result = mysql_query($query);
			}
		}
	$message = 'Persons in Charge for '.$season.' '.$year.' have been updated!';
	}

echo '<span class="date"><h1>Assign Person In Charge</h1></span>';

if(isset($message)){
	echo '<div class="message">'.$message.'</div>';
	}

echo '<div class="picdetail"><form action="add_pic_mobile" method="post">
	<div class="picfloat">Season:
		<select name="season" onchange="this.form.submit();">
			<option value="select" disabled="disabled" selected="selected">- Select -</option>';
foreach ($seasons as $key => $d){
	echo '<option value="' . $d . '" ';
	if (isset($season)){
		if ($season==$d) {echo 'selected="selected"';}
		}
	echo '>' . $d . '</option>';
	}	
echo'</select></div>
	<div class="picfloat">Year:		
	<select name="year" onchange="this.form.submit();">';
for ($y = $start_year; $y<=$end_year; $y++) {
	echo "<option value =\"$y\"";
	if (isset($year)){
		if ($year==$y){
			echo ' selected="selected"';
			}
		}
	echo ">$y</option>";
	}
echo '</select>
	</div><div style="clear:both;"></div>
	<input type="hidden" name="season_submitted" value="TRUE" />
</form></div>';

if ((isset($_POST['season_submitted']))||((isset($_POST['submitted'])))) {
	$season = $_POST['season'];
	$year = $_POST['year'];
	if ($season != 'Summer'){
		$days = array('sat','sun','mon','tue','wed','thu');
		}
	else{
		$days = array('sat','mon','tue','wed','thu');
		}
	
	$entered_pic = array();
	$str = '_'.$year.'_'.strtolower($season);
	$pic_query = "SELECT * FROM pic WHERE schedule like '%$str'";
	$pic_result = mysql_query($pic_query);
	if ($result){
		$num_rows = mysql_num_rows($pic_result);
		if ($num_rows != 0) {
			while($row = mysql_fetch_array($pic_result, MYSQL_ASSOC)){
				$schedule = $row['schedule'];
				$pic_assign = $row['employee_number'];
				$entered_pic[$schedule] = $pic_assign;
				}
			}
		}
		
	echo '<div class="pic_form"><form action="add_pic_mobile" method="post">';
	
	//For Mobile
	echo '<div class="mobile">';
	foreach ($weeks as $wk=>$wv){
		echo '<table class="pic_table"><tr><td><b>'.ucfirst($wv).'</b></td><td></td></tr>';
		foreach ($days as $dk=>$dv){
			echo '<tr><td>'.ucfirst($dv).'</td>';
			$sched_name = $wv.'_'.$dv.'_'.$year.'_'.strtolower($season);
			echo '<td><select name="pic['.$sched_name.']">
				<option value="select" disabled="disabled" selected="selected">- Select -</option>';
			foreach ($pic_poss as $pk=>$pv){
				echo '<option value="' . $pk . '" ';
				if ((array_key_exists($sched_name,$entered_pic))&&($pk == $entered_pic[$sched_name])){
					echo 'selected="selected"';
					}
				echo '>'.$pv[0].'</option>';
				}
			echo '</select></td></tr>';
			}
		echo '</table>';
		}
	echo '</div>';
	
	echo '<div class="pic_submit"><input type="submit" name="save" value="Save PIC" /></div>
		<input type="hidden" name="submitted" value="TRUE" />
		<input type="hidden" name="season" value="'.$season.'" />
		<input type="hidden" name="year" value="'.$year.'" />
		</form></div>';
	}

include ('./includes/footer.html');
?>