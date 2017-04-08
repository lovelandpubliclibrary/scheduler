<?php #timesheets.php
$today = date('Y-m-d');

$query = "SELECT timesheets_start, timesheets_end FROM timesheet_alerts WHERE timesheets_start <= '$today' and timesheets_end >= '$today'";
$result = mysql_query($query);

$num_rows = mysql_num_rows($result);
if ($num_rows != 0){
	echo '<div class="timesheets">
		Timesheets<br/>due today!
		</div>'."\n";
	}

?>