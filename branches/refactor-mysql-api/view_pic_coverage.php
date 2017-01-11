<?php #view_pic_coverage.php

$page_title = "View PIC Coverage" ;
include('./includes/supersessionstart.php');

if (isset($_SESSION['came_from'])){
	$came_from = $_SESSION['came_from'];
	}

include('./includes/allsessionvariables.php');
include ('./includes/header.html');
include ('./includes/supersidebar.html');

$today= date('Y-m-d');

?>
<script>
function deletecoverage()
{
var agree=confirm("Are you sure you wish to delete?");
if (agree){
	return true ;}
else {
	return false ;}
}
</script>
<?php
echo '<div class="divspec">Upcoming PIC Coverage</div>';

if (($came_from == 'edit_pic_coverage') && (isset($_SESSION['success']))){
	$name = $_SESSION['pic_coverage_name'];
	$pid = $_SESSION['pic_coverage_id'];
	$d = $_SESSION['pic_coverage_date'];
	echo '<div class="message"><b>PIC coverage</b> on ' . $d . ' has been updated to '. $name . '.</div>';
	unset($_SESSION['success']);
	}

if (isset($_POST['delete'])){
	$first_name = $_POST['first_name'];
	$pic_coverage_id = $_POST['pic_coverage_id'];
	$date = $_POST['date'];
	$query1 = "DELETE from pic_coverage WHERE pic_coverage_id='$pic_coverage_id'";
	$result1 = mysqli_query($dbc, $query1);
	echo '<div class="message"><b>PIC coverage by</b> '. $first_name . ' on ' . $date . ' has been deleted.</div>';
	}

$query = "SELECT pic_coverage_id, pic_coverage_date, first_name, e.emp_id
	FROM employees e, pic_coverage as c WHERE e.emp_id = c.emp_id
	and pic_coverage_date >= '$today' ORDER BY pic_coverage_date asc";
$result = mysqli_query($dbc, $query);
if ($result){
	$num_rows = mysql_num_rows($result);
	if ($num_rows != 0) {
		echo '<div class="divboxes"><table class="coverage">';
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
			$pic_coverage_id = $row['pic_coverage_id'];
			$pic_coverage_date = $row['pic_coverage_date'];
			$first_name = $row['first_name'];
			$emp_id = $row['emp_id'];
			
			//Date specifics
			$cmonth = date('M', strtotime($pic_coverage_date));
			$cday = date('j', strtotime($pic_coverage_date));
			if ((date('Y', strtotime($pic_coverage_date))) > date('Y')){
				$cyear = date('Y', strtotime($pic_coverage_date));
				$cyear = ', '.$cyear;
				}
			else {
				$cyear = NULL;
				}
			
			echo "<tr><td class=\"picdate\"><span class=\"todate\">$cmonth $cday$cyear</span></td>
				<td class=\"first_name\">$first_name</td>";
			echo '<td><form action="edit_pic_coverage" method="post">
				<input type="hidden" name="pic_coverage_id" value="' . $pic_coverage_id . '"/>
				<input type="hidden" name="emp_id" value="'.$emp_id.'"/>
				<input type="hidden" name="first_name" value="' . $first_name  . '"/>
				<input type="hidden" name="came_from" value="' . $_SERVER['REQUEST_URI'] . '" />
				<input type="hidden" name="date" value="' . $cmonth . ' ' . $cday . $cyear . '"/>
				<input type="hidden" name="from_view_pic" value="TRUE"/>
				<input type="submit" name="submit" value="Edit" /></form></td>
				<td><form action="view_pic_coverage" method="post" onsubmit="return deletecoverage()">
				<input type="hidden" name="pic_coverage_id" value="' . $pic_coverage_id . '"/>
				<input type="hidden" name="emp_id" value="'.$emp_id.'"/>
				<input type="hidden" name="first_name" value="' . $first_name  . '"/>
				<input type="hidden" name="came_from" value="' . $_SERVER['REQUEST_URI'] . '" />
				<input type="hidden" name="date" value="' . $cmonth . ' ' . $cday . $cyear . '"/>
				<input type="hidden" name="delete" value="TRUE" />
				<input type="submit" name="delete" value="Delete" /></form>
				</td>';
			echo '</tr>';
			
			}
		echo '</table></div>';
		}
	}

include ('./includes/footer.html');
?>