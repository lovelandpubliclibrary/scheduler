<?php #replace_employee.php

$page_title = "Replace Employee";
include('./includes/supersessionstart.php');
include('./includes/allsessionvariables.php');
include ('./includes/header.html');
echo '<div id="mobilehack">';
$mobilehack = 1;
include ('./includes/supersidebar.html');
$today= date('Y-m-d');
?>

<script>
function replaceEmployee() {

	}

function DynamicSelect(id1, id2) {
		// Get references
		this.s1 = document.getElementById(id1);
		this.s2 = document.getElementById(id2);
		
		// Parse the dependent select box and create an object representation
		var o = this.s2Obj = {};
		o.id = id2;
		o.options = [];
		var oNodes = this.s2.getElementsByTagName("option"),
			ol = oNodes.length;
		for (var i = 0; i < ol; i++) {
			var cNodes = oNodes[i].childNodes,
			cl = cNodes.length,
			txt;
			for (var j = 0; j < cl; j++) {
				if(cNodes[j].nodeType === 3) {
					txt = cNodes[j].nodeValue;
					break;
				}
			}
			o.options.push({
				dataParentVal: oNodes[i].getAttribute("data-parent-value"),
				val: oNodes[i].getAttribute("value"),
				label: txt
			});
		}
		//console.dir(this.s2Obj);
		
		//	Add handlers and init
		var _this = this; 
		this.s1.onchange = function() {
			_this.update();
		};
		this.update();
	}
	DynamicSelect.prototype.update = function() {
		// Recreate the select box from the object
		var s2New = document.createElement("select");
		s2New.setAttribute("id", this.s2Obj.id);
		s2New.setAttribute("name", "oldemployee");
		var options = this.s2Obj.options,
			ol = options.length;
		for (var i = 0; i < ol; i++) {
			// Only add the relevant options
			if (options[i].dataParentVal === "0" || options[i].dataParentVal === this.s1.options[this.s1.selectedIndex].value) {
				var oNode = document.createElement("option");
				oNode.setAttribute("data-parent-value", options[i].dataParentVal);
				oNode.setAttribute("value", options[i].val);
				var txtNode = document.createTextNode(options[i].label);
				oNode.appendChild(txtNode);
				s2New.appendChild(oNode);
			}
		}
		
		// Swap out old and new select elements
		var s2 = document.getElementById(this.s2Obj.id);
		s2.parentNode.replaceChild(s2New, s2);
	};
	window.onload = function() {
		var pdaDynamicSelect = new DynamicSelect("division", "name");
	};

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
	var agree=confirm("Are you sure you wish to replace?");
	if (agree){
		return true ;}
	else {
		return false ;}
	}
</script>

<div class="mobilewrapper_outer">
<div class="mobilewrapper_inner">
<span class="date"><h1>Replace Employee</h1></span>

<?php

if (isset($_POST['submitted'])) {
	$errors = array();
	
	//Assign Old Employee Variable.
	$oldemp_id = $_POST['oldemployee'];
	
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
		}
		
	//Check for Last Name.
	if (empty($_POST['last_name'])) {
		$errors[] = 'Please enter employee last name.';
		}
	else {
		$ln = escape_data($_POST['last_name']);
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
	
		//Include the new employee in the database

		//Check for previous record.
		$query = "SELECT employee_number, first_name, last_name FROM employees WHERE employee_number='$empn' or (first_name='$fn' and last_name='$ln')";
		$result = mysql_query($query);
		if (mysql_num_rows($result) == 0) {
			$query2 = "SELECT emp_id, first_name FROM employees where first_name='$fn'";
			$result2 = mysql_query($query2);
			
			if (mysql_num_rows($result2) != 0) {
				while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)){
					$emp_id = $row2['emp_id'];
					$query3 = "UPDATE employees SET name_dup='Y' WHERE emp_id='$emp_id'";
					$result3 = mysql_query($query3);
					}
				
				//Make the query.
				$query4 = "INSERT INTO employees(employee_number, first_name, last_name, division, exempt_status, weekly_hours, active, name_dup,
					home_phone, mobile_phone, employee_create) 
					VALUES ('$empn', '$fn', '$ln', '$div', '$exs', '$hrs', 'Active', 'Y', '$safe_home_phone','$safe_mobile_phone', null)";
				$result4 = mysql_query($query4);
				if ($result4) {
					$emp_id = mysql_insert_id();
					$query5 = "UPDATE employees SET active='Inactive' WHERE emp_id='$oldemp_id'";
					$result5 = mysql_query($query5);
					
					$query6 = "SELECT specific_schedule from schedules WHERE schedule_end_date>'$today'";
					$result6 = mysql_query($query6);
					
					while ($row6 = mysql_fetch_array($result6, MYSQL_ASSOC)){
						$specific_schedule = $row6['specific_schedule'];
						$query7 = "INSERT into shifts (week_type, shift_day, emp_id, shift_start, shift_end, 
							desk_start, desk_end, desk_start2, desk_end2, lunch_start, lunch_end, specific_schedule, schedule_create) 
							SELECT week_type, shift_day, $emp_id, shift_start, shift_end, desk_start, desk_end, 
							desk_start2, desk_end2, lunch_start, lunch_end, specific_schedule, null from shifts 
							where emp_id='$oldemp_id' and specific_schedule='$specific_schedule'";
						$result7 = mysql_query($query7);
						}
					
					//Print a message.
					echo '<div class="message"><b>Thank you!</b><br/>
					' . $fn . ' ' . $ln . ' has been added.<br/><br/>
					<a href="replace_employee">Replace another employee</a></div></div></div>';
					include ('./includes/footer.html');
					exit();
					}
				else {
					echo '<div class="errormessage"><div class="errorhead"><h5>Error!</h5></div>
					<div class="errortext">The employee could not be entered due to a system error.
					Please alert the webmaster.</div></div></div></div>';
					include ('./includes/footer.html');
					exit();
					}
				}
			else {
				//Make the query.
				$query3 = "INSERT INTO employees(employee_number, first_name, last_name, division, exempt_status, weekly_hours, active, 
					home_phone, mobile_phone, employee_create) 
					VALUES ('$empn', '$fn', '$ln', '$div', '$exs', '$hrs', 'Active', '$safe_home_phone','$safe_mobile_phone', null)";
				$result3 = mysql_query($query3);
				if ($result3) {
					$emp_id = mysql_insert_id();
					$query4 = "UPDATE employees SET active='Inactive' WHERE emp_id='$oldemp_id'";
					$result4 = mysql_query($query4);
					
					$query5 = "SELECT specific_schedule from schedules WHERE schedule_end_date>'$today'";
					$result5 = mysql_query($query5);
					
					while ($row5 = mysql_fetch_array($result5, MYSQL_ASSOC)){
						$specific_schedule = $row5['specific_schedule'];
						$query6 = "INSERT into shifts (week_type, shift_day, emp_id, shift_start, shift_end, 
							desk_start, desk_end, desk_start2, desk_end2, lunch_start, lunch_end, specific_schedule, schedule_create) 
							SELECT week_type, shift_day, $emp_id, shift_start, shift_end, desk_start, desk_end, 
							desk_start2, desk_end2, lunch_start, lunch_end, specific_schedule, null from shifts 
							where emp_id='$oldemp_id' and specific_schedule='$specific_schedule'";
						$result6 = mysql_query($query6);
						}
						
					//Print a message.
					echo '<div class="message"><b>Thank you!</b><br/>
					' . $fn . ' ' . $ln . ' has been added.<br/><br/>
					<a href="replace_employee">Replace another employee</a></div></div></div>';
					include ('./includes/footer.html');
					exit();
					}
				else {
					echo '<div class="message"><div class="errorhead"><h5>Error!</h5></div>
					<div class="errortext">The employee could not be entered due to a system error.
					Please alert the webmaster.</div></div></div></div>';
					include ('./includes/footer.html');
					exit();
					}
				}
			}
		else {
			echo '<div class="message"><div class="errorhead"><h5>Error!</h5></div>
			<div class="errortext">There is a duplicate record. <br/>The following records already exist:<br/><br/>';
				while ($row = mysql_fetch_array ($result, MYSQL_ASSOC)) {
					echo  'Employee #' . $row['employee_number'] . ', ' . $row['first_name'] . ' ' . $row['last_name'] . '<br/>';
					}
				echo '</div></div>';
			}
		}
	else {
		echo '<div class="message"><div class="errorhead"><h5>Error!</h5></div>
		<div class="errortext">The following error(s) occurred:<br/>';
		foreach ($errors as $msg) { //Print each error
			echo " - $msg<br/>\n";
			}
		echo '</div></div>';
		}
	}

//Get employee info for dynamic selects
$query = "SELECT division, emp_id, first_name, last_name FROM employees WHERE active='Active'
	ORDER BY division asc, last_name asc";
$result = mysql_query($query) or die(mysql_error($dbc));

while ($row = mysql_fetch_array ($result, MYSQL_ASSOC)) {
	$array[]=$row;
	}
?>

<div class="empform">
	<form action="replace_employee" method="post" name="Employee" onsubmit="return validateForm();">
		<span class="dp">Employee to Replace in Future Schedules:</span><br/><br/>
		<div class="indent">
			<select id="division" name="division">
				<option value="0">Select Division...</option>
				<?php foreach ($divisions as $k=>$v){echo '<option value="'.$v.'">'.$v.'</option>';} ?>
			</select><br/>
			<select id="name">
				<option data-parent-value="0" value="0">Select Employee...</option>
<?php
				foreach ($array as $row){
					$employee = $row['first_name'] . ' ' . $row['last_name'];
					$emp_id = $row['emp_id'];
					$division = $row['division'];
					echo '<option data-parent-value="' . $division . '" value="' . $emp_id . '" name="test">'
						. $employee . '</option>';
					}
?>
			</select>
		</div>
		<hr/>
		<div class="newemp">
			<span class="dp">New Employee</span><br/>
			<div class="indent">
				<p><div class="label">Employee Number:</div> <input type="number" name="employee_number" size="20" maxlength="7"
					value="<?php if (isset($_POST['employee_number'])) echo $_POST['employee_number'];?>"/></p>
				<p><div class="label">First Name:</div> <input type="text" name="first_name" size="15" maxlength="15"
					value="<?php if (isset($_POST['first_name'])) echo $_POST['first_name'];?>"/></p>
				<p><div class="label">Last Name:</div> <input type="text" name="last_name" size="15" maxlength="30"
					value="<?php if (isset($_POST['last_name'])) echo $_POST['last_name'];?>"/></p>
				<p><div class="label">Exempt Status:</div> <input type="radio" name="exempt_status" value="Exempt"/>Exempt 
					<input type="radio" name="exempt_status" value="Non-Exempt" checked />Non-Exempt<br/>
				<p><div class="label">Weekly Hours:</div> <input type="number" name="weekly_hours" size="20" maxlength="2"
					value="<?php if (isset($_POST['weekly_hours'])) echo $_POST['weekly_hours'];?>"/></p>
				<p><div class="label">Home Phone:</div> <input type="text" name="home_phone" size="15" maxlength="15"
					value="<?php if (isset($_POST['home_phone'])) echo $_POST['home_phone'];?>"/></p>
				<p><div class="label">Mobile Phone:</div> <input type="text" name="mobile_phone" size="15" maxlength="15"
					value="<?php if (isset($_POST['mobile_phone'])) echo $_POST['mobile_phone'];?>"/></p>
			</div>
			<p><input type="submit" name="submit" value="Replace" /></p>
			<input type="hidden" name="submitted" value="TRUE" />
		</div>
	</form>
</div>
</div>
</div>
		
<?php
include ('./includes/footer.html');
?>
</div>