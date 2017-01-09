<?php #subs_weekly.php
$division = "subs";
$ucdivision = "Subs";

$page_title = "$ucdivision";

include('./includes/sessionstart.php');
include('./includes/allsessionvariables.php');
include ('./includes/header.html');
include ('./includes/subssidebar.html');
include ('./display_functions.php');

if (isset($_GET['today'])){
	$today = $_GET['today'];
	}
elseif (isset($_POST['date'])){
	$date = $_POST['date'];
	$date_array = explode("/",$date);
	$today = $date_array[2].'-'.$date_array[0].'-'.$date_array[1];
	}
else {$today = date('Y-m-d');}
$now = strtotime("$today");

echo '<span class="date"><h1>'.$ucdivision.'</h1></span>'."\n";
echo '<div class="toggle"><a href="/scheduler2/subs">Daily</a> / <span class="toggled">Weekly</span></div>'."\n";
echo '<div id="weekDiv">'."\n";
subs_weekly($division, $now);
echo '</div>'."\n";
echo '<div id="timeoff">'."\n";
division_timeoff($division, $today);
echo '</div>'."\n";
echo "<div class=\"mobile subkey\">\n
	<span class=\"dp\">Color Key:</span>\n
	<table class=\"keys\">\n
		<tr>\n
			<td>\n
				<div class=\"key adult\"></div> &ndash; Adult\n
			</td>\n
			<td>\n
				<div class=\"key children\"></div> &ndash; Children\n
			</td>\n
		</tr>\n
		<tr>\n
			<td>\n
				<div class=\"key custserv\"></div> &ndash; Customer Service\n
			</td>\n
			<td>\n
				<div class=\"key lti\"></div> &ndash; Library Tech\n
			</td>\n
		</tr>\n
		<tr>\n
			<td>\n
				<div class=\"key teen\"></div> &ndash; Teen\n
			</td>\n
		</tr>\n
	</table>\n
</div>\n
<div class=\"mobile subrequestlink\">\n
	<a href=\"/scheduler/sub_needs\">Sub Requests</a>\n
</div>\n";
include ('./includes/footer.html');
?>