<?php #sub_needs.php
$page_title = "Sub Needs" ;
include('./includes/subneedssessionstart.php');

if (isset($_SESSION['came_from'])){
	$came_from = $_SESSION['came_from'];
	}

include('./includes/allsessionvariables.php');

$division = 'All';	
	
$today = date('Y-m-d');

function subs(){
	$query = "SELECT emp_id, concat(first_name, ' ', last_name) as employee_name FROM employees 
		WHERE division = 'Subs' and active = 'Active' ORDER BY last_name asc";
	$result = mysqli_query($dbc, $query);
	while ($row = mysql_fetch_array ($result, MYSQL_ASSOC)){
		$emp_id = $row['emp_id'];
		$name = $row['employee_name'];
		$subs[$emp_id] = $name;
		}
	return $subs;
	}
$subs = subs();

if (($_SESSION['role'] == 'Admin')||($_SESSION['role'] == 'Supervisor')){
	if (isset($_POST['sub_div'])) {
		$_SESSION['sub_needs_division'] = $_POST['division'];
		header ('Location: sub_needs');
		}
	elseif (isset($_SESSION['sub_needs_division'])){
		$division = $_SESSION['sub_needs_division'];
		}
	else{
		$_SESSION['sub_needs_division'] = 'All';
		}
		
	include ('./includes/header.html');
	include ('./includes/supersidebar.html');
?>
	<script>
	function confirmSub(theForm){
		var employee_name = theForm['employee_name'].value;
		var sub_needs_date = theForm['short_date'].value;
		var agree=confirm("Are you sure you wish to confirm "+employee_name+" for "+sub_needs_date+"?");
		if (agree){
			return true ;
			}
		else {
			return false ;
			}
		}
	function deleteSubNeeds(theForm){
		var sub_needs_division = theForm['sub_needs_division'].value;
		var sub_needs_date = theForm['short_date'].value;
		var agree=confirm("Are you sure you wish to delete sub shift on "+sub_needs_date+" for "+sub_needs_division+"?");
		if (agree){
			return true ;}
		else {
			return false ;
			}
		}
	</script>
<?php
	echo '<div class="sub_needs_wrapper"><div class="sub_needs_button"><a href="add_sub_needs">Add Sub Shift</a></div>';
	echo '<span class="date"><h1>Sub Needs</h1></span>'."\n";
	
	if (isset($_POST['add_sub_needs_submitted'])){
		if (isset($_SESSION['sub_needs_division'])&&($_SESSION['sub_needs_division'] == 'All')){
			$division = 'All';
			}
		else{
			$division = $_POST['sub_needs_division'];
			}
		
		$sn_div = $_POST['sub_needs_division'];
		list($sn_mon, $sn_day, $sn_yr) = explode('/',$_POST['sub_needs_date']);
		$sn_date = "$sn_yr-$sn_mon-$sn_day";

		$sns_hr = $_POST['sub_needs_start']['hours'];
		$sns_mn = $_POST['sub_needs_start']['minutes'];
		if (!empty($sns_hr)){
			if ((!is_numeric($sns_hr)) || ((!empty($sns_mn)) && (!is_numeric($sns_mn)))){
				$errors[] = 'Please enter a valid start time.';
				}
			else {
				if ($sns_hr < 7){$sns_hr = $sns_hr+12;}
				if (empty($sns_mn)){
					$sns_mn = '00';
					}
				$sns_time = "$sns_hr:$sns_mn:00";
				}
			}	
		else{
			$errors[] = 'Please enter a start time for this coverage shift.';
			}
	
		$sne_hr = $_POST['sub_needs_end']['hours'];
		$sne_mn = $_POST['sub_needs_end']['minutes'];
		if (!empty($sne_hr)){
			if ((!is_numeric($sne_hr)) || ((!empty($sne_mn)) && (!is_numeric($sne_mn)))){
				$errors[] = 'Please enter a valid end time.';
				}
			else {
				if (empty($sne_mn)){
					$sne_mn = '00';
					}
				if (($sne_hr < $sns_hr)||(($sns_hr == $sne_hr)&&($sne_mn <= $sns_mn))) {$sne_hr = $sne_hr+12;}
				if ($sne_hr < 7){$sne_hr = $sne_hr+12;}
				$sne_time = "$sne_hr:$sne_mn:00";
				}
			}
		else{
			$errors[] = 'Please enter an end time for this coverage shift.';
			}

		//Insert into db
		$query = "INSERT into sub_needs (sub_needs_date, sub_needs_start_time, sub_needs_end_time, sub_needs_division) 
			values ('$sn_date', '$sns_time', '$sne_time', '$sn_div')";
		$result = mysqli_query($dbc, $query) or die(mysql_error($dbc));
		if ($result) {
			echo "<div class=\"message\">Sub need shift entered for <b>$sn_div</b> on <b>$sn_date</b></div>";
			}
		}
	
	if (isset($_POST['confirm'])){
		$division = $_SESSION['sub_needs_division'];
	
		$emp_id = $_POST['emp_id'];
		$name = $_POST['employee_name'];
		$sub_needs_id = $_POST['sub_needs_id'];
		$sub_needs_division = $_POST['sub_needs_division'];
		$sub_needs_date = $_POST['sub_needs_date'];
		$short_date = $_POST['short_date'];
		$sub_needs_start_time = $_POST['sub_needs_start_time'];
		$sub_needs_end_time = $_POST['sub_needs_end_time'];
		
		//Check for overlaps
		$query = "SELECT e.emp_id, division, concat(first_name, ' ', last_name) as employee_name, coverage_division,
			coverage_date, coverage_start_time, coverage_end_time FROM coverage as t, employees as e 
			WHERE e.emp_id = t.emp_id and e.emp_id = '$emp_id' 
			and coverage_date = '$sub_needs_date' and (('$sub_needs_start_time' >= coverage_start_time and 
			'$sub_needs_start_time' < coverage_end_time) 
			or ('$sub_needs_end_time' > coverage_start_time and '$sub_needs_end_time' <= coverage_end_time) 
			or ('$sub_needs_start_time' <= coverage_start_time and '$sub_needs_end_time' >= coverage_end_time))"; 
		$result = mysqli_query($dbc, $query);
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
			}
		
		if (empty($errors)) {
			$query1 = "UPDATE sub_needs set sub_needs_covered='Y' WHERE sub_needs_id='$sub_needs_id'";
			$result1 = mysqli_query($dbc, $query1);
			$query2 = "INSERT into coverage (emp_id, coverage_date, coverage_start_time, coverage_end_time, coverage_division,
				coverage_offdesk, coverage_reason) VALUES ('$emp_id','$sub_needs_date', '$sub_needs_start_time', 
				'$sub_needs_end_time', '$sub_needs_division', 'On', '')";
			$result2 = mysqli_query($dbc, $query2);
			echo '<div class="message"><b>'. $name . ' on ' . $short_date . '</b> has been confirmed.</div>';
			}
		else{
			echo '<div class="errormessage"><h3>Error!</h3><br/>
			The following error(s) occurred:<br/><br/>';
			foreach ($errors as $msg) { //Print each error
				echo " - $msg<br/>\n";
				}
			echo '</div>';
			}
		}
		
	if (isset($_POST['delete'])){
		$division = $_SESSION['sub_needs_division'];
		
		$sub_needs_id = $_POST['sub_needs_id'];
		$short_date = $_POST['short_date'];
		$query = "DELETE from sub_needs WHERE sub_needs_id='$sub_needs_id'";
		$result = mysqli_query($dbc, $query);
		echo '<div class="message">Sub shift on <b>' . $short_date . '</b> has been deleted.</div>';
		}
		
	$div = array('All', 'Adult', 'Children', 'Customer Service', 'LTI', 'Teen');
	echo '<form action="sub_needs" method="post">
		<p class="divform">Sub Request Division: 
			<select name="division" onchange="this.form.submit();">';
			echo $division;
	foreach ($div as $key => $d){
		echo '<option value="' . $d . '" ';
		if (isset($division)){
			if ($division==$d) {echo 'selected="selected"';}
			}
		echo '>' . $d . '</option>';
		}
	echo '</select>
		<input type="hidden" name="sub_div" value="TRUE" />
		</p>
		</form>';

	if (((isset($_POST['sub_div']))||(isset($_SESSION['sub_needs_division']))) && ($division !== 'All')){
		$query = "SELECT sub_needs_id, sub_needs_division, sub_needs_date, sub_needs_start_time, 
			time_format(sub_needs_start_time,'%k') as sub_needs_start, 
			time_format(sub_needs_start_time,'%i') as sub_needs_start_minutes, sub_needs_end_time,
			time_format(sub_needs_end_time,'%k') as sub_needs_end, 
			time_format(sub_needs_end_time,'%i') as sub_needs_end_minutes, sub_needs_emp_id
			FROM sub_needs
			WHERE sub_needs_date >= '$today' and sub_needs_covered = 'N' and sub_needs_division = '$division'
			ORDER by sub_needs_date asc, sub_needs_start_time asc, sub_needs_division asc";
		}
	else {
		$query = "SELECT sub_needs_id, sub_needs_division, sub_needs_date, sub_needs_start_time, 
			time_format(sub_needs_start_time,'%k') as sub_needs_start, 
			time_format(sub_needs_start_time,'%i') as sub_needs_start_minutes, sub_needs_end_time,
			time_format(sub_needs_end_time,'%k') as sub_needs_end, 
			time_format(sub_needs_end_time,'%i') as sub_needs_end_minutes, sub_needs_emp_id
			FROM sub_needs
			WHERE sub_needs_date >= '$today' and sub_needs_covered = 'N'
			ORDER by sub_needs_date asc, sub_needs_start_time asc, sub_needs_division asc";
		}
	$result = mysqli_query($dbc, $query);
	if ($result){
		$num_rows = mysql_num_rows($result);
		if ($num_rows != 0) {
			echo '<div class="divboxes">'."\n".'<table class="sub_needs sort">'."\n";
			echo '<tr class="headrow"><th class="division">Division</th><th class="datetime">Shift</th>
				<th class="assign">Assign</th><th class="confirm"></th><th></th></tr>';
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
				$sub_needs_id = $row['sub_needs_id'];
				$sub_needs_division = $row['sub_needs_division'];
				$sub_needs_date = $row['sub_needs_date'];
				$sub_needs_start_time = $row['sub_needs_start_time'];
				$sub_needs_start_hours = $row['sub_needs_start'];
				$sub_needs_start_minutes = $row['sub_needs_start_minutes'];
				$sub_needs_end_time = $row['sub_needs_end_time'];
				$sub_needs_end_hours = $row['sub_needs_end'];
				$sub_needs_end_minutes = $row['sub_needs_end_minutes'];
				$sub_needs_emp_id = $row['sub_needs_emp_id'];
				$sns12 = NULL;
				$sne12 = NULL;
				
				//Adjust 24-hour time.		
				if ($sub_needs_start_hours > 12){
					$sns12 = $sub_needs_start_hours - 12;
					if ($sub_needs_start_minutes != '00') {
						$sns12 .= ':'.$sub_needs_start_minutes;
						}
					$sne12 = $sub_needs_end_hours - 12;
					if ($sub_needs_end_minutes != '00') {
						$sne12 .= ':'.$sub_needs_end_minutes;
						}
					$sne12 .= 'pm';
					}
				elseif ($sub_needs_start_hours == 12){
					$sns12 = $sub_needs_start_hours;
					if ($sub_needs_start_minutes != '00') {
						$sns12 .= ':'.$sub_needs_start_minutes;
						}
					$sne12 = $sub_needs_end_hours - 12;
					if ($sub_needs_end_minutes != '00') {
						$sne12 .= ':'.$sub_needs_end_minutes;
						}
					$sne12 .= 'pm';
					}					
				else {
					$sns12 = $sub_needs_start_hours;
					if ($sub_needs_start_minutes != '00') {
						$sns12 .= ':'.$sub_needs_start_minutes;
						}
					$sns12 .= 'am';
					if ($sub_needs_end_hours > 12){
						$sne12 = $sub_needs_end_hours - 12;
						if ($sub_needs_end_minutes != '00') {
							$sne12 .= ':'.$sub_needs_end_minutes;
							}
						$sne12 .= 'pm';
						}
					elseif ($sub_needs_end_hours == 12){
						$sne12 = $sub_needs_end_hours;
						if ($sub_needs_end_minutes != '00') {
							$sne12 .= ':'.$sub_needs_end_minutes;
							}
						$sne12 .= 'pm';
						}
					else {
						$sne12 = $sub_needs_end_hours;
						if ($sub_needs_end_minutes != '00') {
							$sne12 .= ':'.$sub_needs_end_minutes;
							}
						$sne12 .= 'am';
						}
					}
				
				//Date specifics
				$snmonth = date('M', strtotime($sub_needs_date));
				$snday = date('j', strtotime($sub_needs_date));
				$sndow = date('D', strtotime($sub_needs_date));
				if ((date('Y', strtotime($sub_needs_date))) > date('Y')){
					$snyear = date('Y', strtotime($sub_needs_date));
					$snyear = ' '.$snyear;
					}
				else {
					$snyear = NULL;
					}
				
				//Find Declines
				$declined = array();
				$declined_ordered = array();
				$query2 = "SELECT emp_id from sub_needs_declined WHERE sub_needs_id='$sub_needs_id'";
				$result2 = mysqli_query($dbc, $query2);
				while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)){
					$emp_id = $row2['emp_id'];
					$declined[] = $emp_id;
					}
				foreach ($subs as $emp_id=>$name){
					if (in_array($emp_id, $declined)){
						$declined_ordered[] = $emp_id;
						}
					}
				
				echo "<tr><td class=\"division\">$sub_needs_division</td>";
				echo "<td class=\"datetime\"><span class=\"todate\">$sndow, $snday $snmonth$snyear</span>";
				echo ", $sns12 - $sne12";
				echo "</td>";
				echo "<td class=\"assign\">";
				if ($sub_needs_emp_id != NULL){
					$sub = $subs[$sub_needs_emp_id];
					echo "Shift assigned to <span class=\"assign_style\">$sub</span>";
					}
				else {
					echo "Shift not yet assigned...";
					if (!empty($declined)){
						echo '<br/><b>Declined by:</b> <span class="decline">';
						foreach ($declined_ordered as $key=>$emp_id){
							if ($key != 0){
								echo ', ';
								}
							echo $subs[$emp_id];
							}
						echo '</span>';
						}
					}
				echo "</td>";
				echo "<td class=\"confirm\">";
				if ($sub_needs_emp_id != NULL){
					echo '<form action="sub_needs" method="post" onsubmit="return confirmSub(this)">
						<input type="hidden" name="sub_needs_id" value="' . $sub_needs_id . '"/>
						<input type="hidden" name="emp_id" value="' . $sub_needs_emp_id . '"/>
						<input type="hidden" name="employee_name" value="' . $sub . '"/>
						<input type="hidden" name="sub_needs_division" value="' . $sub_needs_division . '"/>
						<input type="hidden" name="sub_needs_date" value="' . $sub_needs_date . '"/>
						<input type="hidden" name="short_date" value="' . $snmonth . ' ' .$snday.$snyear . '"/>
						<input type="hidden" name="sub_needs_start_time" value="' . $sub_needs_start_time . '"/>
						<input type="hidden" name="sub_needs_end_time" value="' . $sub_needs_end_time . '"/>
						<input type="hidden" name="confirm" value="TRUE" />
						<input type="submit" name="confirm" value="Confirm" /></form>';
					}
				echo '</td>';
				echo '<td class="delete">';
				if ($sub_needs_emp_id == NULL){
					echo '<form action="sub_needs" method="post" onsubmit="return deleteSubNeeds(this)">
						<input type="hidden" name="sub_needs_id" value="' . $sub_needs_id . '"/>
						<input type="hidden" name="sub_needs_division" value="' . $sub_needs_division . '"/>
						<input type="hidden" name="short_date" value="' . $snmonth . ' ' .$snday.$snyear . '"/>
						<input type="hidden" name="delete" value="TRUE" />
						<input type="submit" name="deletesub" value="Delete" /></form>';
					}
				echo '</td>';
				echo "</tr>\n";
				}
			echo "</table>\n</div>";
			}
		else {
			echo '<div class="divboxes"><table class="sub_needs">
				<tr><td style="padding:3px 20px;">There are no pending sub requests.</td></tr></table></div>';
			}
		}
	echo '</div>';
	}
elseif ($_SESSION['role'] == 'Subs'){
	include ('./includes/header.html');
	include ('./includes/sidebar.html');
?>
	<script>
	$(document).ready(function(){
		$("select[name*='subs']").change(function(){
			getScrollXY();
			var sub = $("option:selected", this).text();
			var emp_id = $(this).val();
			var form = $(this).closest("form").attr("id");
			if (emp_id != 'cancel'){
				if(confirm('Switch sub to '+sub+'?')){
					$('#'+form).submit();
					}
				else{
					$("select option[value='NULL']").attr("selected","selected");
					}
				}
			else {
				if(confirm('Clear sub?')){
					$('#'+form).submit();
					}
				else{
					$("select option[value='NULL']").attr("selected","selected");
					}
				}
			});
		$('.declined_check').change(function(){
			if($(this).is(':checked')){
				$(this).parent().find(".comment").show();
				}
			else {
				$(this).parent().find(".comment").hide();
				}
			});
		});
	</script>
<?php
	echo '<span class="date"><h1>Sub Needs</h1></span>'."\n";
	if (isset($_POST['confirm'])){
		$sub_needs_id = $_POST['sub_needs_id'];
		$sub_needs_emp_id = $_POST['subs_'.$sub_needs_id];
		
		if ($sub_needs_emp_id == 'cancel'){
			$query = "UPDATE sub_needs set sub_needs_emp_id=NULL WHERE sub_needs_id='$sub_needs_id'";
			$result = mysqli_query($dbc, $query);
			}
		else {
			$query1 = "UPDATE sub_needs set sub_needs_emp_id='$sub_needs_emp_id' WHERE sub_needs_id='$sub_needs_id'";
			$result1 = mysqli_query($dbc, $query1);
			}
		}
	if (isset($_POST['decline'])){
		$sub_needs_id = $_POST['sub_needs_id'];
		$emp_id = $_POST['declined'];
		$query2 = "INSERT into sub_needs_declined (sub_needs_id, emp_id) VALUES ('$sub_needs_id', '$emp_id')";
		$result2 = mysqli_query($dbc, $query2);
		}
	if (isset($_POST['decline_comment'])){
		$sub_needs_declined_id = $_POST['sub_needs_declined_id'];
		$comment = escape_data($_POST['declined_comment']);
		$query6 = "UPDATE sub_needs_declined SET comment='$comment' WHERE sub_needs_declined_id = '$sub_needs_declined_id'";
		$result6 = mysqli_query($dbc, $query6);
		}
	if (isset($_POST['undecline'])){
		$sub_needs_declined_id = $_POST['sub_needs_declined_id'];
		$query3 = "DELETE from sub_needs_declined WHERE sub_needs_declined_id='$sub_needs_declined_id'";
		$result3 = mysqli_query($dbc, $query3);
		}
	if (isset($_POST['available'])){
		$sub_needs_id = $_POST['sub_needs_id'];
		$emp_id = $_POST['availability'];
		$query4 = "INSERT into sub_needs_available (sub_needs_id, emp_id) VALUES ('$sub_needs_id', '$emp_id')";
		$result4 = mysqli_query($dbc, $query4);
		}
	if (isset($_POST['available_comment'])){
		$sub_needs_available_id = $_POST['sub_needs_available_id'];
		$comment2 = escape_data($_POST['available_comment']);
		$query7 = "UPDATE sub_needs_available SET comment='$comment2' WHERE sub_needs_available_id = '$sub_needs_available_id'";
		$result7 = mysqli_query($dbc, $query7);
		}
	if (isset($_POST['unavailable'])){
		$sub_needs_available_id = $_POST['sub_needs_available_id'];
		$query5 = "DELETE from sub_needs_available WHERE sub_needs_available_id='$sub_needs_available_id'";
		$result5 = mysqli_query($dbc, $query5);
		}
		
	$query = "SELECT sub_needs_id, sub_needs_division, sub_needs_date, time_format(sub_needs_start_time,'%k') as sub_needs_start, 
		time_format(sub_needs_start_time,'%i') as sub_needs_start_minutes, time_format(sub_needs_end_time,'%k') as sub_needs_end, 
		time_format(sub_needs_end_time,'%i') as sub_needs_end_minutes, sub_needs_emp_id
		FROM sub_needs
		WHERE sub_needs_date >= '$today' and sub_needs_covered = 'N'
		ORDER by sub_needs_date asc, sub_needs_start_time asc, sub_needs_division asc";
	$result = mysqli_query($dbc, $query);
	if ($result){
		$num_rows = mysql_num_rows($result);
		if ($num_rows != 0) {
			echo '<div class="divboxes">'."\n".'<table class="sub_needs">'."\n";
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
				$sub_needs_id = $row['sub_needs_id'];
				$sub_needs_division = $row['sub_needs_division'];
				$sub_needs_date = $row['sub_needs_date'];
				$sub_needs_start_hours = $row['sub_needs_start'];
				$sub_needs_start_minutes = $row['sub_needs_start_minutes'];
				$sub_needs_end_hours = $row['sub_needs_end'];
				$sub_needs_end_minutes = $row['sub_needs_end_minutes'];
				$sub_needs_emp_id = $row['sub_needs_emp_id'];
				$sns12 = NULL;
				$sne12 = NULL;
				
				//Adjust 24-hour time.		
				if ($sub_needs_start_hours > 12){
					$sns12 = $sub_needs_start_hours - 12;
					if ($sub_needs_start_minutes != '00') {
						$sns12 .= ':'.$sub_needs_start_minutes;
						}
					$sne12 = $sub_needs_end_hours - 12;
					if ($sub_needs_end_minutes != '00') {
						$sne12 .= ':'.$sub_needs_end_minutes;
						}
					$sne12 .= 'pm';
					}
				elseif ($sub_needs_start_hours == 12){
					$sns12 = $sub_needs_start_hours;
					if ($sub_needs_start_minutes != '00') {
						$sns12 .= ':'.$sub_needs_start_minutes;
						}
					$sne12 = $sub_needs_end_hours - 12;
					if ($sub_needs_end_minutes != '00') {
						$sne12 .= ':'.$sub_needs_end_minutes;
						}
					$sne12 .= 'pm';
					}					
				else {
					$sns12 = $sub_needs_start_hours;
					if ($sub_needs_start_minutes != '00') {
						$sns12 .= ':'.$sub_needs_start_minutes;
						}
					$sns12 .= 'am';
					if ($sub_needs_end_hours > 12){
						$sne12 = $sub_needs_end_hours - 12;
						if ($sub_needs_end_minutes != '00') {
							$sne12 .= ':'.$sub_needs_end_minutes;
							}
						$sne12 .= 'pm';
						}
					else {
						$sne12 = $sub_needs_end_hours;
						if ($sub_needs_end_minutes != '00') {
							$sne12 .= ':'.$sub_needs_end_minutes;
							}
						$sne12 .= 'am';
						}
					}
				
				//Date specifics
				$snmonth = date('M', strtotime($sub_needs_date));
				$snday = date('j', strtotime($sub_needs_date));
				$sndow = date('D', strtotime($sub_needs_date));
				if ((date('Y', strtotime($sub_needs_date))) > date('Y')){
					$snyear = date('Y', strtotime($sub_needs_date));
					$snyear = ' '.$snyear;
					}
				else {
					$snyear = NULL;
					}
				
				//Find Declines
				$declined = array();
				$query2 = "SELECT * from sub_needs_declined WHERE sub_needs_id='$sub_needs_id'";
				$result2 = mysqli_query($dbc, $query2);
				while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)){
					$sub_needs_declined_id = $row2['sub_needs_declined_id'];
					$emp_id = $row2['emp_id'];
					$comment = $row2['comment'];
					$declined[$emp_id] = array($sub_needs_declined_id=>$comment);
					}

				//Find Availables
				$available = array();
				$query3 = "SELECT * from sub_needs_available WHERE sub_needs_id='$sub_needs_id'";
				$result3 = mysqli_query($dbc, $query3);
				while ($row3 = mysql_fetch_array($result3, MYSQL_ASSOC)){
					$sub_needs_available_id = $row3['sub_needs_available_id'];
					$emp_id = $row3['emp_id'];
					$comment2 = $row3['comment'];
					$available[$emp_id] = array($sub_needs_available_id=>$comment2);
					}
				
				echo "<tr><td class=\"division\">$sub_needs_division</td>";
				echo "<td class=\"datetime\"><span class=\"todate\">$sndow, $snday $snmonth$snyear</span>";
				echo ", $sns12 - $sne12";
				echo "</td>";

				if ($sub_needs_emp_id != NULL){
					echo "<td class=\"assign\" colspan=\"2\">";
					$sub = $subs[$sub_needs_emp_id];
					echo "Shift assigned to <span class=\"assign_style\">$sub</span>";
					echo "</td>";
					}
				else {
					echo "<td class=\"assign\">";
					echo '<b>Decline:</b><br/>';
					foreach($subs as $emp_id=>$name){
						if (array_key_exists($emp_id, $declined)){
							$decarray = $declined[$emp_id];
							foreach ($decarray as $k=>$v){
								$sub_needs_declined_id = $k;
								$comment = $v;
								echo '<form id="undecline" action="sub_needs" method="post" class="undecline" style="float:left;margin-right:5px;">
									<input class="declined_check" type="checkbox" onchange="getScrollXY();this.form.submit();" name="declined" value="'.$emp_id.'"';
								echo ' checked="checked"';
								echo '><span class="decline">'.$name.'</span>';
								echo '<input type="hidden" name="undecline" value="TRUE" />
									<input type="hidden" name="sub_needs_declined_id" value="'.$sub_needs_declined_id.'"/>
									</form>';
								echo '<div class="comment" style="display:inline;text-align:right;">
									<form class="declinecomment" action="sub_needs" method="post">
									<input type="submit" name="submit" value="Save" onclick="getScrollXY();this.form.submit();"/>
									<input type="hidden" name="decline_comment" value="TRUE" />
									<input type="hidden" name="sub_needs_declined_id" value="'.$sub_needs_declined_id.'"/>
									<input type="text" name="declined_comment" size="20" maxlength="140" value="'.$comment.'"/>
									</form>
									</div>';	
								}
							}
						else {
							echo '<form id="decline" action="sub_needs" method="post">
								<input class="declined_check" type="checkbox" onchange="getScrollXY();this.form.submit();" name="declined" value="'.$emp_id.'"';
							echo '><span class="decline">'.$name.'</span>';
							echo '<input type="hidden" name="decline" value="TRUE" />
								<input type="hidden" name="sub_needs_id" value="'.$sub_needs_id.'"/>
								</form>';
							}
						}
					echo "</td>";
					echo "<td class=\"assign\">";
					echo '<b>Available:</b><br/>';
					foreach($subs as $emp_id=>$name){
						if (array_key_exists($emp_id, $available)){
							$avarray = $available[$emp_id];
							foreach ($avarray as $k=>$v){
								$sub_needs_available_id = $k;
								$comment2 = $v;
								echo '<form id="unavailable" action="sub_needs" method="post" class="undecline" style="float:left;margin-right:5px;">
									<input type="checkbox" onchange="getScrollXY();this.form.submit();" name="availability" value="'.$emp_id.'"';
								echo ' checked="checked"';
								echo '><span class="decline">'.$name.'</span><br/>';
								echo '<input type="hidden" name="unavailable" value="TRUE" />
									<input type="hidden" name="sub_needs_available_id" value="' . $sub_needs_available_id . '"/>
									</form>';	
								echo '<div class="comment" style="display:inline;text-align:right;">
									<form class="declinecomment" action="sub_needs" method="post">
									<input type="submit" name="submit" value="Save" onclick="getScrollXY();this.form.submit();"/>
									<input type="hidden" name="available_comment" value="TRUE" />
									<input type="hidden" name="sub_needs_available_id" value="'.$sub_needs_available_id.'"/>
									<input type="text" name="available_comment" size="20" maxlength="140" value="'.$comment2.'"/>
									</form>
									</div>';
								}
							}
						else {
							echo '<form id="available" action="sub_needs" method="post">
								<input type="checkbox" onchange="getScrollXY();this.form.submit();" name="availability" value="'.$emp_id.'"';
							echo '><span class="decline">'.$name.'</span><br/>';
							echo '<input type="hidden" name="available" value="TRUE" />
								<input type="hidden" name="sub_needs_id" value="' . $sub_needs_id . '"/>
								</form>';	
							}
						}
					echo "</td>";
					}

				echo "<td class=\"assign\" style=\"vertical-align:top;\">";
				if ($sub_needs_emp_id == NULL){
					echo "<b>Accept:</b><br/>";
					}
				echo '<form id="switch_sub_'.$sub_needs_id.'" class="subassign" action="sub_needs" method="post">
					<select name="subs_'.$sub_needs_id.'" style="width:142px;">';
				echo "<option value=\"NULL\" selected=\"selected\"></option>\n";
				foreach ($subs as $key => $value) {
					echo "<option value=\"$key\">$value</option>\n";
					}
				if ($sub_needs_emp_id != NULL){
					echo "<option value=\"cancel\">...cancel assignment</option>\n";
					}
				echo '</select>
					<input type="hidden" name="sub_needs_id" value="' . $sub_needs_id . '"/>
					<input type="hidden" name="confirm" value="TRUE" />
					</form>';
				echo '</td>';
				echo "</tr>\n";
				}
			echo "</table>\n</div>";
			}
		else {
			echo '<div class="divboxes"><table class="sub_needs">
				<tr><td style="padding:3px 20px;">There are no pending sub requests.</td></tr></table></div>';
			}
		}
	}
if ((isset($came_from)) && ($came_from == 'sub_needs')){
	if((isset($_POST['scrollTop'])) && (isset($_POST['scrollLeft']))){
?>
<script>
$(document).ready(function(){
	var setTop = <?php if(isset($_POST['scrollTop'])){echo $_POST['scrollTop'];} ?>;
	var setLeft = <?php if(isset($_POST['scrollLeft'])){echo $_POST['scrollLeft'];} ?>;
	$(document).scrollTop(setTop);
	$(document).scrollLeft(setLeft);
});
</script>
<?php
		}
	}
include ('./includes/footer.html');
?>