<?php #pdf_generator.php

require_once("/home/teulberg/dev.lpl-repository.com/scheduler2/dompdf/dompdf_config.inc.php");

ob_start();
echo '<link rel="stylesheet" type="text/css" media="all" href="/home/teulberg/dev.lpl-repository.com/scheduler2/style/dompdf.css" />';
echo '<link rel="stylesheet" type="text/css" media="all" href="/home/teulberg/dev.lpl-repository.com/scheduler2/style/scheduler_tables.css" />';
date_default_timezone_set('America/Denver');
$today = date('Y-m-d');
$day = date('j');
$month = date('F');
$year = date('Y');

$page_title = "$day $month $year";
include ('/home/teulberg/dev.lpl-repository.com/scheduler2/display_functions.php');

require_once ('../mysql_connect_sched2.php'); //Connect to the db.
$divisions = array();
require_once ('../mysql_connect_sched2.php'); //Connect to the db.
$query = "SELECT * from divisions ORDER BY div_name";
$result = mysql_query($query);
while ($row = mysql_fetch_assoc($result)) {
	$divisions[$row['div_link']] = $row['div_name'];
	}
$now = strtotime("$today");

$dom = date('j', $now);
$day_long = date('l', $now);
$month_long = date('F', $now);
	
echo '<div id="wrapper">';
echo "<div class=\"pdf_head\"><h1>$day_long, $dom $month_long $year</h1></div>";
daily_schedule($now, $divisions);
echo '</div>';
$html = ob_get_contents();
ob_end_clean(); 

$old_limit = ini_set("memory_limit", "192M"); 
$dompdf = new DOMPDF();
$dompdf->set_base_path('/'); 
$dompdf->load_html($html);
$dompdf->set_paper('legal', 'portrait');
$dompdf->render();
file_put_contents("/home/teulberg/dev.lpl-repository.com/scheduler2/archives/$today.pdf", $dompdf->output());

?>