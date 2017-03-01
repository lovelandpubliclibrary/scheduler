<?php # add_employee.php

$page_title = "Add Employee";
include('./includes/supersessionstart.php');
include('./includes/allsessionvariables.php');
include ('./includes/header.html');
echo '<div id="mobilehack">';
$mobilehack = 1;
include ('./includes/supersidebar.html');

echo '<span class="date"><h1>Add Employee</h1></span>';

//Check if the form has been submitted.
if (isset($_POST['submitted'])) {
		
	$errors = array(); //Initialize error array.
	
	//Check for Employee Number.
	if (empty($_POST['employee_number'])) {
		$errors[] = 'Please enter the employee number.';
		}
	elseif (!is_numeric($_POST['employee_number'])){
		$errors[] = 'Please enter a valid employee number.';
		}
	else{
		$empn = escape_data($_POST['employee_number']);
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
	elseif (!is_numeric($_POST['weekly_hours'])){
		$errors[] = 'Please enter numeric weekly hours.';
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
	
	if (empty($errors)) {//If everything's okay.
	
		//Include the user in the database

		//Check for previous record.
		$query = "SELECT employee_number, first_name, last_name FROM employees 
			WHERE employee_number='$empn' or (first_name='$fn' and last_name='$ln')";
		$result = mysql_query($query);
		if (mysql_num_rows($result) == 0) {
			$query2 = "SELECT employee_number, first_name FROM employees where first_name='$fn'";
			$result2 = mysql_query($query2);
			
			if (mysql_num_rows($result2) != 0) {
				while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)){
					$empno = $row2['employee_number'];
					$query3 = "UPDATE employees SET name_dup='Y' WHERE employee_number='$empno'";
					$result3 = mysql_query($query3);
					}
				$query4 = "INSERT INTO employees
					(employee_number, first_name, last_name, division, exempt_status, weekly_hours, active, name_dup, 
					home_phone, mobile_phone) 
					VALUES ('$empn', '$fn', '$ln', '$div', '$exs', '$hrs', 'Active', 'Y', '$safe_home_phone','$safe_mobile_phone')";
				$result4 = mysql_query($query4);
				if ($result4) {//If it ran okay.
				
					//Print a message.
					echo '<div class="message">' . $fn . ' ' . $ln . ' has been added.</div>';
					
					}
				else {//If it did not run okay.
					echo '<div class="errormessage"><h3>System Error</h3>
						The employee could not be added due to a system error.
						We apologize for the inconvenience.</div>';
					include ('./includes/footer.html');
					exit();
					}
				}
			else {
				//Make the query.
				$query3 = "INSERT INTO employees
					(employee_number, first_name, last_name, division, exempt_status, weekly_hours, active, 
					home_phone, mobile_phone) 
					VALUES ('$empn', '$fn', '$ln', '$div', '$exs', '$hrs', 'Active', '$safe_home_phone','$safe_mobile_phone')";
				$result3 = mysql_query($query3); //Run the query.
				if ($result3) {//If it ran okay.
					
					//Print a message.
					echo '<div class="message">' . $fn . ' ' . $ln . ' has been added.</div>';
					
					}
				else {//If it did not run okay.
					echo '<div class="errormessage"><h3>System Error</h3>
						The employee could not be added due to a system error.
						We apologize for the inconvenience.</div>';
					include ('./includes/footer.html');
					exit();
					}
				}
			}
		else {
			echo '<div class="errormessage"><h3>Error!</h3><br/>
				There is a duplicate record.<br/>
				The following records already exist:<p>';
				while ($row = mysql_fetch_array ($result, MYSQL_ASSOC)) {
					echo  $row['employee_number'] . ' - ' . $row['first_name'] . ' ' . $row['last_name'] . '<br/>';
					}
			echo '</p></div>';
			}
		}
	else {
		echo '<div class="errormessage"><h3>Error!</h3><br/>
		The following error(s) occurred:<br/><br/>';
		foreach ($errors as $msg) { //Print each error
			echo " - $msg<br/>\n";
			}
		echo '</div>
			<form action="add_employee" method="post">
			<p>Employee Number: <input type="number" name="employee_number" size="20" maxlength="5"
			value="';
		if (isset($_POST['employee_number'])) echo $_POST['employee_number'];
		echo '" /></p>
			<p>First Name: <input type="text" name="first_name" size="15" maxlength="15"
			value="';
		if (isset($_POST['first_name'])) echo $_POST['first_name'];
		echo '" /></p>
			<p>Last Name: <input type="text" name="last_name" size="15" maxlength="30"
			value="';
		if (isset($_POST['last_name'])) echo $_POST['last_name'];
		echo '" /></p>
			<p>Division: 
				<select name="division">
					<option value="select" disabled="disabled" selected="selected">- Select -</option>';
		foreach ($divisions as $k=>$v){
			echo '<option value="'.$v.'"';
			if((isset($_POST['division'])) && ($_POST['division']==$v)){
				echo 'selected="selected"';
				}
			echo '>'.$v.'</option>';
			}
			echo '
				</select>
			</p>
			<p>Exempt Status: <input type="radio" name="exempt_status" value="Exempt"/>Exempt 
			<input type="radio" name="exempt_status" value="Non-Exempt" checked />Non-Exempt<br/>
			<p>Weekly Hours: <input type="number" name="weekly_hours" size="20" maxlength="2"
			value="';
			if (isset($_POST['weekly_hours'])) echo $_POST['weekly_hours'];
			echo '" /></p>
			<p><div class="label">Home Phone:</div> <input type="text" name="home_phone" size="15" maxlength="15"
			value="';
			if (isset($_POST['home_phone'])) echo $_POST['home_phone'];
			echo '" /></p>
			<p><div class="label">Mobile Phone:</div> <input type="text" name="mobile_phone" size="15" maxlength="15"
			value="';
			if (isset($_POST['mobile_phone'])) echo $_POST['mobile_phone'];
			echo '" /></p>
			<p><input type="submit" name="submit" value="Create" /></p>
			<input type="hidden" name="submitted" value="TRUE" />
		</form>';

include ('./includes/footer.html');

exit();		
		}
		mysql_close();
	}
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
</script>
<div class="mobilewrapper_outer">
<div class="mobilewrapper_inner">
<div class="empform">
<form action="add_employee" method="post" name="Employee" onsubmit="return validateForm();">
	<p><div class="label">Employee Number:</div> <input type="number" name="employee_number" size="20" maxlength="7"/></p>
	<p><div class="label">First Name:</div> <input type="text" name="first_name" size="15" maxlength="15"/></p>
	<p><div class="label">Last Name:</div> <input type="text" name="last_name" size="15" maxlength="30"/></p>
	<p><div class="label">Division:</div> 
		<select name="division">
			<option value="select" disabled="disabled" selected="selected">- Select -</option>
<?php foreach ($divisions as $k=>$v){echo '<option value="'.$v.'">'.$v.'</option>';} ?>
		</select>
	</p>
	<p><div class="label">Exempt Status:</div> <input type="radio" name="exempt_status" value="Exempt"/>Exempt 
	<input type="radio" name="exempt_status" value="Non-Exempt" checked />Non-Exempt<br/>
	<p><div class="label">Weekly Hours:</div> <input type="number" name="weekly_hours" size="20" maxlength="2" /></p>
	<p><div class="label">Home Phone:</div> <input type="text" name="home_phone" size="15" maxlength="15"/></p>
	<p><div class="label">Mobile Phone:</div> <input type="text" name="mobile_phone" size="15" maxlength="15"/></p>
	<p><input type="submit" name="submit" value="Create" /></p>
	<input type="hidden" name="submitted" value="TRUE" />
</form>
</div>
</div>
</div>
<?php
include ('./includes/footer.html');
?>
</div>