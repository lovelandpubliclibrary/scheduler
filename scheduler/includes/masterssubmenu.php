<?php # masterssubmenu.php

echo "<div class=\"submenuhead\"><h5>Current Masters</h5></div>\n";

$today = date('Y-m-d');
$query = "SELECT date, week_type FROM dates where date = '$today'";
$result = @mysql_query($query);

while ($row = mysql_fetch_assoc($result)) {
	$w = $row['week_type'];
	}

$y = date('Y');

//Get season.
$query2 = "SELECT memorial_day, labor_day FROM holidays where year='$y'";
$result2 = @mysql_query($query2);

while ($row2 = mysql_fetch_assoc($result2)) {
	$memorial_day = $row2['memorial_day'];
	$labor_day = $row2['labor_day'];
	}

$mem_sat = strtotime ('-2 days', strtotime ($memorial_day));
$lab_sat = strtotime ('-2 days', strtotime ($labor_day));

if ((strtotime($today) >= $mem_sat) && (strtotime($today) < $lab_sat)){ 
	$s = 'summer';
	}
elseif (strtotime($today) < $mem_sat){
	$s = 'spring';
	}
else {
	$s = 'fall';
	}

$ucs = ucwords($s);
$link = 'scheduler/masters';
?>
<div class="sect">
	<span class="dp"><a href="<?php echo "/$link/$y/$s/a/sat"?>"><?php echo "$ucs $y A"; ?></a></span><br/>
	<div class="links">
<?php
	if ($week_type == 'a'){
		echo "<a href=\"/$link/$y/$s/a/sat\">Saturday</a><br/>\n";
		echo "<a href=\"/$link/$y/$s/a/sun\">Sunday</a><br/>\n";
		echo "<a href=\"/$link/$y/$s/a/mon\">Monday</a><br/>\n";
		echo "<a href=\"/$link/$y/$s/a/tue\">Tuesday</a><br/>\n";
		echo "<a href=\"/$link/$y/$s/a/wed\">Wednesday</a><br/>\n";
		echo "<a href=\"/$link/$y/$s/a/thu\">Thursday</a><br/>\n";
		echo "<a href=\"/$link/$y/$s/a/fri\">Friday</a><br/>\n";
	}
?>
	</div>
</div>
<div class="sect">
	<span class="dp"><a href="<?php echo "/$link/$y/$s/b/sat"?>"><?php echo "$ucs $y B"; ?></a></span><br/>
	<div class="links">
<?php
	if ($week_type == 'b'){
		echo "<a href=\"/$link/$y/$s/b/sat\">Saturday</a><br/>\n";
		echo "<a href=\"/$link/$y/$s/b/sun\">Sunday</a><br/>\n";
		echo "<a href=\"/$link/$y/$s/b/mon\">Monday</a><br/>\n";
		echo "<a href=\"/$link/$y/$s/b/tue\">Tuesday</a><br/>\n";
		echo "<a href=\"/$link/$y/$s/b/wed\">Wednesday</a><br/>\n";
		echo "<a href=\"/$link/$y/$s/b/thu\">Thursday</a><br/>\n";
		echo "<a href=\"/$link/$y/$s/b/fri\">Friday</a><br/>\n";
	}
?>
	</div>
</div>
<div class="sect">
	<span class="dp"><a href="<?php echo "/$link/$y/$s/c/sat"?>"><?php echo "$ucs $y C"; ?></a></span><br/>
	<div class="links">
<?php
	if ($week_type == 'c'){
		echo "<a href=\"/$link/$y/$s/c/sat\">Saturday</a><br/>\n";
		echo "<a href=\"/$link/$y/$s/c/sun\">Sunday</a><br/>\n";
		echo "<a href=\"/$link/$y/$s/c/mon\">Monday</a><br/>\n";
		echo "<a href=\"/$link/$y/$s/c/tue\">Tuesday</a><br/>\n";
		echo "<a href=\"/$link/$y/$s/c/wed\">Wednesday</a><br/>\n";
		echo "<a href=\"/$link/$y/$s/c/thu\">Thursday</a><br/>\n";
		echo "<a href=\"/$link/$y/$s/c/fri\">Friday</a><br/>\n";
		}
?>
	</div>
</div>
<div class="sect">
	<span class="dp"><a href="<?php echo "/$link/$y/$s/d/sat"?>"><?php echo "$ucs $y D"; ?></a></span><br/>
	<div class="links">
<?php
	if ($week_type == 'd'){
		echo "<a href=\"/$link/$y/$s/d/sat\">Saturday</a><br/>\n";
		echo "<a href=\"/$link/$y/$s/d/sun\">Sunday</a><br/>\n";
		echo "<a href=\"/$link/$y/$s/d/mon\">Monday</a><br/>\n";
		echo "<a href=\"/$link/$y/$s/d/tue\">Tuesday</a><br/>\n";
		echo "<a href=\"/$link/$y/$s/d/wed\">Wednesday</a><br/>\n";
		echo "<a href=\"/$link/$y/$s/d/thu\">Thursday</a><br/>\n";
		echo "<a href=\"/$link/$y/$s/d/fri\">Friday</a><br/>\n";
		}
?>
	</div>
</div>


