<?php # timesheets.php
date_default_timezone_set('America/Denver');
require_once ('../mysql_connect_sched.php');

$page_title = "Export Timesheets Data";
include('./includes/supersessionstart.php');
include('./includes/allsessionvariables.php');
include ('./includes/header.html');
include ('./includes/supersidebar.html');

echo '<span class="date"><h1>Timesheets</h1></span>';

$today = date('Y-m-d');
//$today = '2013-01-14';

$query = "SELECT timesheets_start FROM timesheets where timesheets_start >= '$today'";
$result = mysql_query($query);
$row = mysql_fetch_array($result, MYSQL_ASSOC);

$enddate = $row['timesheets_start'];

$startdate = strtotime('-14 days', strtotime ($enddate));
$startdate = date('Y-m-d', $startdate);

$emparray = array();

$query5 = "SELECT employee_number, last_name, first_name, exempt_status
	FROM employees WHERE active='Active' ORDER BY last_name";
$result5 = mysql_query($query5);
while ($row5 = mysql_fetch_array($result5, MYSQL_ASSOC)){
	$empno = $row5['employee_number'];
	$emparray["$empno"] = array('last_name'=>$row5['last_name'],
		'first_name'=>$row5['first_name'], 'exempt_status'=>$row5['exempt_status']);
	if ($empno == '88559'){
		$emparray["$empno"] = array('last_name'=>$row5['last_name'],
		'first_name'=>$row5['first_name'], 'exempt_status'=>$row5['exempt_status']);
		}
	}

//Generate list of dates => 14 days from start
function dates_between($start_date, $end_date = false){
	$start_date = is_int($start_date) ? $start_date : strtotime($start_date);
	$end_date = is_int($end_date) ? $end_date : strtotime($end_date);

	$test_date;
	global $array;
	$test_date = $start_date;
     
	for ($i=1; $i<=14; $i++){
		$test_date = strtotime(date('Y-m-d', $test_date)."+1 day");
		$array[] = date('Y-m-d',$test_date);
		}
	}

dates_between("$startdate", "$enddate");

echo '<div class="message"><b>';
echo date('D',strtotime($startdate)+86400).', '.date('M',strtotime($startdate)+86400).' '.date('j',strtotime($startdate)+86400);
echo ' &ndash; ';
echo date('D',strtotime($enddate)).', '.date('M',strtotime($enddate)).' '.date('j',strtotime($enddate));
echo '</b><br/><br/>';

foreach ($array as $key=>$date){
	$now = strtotime($date);

	//Get week type.
	$query = "SELECT date, week_type FROM dates where date = '$date'";
	$result = @mysql_query($query);

	while ($row = mysql_fetch_assoc($result)) {
		$week_type = $row['week_type'];
		}

	$day = date('D', $now);
	$year = date('Y', $now);

	//Get season.
	$query2 = "SELECT memorial_day, labor_day FROM holidays where year='$year'";
	$result2 = @mysql_query($query2);

	while ($row2 = mysql_fetch_assoc($result2)) {
		$memorial_day = $row2['memorial_day'];
		$labor_day = $row2['labor_day'];
		}

	$mem_sat = strtotime ('-2 days', strtotime ($memorial_day));
	$lab_sat = strtotime ('-2 days', strtotime ($labor_day));

	if ((strtotime($today) >= $mem_sat) && (strtotime($today) < $lab_sat)){ 
		$season = 'summer';
		}
	elseif (strtotime($today) < $mem_sat){
		$season = 'spring';
		}
	else {
		$season = 'fall';
		}
	
	//Set dynamic table names.
	$tablename = strtolower($week_type) . '_' . strtolower($day) . '_' . $year . '_' . strtolower($season);
	$assoc_tablename = 'employeeassoc_' . $tablename;
	
	echo $tablename . ' &ndash; exported<br/>';
	
	$filename = "timesheetscsv/day$key.csv";
	if (file_exists($filename)) {
		unlink($filename);
		}

	$query3 = "SELECT employees.employee_number, last_name, first_name, shift_start, shift_end
		FROM employees, $tablename as t, $assoc_tablename as a
		WHERE employees.employee_number=a.employee_number AND t.row_id=a.row_id
		ORDER BY last_name";
	$result3 = mysql_query($query3);
	$fh = fopen("/home/teulberg/lpl-repository.com/scheduler/$filename", "w+");
	while ($row = mysql_fetch_row($result3)) {
		fputs($fh, implode(',', $row)."\n");
		}
	fclose ($fh);
	
	/*if ($result3){
		echo "$filename sent to FTP<br/>";
		}*/
	$query4 = "SELECT employees.employee_number, last_name, first_name, exempt_status, shift_start
		FROM employees, $tablename as t, $assoc_tablename as a 
		WHERE employees.employee_number=a.employee_number AND t.row_id=a.row_id
		ORDER by last_name";
	$result4 = mysql_query($query4);
	while ($row4 = mysql_fetch_array($result4, MYSQL_ASSOC)){
		$empno = $row4['employee_number'];
		$rowarray["$empno"] = array('last_name' => $row4['last_name'], 
			'first_name' =>$row4['first_name'], 'exempt_status' => $row4['exempt_status']);
		}
	
	foreach ($rowarray as $key=>$value){
		if (!in_array($key, $emparray)){
			$emparray["$key"] = $value;
			}
		}	
	}
 
function subval_sort($a,$subkey) {
	foreach($a as $k=>$v) {
		$b[$k] = $v[$subkey];
		}
	asort($b);
	foreach($b as $key=>$val) {
		$c[$key] = $a[$key];
		}
	return $c;
	}

$newarray = subval_sort($emparray,'last_name');

$file = "/home/teulberg/lpl-repository.com/scheduler/timesheetscsv/employees.csv";	
$handle = fopen($file, "w");
$csv = "Employee Number,Last Name,First Name,Exempt Status\r\n";
foreach ($newarray as $empno=>$value){
	$csv .= "$empno,".$value['last_name'].','
		.$value['first_name'].','.$value['exempt_status']."\r\n";
	if ($empno == '88559'){
		$csv .= "$empno,".$value['last_name'].','
		.$value['first_name'].','.$value['exempt_status']."\r\n";
		}
	}
fwrite($handle, $csv);
fclose($handle);

echo '</div>';
include ('./includes/footer.html');