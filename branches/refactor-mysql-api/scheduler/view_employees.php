<?php #view_employees.php

$page_title = 'Library Employees';
include('./includes/supersessionstart.php');

if (isset($_SESSION['came_from'])){
	$came_from = $_SESSION['came_from'];
	}

if (isset($_POST['division'])) {
	$_SESSION['emp_division'] = $_POST['division'];
	header ('Location: view_employees');
	}
elseif (isset($_SESSION['emp_division'])){
	$division = $_SESSION['emp_division'];
	}
else{
	}
	
include('./includes/allsessionvariables.php');
include ('./includes/header.html');
echo '<div id="mobilehack">';
$mobilehack = 1;
include ('./includes/supersidebar.html');

//Page header
echo '<span class="date"><h1>Library Employees</h1></span>';

if (($came_from == 'edit_employee') && (isset($_SESSION['success']))){
	$name = $_SESSION['edit_employee_name'];
	$emp_id = $_SESSION['edit_emp_id'];
	echo '<div class="message"><b>'. $name . '</b> has been updated.</div>';
	unset($_SESSION['success']);
	}
?>

<script>
$(document).ready(function() {
	$('.nametip').each(function(){
		var tooltiptext = 'Home: '+$(this).attr('data-home_phone')+'<br/>Mobile: '+$(this).attr('data-mobile_phone');
		var name = $(this).attr('data-name');
		$(this).qtip({
			content: {
				text: tooltiptext,
				title: 'Contact '+name
				},
			position: {
				my: 'center left',
				at: 'center center'
				},
			style: {
				classes: 'qtip-dark qtip-shadow'
				},
			hide: {
				fixed: true,
                delay: 100
				}
			});
		});
	});

function deleteEmployee(theForm)
{
var employee_name = theForm['employee_name'].value;
var agree=confirm("Are you sure you wish to delete "+employee_name+"?");
if (agree){
	return true ;}
else {
	return false ;}
}
</script>

<script src="./js/sorttable.js"></script>
<div class="mobilewrapper_outer">
<div class="mobilewrapper_inner">

<?php
require_once ('../mysql_connect.php');

if (isset($_POST['delete'])){
	$emp_id = $_POST['emp_id'];
	$name = $_POST['employee_name'];
	$query1 = "UPDATE employees set active='Inactive' WHERE emp_id='$emp_id'";
	$result1 = mysqli_query($dbc, $query1);
	echo '<div class="message"><b>'. $name . '</b> has been deleted.</div>';
	}

echo '<form action="view_employees" method="post">
	<p class="divform">Division: 
		<select name="division" onchange="this.form.submit();">
			<option value="All">All</option>';
foreach ($divisions as $key => $d){
	echo '<option value="' . $d . '" ';
	if (isset($division)){
		if ($division==$d) {echo 'selected="selected"';}
		}
	echo '>' . $d . '</option>';
	}
echo '</select>
		<br/><i>Click column header to re-sort</i>
	</p>
</form>';

if ((isset($division)) && ($division !== 'All')) {
	
	$query = "SELECT emp_id, last_name, first_name, employee_number, exempt_status, weekly_hours, division, 
		home_phone, mobile_phone FROM employees WHERE division like '%".$division."%' and active='Active'
	ORDER BY division ASC, last_name asc";
	$result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));
	$num = mysql_num_rows ($result);

	if ($num>0) {
		echo '<table class="employees sortable"><thead><tr><th><b>Name</b></th><th><b>Emp. Number</b></th>
		<th><b>Status</b></th><th><b>Weekly Hrs</b></th><th><b>Division</b></th><th></th><th></th></tr></thead><tbody>';
	
		while ($row = mysqli_fetch_assoc($result)) {
			echo '<tr><td class="nametip" data-name="'.$row['first_name'].' '.$row['last_name'].'" data-home_phone="'.
			$row['home_phone'].'" data-mobile_phone="'.$row['mobile_phone'].'">' . $row['last_name'] . ', ' . $row['first_name'] . '</td><td>' . 
			$row['employee_number'] . '</td><td>' . $row['exempt_status'] . '</td><td>' . 
			$row['weekly_hours'] . '</td><td>' . $row['division'] . '</td>
			<td><form action="edit_employee" method="post">
			<input type="hidden" name="employee_name" value="' . $row['first_name'] . ' ' . $row['last_name'] . '"/>
			<input type="hidden" name="emp_id" value="' . $row['emp_id'] . '"/>
			<input type="hidden" name="from_view_emp" value="TRUE"/>
			<input type="submit" name="submit" value="Edit" /></form></td>
			<td><form action="view_employees" method="post" onsubmit="return deleteEmployee(this)">
			<input type="hidden" name="emp_id" value="' . $row['emp_id'] . '"/>
			<input type="hidden" name="employee_name" value="' . $row['first_name'] . ' ' . $row['last_name'] . '"/>
			<input type="hidden" name="delete" value="TRUE" />
			<input type="submit" name="delete" value="Delete" /></form>
			</td></tr>';
			}
	
		echo '</tbody></table></div></div>';
	
		mysqli_free_result($result);
		}
	else {
		echo '<p>No results.</p></div></div>';
		}

	mysqli_close($dbc);
	}

else{
	$query = "SELECT emp_id, last_name, first_name, employee_number, exempt_status, weekly_hours, division, 
		home_phone, mobile_phone FROM employees WHERE active='Active'
		ORDER BY division ASC, last_name asc";
	$result = mysqli_query($dbc, $query) or die(mysqli_error($dbc));
	$num = mysqli_num_rows($result);

	if ($num>0) {
		echo '<table class="employees sortable"><thead><tr><th><b>Name</b></th><th><b>Emp. Number</b></th>
		<th><b>Status</b></th><th><b>Weekly Hrs</b></th><th><b>Division</b></th><th></th><th></th></tr></thead><tbody>';
	
		while ($row = mysqli_fetch_assoc($result)) {
			echo '<tr><td class="nametip" data-name="'.$row['first_name'].' '.$row['last_name'].'" data-home_phone="'.
			$row['home_phone'].'" data-mobile_phone="'.$row['mobile_phone'].'">'.$row['last_name'].', '.$row['first_name'].'</td><td>' . 
			$row['employee_number'].'</td><td>'.$row['exempt_status'].'</td><td>'. 
			$row['weekly_hours'].'</td><td>'.$row['division'].'</td>
			<td><form action="edit_employee" method="post">
			<input type="hidden" name="employee_name" value="' . $row['first_name'] . ' ' . $row['last_name'] . '"/>
			<input type="hidden" name="emp_id" value="' . $row['emp_id'] . '"/>
			<input type="hidden" name="from_view_emp" value="TRUE"/>
			<input type="submit" name="submit" value="Edit" /></form></td>
			<td><form action="view_employees" method="post" onsubmit="return deleteEmployee(this)">
			<input type="hidden" name="emp_id" value="' . $row['emp_id'] . '"/>
			<input type="hidden" name="employee_name" value="' . $row['first_name'] . ' ' . $row['last_name'] . '"/>
			<input type="hidden" name="delete" value="TRUE" />
			<input type="submit" name="delete" value="Delete" /></form>
			</td></tr>';
			}
	
		echo '</tbody></table></div></div>';
	
		mysqli_free_result($result);
		}
	else {
		echo '<p>There are currently no employees of that type in the database.</p>';
		echo '<p>We apologize for the inconvenience.</p></div></div>';
		}

	mysqli_close($dbc);
	}

include ('./includes/footer.html');
?>