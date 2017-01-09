<?php
$from = $_SERVER['REQUEST_URI'];
$_SESSION['came_from'] = substr($from, strrpos($from,'/')+1);
$_SESSION['came_from'] = trim(strtok($_SESSION['came_from'], '?'));

$divisions = array();
require_once ('../mysql_connect_sched2.php'); //Connect to the db.
$query = "SELECT * from divisions ORDER BY div_name";
$result = mysql_query($query);
while ($row = mysql_fetch_assoc($result)) {
	$divisions[$row['div_link']] = $row['div_name'];
	}
	
if(isset($_SESSION['this_emp_id'])){
	$this_emp_id = $_SESSION['this_emp_id'];
	$this_assignment_id = $_SESSION['this_assignment_id'];
	$query = "SELECT first_name, last_name, name_dup from employees WHERE emp_id = '$this_emp_id'";
	$result = mysql_query($query);
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
		$this_full_name = $row['first_name'].' '.$row['last_name'];
		}
	}
?>