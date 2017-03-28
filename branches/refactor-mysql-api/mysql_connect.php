<?php # mysql_connect.php

// This file contains the database access information
// This file also establishes a connection to MySQL and selects the database.
// This file should be used for all connections to the database (not mysql_connect_sched2.php for example)

// Set the database access information on constants
DEFINE ('DB_USER', 'lpl_scheduler');
DEFINE ('DB_PASSWORD', 'LGM8FrJ9i977rGI3');
DEFINE ('DB_HOST', 'localhost');
DEFINE ('DB_NAME', 'dev_scheduler');

//Make the connection
$dbc = @mysqli_connect (DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) OR die ('Could not connect to MySQL: ' . mysqli_connect_error());


//Create a function for listing tables
function mysql_table_exists($tablename) {
	$tables = array();
	$tablename = escape_data($tablename);
	$query = "SHOW TABLES FROM DB_NAME LIKE '$tablename'";
	$result = mysqli_query($dbc, $query);
	if ($result){
		while ($row = mysqli_fetch_array($result, MYSQL_NUM)) {
			$tables = $row[0];
		}
	} else {
		$tables = NULL;
	}

	return $tables;
}		
	
//Create a function for escaping data
function escape_data($data){
	global $dbc; //Need the connection
	if (ini_get('magic_quotes_gpc')) {
		$data = stripslashes($data);
	}
		
	// global $dbc;

	return mysqli_real_escape_string($dbc, trim($data));
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


// create a functional replacement for mysql_result
function mysqli_result($res,$row=0,$col=0){ 
    $numrows = mysqli_num_rows($res); 
    if ($numrows && $row <= ($numrows-1) && $row >=0){
        mysqli_data_seek($res,$row);
        $resrow = (is_numeric($col)) ? mysqli_fetch_row($res) : mysqli_fetch_assoc($res);
        if (isset($resrow[$col])){
            return $resrow[$col];
        }
    }
    return false;
}
?>
