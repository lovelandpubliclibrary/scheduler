<?php # edit_employee.php
include('./includes/supersessionstart.php');
$came_from = $_SESSION['came_from'];
	
if (($came_from != 'view_employees') && ($came_from != 'edit_employee')){
	header ('Location: view_employees');
	}

if (isset($_POST['from_view_emp'])){
	$_SESSION['edit_employee_name'] = $_POST['employee_name'];
	$_SESSION['edit_emp_id'] = $_POST['emp_id'];
	header('Location:edit_employee');
	}
else{
	$employee = $_SESSION['edit_employee_name'];
	$emp_id = $_SESSION['edit_emp_id'];
	}
	
include('./includes/allsessionvariables.php');

require_once ('../mysql_connect.php'); //Connect to the db.

$query = "SELECT employee_number, first_name, last_name, division, exempt_status, weekly_hours, home_phone, mobile_phone, employee_lastday, assignment_id
	FROM employees, logins WHERE employees.emp_id='$emp_id' and employees.emp_id=logins.emp_id";
$result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));


while ($row = mysqli_fetch_assoc($result)) {
	$empno = $row['employee_number'];
	$first_name = $row['first_name'];
	$last_name = $row['last_name'];
	$division = $row['division'];
	$exempt_status = $row['exempt_status'];
	$weekly_hours = $row['weekly_hours'];
	$home_phone = $row['home_phone'];
	$mobile_phone = $row['mobile_phone'];
	$emp_ld = $row['employee_lastday'];
	$emp_ld = explode("-", $emp_ld);
	$assignment_id = $row['assignment_id'];

//Check if the form has been submitted.
if (isset($_POST['edited'])) {
		
	$errors = array(); //Initialize error array.
	
	//Check for Employee Number.
	if (empty($_POST['employee_number'])) {
		$errors[] = 'Please enter the employee number.';
		}
	else {
		$empno = escape_data($_POST['employee_number']);
		}
	
	//Check for Assignment ID.
	if (empty($_POST['assignment_id'])) {
		$errors[] = 'Please enter the Assignment ID.';
		}
	else {
		$assignment_id = escape_data($_POST['assignment_id']);
		}

	//Check for First Name.
	if (empty($_POST['first_name'])) {
		$errors[] = 'Please enter employee first name.';
		}
	else {
		$fn = escape_data($_POST['first_name']);
		$fn = ucwords(strtolower($fn));
		}
		
	//Check for Last Name.
	if (empty($_POST['last_name'])) {
		$errors[] = 'Please enter employee last name.';
		}
	else {
		$ln = escape_data($_POST['last_name']);
		$ln = ucwords($ln);
		}
	
	//Assign Division.
	if (empty($_POST['division'])) {
		$errors[] = 'Please choose a division.';
		}
	else {
		$div = $_POST['division'];
		}
	
	//Assign Exempt Status.
	$exs = $_POST['exempt_status'];
	
	//Check for Weekly Hours.
	if (empty($_POST['weekly_hours'])) {
		$errors[] = 'Please enter employee\'s weekly allotted hours.';
		}
	else {
		$hrs = escape_data($_POST['weekly_hours']);
		}
	
	//Clean contact info
	if (!empty($_POST['home_phone'])){
		$home_phone = $_POST['home_phone'];
		$strips = array('-','.',' ','(',')');
		$safe_home_phone = str_replace($strips,'',$home_phone);
		$safe_home_phone = preg_replace("/^(\d{3})(\d{3})(\d{4})$/", "$1-$2-$3", $safe_home_phone);
		}
	else{
		$safe_home_phone = '';
		}
	if (!empty($_POST['mobile_phone'])){
		$mobile_phone = $_POST['mobile_phone'];
		$strips = array('-','.',' ','(',')');
		$safe_mobile_phone = str_replace($strips,'',$mobile_phone);
		$safe_mobile_phone = preg_replace("/^(\d{3})(\d{3})(\d{4})$/", "$1-$2-$3", $safe_mobile_phone);
		}
	else{
		$safe_mobile_phone = '';
		}
		
	//Check for Last Day
	if ((empty($_POST['emp_lastday'])) || (($_POST['emp_lastday']) == '')){
		$emp_lastday = null;
		}
	else {
		if (isset($_POST['def'])){
			list($ld_mon, $ld_day, $ld_yr) = explode('/',$_POST['emp_lastday']);
			$emp_lastday = "$ld_yr-$ld_mon-$ld_day";
			}
		else {
			$emp_lastday = null;
			}
		}
	
	if (empty($errors)) {
	
		$query = "UPDATE employees SET employee_number='$empno', first_name='$fn', last_name='$ln', division='$div', exempt_status='$exs',
			weekly_hours='$hrs', home_phone='$safe_home_phone', mobile_phone='$safe_mobile_phone',employee_lastday=";
		if ($emp_lastday == null){
			$query .= "null";
			}
		else {
			$query .= "'$emp_lastday'";
			}
		$query .= " WHERE emp_id = '$emp_id'";
		$result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));
		
		$query_assign = "UPDATE logins SET assignment_id='$assignment_id' WHERE emp_id='$emp_id'";
		$result_assign = mysqli_query($dbc, $query_assign) or die(mysqli_error($dbc));
		
		// Switch shifts to new division, if applicable
		if ($div != $division){
			$today = date('Y-m-d');
			$query1 = "SELECT specific_schedule from schedules WHERE division='$division'
				and schedule_start_date <= '$today' and schedule_end_date >= '$today'";
			$result1 = mysqli_query($dbc, $query1);
			while ($row1 = mysqli_fetch_assoc($result1)){
				$old_specific_schedule = $row1['specific_schedule'];
				}
			$query2 = "SELECT specific_schedule from schedules WHERE division='$div'
				and schedule_start_date <= '$today' and schedule_end_date >= '$today'";
			$result2 = mysqli_query($dbc, $query2);
			while ($row2 = mysqli_fetch_assoc($result2)){
				$new_specific_schedule = $row2['specific_schedule'];
				}
			if (isset($new_specific_schedule)){
				$query3	= "UPDATE shifts SET specific_schedule = $new_specific_schedule WHERE emp_id='$emp_id' and specific_schedule='$old_specific_schedule'";
				$result3 = mysqli_query($dbc, $query3) or die(mysqli_error());
				}
			}
		
		if ($result && $result_assign) {
			$_SESSION['success'] = TRUE;
			header ('Location: view_employees');

			echo '<div class="message"><b>'. $fn . ' ' . $ln . '</b> has been updated.<br/>
				To edit another employee <a href="view_employees">click here</a></div></div>';
			
			include ('./includes/footer.html');
			exit();
			}
		else {
			$page_title = "Edit $employee" ;
			include('./includes/supersessionstart.php');
			include ('./includes/header.html');
			include ('./includes/supersidebar.html');	
			echo '<div class="errormessage"><h3>System Error</h3>
				The employee could not be added due to a system error.
				We apologize for the inconvenience.</div>';
			include ('./includes/footer.html');
			exit();
			}
		}
	else {
		echo '<div class="errormessage"><h3>Error!</h3><br/>
		The following error(s) occurred:<br/><br/>';
		foreach ($errors as $msg) {
			echo " - $msg<br/>\n";
			}
		echo '</div>';
		}
		mysqli_close($dbc);
	}	

$page_title = "Edit $employee" ;
include ('./includes/header.html');
echo '<div id="mobilehack">';
$mobilehack = 1;
include ('./includes/supersidebar.html');	
	
	?>

<script>
function validateForm() {
	var x=document.Employee.employee_number.value;
	var y=document.Employee.first_name.value;
	var z=document.Employee.last_name.value;
	var aa=document.Employee.division.value;
	var ab=document.Employee.weekly_hours.value;
	
	if (x==null || x==""){
		alert("Please enter an employee number.");
		return false;
		}
	if (x!=parseInt(x)){
		alert("Please enter a valid employee number.");
		return false;
		}
	if (y==null || y==""){
		alert("Please enter a first name.");
		return false;
		}
	if (z==null || z==""){
		alert("Please enter a last name.");
		return false;
		}
	if (aa=="select"){
		alert("Please select a division.");
		return false;
		}
	if (ab==null || ab==""){
		alert("Please enter the weekly hours.");
		return false;
		}
	if (ab!=parseInt(ab)){
		alert("Please enter valid weekly hours.");
		return false;
		}
	}
function showMe (it, box) {
	var val = document.getElementById("def").value;
	var fill = (box.checked) ? val : "";
	var vis = (box.checked) ? "inline" : "none";
	
	document.getElementById(it).style.display = vis;
	document.getElementById("def").value = val;
	//$('input[name=emp_lastday]').val(val);
	}
</script>
<div class="mobilewrapper_outer">
<div class="mobilewrapper_inner">
<div id="edit_emp"><span class="date"><h1>Edit Employee</h1></span>
<div class="empform"><div class="dp">Edit Info for</div>
<div class="emph"><?php echo $employee; ?></div>
<form action="edit_employee" method="post" name="Employee" onsubmit="return validateForm();">
	<p><div class="label">Employee Number:</div> <input type="number" name="employee_number" size="20" maxlength="7"
	value="<?php if (isset($empno)) echo $empno;?>" /></p>
	<p><div class="label">Assignment ID:</div> <input type="text" name="assignment_id" size="20" maxlength="20"
	value="<?php if (isset($assignment_id)) echo $assignment_id;?>" /></p>
	<p><div class="label">First Name:</div> <input type="text" name="first_name" size="15" maxlength="15"
	value="<?php if (isset($first_name)) echo $first_name;?>" /></p>
	<p><div class="label">Last Name:</div> <input type="text" name="last_name" size="15" maxlength="30"
	value="<?php if (isset($last_name)) echo $last_name;?>" /></p>
	<p><div class="label">Division:</div> 
		<select name="division">
			<option value="select" disabled="disabled">- Select -</option>
			<?php foreach ($divisions as $k=>$v){echo '<option value="'.$v.'"';
			if ((isset($division))&&($division == $v)){echo 'selected="selected"';}
			echo '>'.$v.'</option>';} ?>
		</select>
	</p>
	<p><div class="label">Exempt Status:</div> <input type="radio" name="exempt_status" value="Exempt" 
	<?php if ($exempt_status == 'Exempt'){echo 'checked';} ?>/>Exempt 
	<input type="radio" name="exempt_status" value="Non-Exempt" 
	<?php if ($exempt_status == 'Non-Exempt'){echo 'checked';} ?> />Non-Exempt<br/>
	<p><div class="label">Weekly Hours:</div> <input type="number" name="weekly_hours" size="20" maxlength="3"
	value="<?php if (isset($weekly_hours)) echo $weekly_hours;?>" /></p>
	<p><div class="label">Home Phone:</div> <input type="text" name="home_phone" size="15" maxlength="15"
	value="<?php if (isset($home_phone)) echo $home_phone;?>"/></p>
	<p><div class="label">Mobile Phone:</div> <input type="text" name="mobile_phone" size="15" maxlength="15"
	value="<?php if (isset($mobile_phone)) echo $mobile_phone;?>"/></p>
	<?php if (isset($emp_ld[1])){
		echo '<input type="checkbox" name="def" onclick="showMe(\'def\', this)" checked> Set Last Day
			<div id="def" style="display:inline;margin-left:10px;">
				<input class="datepick" id="datepicker1" name="emp_lastday" placeholder="Click to choose" 
				value="'.$emp_ld[1].'/'.$emp_ld[2].'/'.$emp_ld[0].'" />
			</div>';
		}
	else {
		echo '<input type="checkbox" name="def" onclick="showMe(\'def\', this)"> Set Last Day
			<div id="def" style="display:none;margin-left:10px;">
				<input class="datepick" id="datepicker1" name="emp_lastday" placeholder="Click to choose" />
			</div>';
		}
	?>
	<p><input type="submit" name="submit" value="Save" /></p>
	<input type="hidden" name="edited" value="TRUE" />
	<input type="hidden" name="employee_name" value="<?php echo $first_name;?> <?php echo $last_name; ?>" />
</form></div></div></div></div>

<?php
	}
include ('./includes/footer.html');
?>
</div>