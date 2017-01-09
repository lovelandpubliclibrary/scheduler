<?php #add_pic_coverage.php
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
<span class="date"><h1>Schedule PIC Coverage</h1></span>

<?php
$pic_poss = array();

$query = "SELECT emp_id, first_name, last_name FROM employees WHERE active='Active' and pic_status='Y'
	ORDER BY first_name";
$result = mysql_query($query);
if ($result){
	$num_rows = mysql_num_rows($result);
	if ($num_rows != 0) {
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
			$emp_id = $row['emp_id'];
			$fn = $row['first_name'];
			$ln = $row['last_name'];
			$pic_poss[$emp_id] = array($fn,$ln);
			}
		}
	}
else{
	$no_results = TRUE;
	}

if (isset($_POST['submitted'])){
	list($pic_mon, $pic_day, $pic_yr) = explode('/',$_POST['pic_coverage_date']);
	$pic_date = "$pic_yr-$pic_mon-$pic_day";
	$pic = $_POST['pic_poss'];
	
	//Check for overlaps
	$query = "SELECT e.emp_id, first_name FROM employees e, pic_coverage c WHERE 
		e.emp_id = c.emp_id and pic_coverage_date='$pic_date'";
	$result = mysql_query($query);
	if ($result){
	$num_rows = mysql_num_rows($result);
		if ($num_rows != 0) {
			$error = "There is already a PIC scheduled to cover this date.";
			}
		}
	
	if(!isset($error)){
		$query = "INSERT into pic_coverage (pic_coverage_date, emp_id) values ('$pic_date', '$pic')";
		$result = mysql_query($query);
		
		//Echo change success message
		echo "<div class=\"message\"><b>PIC Coverage entered for</b> <a href=\"$pic_yr/";
		echo "$pic_mon/$pic_day\" title=\"See Schedule\">$pic_date</a></div>";
		}
	else{
		echo '<div class="errormessage"><h3>Error!</h3><br/>'.$error.'</div>';
		}
	}

?>

<div class="coverform">
	<form action="add_pic_coverage" method="post" name="DateForm" onsubmit="return validator();">
		<div class="label">Coverage Date:</div>
		<div class="cal">
			<input class="datepick" id="datepicker1" name="pic_coverage_date" placeholder="Click to choose" 
			<?php if ((isset($error)) && (isset($_POST['pic_coverage_date']))){echo 'value="'.$_POST['pic_coverage_date'].'"';}?>/>
		</div><br/>
		<div class="label">PIC:</div>
		<select name="pic_poss">
			<option value="select" disabled="disabled" selected="selected">- Select -</option>
<?php
			foreach ($pic_poss as $pk=>$pv){
				echo '<option value="' . $pk . '" ';
				if ((isset($error))&&($pic == $pk)){
					echo 'selected="selected"';
					}
				echo '>'.$pv[0].'</option>';
				}
?>
		</select>
		<p><input type="submit" name="submit" value="Save" /></p>
			<input type="hidden" name="submitted" value="TRUE" />
	</form>
</div>
</div>
</div>
<?php
include ('./includes/footer.html');
?>