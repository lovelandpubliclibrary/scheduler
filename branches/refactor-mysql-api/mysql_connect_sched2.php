<?php #mysql_connect_sched.php

//This file contains the database access information
//This file also establishes a connection to MySQL and selects the database.

//Set the database access information on constants
DEFINE ('DB_USER', 'lpl_scheduler');
DEFINE ('DB_PASSWORD', 'l0v31and!');
DEFINE ('DB_HOST', 'localhost');
DEFINE ('DB_NAME', 'dev_scheduler');

//Make the connection
$dbc = @mysql_connect (DB_HOST, DB_USER, DB_PASSWORD) OR die
	('Could not connect to MySQL: ' . mysql_error() );
	
//Select the database
@mysql_select_db (DB_NAME) or die
	('Could not select the database: ' . mysql_error() );

//Create a function for listing tables
function mysql_table_exists($tablename) {
	$tables = array();
	$query = "SHOW TABLES FROM lplscheduler like '$tablename'";
	$result = mysql_query($query);
	if ($result){
		while ($row = mysql_fetch_array ($result,MYSQL_NUM)){
			$tables = $row[0];
			}
		}
	else{
		$tables = NULL;
		}
	return $tables;
	}		
	
//Create a function for escaping data
function escape_data($data){
	global $dbc; //Need the connection
	if (ini_get('magic_quotes_gpc')){
		$data = stripslashes($data);
		}
	if (function_exists('mysql_real_escape_string')){
		global $dbc;
		$data = mysql_real_escape_string(trim($data), $dbc);
		}
	else {
		$data = mysql_escape_string(trim($data));
		}
	return $data;
	}
	
//Create a function for calendar_pulldowns
date_default_timezone_set('America/Denver');
function make_calendar_pulldowns($m=NULL, $d=NULL, $y=NULL, $name=NULL) {
	$months = array (1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
		'Jul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec');
	$start_year = date('Y');
	$end_year = $start_year + 5;
	if ($m != NULL){
		echo '<select name="' . $name . '_month">';
		foreach ($months as $key => $value) {
			echo "<option value=\"$key\"";
			if ($key ==$m){
				echo ' selected="selected"';
				}
			echo ">$value</option>\n";
			}
		echo '</select>';
		}
	if ($d != NULL){
		echo '<select name="' . $name . '_day">';
		for ($day = 1; $day<=31; $day++) {
			echo "<option value=\"$day\"";
			if ($day == $d){
				echo ' selected="selected"';
				}
			echo ">$day</option>\n";
			}
		echo '</select>';
		}
	if ($y != NULL){
		echo '<select name="' . $name . '_year">';
		for ($year = $start_year-1; $year<=$end_year; $year++) {
			echo "<option value =\"$year\"";
			if ($year == $y){
				echo ' selected="selected"';
				}
			echo ">$year</option>\n";
			}
		echo '</select>';
		}
	}
	?>