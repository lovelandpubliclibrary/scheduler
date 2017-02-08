<?php #name_dup_update.php
require_once ('/home/teulberg/lpl-repository.com/mysql_connect.php'); //Connect to the db.

$query="SELECT first_name, emp_id, count(*) as c FROM employees WHERE active='Active' GROUP BY first_name having c=1";
$result = mysqli_query($dbc, $query);

while ($row = mysqli_fetch_assoc($result)){
	$emp_id = $row['emp_id'];
	$query2 = "UPDATE employees SET name_dup='N' WHERE emp_id='$emp_id'";
	$result2 = mysqli_query($dbc, $query2);
	}

?>