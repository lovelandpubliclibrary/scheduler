<?php
$from = $_SERVER['REQUEST_URI'];
$_SESSION['came_from'] = substr($from, strrpos($from,'/')+1);

$divisions = array();
require_once ('../mysql_connect_sched2.php'); //Connect to the db.
$query = "SELECT * from divisions ORDER BY div_name";
$result = mysql_query($query);
while ($row = mysql_fetch_assoc($result)) {
	$divisions[$row['div_link']] = $row['div_name'];
	}
	
if(isset($_SESSION['this_empno'])){
	$this_empno = $_SESSION['this_empno'];
	$query = "SELECT first_name, last_name, name_dup from employees WHERE employee_number = '$this_empno'";
	$result = mysql_query($query);
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
		$this_full_name = $row['first_name'].' '.$row['last_name'];
		}
	}
?>