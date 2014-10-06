<?php #emp_logins.php
include('./includes/allsessionvariables.php');
$query = "SELECT employee_number, first_name, last_name, division, supervisor FROM employees e WHERE active='Active'
	and not exists (SELECT 1 from logins l where e.employee_number=l.employee_number)";
$result = mysql_query($query);

while($row = mysql_fetch_array ($result, MYSQL_ASSOC)) {
	$empno = $row['employee_number'];
	$fn = $row['first_name'];
	$ln = $row['last_name'];
	$ln = preg_replace('/[^A-Za-z]/', '', $ln);
	$div = $row['division'];
	$sup = $row['supervisor'];
	
	$username = strtolower(substr($ln,0,5).substr($fn,0,1));
	
	if ($sup=='Y'){
		$query1 = "INSERT into logins (username, password, role, employee_number) 
			VALUES ('$username', sha('l0v31and'),'Supervisor','$empno')";
		}
	elseif($div=='Subs'){
		$query1 = "INSERT into logins (username, password, role, employee_number) 
			VALUES ('$username', sha('LoveLibrary'),'Subs','$empno')";
		}
	else{
		$query1 = "INSERT into logins (username, password, role, employee_number) 
			VALUES ('$username', sha('LoveLibrary'),'Staff','$empno')";
		}
	$result1 = mysql_query($query1);
	if ($result1){echo $username.' entered<br/>';}
	
	}

?>