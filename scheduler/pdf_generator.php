<?php #pdf_generator.php
require_once(__DIR__ . "/dompdf/dompdf_config.inc.php");

ob_start();
echo '<link rel="stylesheet" type="text/css" media="all" href="' . __DIR__ . '/style/dompdf.css" />';
echo '<link rel="stylesheet" type="text/css" media="all" href="' . __DIR__ . '/style/scheduler_tables.css" />';
date_default_timezone_set('America/Denver');
$today = date('Y-m-d');
$day = date('j');
$month = date('F');
$year = date('Y');

$page_title = "$day $month $year";
include (__DIR__ . '/display_functions.php');

$divisions = array();
require_once (__DIR__ . '/../mysql_connect.php'); //Connect to the db.
$query = "SELECT * from divisions ORDER BY div_name";
$result = mysqli_query($dbc, $query);
while ($row = mysqli_fetch_assoc($result)) {
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
file_put_contents(__DIR__ . "/archives/$today.pdf", $dompdf->output());
echo "success";
?>