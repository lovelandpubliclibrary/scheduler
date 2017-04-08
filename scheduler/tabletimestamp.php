<?php
$tables = array(0=>'schedule_template');
require_once ('../mysql_connect_sched.php');
$query = "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA='dev_lplscheduler'
	and left(TABLE_NAME, 2)='a_'";
$result = mysql_query($query);
while ($row = mysql_fetch_row($result)){
	$tables[] = $row[0];
	}
$query = "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA='dev_lplscheduler'
	and left(TABLE_NAME, 2)='b_'";
$result = mysql_query($query);
while ($row = mysql_fetch_row($result)){
	$tables[] = $row[0];
	}
$query = "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA='dev_lplscheduler'
	and left(TABLE_NAME, 2)='c_'";
$result = mysql_query($query);
while ($row = mysql_fetch_row($result)){
	$tables[] = $row[0];
	}
$query = "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA='dev_lplscheduler'
	and left(TABLE_NAME, 2)='d_'";
$result = mysql_query($query);
while ($row = mysql_fetch_row($result)){
	$tables[] = $row[0];
	}
	
foreach ($tables as $key=>$table){
	$success = $table.' ';
	$query = "ALTER table $table add schedule_create timestamp not null default 0";
	$result = mysql_query($query);
	if ($result) {$success .= 'schedule_create ';}
	$query2 = "ALTER table $table add schedule_lastedit 
		timestamp not null default current_timestamp on update current_timestamp";
	$result2 = mysql_query($query2);
	if ($result) {$success .= 'schedule_lastedit';}
	echo $success.'<br/>';
	}

$success = 'employees ';
$query = "ALTER table employees add employee_create timestamp not null default 0";
$result = mysql_query($query);
if ($result) {$success .= 'employee_create ';}
$query2 = "ALTER table employees add employee_lastedit 
	timestamp not null default current_timestamp on update current_timestamp";
$result2 = mysql_query($query2);
if ($result) {$success .= 'employee_lastedit';}
	echo $success.'<br/>';
	
$success = 'coverage ';
$query = "ALTER table coverage add coverage_create timestamp not null default 0";
$result = mysql_query($query);
if ($result) {$success .= 'coverage_create ';}
$query2 = "ALTER table coverage add coverage_lastedit 
	timestamp not null default current_timestamp on update current_timestamp";
$result2 = mysql_query($query2);
if ($result) {$success .= 'coverage_lastedit';}
	echo $success.'<br/>';

$success = 'timeoff ';
$query = "ALTER table timeoff add timeoff_create timestamp not null default 0";
$result = mysql_query($query);
if ($result) {$success .= 'timeoff_create ';}
$query2 = "ALTER table timeoff add timeoff_lastedit 
	timestamp not null default current_timestamp on update current_timestamp";
$result2 = mysql_query($query2);
if ($result) {$success .= 'timeoff_lastedit';}
	echo $success.'<br/>';

$success = 'sub_needs ';
$query = "ALTER table sub_needs add sub_needs_create timestamp not null default 0";
$result = mysql_query($query);
if ($result) {$success .= 'sub_needs_create ';}
$query2 = "ALTER table sub_needs add sub_needs_lastedit 
	timestamp not null default current_timestamp on update current_timestamp";
$result2 = mysql_query($query2);
if ($result) {$success .= 'sub_needs_lastedit';}
	echo $success.'<br/>';
	
$success = 'deficiencies ';
$query = "ALTER table deficiencies add def_create timestamp not null default 0";
$result = mysql_query($query);
if ($result) {$success .= 'def_create ';}
$query2 = "ALTER table deficiencies add def_lastedit 
	timestamp not null default current_timestamp on update current_timestamp";
$result2 = mysql_query($query2);
if ($result) {$success .= 'def_lastedit';}
	echo $success.'<br/>';
?>