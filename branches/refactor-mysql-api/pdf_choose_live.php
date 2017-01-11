<?php #pdf_choose.php

//Create date pull-down function
date_default_timezone_set('America/Denver');

$dates = getdate();

if (isset($_POST['submitted'])){
	list($pdf_mon, $pdf_day, $pdf_yr) = explode('/',$_POST['PDF_date']);
	$pdf_date = "$pdf_yr-$pdf_mon-$pdf_day";

	require_once("/home/teulberg/lpl-repository.com/scheduler2/dompdf/dompdf_config.inc.php");

	ob_start();
	echo '<link rel="stylesheet" type="text/css" media="all" href="/home/teulberg/lpl-repository.com/scheduler2/style/dompdf.css" />';
	echo '<link rel="stylesheet" type="text/css" media="all" href="/home/teulberg/lpl-repository.com/scheduler2/style/scheduler_tables.css" />';

	$today = $pdf_date;
	$day = date('j',strtotime($pdf_date));
	$month = date('F',strtotime($pdf_date));
	$year = date('Y',strtotime($pdf_date));

	include ('display_functions.php');

	$divisions = array();
	require_once ('../mysql_connect_sched2.php');
	$query = "SELECT * from divisions ORDER BY div_name";
	$result = mysqli_query($dbc, $query);
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
	$dompdf->stream("$today.pdf");

	echo $pdf_date;
	
	}
$page_title = "Choose a PDF";
include('./includes/supersessionstart.php');
include('./includes/allsessionvariables.php');
include ('./includes/header.html');
include ('./includes/supersidebar.html');

?>
<span class="date"><h1>Choose Date for PDF</h1></span>
	<div class="pdfdateform">
		<form action="pdf_choose" method="post">
			<div class="label">Choose Date:</div>
			<div class="cal">
				<input class="datepick" id="datepicker1" name="PDF_date" placeholder="Click to choose" />
			</div>
			<p><input type="submit" name="submit" value="Submit" /></p>
			<input type="hidden" name="submitted" value="TRUE" />
		</form>
	</div>

<?php
include ('./includes/footer.html');
?>