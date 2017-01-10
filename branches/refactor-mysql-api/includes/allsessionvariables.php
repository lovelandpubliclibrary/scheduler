<?php
$from = $_SERVER['REQUEST_URI'];
$_SESSION['came_from'] = substr($from, strrpos($from,'/')+1);
$_SESSION['came_from'] = trim(strtok($_SESSION['came_from'], '?'));

$divisions = array();
require_once ('../mysql_connect.php'); //Connect to the db.
$query = "SELECT * FROM divisions ORDER BY div_name";
$result = mysqli_query($dbc, $query);
while ($row = mysqli_fetch_assoc($result)) {
	$divisions[$row['div_link']] = $row['div_name'];
}
	
if(isset($_SESSION['this_emp_id'])){
	$this_emp_id = $_SESSION['this_emp_id'];
	$this_assignment_id = $_SESSION['this_assignment_id'];
	$query = "SELECT first_name, last_name, name_dup from employees WHERE emp_id = '$this_emp_id'";
	$result = mysqli_query($dbc, $query);
	while ($row = mysqli_fetch_assoc($result)){
		$this_full_name = $row['first_name'].' '.$row['last_name'];
	}
}
?>