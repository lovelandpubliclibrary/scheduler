<?php
date_default_timezone_set('America/Denver');
$today = date('Y-m-d');
$array = array();

require_once ('../mysql_connect_sched.php');

$query = "select first_name, e.employee_number, coverage_date, coverage_start_time, coverage_end_time 
	from employees as e, coverage as t, coverageassoc as a 
	where e.employee_number = a.employee_number and t.coverage_id = a.coverage_id and 
	coverage_date >= '$today' and coverage_offdesk='On' order by first_name asc, coverage_date asc";
$result = mysql_query($query) or die(mysql_error($dbc));

while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
	$name = $row['first_name'];
	$empno = $row['employee_number'];
	$c_date = $row['coverage_date'];
	$c_start = $row['coverage_start_time'];
	$c_end = $row['coverage_end_time'];
	
	$array[$name][$c_date][] = array(0=>$c_start, $c_end);
	}

foreach ($array as $name=>$v){
	echo '<b>'.$name.'</b><br/>';
	foreach ($v as $date=>$c_date){
		if (count($c_date) > 1){
			for ($i=0; $i<count($c_date); $i++){
				$c_start = $c_date[$i][0];
				$c_end = $c_date[$i][1];
				//echo $c_start.'-'.$c_end.'<br/>';
				for ($j=$i+1; $j<count($c_date); $j++){
					$cs_next = $c_date[$j][0];
					$ce_next = $c_date[$j][1];
					if ((($c_start <= $cs_next) && ($c_end > $cs_next)) || (($c_start < $ce_next) && ($c_end >= $ce_next))){
						echo 'There is a conflict on '.$date.'<br/>';
						}
					}
				}
			}
		}
	}
?>