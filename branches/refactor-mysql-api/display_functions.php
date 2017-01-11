<?php # display_functions.php

function sortByOrder($a, $b) {
	return $a[1] - $b[1];
	}

//Time to decimal function
function dec_minutes($mins) {
	$dec_mins = $mins/60;
	return $dec_mins;
	}

function daily_schedule($now, $divisions) {

	//Get week type.
	$today = date('Y-m-d' , $now);
	$query = "SELECT date, week_type FROM dates where date = '$today'";
	$result = @mysqli_query($dbc, $query);

	while ($row = mysql_fetch_assoc($result)) {
		$week_type = $row['week_type'];
		}

	$day = date('D', $now);
	$year = date('Y', $now);

	//Get season.
	$query2 = "SELECT memorial_day, labor_day FROM holidays where year='$year'";
	$result2 = @mysqli_query($dbc, $query2);

	while ($row2 = mysql_fetch_assoc($result2)) {
		$memorial_day = $row2['memorial_day'];
		$labor_day = $row2['labor_day'];
		}

	$mem_sat = strtotime ('-2 days', strtotime ($memorial_day));
	$lab_sat = strtotime ('+5 days', strtotime ($labor_day));

	if ((strtotime($today) >= $mem_sat) && (strtotime($today) < $lab_sat)){
		$season = 'summer';
		}
	elseif (strtotime($today) < $mem_sat){
		$season = 'spring';
		}
	else {
		$season = 'fall';
		}

	//Set workday length.
	$normalhours = array(0=>7,7.5,8,8.5,9,9.5,10,10.5,11,11.5,12,12.5,13,13.5,14,14.5,15,15.5,16,16.5,17,17.5,18,18.5,19,19.5);
	$hoursscale = array(0=>7,null,8,null,9,null,10,null,11,null,12,null,13,null,14,null,15,null,16,null,17,null,18,null,19,null);

	$dom = date('j', $now);
	$day_long = date('l', $now);
	$day_short = date('D', $now);
	$month_long = date('F', $now);

	//Get PIC
	$pic_name = '';
	if (in_array($day_short,array('Mon','Tue','Wed','Thu', 'Fri'))){
		$query_pic = "SELECT first_name FROM employees e, pic p, pic_schedules s
			WHERE pic_start_date <= '$today' and pic_end_date >= '$today' and p.pic_schedule_id=s.pic_schedule_id
			and week_type='$week_type' and pic_day='$day'
			and p.emp_id=e.emp_id and e.emp_id not in
			(SELECT emp_id from timeoff WHERE
			timeoff_start_date <= '$today' and timeoff_end_date >= '$today' and
			((timeoff_start_time <= '17:00:00' and timeoff_end_time >= '20:00:00') or
			(timeoff_start_time <= '17:00:00' and (timeoff_end_time > '17:00:00' and timeoff_end_time <= '20:00:00')) or
			((timeoff_start_time >= '17:00:00' and timeoff_start_time < '20:00:00') and timeoff_end_time >= '20:00:00')
			))";
		}
	else{
		$query_pic = "SELECT first_name FROM employees e, pic p, pic_schedules s
			WHERE pic_start_date <= '$today' and pic_end_date >= '$today' and p.pic_schedule_id=s.pic_schedule_id
			and week_type='$week_type' and pic_day='$day'
			and p.emp_id=e.emp_id and e.emp_id not in
			(SELECT emp_id from timeoff WHERE timeoff_start_date <= '$today' and timeoff_end_date >= '$today')";
		}
	$result_pic = mysqli_query($dbc, $query_pic);
	if($result_pic){
		while ($row = mysql_fetch_array($result_pic, MYSQL_ASSOC)){
			$pic_name = $row['first_name'];
			}
		}
	else{
		$pic_name = '';
		}

	$query_pic_cover = "SELECT first_name from employees e, pic_coverage c WHERE pic_coverage_date='$today' and
		e.emp_id=c.emp_id";
	$result_pic_cover = mysqli_query($dbc, $query_pic_cover);
	if($result_pic_cover){
		while ($row = mysql_fetch_array($result_pic_cover, MYSQL_ASSOC)){
			$pic_name = $row['first_name'];
			}
		}

	echo '<div id="schedDiv">'."\n";
	echo '<input class="prev" type="button" onclick="loadprevFull()" value="Previous" />
		<input class="next" type="button" value="Next" onclick="loadnextFull()" />'."\n";
	echo "<div class=\"screen\"><span class=\"date\"><h1>$day_long, $dom $month_long $year</h1></span></div>\n";
	echo "<div class=\"mobile\"><span class=\"date\"><h1>$day_short, $dom $month_long $year</h1></span></div>\n";
	echo '<div class="week_type">'.ucwords($week_type).' Schedule</div>'."\n";

	//See if library closed.
	$query_closure = "SELECT * from closures WHERE closure_date='$today' limit 1";
	$result_closure = mysqli_query($dbc, $query_closure);
	if ($result_closure){
		$num_rows = mysql_num_rows($result_closure);
		if ($num_rows != 0){
			while ($row = mysql_fetch_array($result_closure, MYSQL_ASSOC)){
				$cd_date = $row['closure_date'];
				$cd_start = $row['closure_start_time'];
				$cd_end = $row['closure_end_time'];
				$cd_reason = $row['closure_reason'];
				}
			if (($cd_start == '00:01:00')&&($cd_end == '23:59:00')){
				$allday_closure_message = "<div class=\"error\"><div class=\"message\"><h4>Library Closed</h4>
					<p>The library is closed all day for $cd_reason</p></div></div></div>";
				}
			else{
				if (($cd_start != '00:01:00')&&($cd_end != '23:59:00')){
					$close_start = explode(':',$cd_start);
					$close_start_hr = $close_start[0];
					$close_start_mn = $close_start[1];
					$close_end = explode(':',$cd_end);
					$close_end_hr = $close_end[0];
					$close_end_mn = $close_end[1];
					if ($close_start_hr > 12){
						$ss12 = $close_start_hr - 12;
						}
					elseif($close_start_hr == 0){
						$ss12 = NULL;
						}
					else{
						$ss12 = $close_start_hr;
						}
					if ($close_start_mn != '00') {
						$ss12 .= ':'.$close_start_mn;
						}

					if ($close_end_hr > 12){
						$se12 = $close_end_hr - 12;
						}
					elseif($close_end_hr == 0){
						$se12 = NULL;
						}
					else{
						$se12 = $close_end_hr;
						}
					if ($close_end_mn != '00') {
						$se12 .= ':'.$close_end_mn;
						}
					$closure_times = $ss12.' - '.$se12;
					//Decimalize times
					if ($close_start_mn != '00') {
						$close_start_hr += dec_minutes($close_start_mn);
						}
					if ($close_end_mn != '00') {
						$close_end_hr += dec_minutes($close_end_mn);
						}
					}
				elseif ($cd_start != '00:01:00'){
					$close_start = explode(':',$cd_start);
					$close_start_hr = $close_start[0];
					$close_start_mn = $close_start[1];
					if ($close_start_hr > 12){
						$ss12 = $close_start_hr - 12;
						}
					elseif($close_start_hr == 0){
						$ss12 = NULL;
						}
					else{
						$ss12 = $close_start_hr;
						}
					if ($close_start_mn != '00') {
						$ss12 .= ':'.$close_start_mn;
						}
					$closure_times = 'After '.$ss12;
					//Decimalize times
					if ($close_start_mn != '00') {
						$close_start_hr += dec_minutes($close_start_mn);
						}
					$close_end_hr = 23;
					}
				elseif ($cd_end != '23:59:00'){
					$close_end = explode(':',$cd_end);
					$close_end_hr = $close_end[0];
					$close_end_mn = $close_end[1];
					if ($close_end_hr > 12){
						$se12 = $close_end_hr - 12;
						}
					elseif($close_end_hr == 0){
						$se12 = NULL;
						}
					else{
						$se12 = $close_end_hr;
						}
					if ($close_end_mn != '00') {
						$se12 .= ':'.$close_end_mn;
						}
					$closure_times = 'Until '.$se12;
					if ($close_end_mn != '00') {
						$close_end_hr += dec_minutes($close_end_mn);
						}
					$close_start_hr = 0;
					}
				$closure_message = "<div class=\"closure_message\"><h4>Library Closed</h4>
					The library is closed $closure_times for $cd_reason</div>";
				}
			}
		if(isset($allday_closure_message)){
			echo $allday_closure_message;
			}
		elseif ($season == 'summer' && $day_short == 'Sun'){
			echo "<div class=\"error\"><div class=\"message\"><h4>Library Closed</h4>
				<p>The library is closed Sundays during the summer.</p></div></div></div>";
			}
		else{
			if(isset($closure_message)){
				echo $closure_message;
				}

			echo '<div class="pic"><b>Person In Charge:</b> ';
			if (in_array($day_short,array('Mon','Tue','Wed','Thu'))){
				echo '(5-8pm) ';
				}
			elseif($day_short == 'Fri'){
				echo '(5-6pm) ';
				}
			if ($pic_name != ''){echo $pic_name.' &ndash; ';
				}
			echo '<b>x2778</b></div>'."\n";

			//See if schedules exist.
			$query20 = "SELECT first_name, e.emp_id, time_format(shift_start,'%k') as shift_start
				from employees as e, shifts as a, schedules as s
				WHERE e.emp_id = a.emp_id and schedule_start_date <= '$today' and schedule_end_date >= '$today'
				and week_type='$week_type' and shift_day='$day' and a.specific_schedule=s.specific_schedule";
			$result20 = mysqli_query($dbc, $query20);
			if ($result20) {
				$num_rows = mysql_num_rows($result20);
				if ($num_rows != 0) {

				//Create and display division rows.
				foreach ($divisions as $division=>$divrow){
					if ($division != 'subs'){
						$specific_schedule = '';
						//Get schedule data.
						$query1 = "SELECT specific_schedule from schedules WHERE division='$divrow'
							and schedule_start_date <= '$today' and schedule_end_date >= '$today'";
						$result1 = mysqli_query($dbc, $query1);
						while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)){
							$specific_schedule = $row1['specific_schedule'];
							}
						$query2 = "SELECT first_name, last_name, name_dup, e.emp_id, time_format(shift_start,'%k') as shift_start,
							time_format(shift_start,'%i') as shift_start_minutes, time_format(shift_end,'%k') as shift_end,
							time_format(shift_end,'%i') as shift_end_minutes, time_format(desk_start,'%k') as desk_start,
							time_format(desk_start,'%i') as desk_start_minutes, time_format(desk_end,'%k') as desk_end,
							time_format(desk_end,'%i') as desk_end_minutes, time_format(desk_start2,'%k') as desk_start2,
							time_format(desk_start2,'%i') as desk_start2_minutes, time_format(desk_end2,'%k') as desk_end2,
							time_format(desk_end2,'%i') as desk_end2_minutes, time_format(lunch_start,'%k') as lunch_start,
							time_format(lunch_start,'%i') as lunch_start_minutes, time_format(lunch_end,'%k') as lunch_end,
							time_format(lunch_end,'%i') as lunch_end_minutes from employees as e, shifts as a
							WHERE a.specific_schedule = '$specific_schedule' and e.emp_id = a.emp_id
							and week_type='$week_type' and shift_day='$day'
							and e.active = 'Active' and (e.employee_lastday >= '$today' or e.employee_lastday is null)
							order by exempt_status asc, weekly_hours desc, first_name asc";
						$result2 = mysqli_query($dbc, $query2);

						echo '<div class="divrow"><h3><a href="/scheduler/' . $division . '/daily">' . $divrow . '</a>';
						if ($divrow == 'Admin'){
							echo ' / <a href="/scheduler/subs/daily">Subs</a>';
							}
						echo '</h3></div>'."\n";
						echo '<div class="dompdf"><h3>' . $divrow . '</h3></div>'."\n";

						$num_rows = mysql_num_rows($result2);
						if ($num_rows != 0) {
							//Initialize alert arrays.
							if ($day == 'Sat'){
								$alertarray = array(0=>0,1=>0,2=>0,3=>0,4=>1,5=>1,6=>1,7=>1,
									8=>1,9=>1,10=>1,11=>1,12=>1,13=>1,14=>1,15=>1,16=>1,17=>1,
									18=>1,19=>1,20=>1,21=>1,22=>0,23=>0,24=>0,25=>0);
								$alert_custserv = array(0=>0,1=>0,2=>0,3=>0,4=>2,5=>2,6=>2,7=>2,
									8=>2,9=>2,10=>2,11=>2,12=>2,13=>2,14=>2,15=>2,16=>2,17=>2,
									18=>2,19=>2,20=>2,21=>2,22=>0,23=>0,24=>0,25=>0);
								$alert_children = array(0=>0,1=>0,2=>0,3=>0,4=>1,5=>1,6=>1,7=>1,
									8=>1,9=>1,10=>1,11=>1,12=>1,13=>1,14=>1,15=>1,16=>1,17=>1,
									18=>1,19=>1,20=>1,21=>1,22=>0,23=>0,24=>0,25=>0);
								$alert_adult = array(0=>0,1=>0,2=>0,3=>0,4=>1,5=>1,6=>2,7=>2,
									8=>2,9=>2,10=>2,11=>2,12=>2,13=>2,14=>2,15=>2,16=>2,17=>2,
									18=>2,19=>2,20=>2,21=>2,22=>0,23=>0,24=>0,25=>0);
								}
							elseif ($day == 'Sun'){
								$alertarray = array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,7=>0,
									8=>0,9=>0,10=>0,11=>0,12=>1,13=>1,14=>1,15=>1,16=>1,17=>1,
									18=>1,19=>1,20=>0,21=>0,22=>0,23=>0,24=>0,25=>0);
								$alert_custserv = array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,7=>0,
									8=>0,9=>0,10=>0,11=>0,12=>2,13=>2,14=>2,15=>2,16=>2,17=>2,
									18=>2,19=>2,20=>0,21=>0,22=>0,23=>0,24=>0,25=>0);
								$alert_children = array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,7=>0,
									8=>0,9=>0,10=>0,11=>0,12=>1,13=>1,14=>1,15=>1,16=>1,17=>1,
									18=>1,19=>1,20=>0,21=>0,22=>0,23=>0,24=>0,25=>0);
								$alert_adult = array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,7=>0,
									8=>0,9=>0,10=>0,11=>0,12=>2,13=>2,14=>2,15=>2,16=>2,17=>2,
									18=>2,19=>2,20=>0,21=>0,22=>0,23=>0,24=>0,25=>0);
								}
							elseif ($day == 'Fri') {
								$alertarray = array(0=>0,1=>0,2=>0,3=>0,4=>1,5=>1,6=>1,7=>1,
									8=>1,9=>1,10=>1,11=>1,12=>1,13=>1,14=>1,15=>1,16=>1,17=>1,
									18=>1,19=>1,20=>1,21=>1,22=>0,23=>0,24=>0,25=>0);
								$alert_custserv = array(0=>0,1=>0,2=>0,3=>0,4=>2,5=>2,6=>2,7=>2,
									8=>2,9=>2,10=>2,11=>2,12=>2,13=>2,14=>2,15=>2,16=>2,17=>2,
									18=>2,19=>2,20=>2,21=>2,22=>0,23=>0,24=>0,25=>0);
								$alert_children = array(0=>0,1=>0,2=>0,3=>0,4=>1,5=>2,6=>2,7=>2,
									8=>2,9=>2,10=>1,11=>1,12=>1,13=>1,14=>1,15=>1,16=>1,17=>1,
									18=>1,19=>1,20=>1,21=>1,22=>0,23=>0,24=>0,25=>0);
								$alert_adult = array(0=>0,1=>0,2=>0,3=>0,4=>1,5=>1,6=>2,7=>2,
									8=>2,9=>2,10=>2,11=>2,12=>2,13=>2,14=>2,15=>2,16=>2,17=>2,
									18=>2,19=>2,20=>2,21=>2,22=>0,23=>0,24=>0,25=>0);
								}
							elseif ($day == 'Wed') {
								$alertarray = array(0=>0,1=>0,2=>0,3=>0,4=>1,5=>1,6=>1,7=>1,
									8=>1,9=>1,10=>1,11=>1,12=>1,13=>1,14=>1,15=>1,16=>1,17=>1,
									18=>1,19=>1,20=>1,21=>1,22=>1,23=>1,24=>1,25=>1);
								$alert_custserv = array(0=>0,1=>0,2=>0,3=>0,4=>2,5=>2,6=>2,7=>2,
									8=>2,9=>2,10=>2,11=>2,12=>2,13=>2,14=>2,15=>2,16=>2,17=>2,
									18=>2,19=>2,20=>2,21=>2,22=>2,23=>2,24=>2,25=>2);
								$alert_children = array(0=>0,1=>0,2=>0,3=>0,4=>1,5=>1,6=>2,7=>2,
									8=>2,9=>2,10=>1,11=>1,12=>1,13=>1,14=>2,15=>2,16=>2,17=>2,
									18=>2,19=>2,20=>2,21=>2,22=>1,23=>1,24=>1,25=>1);
								$alert_adult = array(0=>0,1=>0,2=>0,3=>0,4=>1,5=>1,6=>2,7=>2,
									8=>2,9=>2,10=>2,11=>2,12=>2,13=>2,14=>2,15=>2,16=>2,17=>2,
									18=>2,19=>2,20=>1,21=>1,22=>1,23=>1,24=>1,25=>1);
								}
							else {
								$alertarray = array(0=>0,1=>0,2=>0,3=>0,4=>1,5=>1,6=>1,7=>1,
									8=>1,9=>1,10=>1,11=>1,12=>1,13=>1,14=>1,15=>1,16=>1,17=>1,
									18=>1,19=>1,20=>1,21=>1,22=>1,23=>1,24=>1,25=>1);
								$alert_custserv = array(0=>0,1=>0,2=>0,3=>0,4=>2,5=>2,6=>2,7=>2,
									8=>2,9=>2,10=>2,11=>2,12=>2,13=>2,14=>2,15=>2,16=>2,17=>2,
									18=>2,19=>2,20=>2,21=>2,22=>2,23=>2,24=>2,25=>2);
								$alert_children = array(0=>0,1=>0,2=>0,3=>0,4=>1,5=>1,6=>2,7=>2,
									8=>2,9=>2,10=>1,11=>1,12=>1,13=>1,14=>2,15=>2,16=>2,17=>2,
									18=>2,19=>2,20=>2,21=>2,22=>1,23=>1,24=>1,25=>1);
								$alert_adult = array(0=>0,1=>0,2=>0,3=>0,4=>1,5=>1,6=>2,7=>2,
									8=>2,9=>2,10=>2,11=>2,12=>2,13=>2,14=>2,15=>2,16=>2,17=>2,
									18=>2,19=>2,20=>2,21=>2,22=>2,23=>2,24=>2,25=>2);
								}
							$multi_alert = array();

							//Initialize deficiency arrays.
							$deficiencies = array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,7=>0,8=>0,9=>0,10=>0,11=>0,12=>0,
								13=>0,14=>0,15=>0,16=>0,17=>0,18=>0,19=>0,20=>0,21=>0,22=>0,23=>0,24=>0,25=>0);
							$def_array = null;

							echo '<div class="divboxes">'."\n".'<table class="dptsched '.$divrow.'" style="border-collapse:collapse;" cellspacing="0">'."\n";
							echo '<tr class="times"><td class="first_name"></td>';

							//Create time scale.
							foreach ($hoursscale as $hr){
								if ($hr>12){
									$hr = $hr-12;
									}
								echo '<td class="off"><div class="hr">' . $hr . '</div></td>';
								}
							echo '<td class="shift"><div class="hr">8</div></td>';
							echo '<td class="timeoff_reason"></td></tr>'."\n";

							//Create and display employee rows.
							while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)){
								$emp_id = $row2['emp_id'];
								$first_name = $row2['first_name'];
								if ($row2['name_dup'] == 'Y'){
									$last_initial = substr($row2['last_name'],0,1);
									$first_name .= ' ' . $last_initial . '.';
									}
								$shift_start = $row2['shift_start'];
								$shift_start_minutes = $row2['shift_start_minutes'];
								$shift_end = $row2['shift_end'];
								$shift_end_minutes = $row2['shift_end_minutes'];
								$desk_start = $row2['desk_start'];
								$desk_start_minutes = $row2['desk_start_minutes'];
								$desk_end = $row2['desk_end'];
								$desk_end_minutes = $row2['desk_end_minutes'];
								$desk_start2 = $row2['desk_start2'];
								$desk_start2_minutes = $row2['desk_start2_minutes'];
								$desk_end2 = $row2['desk_end2'];
								$desk_end2_minutes = $row2['desk_end2_minutes'];
								$lunch_start = $row2['lunch_start'];
								$lunch_start_minutes = $row2['lunch_start_minutes'];
								$lunch_end = $row2['lunch_end'];
								$lunch_end_minutes = $row2['lunch_end_minutes'];
								$multi_alert[$emp_id] = array(0=>0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);

								//Adjust 24-hour time.
								if ($shift_start > 12){
									$ss12 = $shift_start - 12;
									}
								elseif($shift_start == 0){
									$ss12 = NULL;
									}
								else{
									$ss12 = $shift_start;
									}
								if ($shift_start_minutes != '00') {
									$ss12 .= ':'.$shift_start_minutes;
									}

								if ($shift_end > 12){
									$se12 = $shift_end - 12;
									}
								elseif($shift_end == 0){
									$se12 = NULL;
									}
								else{
									$se12 = $shift_end;
									}
								if ($shift_end_minutes != '00') {
									$se12 .= ':'.$shift_end_minutes;
									}

								//Decimalize times
								if ($shift_start_minutes != '00') {
									$shift_start += dec_minutes($shift_start_minutes);
									}
								if ($shift_end_minutes != '00') {
									$shift_end += dec_minutes($shift_end_minutes);
									}
								if ($desk_start_minutes != '00') {
									$desk_start += dec_minutes($desk_start_minutes);
									}
								if ($desk_end_minutes != '00') {
									$desk_end += dec_minutes($desk_end_minutes);
									}
								if ($desk_start2_minutes != '00') {
									$desk_start2 += dec_minutes($desk_start2_minutes);
									}
								if ($desk_end2_minutes != '00') {
									$desk_end2 += dec_minutes($desk_end2_minutes);
									}
								if ($lunch_start_minutes != '00') {
									$lunch_start += dec_minutes($lunch_start_minutes);
									}
								if ($lunch_end_minutes != '00') {
									$lunch_end += dec_minutes($lunch_end_minutes);
									}

								$query3 = "SELECT emp_id, timeoff_start_date, time_format(timeoff_start_time,'%k') as timeoff_start,
									time_format(timeoff_start_time,'%i') as timeoff_start_minutes, timeoff_end_date,
									time_format(timeoff_end_time,'%k') as timeoff_end, time_format(timeoff_end_time,'%i') as timeoff_end_minutes,
									timeoff_reason from timeoff as t where emp_id='$emp_id' and
									timeoff_start_date <= '$today' and timeoff_end_date >= '$today'";
								$result3 = mysqli_query($dbc, $query3) or die(mysql_error($dbc));

									$array = array();
									while ($row3 = mysql_fetch_array ($result3, MYSQL_ASSOC)){
										$tostartd = $row3['timeoff_start_date'];
										$tostart = $row3['timeoff_start'];
										$tostart_minutes = $row3['timeoff_start_minutes'];
										$toendd = $row3['timeoff_end_date'];
										$toend = $row3['timeoff_end'];
										$toend_minutes = $row3['timeoff_end_minutes'];
										$toreason = $row3['timeoff_reason'];

										if ($tostartd != $today){
											$tostart = 1;
											}
										if ($toendd != $today){
											$toend = 23;
											}

										if ($tostart_minutes != '00') {
											$tostart += dec_minutes($tostart_minutes);
											}
										if ($toend_minutes != '00') {
											$toend += dec_minutes($toend_minutes);
											}

										$array[] = array('tos'=>$tostart, 'toe'=>$toend, 'tor'=>$toreason);
										}

								$query4 = "SELECT emp_id, coverage_date, time_format(coverage_start_time,'%k') as coverage_start,
									time_format(coverage_start_time,'%i') as coverage_start_minutes, time_format(coverage_end_time,'%k') as coverage_end,
									time_format(coverage_end_time,'%i') as coverage_end_minutes, coverage_offdesk, coverage_reason
									FROM coverage as c
									where emp_id='$emp_id' and coverage_date = '$today'
									and c.coverage_division= '$divrow'";
								$result4 = mysqli_query($dbc, $query4) or die(mysql_error($dbc));
								if (mysql_num_rows($result) != 0) {
									$cov_array = array();
									while ($row4 = mysql_fetch_array ($result4, MYSQL_ASSOC)){
										$cov_start = $row4['coverage_start'];
										$cov_start_minutes = $row4['coverage_start_minutes'];
										if ($cov_start_minutes != '00') {
											$cov_start += dec_minutes($cov_start_minutes);
											}
										$cov_end = $row4['coverage_end'];
										$cov_end_minutes = $row4['coverage_end_minutes'];
										if ($cov_end_minutes != '00') {
											$cov_end += dec_minutes($cov_end_minutes);
											}
										$cov_offdesk = $row4['coverage_offdesk'];
										$cov_reason = $row4['coverage_reason'];
										$cov_array[] = array('cov_start'=>$cov_start, 'cov_end'=>$cov_end,
											'cov_offdesk'=>$cov_offdesk, 'cov_reason'=>$cov_reason);
										}
									}

								echo '<tr class="emps"><td class="first_name">' . $first_name . '</td>';

								//Apply correct cell classes for visual styling.
								foreach ($normalhours as $key=>$hr){
									$css=null;
									if (is_float($hr)){
										$css='decimal';
										}
									$class='off';
									if (($hr >= $shift_start) && ($hr < $shift_end)){
										$class='here';
										}
									if (($hr >= $desk_start) && ($hr < $desk_end)){
										$class='on';
										$multi_alert[$emp_id][$key] = 1;

										if (($hr >= $lunch_start) && ($hr < $lunch_end)){
											$class='off';
											$multi_alert[$emp_id][$key] = 0;
											}
										}
									if (($hr >= $desk_start2) && ($hr < $desk_end2)){
										$class='on';
										$multi_alert[$emp_id][$key] = 1;

										if (($hr >= $lunch_start) && ($hr < $lunch_end)){
											$class='off';
											$multi_alert[$emp_id][$key] = 0;
											}
										}
									if (($hr >= $lunch_start) && ($hr < $lunch_end)){
										$class='off';
										}

									foreach ($array as $timeoff){
										if (($hr >= $timeoff['tos']) && ($hr < $timeoff['toe'])){
											$class='off';

											if ((($hr >= $desk_start) && ($hr < $desk_end)) ||
												(($hr >= $desk_start2) && ($hr < $desk_end2))){
												$multi_alert[$emp_id][$key] = 0;
												}
											}
										}
									foreach ($cov_array as $cover){
										if (($hr >= $cover['cov_start']) && ($hr < $cover['cov_end'])){
											if ($cover['cov_offdesk'] == 'Off'){
												$class='here';
												if ((($hr >= $desk_start) && ($hr < $desk_end)) ||
													(($hr >= $desk_start2) && ($hr < $desk_end2))){
													$multi_alert[$emp_id][$key] = 0;
													}
												}
											elseif($cover['cov_offdesk'] == 'Busy'){
												$class='busy';
												if ((($hr >= $desk_start) && ($hr < $desk_end)) ||
													(($hr >= $desk_start2) && ($hr < $desk_end2))){
													$multi_alert[$emp_id][$key] = 0;
													}
												}
											else {
												$class='on';
												$multi_alert[$emp_id][$key] = 1;
												--$deficiencies[$key];
												}
											}
										}
									if ((isset($close_start_hr))&&(($hr >= $close_start_hr) && ($hr < $close_end_hr))){
										$class='closed';
										$multi_alert[$emp_id][$key] = 1;
										}

									echo '<td class="' . $class;
									if (isset($css)){ echo ' ' . $css;}
									echo '"></td>';
									$classrow[] = $class;
									}
								if (isset($ss12)){
									echo '<td class="shift">' . $ss12 . ' - ' . $se12 . '</td>';
									}
								else{
									echo '<td class="shift"></td>';
									}
								echo '<td class="timeoff_reason">';
								if ((isset($array)) || (isset($cov_array))){
									echo '<div class="td_outer"><div class="td_inner">';
									if (isset($array)){
										foreach ($array as $timeoff){
											if ($timeoff['tor'] != ''){
												echo '- ' . $timeoff['tor'] . ' ';
												}
											}
										}
									if (isset($cov_array)){
										foreach ($cov_array as $cover){
											if ($cover['cov_reason'] != NULL){
												echo '- ' . $cover['cov_reason'] . ' ';
												}
											}
										}
									echo '</div></div>';
									}
								echo '</td></tr>'."\n";
								}

							//Get sub data.
							$sub_array = array();
							$query5 = "SELECT first_name, last_name, name_dup, e.emp_id, coverage_end_time as cet,
								time_format(coverage_start_time,'%k') as coverage_start,
								time_format(coverage_start_time,'%i') as coverage_start_minutes,
								time_format(coverage_end_time,'%k') as coverage_end,
								time_format(coverage_end_time,'%i') as coverage_end_minutes,
								coverage_offdesk, coverage_reason, division
								FROM employees as e, coverage as c
								WHERE (division not like '%".$divrow."%') and c.coverage_division = '$divrow' and
								coverage_date='$today' and e.emp_id = c.emp_id and e.active = 'Active'
								ORDER BY last_name asc, cet asc";
							$result5 = mysqli_query($dbc, $query5) or die(mysql_error($dbc));

							while ($row5 = mysql_fetch_array ($result5, MYSQL_ASSOC)) {
								$emp_id = $row5['emp_id'];
								$first_name = $row5['first_name'];
								if ($row2['name_dup'] == 'Y'){
									$last_initial = substr($row2['last_name'],0,1);
									$first_name .= ' ' . $last_initial . '.';
									}
								$coverage_start = $row5['coverage_start'];
								$coverage_start_minutes = $row5['coverage_start_minutes'];
								$coverage_end = $row5['coverage_end'];
								$coverage_end_minutes = $row5['coverage_end_minutes'];

								$coverage_offdesk = $row5['coverage_offdesk'];
								$coverage_reason = $row5['coverage_reason'];

								$multi_alert[$emp_id] = array(0=>0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);

								//Adjust 24-hour time.
								if ($coverage_start > 12){
									$cs12 = $coverage_start - 12;
									}
								else{
									$cs12 = $coverage_start;
									}
								if ($coverage_start_minutes != '00') {
									$cs12 .= ':'.$coverage_start_minutes;
									}
								if ($coverage_end > 12){
									$ce12 = $coverage_end - 12;
									}
								else{
									$ce12 = $coverage_end;
									}
								if ($coverage_end_minutes != '00') {
									$ce12 .= ':'.$coverage_end_minutes;
									}

								if ($coverage_start_minutes != '00') {
									$coverage_start += dec_minutes($coverage_start_minutes);
									}
								if ($coverage_end_minutes != '00') {
									$coverage_end += dec_minutes($coverage_end_minutes);
									}

								$sub_array[$first_name][] = array('emp_id'=>$emp_id, 'coverage_start'=>$coverage_start,
									'cs12'=>$cs12, 'coverage_end'=>$coverage_end, 'ce12'=>$ce12,
									'coverage_offdesk'=>$coverage_offdesk, 'coverage_reason'=>$coverage_reason);
								}

							foreach ($sub_array as $first_name=>$subs){
								echo '<tr class="emps subs"><td class="first_name">' . $first_name . '</td>';

								foreach ($normalhours as $key=>$hr){
									$css=null;
									if (is_float($hr)){
										$css='decimal';
										}
									$class='off';
									foreach ($subs as $keys){
										$emp_id = $keys['emp_id'];
										if (($hr >= $keys['coverage_start']) && ($hr < $keys['coverage_end'])){
											if ($keys['coverage_offdesk'] == 'Off'){
												$class='here';
												}
											elseif ($keys['coverage_offdesk'] == 'Busy'){
												$class='busy';
												}
											else {
												$class='on';
												$multi_alert[$emp_id][$key] = 1;
												--$deficiencies[$key];
												}
											}
										}
									if ((isset($close_start_hr))&&(($hr >= $close_start_hr) && ($hr < $close_end_hr))){
										$class='closed';
										$multi_alert[$emp_id][$key] = 1;
										}
									echo '<td class="' . $class;
									if (isset($css)){ echo ' ' . $css;}
									echo '"></td>';
									}

								echo '<td class="shift">';
								echo '<div class="td_outer"><div class="td_inner">';
								foreach ($subs as $keys=>$array){
									if ($keys == 0){
										echo $array['cs12'] . ' - ' . $array['ce12'];
										}
									else {
										echo ', ' .  $array['cs12'] . ' - ' . $array['ce12'];
										}
									}
								echo '</div></div>';
								echo '</td>';
								echo '<td class="timeoff_reason">';
								echo '<div class="td_outer"><div class="td_inner">';
								foreach ($subs as $keys){
									if ($keys['coverage_reason'] != NULL){
										echo '- ' . $keys['coverage_reason'] . ' ';
										}
									}
								echo '</div></div>';
								echo '</td></tr>'."\n";
								}

							//Get deficiencies data.
							$query6 = "SELECT time_format(def_start,'%k') as def_start, time_format(def_start,'%i') as def_start_minutes,
								time_format(def_end,'%k') as def_end, time_format(def_end,'%i') as def_end_minutes
								FROM deficiencies WHERE def_schedule='$specific_schedule'
								and def_week='$week_type' and def_day='$day' and def_division='$divrow'";
							$result6 = mysqli_query($dbc, $query6) or die(mysql_error($dbc));
							if (mysql_num_rows($result6) != 0) {
								while ($row6 = mysql_fetch_array ($result6, MYSQL_ASSOC)){
									$def_start = $row6['def_start'];
									$def_start_minutes = $row6['def_start_minutes'];
									$def_end = $row6['def_end'];
									$def_end_minutes = $row6['def_end_minutes'];

									//Adjust 24-hour time.
									if ($def_start > 12){
										$defs12 = $def_start - 12;
										}
									else{
										$defs12 = $def_start;
										}
									if ($def_start_minutes != '00') {
										$defs12 .= ':'.$def_start_minutes;
										}
									if ($def_end > 12){
										$defe12 = $def_end - 12;
										}
									else{
										$defe12 = $def_end;
										}
									if ($def_end_minutes != '00') {
										$defs12 .= ':'.$def_end_minutes;
										}

									if ($def_start_minutes != '00') {
										$def_start += dec_minutes($def_start_minutes);
										}
									if ($def_end_minutes != '00') {
										$def_end += dec_minutes($def_end_minutes);
										}

									$def_array[] = array('def_start'=>$def_start,
										'defs12'=>$defs12, 'def_end'=>$def_end, 'defe12'=>$defe12);
									}
								foreach ($def_array as $key=>$def){
									foreach ($normalhours as $key=>$hr){
										if (($hr >= $def['def_start']) && ($hr < $def['def_end'])){
											++$deficiencies[$key];
											--$alertarray[$key];
											--$alert_custserv[$key];
											--$alert_children[$key];
											--$alert_adult[$key];
											}
										}
									}
								if (in_array(1,$deficiencies)){
									echo '<tr class="emps"><td class="first_name"><b>SUB</b></td>';
									foreach ($deficiencies as $key=>$hr){
										$css=null;
										if ($key&1){
											$css='decimal';
											}
										$class='off';
										if ($hr > 0){
											$class='def';
											}
										echo '<td class="' . $class;
										if (isset($css)){ echo ' ' . $css;}
										echo '"></td>';
										}
									echo '<td class="shift">';
									echo '<div class="td_outer"><div class="td_inner">';
									foreach ($def_array as $key=>$def){
										if ($key == 0){
											echo $def['defs12'] . ' - ' . $def['defe12'];
											}
										else {
											echo ', ' .  $def['defs12'] . ' - ' . $def['defe12'];
											}
										}
									echo '</div></div>';
									echo '</td>';
									echo '<td class="timeoff_reason"></td></tr>'."\n";
									}
								}

							//Print alert data.
							foreach ($multi_alert as $emp_id=>$array){
								foreach ($array as $key=>$hr){
									if ($hr == 1){
										--$alertarray[$key];
										--$alert_custserv[$key];
										--$alert_children[$key];
										--$alert_adult[$key];
										}
									}
								}

							if (($divrow != 'Admin') && ($divrow != 'Tech Services') && ($divrow != 'Pages')){
								if ($divrow == 'Customer Service'){
									if (in_array(1,$alert_custserv)){
										echo '<tr class="emps"><td class="first_name"><b>NEEDED</b></td>';

										foreach ($alert_custserv as $key=>$hr){
											$css=null;
											if ($key&1){
												$css='decimal';
												}
											$class='off';
											if ($hr > 0){
												$class='alert';
												}
											echo '<td class="' . $class;
											if (isset($css)){ echo ' ' . $css;}
											echo '"></td>';
											}

										echo '<td class="shift"></td>';
										echo '<td class="timeoff_reason"></td></tr>'."\n";
										}
									}
								elseif ($divrow == 'Children'){
									if (in_array(1,$alert_children)){
										echo '<tr class="emps"><td class="first_name"><b>NEEDED</b></td>';

										foreach ($alert_children as $key=>$hr){
											$css=null;
											if ($key&1){
												$css='decimal';
												}
											$class='off';
											if ($hr > 0){
												$class='alert';
												}
											echo '<td class="' . $class;
											if (isset($css)){ echo ' ' . $css;}
											echo '"></td>';
											}

										echo '<td class="shift"></td>';
										echo '<td class="timeoff_reason"></td></tr>'."\n";
										}
									}
								elseif ($divrow == 'Adult'){
									if (in_array(1,$alert_adult)){
										echo '<tr class="emps"><td class="first_name"><b>NEEDED</b></td>';

										foreach ($alert_adult as $key=>$hr){
											$css=null;
											if ($key&1){
												$css='decimal';
												}
											$class='off';
											if ($hr > 0){
												$class='alert';
												}
											echo '<td class="' . $class;
											if (isset($css)){ echo ' ' . $css;}
											echo '"></td>';
											}

										echo '<td class="shift"></td>';
										echo '<td class="timeoff_reason"></td></tr>'."\n";
										}
									}
								else {
									if (in_array(1,$alertarray)){
										echo '<tr class="emps"><td class="first_name"><b>NEEDED</b></td>';

										foreach ($alertarray as $key=>$hr){
											$css=null;
											if ($key&1){
												$css='decimal';
												}
											$class='off';
											if ($hr > 0){
												$class='alert';
												}
											echo '<td class="' . $class ;
											if (isset($css)){ echo ' ' . $css;}
											echo '"></td>';
											}

										echo '<td class="shift"></td>';
										echo '<td class="timeoff_reason"></td></tr>'."\n";
										}
									}
								}

							//Sub rows for Admin
								if ($divrow == 'Admin'){
									echo '<tr class="emps"><td class="empty"></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
										<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
										<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td class="empty"></td><td class="empty"></td></tr>'."\n";

								//Get Subs employee data.
								$query2 = "SELECT first_name, last_name, name_dup, emp_id from employees as e
									where division = 'Subs' and active = 'Active' order by first_name asc";
								$result2 = mysqli_query($dbc, $query2) or die(mysql_error($dbc));

								//Create and display employee rows.
								while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)){
									$emp_id = $row2['emp_id'];
									$first_name = $row2['first_name'];
									if ($row2['name_dup'] == 'Y'){
										$last_initial = substr($row2['last_name'],0,1);
										$first_name .= ' ' . $last_initial . '.';
										}

									$query3 = "SELECT emp_id, timeoff_start_date, time_format(timeoff_start_time,'%k') as timeoff_start,
										time_format(timeoff_start_time,'%i') as timeoff_start_minutes, timeoff_end_date,
										time_format(timeoff_end_time,'%k') as timeoff_end, time_format(timeoff_end_time,'%i') as timeoff_end_minutes,
										timeoff_reason from timeoff as t where emp_id='$emp_id' and
										timeoff_start_date <= '$today' and timeoff_end_date >= '$today'";
									$result3 = mysqli_query($dbc, $query3) or die(mysql_error($dbc));

										$array = array();
										while ($row3 = mysql_fetch_array ($result3, MYSQL_ASSOC)){
											$tostartd = $row3['timeoff_start_date'];
											$tostart = $row3['timeoff_start'];
											$tostart_minutes = $row3['timeoff_start_minutes'];
											$toendd = $row3['timeoff_end_date'];
											$toend = $row3['timeoff_end'];
											$toend_minutes = $row3['timeoff_end_minutes'];
											$toreason = $row3['timeoff_reason'];

											if ($tostartd != $today){
												$tostart = 1;
												}
											if ($toendd != $today){
												$toend = 23;
												}

											if ($tostart_minutes != '00') {
												$tostart += dec_minutes($tostart_minutes);
												}
											if ($toend_minutes != '00') {
												$toend += dec_minutes($toend_minutes);
												}

											$array[] = array('tos'=>$tostart, 'toe'=>$toend, 'tor'=>$toreason);
											}

										$query4 = "SELECT emp_id, coverage_date, coverage_end_time as cet,
											time_format(coverage_start_time,'%k') as coverage_start,
											time_format(coverage_start_time,'%i') as coverage_start_minutes, time_format(coverage_end_time,'%k') as coverage_end,
											time_format(coverage_end_time,'%i') as coverage_end_minutes, coverage_offdesk, coverage_reason from coverage as c
											where emp_id='$emp_id' and coverage_date = '$today'
											ORDER BY cet asc";
										$result4 = mysqli_query($dbc, $query4) or die(mysql_error($dbc));
										if (mysql_num_rows($result) != 0) {
											$cov_array = array();
											while ($row4 = mysql_fetch_array ($result4, MYSQL_ASSOC)){
												$coverage_start = $row4['coverage_start'];
												$coverage_start_minutes = $row4['coverage_start_minutes'];
												$coverage_end = $row4['coverage_end'];
												$coverage_end_minutes = $row4['coverage_end_minutes'];

												$coverage_offdesk = $row4['coverage_offdesk'];
												$coverage_reason = $row4['coverage_reason'];

												//Adjust 24-hour time.
												if ($coverage_start > 12){
													$cs12 = $coverage_start - 12;
													}
												else{
													$cs12 = $coverage_start;
													}
												if ($coverage_start_minutes != '00') {
													$cs12 .= ':'.$coverage_start_minutes;
													}
												if ($coverage_end > 12){
													$ce12 = $coverage_end - 12;
													}
												else{
													$ce12 = $coverage_end;
													}
												if ($coverage_end_minutes != '00') {
													$ce12 .= ':'.$coverage_end_minutes;
													}

												if ($coverage_start_minutes != '00') {
													$coverage_start += dec_minutes($coverage_start_minutes);
													}
												if ($coverage_end_minutes != '00') {
													$coverage_end += dec_minutes($coverage_end_minutes);
													}

												$cov_array[] = array('coverage_start'=>$coverage_start,
													'cs12'=>$cs12, 'coverage_end'=>$coverage_end, 'ce12'=>$ce12,
													'coverage_offdesk'=>$coverage_offdesk, 'coverage_reason'=>$coverage_reason);
												}

										echo '<tr class="emps"><td class="first_name">' . $first_name . '</td>';

										//Apply correct cell classes for visual styling.
										foreach ($normalhours as $key=>$hr){
											$css=null;
											if (is_float($hr)){
												$css='decimal';
												}
											$class='off';

											foreach ($array as $timeoff){
												if (($hr >= $timeoff['tos']) && ($hr < $timeoff['toe'])){
													$class='off';
													}
												}
											foreach ($cov_array as $cover){
												if (($hr >= $cover['coverage_start']) && ($hr < $cover['coverage_end'])){
													if ($cover['coverage_offdesk'] == 'Off'){
														$class='here';
														}
													else {
														$class='on';
														}
													}
												}
											if ((isset($close_start_hr))&&(($hr >= $close_start_hr) && ($hr < $close_end_hr))){
												$class='closed';
												}

											echo '<td class="' . $class;
											if (isset($css)){ echo ' ' . $css;}
											echo '"></td>';
											$classrow[] = $class;
											}

										echo '<td class="shift">';
										echo '<div class="td_outer"><div class="td_inner">';
										foreach ($cov_array as $keys=>$cover){
											if ($keys == 0){
												echo $cover['cs12'] . ' - ' . $cover['ce12'];
												}
											else {
												echo ', ' .  $cover['cs12'] . ' - ' . $cover['ce12'];
												}
											}
										echo '</div></div>';
										echo '</td>';

										echo '<td class="timeoff_reason">';
										echo '<div class="td_outer"><div class="td_inner">';
										if (isset($array)){
											foreach ($array as $timeoff){
												if ($timeoff['tor'] != ''){
													echo '- ' . $timeoff['tor'] . ' ';
													}
												}
											}
										foreach ($cov_array as $cover){
											if ($cover['coverage_reason'] != NULL){
												echo '- ' . $cover['coverage_reason'] . ' ';
												}
											}
										echo '</div></div>';
										echo '</td></tr>'."\n";
										}
									}
								}

							echo '</table></div><br/>'."\n";
							}
						else {
							echo '<div class="diverror">This division schedule has not yet been entered.
								For more information, please see your division head.</div>';
							}
						}
					}
				echo '</div>';
				}
				else {
					echo '<div class="error"><h4>This schedule does not yet exist!</h4>
						For more information, please see your division head.</div></div>';
					}
				}
			else {
				echo '<div class="error"><h4>This schedule does not yet exist!</h4>
					For more information, please see your division head.</div></div>';
				}
			}
		}
	}

function division_daily($division, $now) {
	//Get week type.
	$today = date('Y-m-d' , $now);
	$query = "SELECT date, week_type FROM dates where date = '$today'";
	$result = @mysqli_query($dbc, $query);

	while ($row = mysql_fetch_assoc($result)) {
		$week_type = $row['week_type'];
		}

	$day = date('D', $now);
	$year = date('Y', $now);

	//Get season.
	$query2 = "SELECT memorial_day, labor_day FROM holidays where year='$year'";
	$result2 = @mysqli_query($dbc, $query2);

	while ($row2 = mysql_fetch_assoc($result2)) {
		$memorial_day = $row2['memorial_day'];
		$labor_day = $row2['labor_day'];
		}

	$mem_sat = strtotime ('-2 days', strtotime ($memorial_day));
	$lab_sat = strtotime ('+5 days', strtotime ($labor_day));

	if ((strtotime($today) >= $mem_sat) && (strtotime($today) < $lab_sat)){
		$season = 'summer';
		}
	elseif (strtotime($today) < $mem_sat){
		$season = 'spring';
		}
	else {
		$season = 'fall';
		}

	//Set workday length.
	$normalhours = array(0=>7,7.5,8,8.5,9,9.5,10,10.5,11,11.5,12,12.5,13,13.5,14,14.5,15,15.5,16,16.5,17,17.5,18,18.5,19,19.5);
	$hoursscale = array(0=>7,null,8,null,9,null,10,null,11,null,12,null,13,null,14,null,15,null,16,null,17,null,18,null,19,null);

	$dom = date('j', $now);
	$day_long = date('l', $now);
	$day_short = date('D',$now);
	$month_long = date('F', $now);

	//Get PIC
	$pic_name = '';
	if (in_array($day_short,array('Mon','Tue','Wed','Thu', 'Fri'))){
		$query_pic = "SELECT first_name FROM employees e, pic p, pic_schedules s
			WHERE pic_start_date <= '$today' and pic_end_date >= '$today' and p.pic_schedule_id=s.pic_schedule_id
			and week_type='$week_type' and pic_day='$day'
			and p.emp_id=e.emp_id and e.emp_id not in
			(SELECT emp_id from timeoff WHERE
			timeoff_start_date <= '$today' and timeoff_end_date >= '$today' and
			((timeoff_start_time <= '17:00:00' and timeoff_end_time >= '20:00:00') or
			(timeoff_start_time <= '17:00:00' and (timeoff_end_time > '17:00:00' and timeoff_end_time <= '20:00:00')) or
			((timeoff_start_time >= '17:00:00' and timeoff_start_time < '20:00:00') and timeoff_end_time >= '20:00:00')
			))";
		}
	else{
		$query_pic = "SELECT first_name FROM employees e, pic p, pic_schedules s
			WHERE pic_start_date <= '$today' and pic_end_date >= '$today' and p.pic_schedule_id=s.pic_schedule_id
			and week_type='$week_type' and pic_day='$day'
			and p.emp_id=e.emp_id and e.emp_id not in
			(SELECT emp_id from timeoff WHERE timeoff_start_date <= '$today' and timeoff_end_date >= '$today')";
		}
	$result_pic = mysqli_query($dbc, $query_pic);
	if($result_pic){
		while ($row = mysql_fetch_array($result_pic, MYSQL_ASSOC)){
			$pic_name = $row['first_name'];
			}
		}
	else{
		$pic_name = '';
		}

	$query_pic_cover = "SELECT first_name from employees e, pic_coverage c WHERE pic_coverage_date='$today' and
		e.emp_id=c.emp_id";
	$result_pic_cover = mysqli_query($dbc, $query_pic_cover);
	if($result_pic_cover){
		while ($row = mysql_fetch_array($result_pic_cover, MYSQL_ASSOC)){
			$pic_name = $row['first_name'];
			}
		}


	//Standardize division names for DB
	if ($division =='customerservice'){
		$division = 'Customer Service';
		}
	if ($division == 'techservices'){
		$division = 'Tech Services';
		}
	if ($division == 'lti'){
		$ucdivision = 'Library Tech & Innovation';
		}
	else {
		$ucdivision = ucwords($division);
		}

	echo '<div class="division_specific">'."\n".'<div class="divspec_head">'."\n".
		'<input class="prev" type="button" onclick="loadprevDiv()" value="Previous" />'."\n".
		'<input class="next" type="button" value="Next" onclick="loadnextDiv()" />'."\n";
	echo "<div class=\"divspec screen\">$day_long, $dom $month_long $year</div>\n";
	echo "<div class=\"divspec mobile\">$day_short, $dom $month_long $year</div>\n";
	echo '<div class="week_type">'. ucwords($week_type) . ' Schedule</div>'."\n";


	//See if library closed.
	$query_closure = "SELECT * from closures WHERE closure_date='$today' limit 1";
	$result_closure = mysqli_query($dbc, $query_closure);
	if ($result_closure){
		$num_rows = mysql_num_rows($result_closure);
		if ($num_rows != 0){
			while ($row = mysql_fetch_array($result_closure, MYSQL_ASSOC)){
				$cd_date = $row['closure_date'];
				$cd_start = $row['closure_start_time'];
				$cd_end = $row['closure_end_time'];
				$cd_reason = $row['closure_reason'];
				}
			if (($cd_start == '00:01:00')&&($cd_end == '23:59:00')){
				$allday_closure_message = "<div class=\"error\"><div class=\"message\"><h4>Library Closed</h4>
					<p>The library is closed all day for $cd_reason</p></div></div></div>";
				}
			else{
				if (($cd_start != '00:01:00')&&($cd_end != '23:59:00')){
					$close_start = explode(':',$cd_start);
					$close_start_hr = $close_start[0];
					$close_start_mn = $close_start[1];
					$close_end = explode(':',$cd_end);
					$close_end_hr = $close_end[0];
					$close_end_mn = $close_end[1];
					if ($close_start_hr > 12){
						$ss12 = $close_start_hr - 12;
						}
					elseif($close_start_hr == 0){
						$ss12 = NULL;
						}
					else{
						$ss12 = $close_start_hr;
						}
					if ($close_start_mn != '00') {
						$ss12 .= ':'.$close_start_mn;
						}

					if ($close_end_hr > 12){
						$se12 = $close_end_hr - 12;
						}
					elseif($close_end_hr == 0){
						$se12 = NULL;
						}
					else{
						$se12 = $close_end_hr;
						}
					if ($close_end_mn != '00') {
						$se12 .= ':'.$close_end_mn;
						}
					$closure_times = $ss12.' - '.$se12;
					//Decimalize times
					if ($close_start_mn != '00') {
						$close_start_hr += dec_minutes($close_start_mn);
						}
					if ($close_end_mn != '00') {
						$close_end_hr += dec_minutes($close_end_mn);
						}
					}
				elseif ($cd_start != '00:01:00'){
					$close_start = explode(':',$cd_start);
					$close_start_hr = $close_start[0];
					$close_start_mn = $close_start[1];
					if ($close_start_hr > 12){
						$ss12 = $close_start_hr - 12;
						}
					elseif($close_start_hr == 0){
						$ss12 = NULL;
						}
					else{
						$ss12 = $close_start_hr;
						}
					if ($close_start_mn != '00') {
						$ss12 .= ':'.$close_start_mn;
						}
					$closure_times = 'After '.$ss12;
					//Decimalize times
					if ($close_start_mn != '00') {
						$close_start_hr += dec_minutes($close_start_mn);
						}
					$close_end_hr = 23;
					}
				elseif ($cd_end != '23:59:00'){
					$close_end = explode(':',$cd_end);
					$close_end_hr = $close_end[0];
					$close_end_mn = $close_end[1];
					if ($close_end_hr > 12){
						$se12 = $close_end_hr - 12;
						}
					elseif($close_end_hr == 0){
						$se12 = NULL;
						}
					else{
						$se12 = $close_end_hr;
						}
					if ($close_end_mn != '00') {
						$se12 .= ':'.$close_end_mn;
						}
					$closure_times = 'Until '.$se12;
					if ($close_end_mn != '00') {
						$close_end_hr += dec_minutes($close_end_mn);
						}
					$close_start_hr = 0;
					}
				$closure_message = "<div class=\"closure_message\"><h4>Library Closed</h4>
					The library is closed $closure_times for $cd_reason</div>";
				}
			}
		if(isset($allday_closure_message)){
			echo $allday_closure_message;
			}
		elseif ($season == 'summer' && $day_short == 'Sun'){
			echo "<div class=\"error\"><div class=\"message\"><h4>Library Closed</h4>
				<p>The library is closed Sundays during the summer.</p></div></div></div>";
			}
		else{
			if(isset($closure_message)){
				echo $closure_message;
				}
			echo '<div class="pic"><b>Person In Charge:</b> ';
			if (in_array($day_short,array('Mon','Tue','Wed','Thu'))){
				echo '(5-8pm) ';
				}
			elseif($day_short == 'Fri'){
				echo '(5-6pm) ';
				}
			if ($pic_name != ''){echo $pic_name.' &ndash; ';
				}
			echo '<b>x2778</b></div>'."\n";
			echo '</div>';
			//See if schedules exist.
			$query20 = "SELECT first_name, e.emp_id, time_format(shift_start,'%k') as shift_start
				from employees as e, shifts as a, schedules as s
				where e.emp_id = a.emp_id and schedule_start_date <= '$today' and schedule_end_date >= '$today'
				and week_type='$week_type' and shift_day='$day' and e.division = '$division'
				and a.specific_schedule=s.specific_schedule";
			$result20 = mysqli_query($dbc, $query20);
			if ($result20){
				$num_rows = mysql_num_rows($result20);
				if ($num_rows != 0) {
					$divrow = ucwords($division);
					$specific_schedule = '';
					//Get schedule data.
					$query1 = "SELECT specific_schedule from schedules WHERE division='$divrow'
						and schedule_start_date <= '$today' and schedule_end_date >= '$today'";
					$result1 = mysqli_query($dbc, $query1);
					while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)){
						$specific_schedule = $row1['specific_schedule'];
						}
					$query2 = "SELECT first_name, last_name, name_dup, e.emp_id, time_format(shift_start,'%k') as shift_start,
						time_format(shift_start,'%i') as shift_start_minutes, time_format(shift_end,'%k') as shift_end,
						time_format(shift_end,'%i') as shift_end_minutes, time_format(desk_start,'%k') as desk_start,
						time_format(desk_start,'%i') as desk_start_minutes, time_format(desk_end,'%k') as desk_end,
						time_format(desk_end,'%i') as desk_end_minutes, time_format(desk_start2,'%k') as desk_start2,
						time_format(desk_start2,'%i') as desk_start2_minutes, time_format(desk_end2,'%k') as desk_end2,
						time_format(desk_end2,'%i') as desk_end2_minutes, time_format(lunch_start,'%k') as lunch_start,
						time_format(lunch_start,'%i') as lunch_start_minutes, time_format(lunch_end,'%k') as lunch_end,
						time_format(lunch_end,'%i') as lunch_end_minutes from employees as e, shifts as a
						WHERE a.specific_schedule = '$specific_schedule' and e.emp_id = a.emp_id
						and week_type='$week_type' and shift_day='$day'
						and e.active = 'Active' and (e.employee_lastday >= '$today' or e.employee_lastday is null)
						order by exempt_status asc, weekly_hours desc, first_name asc";
					$result2 = mysqli_query($dbc, $query2);

					//Initialize alert arrays.
					if ($day == 'Sat'){
						$alertarray = array(0=>0,1=>0,2=>0,3=>0,4=>1,5=>1,6=>1,7=>1,
							8=>1,9=>1,10=>1,11=>1,12=>1,13=>1,14=>1,15=>1,16=>1,17=>1,
							18=>1,19=>1,20=>1,21=>1,22=>0,23=>0,24=>0,25=>0);
						$alert_custserv = array(0=>0,1=>0,2=>0,3=>0,4=>2,5=>2,6=>2,7=>2,
							8=>2,9=>2,10=>2,11=>2,12=>2,13=>2,14=>2,15=>2,16=>2,17=>2,
							18=>2,19=>2,20=>2,21=>2,22=>0,23=>0,24=>0,25=>0);
						$alert_children = array(0=>0,1=>0,2=>0,3=>0,4=>1,5=>1,6=>1,7=>1,
							8=>1,9=>1,10=>1,11=>1,12=>1,13=>1,14=>1,15=>1,16=>1,17=>1,
							18=>1,19=>1,20=>1,21=>1,22=>0,23=>0,24=>0,25=>0);
						$alert_adult = array(0=>0,1=>0,2=>0,3=>0,4=>1,5=>1,6=>2,7=>2,
							8=>2,9=>2,10=>2,11=>2,12=>2,13=>2,14=>2,15=>2,16=>2,17=>2,
							18=>2,19=>2,20=>2,21=>2,22=>0,23=>0,24=>0,25=>0);
						}
					elseif ($day == 'Sun'){
						$alertarray = array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,7=>0,
							8=>0,9=>0,10=>0,11=>0,12=>1,13=>1,14=>1,15=>1,16=>1,17=>1,
							18=>1,19=>1,20=>0,21=>0,22=>0,23=>0,24=>0,25=>0);
						$alert_custserv = array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,7=>0,
							8=>0,9=>0,10=>0,11=>0,12=>2,13=>2,14=>2,15=>2,16=>2,17=>2,
							18=>2,19=>2,20=>0,21=>0,22=>0,23=>0,24=>0,25=>0);
						$alert_children = array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,7=>0,
							8=>0,9=>0,10=>0,11=>0,12=>1,13=>1,14=>1,15=>1,16=>1,17=>1,
							18=>1,19=>1,20=>0,21=>0,22=>0,23=>0,24=>0,25=>0);
						$alert_adult = array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,7=>0,
							8=>0,9=>0,10=>0,11=>0,12=>2,13=>2,14=>2,15=>2,16=>2,17=>2,
							18=>2,19=>2,20=>0,21=>0,22=>0,23=>0,24=>0,25=>0);
						}
					elseif ($day == 'Fri') {
						$alertarray = array(0=>0,1=>0,2=>0,3=>0,4=>1,5=>1,6=>1,7=>1,
							8=>1,9=>1,10=>1,11=>1,12=>1,13=>1,14=>1,15=>1,16=>1,17=>1,
							18=>1,19=>1,20=>1,21=>1,22=>0,23=>0,24=>0,25=>0);
						$alert_custserv = array(0=>0,1=>0,2=>0,3=>0,4=>2,5=>2,6=>2,7=>2,
							8=>2,9=>2,10=>2,11=>2,12=>2,13=>2,14=>2,15=>2,16=>2,17=>2,
							18=>2,19=>2,20=>2,21=>2,22=>0,23=>0,24=>0,25=>0);
						$alert_children = array(0=>0,1=>0,2=>0,3=>0,4=>1,5=>2,6=>2,7=>2,
							8=>2,9=>2,10=>1,11=>1,12=>1,13=>1,14=>1,15=>1,16=>1,17=>1,
							18=>1,19=>1,20=>1,21=>1,22=>0,23=>0,24=>0,25=>0);
						$alert_adult = array(0=>0,1=>0,2=>0,3=>0,4=>1,5=>1,6=>2,7=>2,
							8=>2,9=>2,10=>2,11=>2,12=>2,13=>2,14=>2,15=>2,16=>2,17=>2,
							18=>2,19=>2,20=>2,21=>2,22=>0,23=>0,24=>0,25=>0);
						}
					elseif ($day == 'Wed') {
						$alertarray = array(0=>0,1=>0,2=>0,3=>0,4=>1,5=>1,6=>1,7=>1,
							8=>1,9=>1,10=>1,11=>1,12=>1,13=>1,14=>1,15=>1,16=>1,17=>1,
							18=>1,19=>1,20=>1,21=>1,22=>1,23=>1,24=>1,25=>1);
						$alert_custserv = array(0=>0,1=>0,2=>0,3=>0,4=>2,5=>2,6=>2,7=>2,
							8=>2,9=>2,10=>2,11=>2,12=>2,13=>2,14=>2,15=>2,16=>2,17=>2,
							18=>2,19=>2,20=>2,21=>2,22=>2,23=>2,24=>2,25=>2);
						$alert_children = array(0=>0,1=>0,2=>0,3=>0,4=>1,5=>1,6=>2,7=>2,
							8=>2,9=>2,10=>1,11=>1,12=>1,13=>1,14=>2,15=>2,16=>2,17=>2,
							18=>2,19=>2,20=>2,21=>2,22=>1,23=>1,24=>1,25=>1);
						$alert_adult = array(0=>0,1=>0,2=>0,3=>0,4=>1,5=>1,6=>2,7=>2,
							8=>2,9=>2,10=>2,11=>2,12=>2,13=>2,14=>2,15=>2,16=>2,17=>2,
							18=>2,19=>2,20=>1,21=>1,22=>1,23=>1,24=>1,25=>1);
						}
					else {
						$alertarray = array(0=>0,1=>0,2=>0,3=>0,4=>1,5=>1,6=>1,7=>1,
							8=>1,9=>1,10=>1,11=>1,12=>1,13=>1,14=>1,15=>1,16=>1,17=>1,
							18=>1,19=>1,20=>1,21=>1,22=>1,23=>1,24=>1,25=>1);
						$alert_custserv = array(0=>0,1=>0,2=>0,3=>0,4=>2,5=>2,6=>2,7=>2,
							8=>2,9=>2,10=>2,11=>2,12=>2,13=>2,14=>2,15=>2,16=>2,17=>2,
							18=>2,19=>2,20=>2,21=>2,22=>2,23=>2,24=>2,25=>2);
						$alert_children = array(0=>0,1=>0,2=>0,3=>0,4=>1,5=>1,6=>2,7=>2,
							8=>2,9=>2,10=>1,11=>1,12=>1,13=>1,14=>2,15=>2,16=>2,17=>2,
							18=>2,19=>2,20=>2,21=>2,22=>1,23=>1,24=>1,25=>1);
						$alert_adult = array(0=>0,1=>0,2=>0,3=>0,4=>1,5=>1,6=>2,7=>2,
							8=>2,9=>2,10=>2,11=>2,12=>2,13=>2,14=>2,15=>2,16=>2,17=>2,
							18=>2,19=>2,20=>2,21=>2,22=>2,23=>2,24=>2,25=>2);
						}
					$multi_alert = array();

					//Initialize deficiency arrays.
					$deficiencies = array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,7=>0,8=>0,9=>0,10=>0,11=>0,12=>0,
						13=>0,14=>0,15=>0,16=>0,17=>0,18=>0,19=>0,20=>0,21=>0,22=>0,23=>0,24=>0,25=>0);
					$def_array = null;

					echo '<div class="divboxes">'."\n".'<table class="dptsched" style="border-collapse:collapse;" cellspacing="0">'."\n";
					echo '<tr class="times"><td class="first_name"></td>';

					//Create time scale.
					foreach ($hoursscale as $hr){
						if ($hr>12){
							$hr = $hr-12;
							}
						echo '<td class="off"><div class="hr">' . $hr . '</div></td>';
						}
					echo '<td class="shift"><div class="hr">8</div></td>';
					echo '<td class="timeoff_reason"></td></tr>'."\n";

					//Create and display employee rows.
					while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)){
						$emp_id = $row2['emp_id'];
						$first_name = $row2['first_name'];
						if ($row2['name_dup'] == 'Y'){
							$last_initial = substr($row2['last_name'],0,1);
							$first_name .= ' ' . $last_initial . '.';
							}
						$shift_start = $row2['shift_start'];
						$shift_start_minutes = $row2['shift_start_minutes'];
						$shift_end = $row2['shift_end'];
						$shift_end_minutes = $row2['shift_end_minutes'];
						$desk_start = $row2['desk_start'];
						$desk_start_minutes = $row2['desk_start_minutes'];
						$desk_end = $row2['desk_end'];
						$desk_end_minutes = $row2['desk_end_minutes'];
						$desk_start2 = $row2['desk_start2'];
						$desk_start2_minutes = $row2['desk_start2_minutes'];
						$desk_end2 = $row2['desk_end2'];
						$desk_end2_minutes = $row2['desk_end2_minutes'];
						$lunch_start = $row2['lunch_start'];
						$lunch_start_minutes = $row2['lunch_start_minutes'];
						$lunch_end = $row2['lunch_end'];
						$lunch_end_minutes = $row2['lunch_end_minutes'];
						$multi_alert[$emp_id] = array(0=>0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);

						//Adjust 24-hour time.
						if ($shift_start > 12){
							$ss12 = $shift_start - 12;
							}
						elseif($shift_start == 0){
							$ss12 = NULL;
							}
						else{
							$ss12 = $shift_start;
							}
						if ($shift_start_minutes != '00') {
							$ss12 .= ':'.$shift_start_minutes;
							}

						if ($shift_end > 12){
							$se12 = $shift_end - 12;
							}
						elseif($shift_end == 0){
							$se12 = NULL;
							}
						else{
							$se12 = $shift_end;
							}
						if ($shift_end_minutes != '00') {
							$se12 .= ':'.$shift_end_minutes;
							}

						//Decimalize times
						if ($shift_start_minutes != '00') {
							$shift_start += dec_minutes($shift_start_minutes);
							}
						if ($shift_end_minutes != '00') {
							$shift_end += dec_minutes($shift_end_minutes);
							}
						if ($desk_start_minutes != '00') {
							$desk_start += dec_minutes($desk_start_minutes);
							}
						if ($desk_end_minutes != '00') {
							$desk_end += dec_minutes($desk_end_minutes);
							}
						if ($desk_start2_minutes != '00') {
							$desk_start2 += dec_minutes($desk_start2_minutes);
							}
						if ($desk_end2_minutes != '00') {
							$desk_end2 += dec_minutes($desk_end2_minutes);
							}
						if ($lunch_start_minutes != '00') {
							$lunch_start += dec_minutes($lunch_start_minutes);
							}
						if ($lunch_end_minutes != '00') {
							$lunch_end += dec_minutes($lunch_end_minutes);
							}

						$query3 = "SELECT emp_id, timeoff_start_date, time_format(timeoff_start_time,'%k') as timeoff_start,
							time_format(timeoff_start_time,'%i') as timeoff_start_minutes, timeoff_end_date,
							time_format(timeoff_end_time,'%k') as timeoff_end, time_format(timeoff_end_time,'%i') as timeoff_end_minutes,
							timeoff_reason from timeoff as t where emp_id='$emp_id' and
							timeoff_start_date <= '$today' and timeoff_end_date >= '$today'";
						$result3 = mysqli_query($dbc, $query3) or die(mysql_error($dbc));

						$array = array();
						while ($row3 = mysql_fetch_array ($result3, MYSQL_ASSOC)){
							$tostartd = $row3['timeoff_start_date'];
							$tostart = $row3['timeoff_start'];
							$tostart_minutes = $row3['timeoff_start_minutes'];
							$toendd = $row3['timeoff_end_date'];
							$toend = $row3['timeoff_end'];
							$toend_minutes = $row3['timeoff_end_minutes'];
							$toreason = $row3['timeoff_reason'];

							if ($tostartd != $today){
								$tostart = 1;
								}
							if ($toendd != $today){
								$toend = 23;
								}

							if ($tostart_minutes != '00') {
								$tostart += dec_minutes($tostart_minutes);
								}
							if ($toend_minutes != '00') {
								$toend += dec_minutes($toend_minutes);
								}

							$array[] = array('tos'=>$tostart, 'toe'=>$toend, 'tor'=>$toreason);
							}

						$query4 = "SELECT emp_id, coverage_date, time_format(coverage_start_time,'%k') as coverage_start,
							time_format(coverage_start_time,'%i') as coverage_start_minutes, time_format(coverage_end_time,'%k') as coverage_end,
							time_format(coverage_end_time,'%i') as coverage_end_minutes, coverage_offdesk, coverage_reason
							FROM coverage as c
							where emp_id='$emp_id' and coverage_date = '$today'
							and c.coverage_division= '$divrow'";
						$result4 = mysqli_query($dbc, $query4) or die(mysql_error($dbc));
						if (mysql_num_rows($result) != 0) {
							$cov_array = array();
							while ($row4 = mysql_fetch_array ($result4, MYSQL_ASSOC)){
								$cov_start = $row4['coverage_start'];
								$cov_start_minutes = $row4['coverage_start_minutes'];
								if ($cov_start_minutes != '00') {
									$cov_start += dec_minutes($cov_start_minutes);
									}
								$cov_end = $row4['coverage_end'];
								$cov_end_minutes = $row4['coverage_end_minutes'];
								if ($cov_end_minutes != '00') {
									$cov_end += dec_minutes($cov_end_minutes);
									}
								$cov_offdesk = $row4['coverage_offdesk'];
								$cov_reason = $row4['coverage_reason'];
								$cov_array[] = array('cov_start'=>$cov_start, 'cov_end'=>$cov_end,
									'cov_offdesk'=>$cov_offdesk, 'cov_reason'=>$cov_reason);
								}
							}

						echo '<tr class="emps"><td class="first_name">' . $first_name . '</td>';

						//Apply correct cell classes for visual styling.
						foreach ($normalhours as $key=>$hr){
							$css=null;
							if (is_float($hr)){
								$css='decimal';
								}
							$class='off';
							if (($hr >= $shift_start) && ($hr < $shift_end)){
								$class='here';
								}
							if (($hr >= $desk_start) && ($hr < $desk_end)){
								$class='on';
								$multi_alert[$emp_id][$key] = 1;

								if (($hr >= $lunch_start) && ($hr < $lunch_end)){
									$class='off';
									$multi_alert[$emp_id][$key] = 0;
									}
								}
							if (($hr >= $desk_start2) && ($hr < $desk_end2)){
								$class='on';
								$multi_alert[$emp_id][$key] = 1;

								if (($hr >= $lunch_start) && ($hr < $lunch_end)){
									$class='off';
									$multi_alert[$emp_id][$key] = 0;
									}
								}
							if (($hr >= $lunch_start) && ($hr < $lunch_end)){
								$class='off';
								}

							foreach ($array as $timeoff){
								if (($hr >= $timeoff['tos']) && ($hr < $timeoff['toe'])){
									$class='off';

									if ((($hr >= $desk_start) && ($hr < $desk_end)) ||
										(($hr >= $desk_start2) && ($hr < $desk_end2))){
										$multi_alert[$emp_id][$key] = 0;
										}
									}
								}
							foreach ($cov_array as $cover){
								if (($hr >= $cover['cov_start']) && ($hr < $cover['cov_end'])){
									if ($cover['cov_offdesk'] == 'Off'){
										$class='here';
										if ((($hr >= $desk_start) && ($hr < $desk_end)) ||
											(($hr >= $desk_start2) && ($hr < $desk_end2))){
											$multi_alert[$emp_id][$key] = 0;
											}
										}
									elseif($cover['cov_offdesk'] == 'Busy'){
										$class='busy';
										if ((($hr >= $desk_start) && ($hr < $desk_end)) ||
											(($hr >= $desk_start2) && ($hr < $desk_end2))){
											$multi_alert[$emp_id][$key] = 0;
											}
										}
									else {
										$class='on';
										$multi_alert[$emp_id][$key] = 1;
										}
									}
								}

							if ((isset($close_start_hr))&&(($hr >= $close_start_hr) && ($hr < $close_end_hr))){
								$class='closed';
								$multi_alert[$emp_id][$key] = 1;
								}

							echo '<td class="' . $class;
							if (isset($css)){ echo ' ' . $css;}
							echo '"></td>';
							$classrow[] = $class;
							}
						if (isset($ss12)){
							echo '<td class="shift">' . $ss12 . ' - ' . $se12 . '</td>';
							}
						else{
							echo '<td class="shift"></td>';
							}
						echo '<td class="timeoff_reason">';
						if ((isset($array))||(isset($cov_array))){
							echo '<div class="td_outer"><div class="td_inner">';
							if (isset($array)){
								foreach ($array as $timeoff){
									if ($timeoff['tor'] != ''){
										echo '- ' . $timeoff['tor'] . ' ';
										}
									}
								}
							if (isset($cov_array)){
								foreach ($cov_array as $cover){
									if ($cover['cov_reason'] != NULL){
										echo '- ' . $cover['cov_reason'] . ' ';
										}
									}
								}
							echo '</div></div>';
							}

						echo '</td></tr>'."\n";
						}

					//Get sub data.
					$sub_array = array();
					$query5 = "SELECT first_name, last_name, name_dup, e.emp_id, coverage_end_time as cet,
						time_format(coverage_start_time,'%k') as coverage_start,
						time_format(coverage_start_time,'%i') as coverage_start_minutes,
						time_format(coverage_end_time,'%k') as coverage_end,
						time_format(coverage_end_time,'%i') as coverage_end_minutes,
						coverage_offdesk, coverage_reason, division
						FROM employees as e, coverage as c
						WHERE (division not like '%".$divrow."%') and c.coverage_division = '$divrow' and
						coverage_date='$today' and e.emp_id = c.emp_id and e.active = 'Active'
						ORDER BY last_name asc, cet asc";
					$result5 = mysqli_query($dbc, $query5) or die(mysql_error($dbc));
					while ($row5 = mysql_fetch_array ($result5, MYSQL_ASSOC)) {
						$emp_id = $row5['emp_id'];
						$first_name = $row5['first_name'];
						if ($row2['name_dup'] == 'Y'){
							$last_initial = substr($row2['last_name'],0,1);
							$first_name .= ' ' . $last_initial . '.';
							}
						$coverage_start = $row5['coverage_start'];
						$coverage_start_minutes = $row5['coverage_start_minutes'];
						$coverage_end = $row5['coverage_end'];
						$coverage_end_minutes = $row5['coverage_end_minutes'];

						$coverage_offdesk = $row5['coverage_offdesk'];
						$coverage_reason = $row5['coverage_reason'];

						$multi_alert[$emp_id] = array(0=>0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);

						//Adjust 24-hour time.
						if ($coverage_start > 12){
							$cs12 = $coverage_start - 12;
							}
						else{
							$cs12 = $coverage_start;
							}
						if ($coverage_start_minutes != '00') {
							$cs12 .= ':'.$coverage_start_minutes;
							}
						if ($coverage_end > 12){
							$ce12 = $coverage_end - 12;
							}
						else{
							$ce12 = $coverage_end;
							}
						if ($coverage_end_minutes != '00') {
							$ce12 .= ':'.$coverage_end_minutes;
							}

						if ($coverage_start_minutes != '00') {
							$coverage_start += dec_minutes($coverage_start_minutes);
							}
						if ($coverage_end_minutes != '00') {
							$coverage_end += dec_minutes($coverage_end_minutes);
							}

						$sub_array[$first_name][] = array('emp_id'=>$emp_id, 'coverage_start'=>$coverage_start,
							'cs12'=>$cs12, 'coverage_end'=>$coverage_end, 'ce12'=>$ce12,
							'coverage_offdesk'=>$coverage_offdesk, 'coverage_reason'=>$coverage_reason);
						}

					foreach ($sub_array as $first_name=>$subs){
						echo '<tr class="emps subs"><td class="first_name">' . $first_name . '</td>';

						foreach ($normalhours as $key=>$hr){
							$css=null;
							if (is_float($hr)){
								$css='decimal';
								}
							$class='off';
							foreach ($subs as $keys){
								$emp_id = $keys['emp_id'];
								if (($hr >= $keys['coverage_start']) && ($hr < $keys['coverage_end'])){
									if ($keys['coverage_offdesk'] == 'Off'){
										$class='here';
										}
									elseif ($keys['coverage_offdesk'] == 'Busy'){
										$class='busy';
										}
									else {
										$class='on';
										$multi_alert[$emp_id][$key] = 1;
										--$deficiencies[$key];
										}
									}
								}
							if ((isset($close_start_hr))&&(($hr >= $close_start_hr) && ($hr < $close_end_hr))){
								$class='closed';
								$multi_alert[$emp_id][$key] = 1;
								}

							echo '<td class="' . $class;
							if (isset($css)){ echo ' ' . $css;}
							echo '"></td>';
							}

						echo '<td class="shift">';
						echo '<div class="td_outer"><div class="td_inner">';
						foreach ($subs as $keys=>$array){
							if ($keys == 0){
								echo $array['cs12'] . ' - ' . $array['ce12'];
								}
							else {
								echo ', ' .  $array['cs12'] . ' - ' . $array['ce12'];
								}
							}
						echo '</div></div>';
						echo '</td>';
						echo '<td class="timeoff_reason">';
						echo '<div class="td_outer"><div class="td_inner">';
						foreach ($subs as $keys){
							if ($keys['coverage_reason'] != NULL){
								echo '- ' . $keys['coverage_reason'] . ' ';
								}
							}
						echo '</div></div>';
						echo '</td></tr>'."\n";
						}

					//Get deficiencies data.
					$query6 = "SELECT time_format(def_start,'%k') as def_start, time_format(def_start,'%i') as def_start_minutes,
						time_format(def_end,'%k') as def_end, time_format(def_end,'%i') as def_end_minutes
						FROM deficiencies WHERE def_schedule=$specific_schedule
						and def_week='$week_type' and def_day='$day' and def_division='$divrow'";
					$result6 = mysqli_query($dbc, $query6) or die(mysql_error($dbc));
					if (mysql_num_rows($result6) != 0) {
						while ($row6 = mysql_fetch_array ($result6, MYSQL_ASSOC)){
							$def_start = $row6['def_start'];
							$def_start_minutes = $row6['def_start_minutes'];
							$def_end = $row6['def_end'];
							$def_end_minutes = $row6['def_end_minutes'];

							//Adjust 24-hour time.
							if ($def_start > 12){
								$defs12 = $def_start - 12;
								}
							else{
								$defs12 = $def_start;
								}
							if ($def_start_minutes != '00') {
								$defs12 .= ':'.$def_start_minutes;
								}
							if ($def_end > 12){
								$defe12 = $def_end - 12;
								}
							else{
								$defe12 = $def_end;
								}
							if ($def_end_minutes != '00') {
								$defs12 .= ':'.$def_end_minutes;
								}

							if ($def_start_minutes != '00') {
								$def_start += dec_minutes($def_start_minutes);
								}
							if ($def_end_minutes != '00') {
								$def_end += dec_minutes($def_end_minutes);
								}

							$def_array[] = array('def_start'=>$def_start,
								'defs12'=>$defs12, 'def_end'=>$def_end, 'defe12'=>$defe12);
							}
						foreach ($def_array as $key=>$def){
							foreach ($normalhours as $key=>$hr){
								if (($hr >= $def['def_start']) && ($hr < $def['def_end'])){
									--$alertarray[$key];
									--$alert_custserv[$key];
									--$alert_children[$key];
									--$alert_adult[$key];
									++$deficiencies[$key];
									}
								}
							}
						if (in_array("1",$deficiencies)){
							echo '<tr class="emps"><td class="first_name"><b>SUB</b></td>';
							foreach ($deficiencies as $key=>$hr){
								$css=null;
								if ($key&1){
									$css='decimal';
									}
								$class='off';
								if ($hr > 0){
									$class='def';
									}
								echo '<td class="' . $class;
								if (isset($css)){ echo ' ' . $css;}
								echo '"></td>';
								}
							echo '<td class="shift">';
							echo '<div class="td_outer"><div class="td_inner">';
							foreach ($def_array as $key=>$def){
								if ($key == 0){
									echo $def['defs12'] . ' - ' . $def['defe12'];
									}
								else {
									echo ', ' .  $def['defs12'] . ' - ' . $def['defe12'];
									}
								}
							echo '</div></div>';
							echo '</td>';
							echo '<td class="timeoff_reason"></td></tr>'."\n";
							}
						}

					//Print alert data.
					foreach ($multi_alert as $emp_id=>$array){
						foreach ($array as $key=>$hr){
							if ($hr == 1){
								--$alertarray[$key];
								--$alert_custserv[$key];
								--$alert_children[$key];
								--$alert_adult[$key];
								}
							}
						}
					if (($divrow != 'Admin') && ($divrow != 'Tech Services')&&($divrow != 'Pages')){
						if ($divrow == 'Customer Service'){
							if (in_array("1",$alert_custserv)){
								echo '<tr class="emps"><td class="first_name"><b>NEEDED</b></td>';

								foreach ($alert_custserv as $key=>$hr){
									$css=null;
									if ($key&1){
										$css='decimal';
										}
									$class='off';
									if ($hr > 0){
										$class='alert';
										}
									echo '<td class="' . $class;
									if (isset($css)){ echo ' ' . $css;}
									echo '"></td>';
									}

								echo '<td class="shift"></td>';
								echo '<td class="timeoff_reason"></td></tr>'."\n";
								}
							}
						elseif ($divrow == 'Children'){
							if (in_array("1",$alert_children)){
								echo '<tr class="emps"><td class="first_name"><b>NEEDED</b></td>';

								foreach ($alert_children as $key=>$hr){
									$css=null;
									if ($key&1){
										$css='decimal';
										}
									$class='off';
									if ($hr > 0){
										$class='alert';
										}
									echo '<td class="' . $class;
									if (isset($css)){ echo ' ' . $css;}
									echo '"></td>';
									}

								echo '<td class="shift"></td>';
								echo '<td class="timeoff_reason"></td></tr>'."\n";
								}
							}
						elseif ($divrow == 'Adult'){
							if (in_array("1",$alert_adult)){
								echo '<tr class="emps"><td class="first_name"><b>NEEDED</b></td>';

								foreach ($alert_adult as $key=>$hr){
									$css=null;
									if ($key&1){
										$css='decimal';
										}
									$class='off';
									if ($hr > 0){
										$class='alert';
										}
									echo '<td class="' . $class;
									if (isset($css)){ echo ' ' . $css;}
									echo '"></td>';
									}

								echo '<td class="shift"></td>';
								echo '<td class="timeoff_reason"></td></tr>'."\n";
								}
							}
						else {
							if (in_array("1",$alertarray)){
								echo '<tr class="emps"><td class="first_name"><b>NEEDED</b></td>';

								foreach ($alertarray as $key=>$hr){
									$css=null;
									if ($key&1){
										$css='decimal';
										}
									$class='off';
									if ($hr > 0){
										$class='alert';
										}
									echo '<td class="' . $class ;
									if (isset($css)){ echo ' ' . $css;}
									echo '"></td>';
									}

								echo '<td class="shift"></td>';
								echo '<td class="timeoff_reason"></td></tr>'."\n";
								}
							}
						}
					echo '</table>'."\n".'</div></div>';
					}
				else {
					echo '<div class="error"><h4>This schedule does not yet exist!</h4>
						For more information, please see your division head.</div></div>';
					}
				}
			else {
				echo '<div class="error"><h4>This schedule does not yet exist!</h4>
					For more information, please see your division head.</div></div>';
				}

			}
		}
	}

function subs_specific($division, $now) {
	//Get week type.
	$today = date('Y-m-d' , $now);
	$query = "SELECT date, week_type FROM dates where date = '$today'";
	$result = @mysqli_query($dbc, $query);

	while ($row = mysql_fetch_assoc($result)) {
		$week_type = $row['week_type'];
		}

	$day = date('D', $now);
	$year = date('Y', $now);

	//Get season.
	$query2 = "SELECT memorial_day, labor_day FROM holidays where year='$year'";
	$result2 = @mysqli_query($dbc, $query2);

	while ($row2 = mysql_fetch_assoc($result2)) {
		$memorial_day = $row2['memorial_day'];
		$labor_day = $row2['labor_day'];
		}

	$mem_sat = strtotime ('-2 days', strtotime ($memorial_day));
	$lab_sat = strtotime ('+5 days', strtotime ($labor_day));

	if ((strtotime($today) >= $mem_sat) && (strtotime($today) < $lab_sat)){
		$season = 'summer';
		}
	elseif (strtotime($today) < $mem_sat){
		$season = 'spring';
		}
	else {
		$season = 'fall';
		}

	//Set workday length.
	$normalhours = array(0=>7,7.5,8,8.5,9,9.5,10,10.5,11,11.5,12,12.5,13,13.5,14,14.5,15,15.5,16,16.5,17,17.5,18,18.5,19,19.5);
	$hoursscale = array(0=>7,null,8,null,9,null,10,null,11,null,12,null,13,null,14,null,15,null,16,null,17,null,18,null,19,null);

	//Set dynamic table name.
	$tablename = strtolower($week_type) . '_' . strtolower($day) . '_' . $year . '_' . strtolower($season);

	$dom = date('j', $now);
	$day_long = date('l', $now);
	$day_short = date('D', $now);
	$month_long = date('F', $now);

	$ucdivision = $division;

	//Get PIC
	$pic_name = '';
	if (in_array($day_short,array('Mon','Tue','Wed','Thu', 'Fri'))){
		$query_pic = "SELECT first_name FROM employees e, pic p, pic_schedules s
			WHERE pic_start_date <= '$today' and pic_end_date >= '$today' and p.pic_schedule_id=s.pic_schedule_id
			and week_type='$week_type' and pic_day='$day'
			and p.emp_id=e.emp_id and e.emp_id not in
			(SELECT emp_id from timeoff WHERE
			timeoff_start_date <= '$today' and timeoff_end_date >= '$today' and
			((timeoff_start_time <= '17:00:00' and timeoff_end_time >= '20:00:00') or
			(timeoff_start_time <= '17:00:00' and (timeoff_end_time > '17:00:00' and timeoff_end_time <= '20:00:00')) or
			((timeoff_start_time >= '17:00:00' and timeoff_start_time < '20:00:00') and timeoff_end_time >= '20:00:00')
			))";
		}
	else{
		$query_pic = "SELECT first_name FROM employees e, pic p, pic_schedules s
			WHERE pic_start_date <= '$today' and pic_end_date >= '$today' and p.pic_schedule_id=s.pic_schedule_id
			and week_type='$week_type' and pic_day='$day'
			and p.emp_id=e.emp_id and e.emp_id not in
			(SELECT emp_id from timeoff WHERE timeoff_start_date <= '$today' and timeoff_end_date >= '$today')";
		}
	$result_pic = mysqli_query($dbc, $query_pic);
	if($result_pic){
		while ($row = mysql_fetch_array($result_pic, MYSQL_ASSOC)){
			$pic_name = $row['first_name'];
			}
		}
	else{
		$pic_name = '';
		}

	$query_pic_cover = "SELECT first_name from employees e, pic_coverage c WHERE pic_coverage_date='$today' and
		e.emp_id=c.emp_id";
	$result_pic_cover = mysqli_query($dbc, $query_pic_cover);
	if($result_pic_cover){
		while ($row = mysql_fetch_array($result_pic_cover, MYSQL_ASSOC)){
			$pic_name = $row['first_name'];
			}
		}

	echo '<div class="division_specific">'."\n".'<div class="divspec_head">'."\n".
		'<input class="prev" type="button" onclick="loadprevSubs()" value="Previous" />'."\n".
		'<input class="next" type="button" value="Next" onclick="loadnextSubs()" />'."\n";
	echo "<div class=\"divspec screen\">$day_long, $dom $month_long $year</div>\n";
	echo "<div class=\"divspec mobile\">$day_short, $dom $month_long $year</div>\n";
	echo '<div class="week_type">'. ucwords($week_type) . ' Schedule</div>'."\n";

	//See if library closed.
	$query_closure = "SELECT * from closures WHERE closure_date='$today' limit 1";
	$result_closure = mysqli_query($dbc, $query_closure);
	if ($result_closure){
		$num_rows = mysql_num_rows($result_closure);
		if ($num_rows != 0){
			while ($row = mysql_fetch_array($result_closure, MYSQL_ASSOC)){
				$cd_date = $row['closure_date'];
				$cd_start = $row['closure_start_time'];
				$cd_end = $row['closure_end_time'];
				$cd_reason = $row['closure_reason'];
				}
			if (($cd_start == '00:01:00')&&($cd_end == '23:59:00')){
				$allday_closure_message = "<div class=\"error\"><div class=\"message\"><h4>Library Closed</h4>
					<p>The library is closed all day for $cd_reason</p></div></div></div>";
				}
			else{
				if (($cd_start != '00:01:00')&&($cd_end != '23:59:00')){
					$close_start = explode(':',$cd_start);
					$close_start_hr = $close_start[0];
					$close_start_mn = $close_start[1];
					$close_end = explode(':',$cd_end);
					$close_end_hr = $close_end[0];
					$close_end_mn = $close_end[1];
					if ($close_start_hr > 12){
						$ss12 = $close_start_hr - 12;
						}
					elseif($close_start_hr == 0){
						$ss12 = NULL;
						}
					else{
						$ss12 = $close_start_hr;
						}
					if ($close_start_mn != '00') {
						$ss12 .= ':'.$close_start_mn;
						}

					if ($close_end_hr > 12){
						$se12 = $close_end_hr - 12;
						}
					elseif($close_end_hr == 0){
						$se12 = NULL;
						}
					else{
						$se12 = $close_end_hr;
						}
					if ($close_end_mn != '00') {
						$se12 .= ':'.$close_end_mn;
						}
					$closure_times = $ss12.' - '.$se12;
					//Decimalize times
					if ($close_start_mn != '00') {
						$close_start_hr += dec_minutes($close_start_mn);
						}
					if ($close_end_mn != '00') {
						$close_end_hr += dec_minutes($close_end_mn);
						}
					}
				elseif ($cd_start != '00:01:00'){
					$close_start = explode(':',$cd_start);
					$close_start_hr = $close_start[0];
					$close_start_mn = $close_start[1];
					if ($close_start_hr > 12){
						$ss12 = $close_start_hr - 12;
						}
					elseif($close_start_hr == 0){
						$ss12 = NULL;
						}
					else{
						$ss12 = $close_start_hr;
						}
					if ($close_start_mn != '00') {
						$ss12 .= ':'.$close_start_mn;
						}
					$closure_times = 'After '.$ss12;
					//Decimalize times
					if ($close_start_mn != '00') {
						$close_start_hr += dec_minutes($close_start_mn);
						}
					$close_end_hr = 23;
					}
				elseif ($cd_end != '23:59:00'){
					$close_end = explode(':',$cd_end);
					$close_end_hr = $close_end[0];
					$close_end_mn = $close_end[1];
					if ($close_end_hr > 12){
						$se12 = $close_end_hr - 12;
						}
					elseif($close_end_hr == 0){
						$se12 = NULL;
						}
					else{
						$se12 = $close_end_hr;
						}
					if ($close_end_mn != '00') {
						$se12 .= ':'.$close_end_mn;
						}
					$closure_times = 'Until '.$se12;
					if ($close_end_mn != '00') {
						$close_end_hr += dec_minutes($close_end_mn);
						}
					$close_start_hr = 0;
					}
				$closure_message = "<div class=\"closure_message\"><h4>Library Closed</h4>
					The library is closed $closure_times for $cd_reason</div>";
				}
			}
		if(isset($allday_closure_message)){
			echo $allday_closure_message;
			}
		elseif ($season == 'summer' && $day_short == 'Sun'){
			echo "<div class=\"error\"><div class=\"message\"><h4>Library Closed</h4>
				<p>The library is closed Sundays during the summer.</p></div></div></div>";
			}
		else{
			if(isset($closure_message)){
				echo $closure_message;
				}
			echo '<div class="pic"><b>Person In Charge:</b> ';
			if (in_array($day_short,array('Mon','Tue','Wed','Thu'))){
				echo '(5-8pm) ';
				}
			elseif($day_short == 'Fri'){
				echo '(5-6pm) ';
				}
			if ($pic_name != ''){echo $pic_name.' &ndash; ';
				}
			echo '<b>x2778</b></div>'."\n";
			echo "</div>\n";
			//Get Subs names.
			$query = "SELECT first_name, last_name, name_dup, emp_id FROM employees
				WHERE division = 'Subs' and active = 'Active'
				and (employee_lastday >= '$today' or employee_lastday is null) ORDER BY first_name asc";
			$result = mysqli_query($dbc, $query);
			if ($result){
				$num_rows = mysql_num_rows($result);
				if ($num_rows != 0){
					echo '<div class="divboxes">'."\n".'<table class="dptsched" style="border-collapse:collapse;" cellspacing="0">'."\n";
					echo '<tr class="times"><td class="first_name"></td>';

					//Create time scale.
					foreach ($hoursscale as $hr){
						if ($hr>12){
							$hr = $hr-12;
							}
						echo '<td class="off"><div class="hr">' . $hr . '</div></td>';
						}
					echo '<td class="shift"><div class="hr">8</div></td>';
					echo '<td class="timeoff_reason"></td></tr>'."\n";
					while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
						$emp_id = $row['emp_id'];
						$first_name = $row['first_name'];
						if ($row['name_dup'] == 'Y'){
							$last_initial = substr($row['last_name'],0,1);
							$first_name .= ' ' . $last_initial . '.';
							}
						$cov_array = array();

						echo '<tr class="emps"><td class="first_name">' . $first_name . '</td>';

						$query2 = "SELECT emp_id, coverage_date, coverage_end_time as cet,
							time_format(coverage_start_time,'%k') as coverage_start,
							time_format(coverage_start_time,'%i') as coverage_start_minutes,
							time_format(coverage_end_time,'%k') as coverage_end,
							time_format(coverage_end_time,'%i') as coverage_end_minutes,
							coverage_division, coverage_offdesk, coverage_reason
							FROM coverage as c
							WHERE emp_id='$emp_id' and coverage_date = '$today'
							ORDER BY cet asc";
						$result2 = mysqli_query($dbc, $query2);

						if (mysql_num_rows($result2) != 0) {
							while ($row2 = mysql_fetch_array ($result2, MYSQL_ASSOC)){
								$coverage_start = $row2['coverage_start'];
								$coverage_start_minutes = $row2['coverage_start_minutes'];
								$coverage_end = $row2['coverage_end'];
								$coverage_end_minutes = $row2['coverage_end_minutes'];
								$coverage_division = $row2['coverage_division'];
								$coverage_offdesk = $row2['coverage_offdesk'];
								$coverage_reason = $row2['coverage_reason'];

								//Adjust 24-hour time.
								if ($coverage_start > 12){
									$cs12 = $coverage_start - 12;
									}
								else{
									$cs12 = $coverage_start;
									}
								if ($coverage_start_minutes != '00') {
									$cs12 .= ':'.$coverage_start_minutes;
									}
								if ($coverage_end > 12){
									$ce12 = $coverage_end - 12;
									}
								else{
									$ce12 = $coverage_end;
									}
								if ($coverage_end_minutes != '00') {
									$ce12 .= ':'.$coverage_end_minutes;
									}

								//Decimalize Times
								if ($coverage_start_minutes != '00') {
									$coverage_start += dec_minutes($coverage_start_minutes);
									}
								if ($coverage_end_minutes != '00') {
									$coverage_end += dec_minutes($coverage_end_minutes);
									}

								$cov_array[] = array('coverage_division'=>$coverage_division,
									'coverage_start'=>$coverage_start, 'coverage_end'=>$coverage_end,
									'cs12'=>$cs12, 'ce12'=>$ce12,
									'coverage_offdesk'=>$coverage_offdesk, 'coverage_reason'=>$coverage_reason);
								}
							}

						//Apply correct cell classes for visual styling.
						foreach ($normalhours as $key=>$hr){
							$css=null;
							if (is_float($hr)){
								$css='decimal';
								}
							$class='off';

							foreach ($cov_array as $cover){
								$coverage_division = $cover['coverage_division'];
								if (($hr >= $cover['coverage_start']) && ($hr < $cover['coverage_end'])){
									if ($coverage_division == 'Adult'){
										$class='adult';
										}
									elseif ($coverage_division == 'Children'){
										$class='children';
										}
									elseif ($coverage_division == 'Customer Service'){
										$class='custserv';
										}
									elseif ($coverage_division == 'LTI'){
										$class='lti';
										}
									elseif ($coverage_division == 'Teen'){
										$class='teen';
										}
									else {
										$class='on';
										}
									}
								}
							if ((isset($close_start_hr))&&(($hr >= $close_start_hr) && ($hr < $close_end_hr))){
								$class='closed';
								}

							echo '<td class="' . $class;
							if (isset($css)){ echo ' ' . $css;}
							echo '"></td>';
							}

							echo '<td class="shift">';
							echo '<div class="td_outer"><div class="td_inner">';
							foreach ($cov_array as $keys=>$cover){
								if ($keys == 0){
									echo $cover['cs12'] . ' - ' . $cover['ce12'];
									}
								else {
									echo ', ' .  $cover['cs12'] . ' - ' . $cover['ce12'];
									}
								}
							echo '</div></div>';
							echo '</td>';
							echo '<td class="timeoff_reason">';
							echo '<div class="td_outer"><div class="td_inner">';
							foreach ($cov_array as $cover){
								if ($cover['coverage_reason'] != NULL){
									echo '- ' . $cover['coverage_reason'] . ' ';
									}
								}
							echo '</div></div>';
							echo '</td></tr>'."\n";
						}
					echo "</table>\n</div>\n</div>\n";
					}
				else {
					echo '<div class="error"><h4>No subs are entered!</h4>
						For more information, please see the library director.</div></div>';
					}
				}
			else {
				echo '<div class="error"><h4>No subs are entered!</h4>
					For more information, please see the library director.</div></div>';
				}
			}
		}
	}

function division_timeoff($division, $today){
	$query = "SELECT first_name, last_name, name_dup, e.emp_id, time_format(timeoff_start_time,'%k') as timeoff_start,
		time_format(timeoff_start_time,'%i') as timeoff_start_minutes, time_format(timeoff_end_time,'%k') as timeoff_end,
		time_format(timeoff_end_time,'%i') as timeoff_end_minutes, timeoff_start_date, timeoff_end_date
		FROM employees as e, timeoff as t, divisions
		WHERE div_link = '$division' and division like concat('%',div_name,'%') and e.emp_id = t.emp_id and e.active = 'Active'
		and (e.employee_lastday >= '$today' or e.employee_lastday is null)
		and (timeoff_start_date >= '$today' OR (timeoff_start_date < '$today' AND timeoff_end_date >= '$today'))
		ORDER by timeoff_start_date asc, first_name asc";
	$result = mysqli_query($dbc, $query);
	if ($result){
		$num_rows = mysql_num_rows($result);
		if ($num_rows != 0) {
			echo '<div class="divspec">Upcoming Timeoff</div>'."\n";
			echo '<div class="divboxes">'."\n".'<table class="timeoff">'."\n";
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
				$first_name = $row['first_name'];
				if ($row['name_dup'] == 'Y'){
					$last_initial = substr($row['last_name'],0,1);
					$first_name .= ' ' . $last_initial . '.';
					}
				$emp_id = $row['emp_id'];
				$timeoff_start_hours = $row['timeoff_start'];
				$timeoff_start_minutes = $row['timeoff_start_minutes'];
				$timeoff_end_hours = $row['timeoff_end'];
				$timeoff_end_minutes = $row['timeoff_end_minutes'];
				$timeoff_start_date = $row['timeoff_start_date'];
				$timeoff_end_date = $row['timeoff_end_date'];
				$ts12 = NULL;
				$te12 = NULL;

				//Adjust 24-hour time.
				if ($timeoff_end_hours > 12){
					$te12 = $timeoff_end_hours - 12;
					$te12 .= 'pm';
					}
				elseif($timeoff_end_hours == 0){
					$te12 = NULL;
					}
				else{
					$te12 = $timeoff_end_hours;
					}
				if ($timeoff_end_minutes != '00') {
					$te12 .= ':'.$timeoff_end_minutes;
					}

				//Date specifics
				$tosmonth = date('M', strtotime($timeoff_start_date));
				$tosday = date('j', strtotime($timeoff_start_date));
				if ((date('Y', strtotime($timeoff_start_date))) > date('Y')){
					$tosyear = date('Y', strtotime($timeoff_start_date));
					$tosyear = ', '.$tosyear;
					}
				else {
					$tosyear = NULL;
					}
				$toemonth = date('M', strtotime($timeoff_end_date));
				$toeday = date('j', strtotime($timeoff_end_date));
				if ((date('Y', strtotime($timeoff_start_date))) > date('Y')){
					$toeyear = date('Y', strtotime($timeoff_start_date));
					$toeyear = ', '.$toeyear;
					}
				else {
					$toeyear = NULL;
					}

				echo "<tr><td class=\"first_name\">$first_name</td>";
				if ($timeoff_start_date == $timeoff_end_date){
					echo "<td class=\"datetime\"><span class=\"todate\">$tosmonth $tosday$tosyear</span>";
					if (($timeoff_start_hours != '00') && ($timeoff_end_hours != 23)){
						if ($timeoff_start_hours > 12){
							$ts12 = $timeoff_start_hours - 12;
							if ($timeoff_start_minutes != '00') {
								$ts12 .= ':'.$timeoff_start_minutes;
								}
							$te12 = $timeoff_end_hours - 12;
							if ($timeoff_end_minutes != '00') {
								$te12 .= ':'.$timeoff_end_minutes;
								}
							$te12 .= 'pm';
							}
						elseif ($timeoff_start_hours == 12){
							$ts12 = $timeoff_start_hours;
							if ($timeoff_start_minutes != '00') {
								$ts12 .= ':'.$timeoff_start_minutes;
								}
							$te12 = $timeoff_end_hours - 12;
							if ($timeoff_end_minutes != '00') {
								$te12 .= ':'.$timeoff_end_minutes;
								}
							$te12 .= 'pm';
							}
						else {
							$ts12 = $timeoff_start_hours;
							if ($timeoff_start_minutes != '00') {
								$ts12 .= ':'.$timeoff_start_minutes;
								}
							$ts12 .= 'am';
							if ($timeoff_end_hours > 12){
								$te12 = $timeoff_end_hours - 12;
								if ($timeoff_end_minutes != '00') {
									$te12 .= ':'.$timeoff_end_minutes;
									}
								$te12 .= 'pm';
								}
							else {
								$te12 = $timeoff_end_hours;
								if ($timeoff_end_minutes != '00') {
									$te12 .= ':'.$timeoff_end_minutes;
									}
								$te12 .= 'am';
								}
							}
						echo ", $ts12 - $te12";
						}
					echo "</td>";
					}
				else {
					if ($timeoff_start_hours > 12){
						$ts12 = $timeoff_start_hours - 12;
						if ($timeoff_start_minutes != '00') {
							$ts12 .= ':'.$timeoff_start_minutes;
							}
						$ts12 .= 'pm';
						}
					elseif($timeoff_start_hours == 12){
						$ts12 = $timeoff_start_hours;
						if ($timeoff_start_minutes != '00') {
							$ts12 .= ':'.$timeoff_start_minutes;
							}
						$ts12 .= 'pm';
						}
					elseif($timeoff_start_hours == 0){
						$ts12 = NULL;
						}
					else{
						$ts12 = $timeoff_start_hours;
						if ($timeoff_start_minutes != '00') {
							$ts12 .= ':'.$timeoff_start_minutes;
							}
						$ts12 .= 'am';
						}
					if ($timeoff_end_hours > 12){
						$te12 = $timeoff_end_hours - 12;
						if ($timeoff_end_minutes != '00') {
							$te12 .= ':'.$timeoff_end_minutes;
							}
						$te12 .= 'pm';
						}
					elseif($timeoff_end_hours == 12){
						$te12 = $timeoff_end_hours;
						if ($timeoff_end_minutes != '00') {
							$te12 .= ':'.$timeoff_end_minutes;
							}
						$te12 .= 'pm';
						}
					elseif($timeoff_end_hours == 0){
						$te12 = NULL;
						}
					else{
						$te12 = $timeoff_end_hours;
						if ($timeoff_end_minutes != '00') {
							$te12 .= ':'.$timeoff_end_minutes;
							}
						$te12 .= 'am';
						}
					echo "<td class=\"datetime\"><span class=\"todate\">$tosmonth $tosday$tosyear</span>";
					if ($timeoff_start_hours != '00'){
						echo ", $ts12";
						}
					echo " &ndash; <span class=\"todate\">$toemonth $toeday$toeyear</span>";
					if ($timeoff_end_hours != '23'){
						echo ", $te12";
						}
					}
				echo "</tr>\n";
				}
			echo "</table>\n</div>";
			}
		}
	}

function subs_weekly($division, $now) {
	//Get week dates
	$dow = date('D', $now);
	if ($dow == 'Sat'){
		$startdate = $now;
		$enddate = strtotime('+6 days' , $now);
		}
	elseif ($dow == 'Sun'){
		$startdate = strtotime('-1 days', $now);
		$enddate = strtotime('+5 days', $now);
		}
	elseif ($dow == 'Mon'){
		$startdate = strtotime('-2 days', $now);
		$enddate = strtotime('+4 days', $now);
		}
	elseif ($dow == 'Tue'){
		$startdate = strtotime('-3 days', $now);
		$enddate = strtotime('+3 days', $now);
		}
	elseif ($dow == 'Wed'){
		$startdate = strtotime('-4 days', $now);
		$enddate = strtotime('+2 days', $now);
		}
	elseif ($dow == 'Thu'){
		$startdate = strtotime('-5 days', $now);
		$enddate = strtotime('+1 days', $now);
		}
	elseif ($dow == 'Fri'){
		$startdate = strtotime('-6 days', $now);
		$enddate = $now;
		}
	$dates = array();
	for($i=0;$i<7;$i++){
		$dates[$i] = date('Y-m-d', strtotime("+$i days", $startdate));
		}
	$shortdates = array();
	for($i=0;$i<7;$i++){
		$shortdates[$i] = date('D, j M', strtotime("+$i days", $startdate));
		}

	$startday = date('j', $startdate);
	$startmon_long = date('F', $startdate);
	$startmon_short = date('M', $startdate);
	$startyear = date('Y', $startdate);
	$endday = date('j', $enddate);
	$endmon_long = date('F', $enddate);
	$endmon_short = date('M', $enddate);
	$endyear = date('Y', $enddate);

	//Get week type.
	$today = date('Y-m-d' , $now);
	$query = "SELECT date, week_type FROM dates where date = '$today'";
	$result = @mysqli_query($dbc, $query);

	while ($row = mysql_fetch_assoc($result)) {
		$week_type = $row['week_type'];
		}

	$day = date('D', $now);
	$year = date('Y', $now);

	//Get season.
	$query2 = "SELECT memorial_day, labor_day FROM holidays where year='$year'";
	$result2 = @mysqli_query($dbc, $query2);

	while ($row2 = mysql_fetch_assoc($result2)) {
		$memorial_day = $row2['memorial_day'];
		$labor_day = $row2['labor_day'];
		}

	$mem_sat = strtotime ('-2 days', strtotime ($memorial_day));
	$lab_sat = strtotime ('+5 days', strtotime ($labor_day));

	if ((strtotime($today) >= $mem_sat) && (strtotime($today) < $lab_sat)){
		$season = 'summer';
		}
	elseif (strtotime($today) < $mem_sat){
		$season = 'spring';
		}
	else {
		$season = 'fall';
		}

	//Get Subs names.
	$query = "SELECT first_name, last_name, name_dup, emp_id FROM employees, divisions
		WHERE div_link = '$division' and division=div_name and active = 'Active'
		and (employee_lastday >= '$today' or employee_lastday is null) ORDER BY first_name asc";
	$result = mysqli_query($dbc, $query);
	if ($result){
		$num_rows = mysql_num_rows($result);
		if ($num_rows != 0){
			echo '<div class="division_specific">'."\n".'<div class="divspec_head">'."\n".
				'<input class="prev" type="button" onclick="loadprevweekSubs()" value="Previous" />'."\n".
				'<input class="next" type="button" value="Next" onclick="loadnextweekSubs()" />'."\n";
			echo "<div class=\"divspec screen\">$startday $startmon_long";
			if ($startyear != $endyear){echo $startyear;}
			echo " &ndash; $endday $endmon_long $endyear</div>\n";
			echo "<div class=\"divspec mobile\">$startday $startmon_short";
			if ($startyear != $endyear){echo $startyear;}
			echo " &ndash; $endday $endmon_short $endyear</div>\n";
			echo '<div class="week_type">'. ucwords($week_type) . ' Schedule</div>'."\n";
			echo "</div>\n";

			echo '<div class="divboxes">'."\n".'<table class="divisions top subsweekly" cellspacing="0">'."\n";
			echo '<tr class="divisions days screen"><td></td>';
			foreach ($shortdates as $k=>$v){
				echo '<td class="day">'.$v.'</td>';
				}
			echo '<td class="hrs">Hrs</td></tr>'."\n";
			echo '<tr style="display:none;"></tr>'."\n".'<tr class="divisions days mobile"><td></td>
				<td class="day"><div class="smday">Sat</div></td><td class="day"><div class="smday">Sun</div></td>
				<td class="day"><div class="smday">Mon</div></td><td class="day"><div class="smday">Tue</div></td>
				<td class="day"><div class="smday">Wed</div></td><td class="day"><div class="smday">Thu</div></td>
				<td class="day"><div class="smday">Fri</div></td><td class="hrs"><div class="smday">Hrs</div></td></tr>'."\n";

			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
				$first_name = $row['first_name'];
				if ($row['name_dup'] == 'Y'){
					$last_initial = substr($row['last_name'],0,1);
					$first_name .= ' ' . $last_initial . '.';
					}
				$emp_id = $row['emp_id'];
				$hr_total = 0;

				echo '<tr class="divisions"><td class="first_name">' . $first_name . '</td>';

				foreach ($dates as $k=>$v) {
					$cov_array = array();
					$query2 = "SELECT emp_id, coverage_date, coverage_end_time as cet,
						time_format(coverage_start_time,'%k') as coverage_start,
						time_format(coverage_start_time,'%i') as coverage_start_minutes,
						time_format(coverage_end_time,'%k') as coverage_end,
						time_format(coverage_end_time,'%i') as coverage_end_minutes,
						coverage_division
						FROM coverage as c
						WHERE emp_id='$emp_id' and coverage_date = '$v'
						ORDER BY cet asc";
					$result2 = mysqli_query($dbc, $query2);

					if (mysql_num_rows($result2) != 0) {
						while ($row2 = mysql_fetch_array ($result2, MYSQL_ASSOC)){
							$coverage_start = $row2['coverage_start'];
							$coverage_start_minutes = $row2['coverage_start_minutes'];
							$coverage_end = $row2['coverage_end'];
							$coverage_end_minutes = $row2['coverage_end_minutes'];
							$coverage_division = $row2['coverage_division'];
							if ($coverage_division == 'Customer Service'){
								$coverage_division = 'CS';
								}
							elseif ($coverage_division == 'Tech Services'){
								$coverage_division = 'Tech';
								}

							//Adjust 24-hour time.
							if ($coverage_start > 12){
								$cs12 = $coverage_start - 12;
								}
							else{
								$cs12 = $coverage_start;
								}
							if ($coverage_start_minutes != '00') {
								$cs12 .= ':'.$coverage_start_minutes;
								}
							if ($coverage_end > 12){
								$ce12 = $coverage_end - 12;
								}
							else{
								$ce12 = $coverage_end;
								}
							if ($coverage_end_minutes != '00') {
								$ce12 .= ':'.$coverage_end_minutes;
								}

							//Decimalize Times
							if ($coverage_start_minutes != '00') {
								$coverage_start += dec_minutes($coverage_start_minutes);
								}
							if ($coverage_end_minutes != '00') {
								$coverage_end += dec_minutes($coverage_end_minutes);
								}

							$cov_array[] = array('coverage_division'=>$coverage_division,
								'coverage_start'=>$coverage_start, 'coverage_end'=>$coverage_end,
								'cs12'=>$cs12, 'ce12'=>$ce12);
							}
						echo '<td class="shift">';
						foreach ($cov_array as $key=>$covarray){
							echo $covarray['cs12'].'-'.$covarray['ce12'].', '.$covarray['coverage_division'].'<br/>';
							$coverage_total = $covarray['coverage_end'] - $covarray['coverage_start'];
							$hr_total += $coverage_total;
							}
						echo '</td>';
						}
					else {
						echo '<td class="shift"></td>';
						}
					}
				echo '<td class="hr_total">'.$hr_total.'</td>';
				echo '</tr>'."\n";
				}
			echo '</table>'."\n".'</div>'."\n".'</div>'."\n";
			}
		}
	}

function division_weekly($division, $now) {
	//Get week dates
	$dow = date('D', $now);
	if ($dow == 'Sat'){
		$startdate = $now;
		$enddate = strtotime('+6 days' , $now);
		}
	elseif ($dow == 'Sun'){
		$startdate = strtotime('-1 days', $now);
		$enddate = strtotime('+5 days', $now);
		}
	elseif ($dow == 'Mon'){
		$startdate = strtotime('-2 days', $now);
		$enddate = strtotime('+4 days', $now);
		}
	elseif ($dow == 'Tue'){
		$startdate = strtotime('-3 days', $now);
		$enddate = strtotime('+3 days', $now);
		}
	elseif ($dow == 'Wed'){
		$startdate = strtotime('-4 days', $now);
		$enddate = strtotime('+2 days', $now);
		}
	elseif ($dow == 'Thu'){
		$startdate = strtotime('-5 days', $now);
		$enddate = strtotime('+1 days', $now);
		}
	elseif ($dow == 'Fri'){
		$startdate = strtotime('-6 days', $now);
		$enddate = $now;
		}
	$dates = array();
	for($i=0;$i<7;$i++){
		$dates[$i] = date('Y-m-d', strtotime("+$i days", $startdate));
		}
	$shortdates = array();
	for($i=0;$i<7;$i++){
		$shortdates[$i] = date('D, j M', strtotime("+$i days", $startdate));
		}

	$startday = date('j', $startdate);
	$startmon_long = date('F', $startdate);
	$startmon_short = date('M', $startdate);
	$startyear = date('Y', $startdate);
	$endday = date('j', $enddate);
	$endmon_long = date('F', $enddate);
	$endmon_short = date('M', $enddate);
	$endyear = date('Y', $enddate);

	//Get week type.
	$today = date('Y-m-d' , $now);
	$query = "SELECT date, week_type FROM dates where date = '$today'";
	$result = @mysqli_query($dbc, $query);

	while ($row = mysql_fetch_assoc($result)) {
		$week_type = $row['week_type'];
		}

	$day = date('D', $now);
	$year = date('Y', $now);

	//Get season.
	$query2 = "SELECT memorial_day, labor_day FROM holidays where year='$year'";
	$result2 = @mysqli_query($dbc, $query2);

	while ($row2 = mysql_fetch_assoc($result2)) {
		$memorial_day = $row2['memorial_day'];
		$labor_day = $row2['labor_day'];
		}

	$mem_sat = strtotime ('-2 days', strtotime ($memorial_day));
	$lab_sat = strtotime ('+5 days', strtotime ($labor_day));

	if ((strtotime($today) >= $mem_sat) && (strtotime($today) < $lab_sat)){
		$season = 'summer';
		}
	elseif (strtotime($today) < $mem_sat){
		$season = 'spring';
		}
	else {
		$season = 'fall';
		}
	//Get division names.
	$div_query = "SELECT div_name from divisions WHERE div_link = '$division'";
	$div_result = mysqli_query($dbc, $div_query);
	while ($div_row = mysql_fetch_array($div_result, MYSQL_ASSOC)){
		$div_name = $div_row['div_name'];
		}
	$query = "SELECT first_name, last_name, name_dup, emp_id, division FROM employees, divisions
		WHERE div_link = '$division' and division like concat('%',div_name,'%') and active = 'Active'
		and (employee_lastday >= '$today' or employee_lastday is null) ORDER BY exempt_status asc, weekly_hours desc, first_name asc";
	$result = mysqli_query($dbc, $query);
	if ($result){
		$num_rows = mysql_num_rows($result);
		if ($num_rows != 0){
			echo '<div class="division_specific">'."\n".'<div class="divspec_head">'."\n".
				'<input class="prev" type="button" onclick="loadprevweekDiv()" value="Previous" />'."\n".
				'<input class="next" type="button" value="Next" onclick="loadnextweekDiv()" />'."\n";
			echo "<div class=\"divspec screen\">$startday $startmon_long";
			if ($startyear != $endyear){echo ' '.$startyear;}
			echo " &ndash; $endday $endmon_long $endyear</div>\n";
			echo "<div class=\"divspec mobile\">$startday $startmon_short";
			if ($startyear != $endyear){echo $startyear;}
			echo " &ndash; $endday $endmon_short $endyear</div>\n";
			echo '<div class="week_type">'. ucwords($week_type) . ' Schedule</div>'."\n";
			echo "</div>\n";

			echo '<div class="divboxes">'."\n".'<table class="divisions top divweekly" cellspacing="0">'."\n";
			echo '<tr class="divisions days screen"><td></td>';
			foreach ($shortdates as $k=>$v){
				echo '<td class="day">'.$v.'</td>';
				}
			echo '<td class="hrs">Hrs</td></tr>'."\n";
			echo '<tr style="display:none;"></tr>'."\n".'<tr class="divisions days mobile"><td></td>
				<td class="day"><div class="smday">Sat</div></td><td class="day"><div class="smday">Sun</div></td>
				<td class="day"><div class="smday">Mon</div></td><td class="day"><div class="smday">Tue</div></td>
				<td class="day"><div class="smday">Wed</div></td><td class="day"><div class="smday">Thu</div></td>
				<td class="day"><div class="smday">Fri</div></td><td class="hrs"><div class="smday">Hrs</div></td></tr>'."\n";

			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
				$first_name = $row['first_name'];
				if ($row['name_dup'] == 'Y'){
					$last_initial = substr($row['last_name'],0,1);
					$first_name .= ' ' . $last_initial . '.';
					}
				$emp_id = $row['emp_id'];
				$hr_total = 0;

				echo '<tr class="divisions"><td class="first_name">' . $first_name . '</td>';

				foreach ($dates as $k=>$v) {
					$hour_keys = array(7,7.5,8,8.5,9,9.5,10,10.5,11,11.5,12,12.5,13,13.5,14,14.5,15,15.5,16,16.5,17,17.5,18,18.5,19,19.5);
					$details_array = array_fill_keys($hour_keys, 'N');
					echo '<td class="shift">';
					$day = date('D', strtotime($v));
					$query1 = "SELECT specific_schedule from schedules WHERE division='$div_name'
						and schedule_start_date <= '$v' and schedule_end_date >= '$v'";
					$result1 = mysqli_query($dbc, $query1);
					while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)){
						$specific_schedule = $row1['specific_schedule'];
						}
					$query2 = "SELECT time_format(shift_start,'%k') as shift_start,
						time_format(shift_start,'%i') as shift_start_minutes, time_format(shift_end,'%k') as shift_end,
						time_format(shift_end,'%i') as shift_end_minutes, time_format(desk_start,'%k') as desk_start,
						time_format(desk_start,'%i') as desk_start_minutes, time_format(desk_end,'%k') as desk_end,
						time_format(desk_end,'%i') as desk_end_minutes, time_format(desk_start2,'%k') as desk_start2,
						time_format(desk_start2,'%i') as desk_start2_minutes, time_format(desk_end2,'%k') as desk_end2,
						time_format(desk_end2,'%i') as desk_end2_minutes, time_format(lunch_start,'%k') as lunch_start,
						time_format(lunch_start,'%i') as lunch_start_minutes, time_format(lunch_end,'%k') as lunch_end,
						time_format(lunch_end,'%i') as lunch_end_minutes
						FROM employees as e, shifts as a
						WHERE e.emp_id = '$emp_id' and specific_schedule='$specific_schedule'
						and e.emp_id = a.emp_id
						and week_type='$week_type' and shift_day='$day'
						and e.active = 'Active' and (e.employee_lastday >= '$v' or e.employee_lastday is null)";
					$result2 = mysqli_query($dbc, $query2);
					if($result2){
						while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)){
							$working_start = 0;
							$working_end = 0;
							$working_desk_start = 0;
							$working_desk_end = 0;
							$working_desk_start2 = 0;
							$working_desk_end2 = 0;
							$shift_start = $row2['shift_start'];
							$shift_start_minutes = $row2['shift_start_minutes'];
							$shift_end = $row2['shift_end'];
							$shift_end_minutes = $row2['shift_end_minutes'];
							$desk_start = $row2['desk_start'];
							$desk_start_minutes = $row2['desk_start_minutes'];
							$desk_end = $row2['desk_end'];
							$desk_end_minutes = $row2['desk_end_minutes'];
							$desk_start2 = $row2['desk_start2'];
							$desk_start2_minutes = $row2['desk_start2_minutes'];
							$desk_end2 = $row2['desk_end2'];
							$desk_end2_minutes = $row2['desk_end2_minutes'];
							$lunch_start = $row2['lunch_start'];
							$lunch_start_minutes = $row2['lunch_start_minutes'];
							$lunch_end = $row2['lunch_end'];
							$lunch_end_minutes = $row2['lunch_end_minutes'];
							$shift_array = array();
							$desk_array = array();

							if ($shift_start_minutes != '00') {
								$working_start = $shift_start + dec_minutes($shift_start_minutes);
								}
							else{
								$working_start = $shift_start;
								}
							if ($shift_end_minutes != '00') {
								$working_end = $shift_end + dec_minutes($shift_end_minutes);
								}
							else{
								$working_end = $shift_end;
								}

							if ($desk_start != '00'){
								if ($desk_start_minutes != '00') {
									$working_desk_start = $desk_start + dec_minutes($desk_start_minutes);
									}
								else{
									$working_desk_start = $desk_start;
									}
								if ($desk_end_minutes != '00') {
									$working_desk_end = $desk_end + dec_minutes($desk_end_minutes);
									}
								else{
									$working_desk_end = $desk_end;
									}
								}
							if ($desk_start2 != '00'){
								if ($desk_start2_minutes != '00') {
									$working_desk_start2 = $desk_start2 + dec_minutes($desk_start2_minutes);
									}
								else{
									$working_desk_start2 = $desk_start2;
									}
								if ($desk_end2_minutes != '00') {
									$working_desk_end2 = $desk_end2 + dec_minutes($desk_end2_minutes);
									}
								else{
									$working_desk_end2 = $desk_end2;
									}
								}

							if ($lunch_start_minutes != '00') {
								$lunch_start += dec_minutes($lunch_start_minutes);
								}
							if ($lunch_end_minutes != '00') {
								$lunch_end += dec_minutes($lunch_end_minutes);
								}

							$query3 = "SELECT timeoff_start_date, time_format(timeoff_start_time,'%k') as timeoff_start,
								time_format(timeoff_start_time,'%i') as timeoff_start_minutes, timeoff_end_date,
								time_format(timeoff_end_time,'%k') as timeoff_end, time_format(timeoff_end_time,'%i') as timeoff_end_minutes
								from timeoff as t where emp_id='$emp_id' and
								timeoff_start_date <= '$v' and timeoff_end_date >= '$v' order by timeoff_start_date asc, timeoff_start asc";
							$result3 = mysqli_query($dbc, $query3);

							$timeoff_array = array();
							while ($row3 = mysql_fetch_array ($result3, MYSQL_ASSOC)){
								$tostartd = $row3['timeoff_start_date'];
								$tostart = $row3['timeoff_start'];
								$tostart_minutes = $row3['timeoff_start_minutes'];
								$toendd = $row3['timeoff_end_date'];
								$toend = $row3['timeoff_end'];
								$toend_minutes = $row3['timeoff_end_minutes'];

								if ($tostartd != $v){
									$tostart = 1;
									}
								if ($toendd != $v){
									$toend = 23;
									}

								if ($tostart_minutes != '00') {
									$tostart += dec_minutes($tostart_minutes);
									}
								if ($toend_minutes != '00') {
									$toend += dec_minutes($toend_minutes);
									}

								$timeoff_array[] = array('tos'=>$tostart, 'toe'=>$toend);
								}

							$query5 = "SELECT time_format(closure_start_time,'%k') as closure_start,
								time_format(closure_start_time,'%i') as closure_start_minutes,
								time_format(closure_end_time,'%k') as closure_end,
								time_format(closure_end_time,'%i') as closure_end_minutes
								from closures WHERE closure_date='$v' limit 1";
							$result5 = mysqli_query($dbc, $query5);

							while ($row5 = mysql_fetch_array ($result5, MYSQL_ASSOC)){
								$cd_start = $row5['closure_start'];
								$cd_start_minutes = $row5['closure_start_minutes'];
								$cd_end = $row5['closure_end'];
								$cd_end_minutes = $row5['closure_end_minutes'];

								if ($cd_start_minutes != '00') {
									$cd_start += dec_minutes($cd_start_minutes);
									}
								if ($cd_end_minutes != '00') {
									$cd_end += dec_minutes($cd_end_minutes);
									}

								$timeoff_array[] = array('tos'=>$cd_start, 'toe'=>$cd_end);
								}

							$query4 = "SELECT emp_id, coverage_date, time_format(coverage_start_time,'%k') as coverage_start,
								time_format(coverage_start_time,'%i') as coverage_start_minutes, time_format(coverage_end_time,'%k') as coverage_end,
								time_format(coverage_end_time,'%i') as coverage_end_minutes, coverage_offdesk
								FROM coverage as c
								where emp_id='$emp_id' and coverage_date = '$v'
								and c.coverage_division= '$division'";
							$result4 = mysqli_query($dbc, $query4);
							if (mysql_num_rows($result) != 0) {
								$cov_array = array();
								while ($row4 = mysql_fetch_array ($result4, MYSQL_ASSOC)){
									$cov_start = $row4['coverage_start'];
									$cov_start_minutes = $row4['coverage_start_minutes'];
									if ($cov_start_minutes != '00') {
										$cov_start += dec_minutes($cov_start_minutes);
										}
									$cov_end = $row4['coverage_end'];
									$cov_end_minutes = $row4['coverage_end_minutes'];
									if ($cov_end_minutes != '00') {
										$cov_end += dec_minutes($cov_end_minutes);
										}
									$cov_offdesk = $row4['coverage_offdesk'];
									$cov_array[] = array('cov_start'=>$cov_start, 'cov_end'=>$cov_end,
										'cov_offdesk'=>$cov_offdesk);
									}
								}

							foreach ($details_array as $hour=>$state){
								if (($hour >= $working_start) && ($hour < $working_end)){
									$details_array[$hour] = 'Y';
									}
								if (($hour >= $working_desk_start) && ($hour < $working_desk_end)){
									$details_array[$hour] = 'On';
									}
								if (($hour >= $working_desk_start2) && ($hour < $working_desk_end2)){
									$details_array[$hour] = 'On';
									}
								if (($hour >= $lunch_start) && ($hour < $lunch_end)){
									$details_array[$hour] = 'N';
									}
								foreach ($timeoff_array as $row=>$timeoff){
									if (($hour >= $timeoff['tos']) && ($hour < $timeoff['toe'])){
										$details_array[$hour] = 'N';
										}
									}
								foreach ($cov_array as $row=>$cov){
									if (($hour >= $cov['cov_start']) && ($hour < $cov['cov_end'])){
										$details_array[$hour] = $cov['cov_offdesk'];
										}
									}
								}

							$chunks = array();
							$last_state = '';
							$count = 0;
							$shift_count = 0;
							foreach ($details_array as $hour=>$state){
								if ($state != $last_state){
									if ($last_state == 'N'){
										$chunks['shift'][$shift_count]['start'] = $hour;
										}
									if (($state == 'N')&&($last_state != '')){
										$chunks['shift'][$shift_count]['end'] = $hour;
										$shift_count++;
										}

									if (isset($chunks[$last_state][$count])){
										$chunks[$last_state][$count]['end'] = $hour;
										}
									$count++;
									$chunks[$state][$count] = array('start'=>$hour);
									$last_state = $state;
									}
								if ($hour==19.5){
									$chunks[$last_state][$count]['end'] = 20;
									if ((isset($chunks['shift'][$shift_count]['start']))&&(!isset($chunks['shift'][$shift_count]['end']))){
										$chunks['shift'][$shift_count]['end'] = 20;
										}
									}
								}

							//Adjust 24-hour time.

							$desk_display = '';
							if (isset($chunks['On'])){
								$desk_count = 0;
								foreach ($chunks['On'] as $k=>$arr){
									if ((int)$arr['start'] > 12){
										$ds12 = $arr['start'] - 12;
										}
									else{
										$ds12 = $arr['start'];
										}
									if (round($ds12, 0) != $ds12){
										$ds12 = (int)$ds12.':'.(int)(($ds12-(int)$ds12) * 60);
										}
									if ((int)$arr['end'] > 12){
										$de12 = $arr['end'] - 12;
										}
									else{
										$de12 = $arr['end'];
										}
									if (round($de12, 0) != $de12){
										$de12 = (int)$de12.':'.(int)(($de12-(int)$de12) * 60);
										}

									if($desk_count>0){
										$desk_display .= ', '. $ds12 . '-' . $de12;
										}
									else{
										$desk_display .= $ds12 . '-' . $de12;
										}
									$desk_count++;
									}
								}

							$shift_display = '';
							if((count($timeoff_array) >= 1)||(empty($working_start))){
								if(isset($chunks['shift'])){
									$counter = 0;
									foreach ($chunks['shift'] as $k=>$arr){
										if ((int)$arr['start'] > 12){
											$ss12 = $arr['start'] - 12;
											}
										else{
											$ss12 = $arr['start'];
											}
										if (round($ss12, 0) != $ss12){
											$ss12 = (int)$ss12.':'.(int)(($ss12-(int)$ss12) * 60);
											}

										if ((int)$arr['end'] > 12){
											$se12 = $arr['end'] - 12;
											}
										else{
											$se12 = $arr['end'];
											}
										if (round($se12, 0) != $se12){
											$se12 = (int)$se12.':'.(int)(($se12-(int)$se12) * 60);
											}
										if ($counter>0){
											$shift_display .= ', '. $ss12 . '-' . $se12;
											}
										else{
											$shift_display .= $ss12 . '-' . $se12;
											}
										$counter++;
										}
									}
								}
							else{
								if ((int)$working_start > 12){
									$ss12 = $working_start - 12;
									}
								elseif($working_start == 0){
									$ss12 = NULL;
									}
								else{
									$ss12 = $working_start;
									}
								if (round($ss12, 0) != $ss12){
									$ss12 = (int)$ss12.':'.(int)(($ss12-(int)$ss12) * 60);
									}

								if ((int)$working_end > 12){
									$se12 = $working_end - 12;
									}
								elseif($working_end == 0){
									$se12 = NULL;
									}
								else{
									$se12 = $working_end;
									}
								if (round($se12, 0) != $se12){
									$se12 = (int)$se12.':'.(int)(($se12-(int)$se12) * 60);
									}
								if (isset($ss12)){
									$shift_display .= $ss12 . '-' . $se12;
									}
								}

							$ls12 = '';
							if(isset($chunks['shift'])){
								if ((int)$lunch_start > 12){
									$ls12 = (int)$lunch_start - 12;
									}
								elseif($lunch_start == 0){
									$ls12 = NULL;
									}
								else{
									$ls12 = (int)$lunch_start;
									}
								if (($lunch_start_minutes != '00') && ($lunch_start_minutes != null)){
									$ls12 .= ':'.$lunch_start_minutes;
									}

								if ((int)$lunch_end > 12){
									$le12 = (int)$lunch_end - 12;
									}
								elseif($lunch_end == 0){
									$le12 = NULL;
									}
								else{
									$le12 = (int)$lunch_end;
									}
								if (($lunch_end_minutes != '00') && ($lunch_end_minutes != null)){
									$le12 .= ':'.$lunch_end_minutes;
									}
								}

							if (!empty($shift_display)){
								echo $shift_display;
								}
							if (!empty($desk_display)){
								echo '<br/><span class="desk">'.$desk_display.'</span>';
								}
							elseif ($division != 'pages'){
								echo '<br/>';
								}
							if (!empty($ls12)){
								echo '<br/><span class="lunch">'.$ls12.'-'.$le12.'</span>';
								}
							elseif ($division != 'pages'){
								echo '<br/>&nbsp;';
								}

							//Calculate Hour Totals
							if((count($timeoff_array) >= 1)||(empty($working_start))){
								if(isset($chunks['shift'])){
									foreach ($chunks['shift'] as $k=>$arr){
										$shift_total = $arr['end'] - $arr['start'];
										$hr_total += $shift_total;
										}
									}
								}
							else{
								$shift_total = $working_end - $working_start;
								$hr_total += $shift_total;
								$lunch_total = $lunch_end - $lunch_start;
								$hr_total -= $lunch_total;
								}
							}
						}
					echo '</td>';
					}
				echo '<td class="hr_total">'.$hr_total.'</td>';
				echo '</tr>'."\n";
				}
			echo '</table>'."\n".'</div>'."\n".'</div>'."\n";
			}
		}
	}

function division_master($sched_id){
	$today = date('Y-m-d');
	$week_types = array('a','b','c','d');
	$daysofweek = array('sat','sun','mon','tue','wed','thu','fri');
	$shifts = array();

	$query = "SELECT * from schedules, divisions WHERE schedule_id='$sched_id' and division=div_name";
	$result = mysqli_query($dbc, $query);

	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
		$division = $row['div_link'];
		$specific_schedule = $row['specific_schedule'];
		$schedule_start_date = $row['schedule_start_date'];
		$schedule_end_date = $row['schedule_end_date'];
		$schedule_start_year = date('Y', strtotime($schedule_start_date));
		$schedule_end_year = date('Y', strtotime($schedule_end_date));

		if ($schedule_start_year == $schedule_end_year){
			$schedule_start_short = date('j M', strtotime($schedule_start_date));
			$schedule_end_short = date('j M Y', strtotime($schedule_end_date));
			$schedule_start_long = date('j F', strtotime($schedule_start_date));
			$schedule_end_long = date('j F Y', strtotime($schedule_end_date));
			}
		else{
			$schedule_start_short = date('j M Y', strtotime($schedule_start_date));
			$schedule_end_short = date('j M Y', strtotime($schedule_end_date));
			$schedule_start_long = date('j F Y', strtotime($schedule_start_date));
			$schedule_end_long = date('j F Y', strtotime($schedule_end_date));
			}
		}

	echo "<script>
		$(document).ready(function() {
			function showDivWeek(){
				week = $('.div_focus').data(\"week\");
				$('.divboxes').hide();
				$('.divboxes.'+week).show();
				}
			showDivWeek();
			$('.div_week').click(function(){
				$(this).siblings('div.div_week').removeClass('div_focus');
				$(this).addClass('div_focus');
				showDivWeek();
				});
			});
		</script>";

	echo '<div class="division_specific">'."\n".'<div class="divspec_head">'."\n";
	echo "<div class=\"divspec screen\">Master: $schedule_start_long &ndash; $schedule_end_long</div>\n";
	echo "<div class=\"divspec mobile\">Master: $schedule_start_short &ndash; $schedule_end_short</div>\n";
	echo "<div class=\"div_weeks\">
		<div class=\"div_week div_focus\" data-week=\"a\">A</div><div class=\"div_week\" data-week=\"b\">B</div>";
	echo "<div class=\"div_week\" data-week=\"c\">C</div><div class=\"div_week\" data-week=\"d\">D</div></div>\n";

	$query1 = "SELECT * from shifts WHERE specific_schedule='$specific_schedule'";
	$result1 = mysqli_query($dbc, $query1);
	if($result1){
		while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC)){
			$week_type = $row1['week_type'];
			$day = $row1['shift_day'];
			$emp_id = $row1['emp_id'];
			$shifts[$week_type][$day][$emp_id] = array('shift_start'=>$row1['shift_start'],'shift_end'=>$row1['shift_end'],'desk_start'=>$row1['desk_start'],'desk_end'=>$row1['desk_end'],'desk_start2'=>$row1['desk_start2'],'desk_end2'=>$row1['desk_end2'],'lunch_start'=>$row1['lunch_start'],'lunch_end'=>$row1['lunch_end']);
			}
		}

	foreach ($week_types as $k=>$v){
		echo '<div class="divboxes '.$v.'"><table class="divisions top" cellspacing="0">'."\n".
			'<tr class="divisions days screen"><td></td>';
		foreach ($daysofweek as $k1=>$v1){
			echo '<td class="day">'.date('l',strtotime($v1)).'</td>';
			}
		echo '<td class="hrs">Hrs</td></tr>'."\n";
		echo '<tr style="display:none;"></tr>'."\n".'<tr class="divisions days mobile"><td></td>';
		foreach ($daysofweek as $k1=>$v1){
			echo '<td class="day">'.$v1.'</td>';
			}
		echo '<td class="hrs">Hrs</td></tr>'."\n";

		$query = "SELECT first_name, last_name, name_dup, emp_id FROM employees, divisions
			WHERE div_link = '$division' and division like concat('%',div_name,'%') and active = 'Active'
			and (employee_lastday >= '$today' or employee_lastday is null)
			ORDER BY division asc, exempt_status asc, weekly_hours desc, first_name asc";
		$result = mysqli_query($dbc, $query);
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
			$first_name = $row['first_name'];
			if ($row['name_dup'] == 'Y'){
				$last_initial = substr($row['last_name'],0,1);
				$first_name .= ' ' . $last_initial . '.';
				}
			$emp_id = $row['emp_id'];
			$hr_total = 0;

			echo '<tr class="divisions"><td class="first_name">' . $first_name . '</td>';

			foreach ($daysofweek as $k1=>$v1){
				if ((array_key_exists($v, $shifts)) && (array_key_exists($v1, $shifts[$v])) && (array_key_exists($emp_id, $shifts[$v][$v1]))){
					list($shift_start, $shift_start_minutes, $shift_start_seconds) = explode(":", $shifts[$v][$v1][$emp_id]['shift_start']);
					list($shift_end, $shift_end_minutes, $shift_end_seconds) = explode(":", $shifts[$v][$v1][$emp_id]['shift_end']);
					list($desk_start, $desk_start_minutes, $desk_start_seconds) = explode(":", $shifts[$v][$v1][$emp_id]['desk_start']);
					list($desk_end, $desk_end_minutes, $desk_end_seconds) = explode(":", $shifts[$v][$v1][$emp_id]['desk_end']);
					list($desk_start2, $desk_start2_minutes, $desk_start2_seconds) = explode(":", $shifts[$v][$v1][$emp_id]['desk_start2']);
					list($desk_end2, $desk_end2_minutes, $desk_end2_seconds) = explode(":", $shifts[$v][$v1][$emp_id]['desk_end2']);
					list($lunch_start, $lunch_start_minutes, $lunch_start_seconds) = explode(":", $shifts[$v][$v1][$emp_id]['lunch_start']);
					list($lunch_end, $lunch_end_minutes, $lunch_end_seconds) = explode(":", $shifts[$v][$v1][$emp_id]['lunch_end']);

					//Adjust 24-hour time.
					if ($shift_start > 12){
						$ss12 = (int)$shift_start - 12;
						}
					elseif($shift_start == 0){
						$ss12 = NULL;
						}
					else{
						$ss12 = (int)$shift_start;
						}
					if (($shift_start_minutes != '00') && ($shift_start_minutes != null)){
						$ss12 .= ':'.$shift_start_minutes;
						}

					if ($shift_end > 12){
						$se12 = (int)$shift_end - 12;
						}
					elseif($shift_end == 0){
						$se12 = NULL;
						}
					else{
						$se12 = (int)$shift_end;
						}
					if (($shift_end_minutes != '00') && ($shift_end_minutes != null)){
						$se12 .= ':'.$shift_end_minutes;
						}
					if ($desk_start > 12){
						$ds12 = (int)$desk_start - 12;
						}
					elseif($desk_start == 0){
						$ds12 = NULL;
						}
					else{
						$ds12 = (int)$desk_start;
						}
					if (($desk_start_minutes != '00') && ($desk_start_minutes != null)){
						$ds12 .= ':'.$desk_start_minutes;
						}

					if ($desk_end > 12){
						$de12 = (int)$desk_end - 12;
						}
					elseif($desk_end == 0){
						$de12 = NULL;
						}
					else{
						$de12 = (int)$desk_end;
						}
					if (($desk_end_minutes != '00') && ($desk_end_minutes != null)){
						$de12 .= ':'.$desk_end_minutes;
						}
					if ($desk_start2 > 12){
						$ds212 = (int)$desk_start2 - 12;
						}
					elseif($desk_start2 == 0){
						$ds212 = NULL;
						}
					else{
						$ds212 = (int)$desk_start2;
						}
					if (($desk_start2_minutes != '00') && ($desk_start2_minutes != null)){
						$ds212 .= ':'.$desk_start2_minutes;
						}

					if ($desk_end2 > 12){
						$de212 = (int)$desk_end2 - 12;
						}
					elseif($desk_end2 == 0){
						$de212 = NULL;
						}
					else{
						$de212 = (int)$desk_end2;
						}
					if (($desk_end2_minutes != '00') && ($desk_end2_minutes != null)){
						$de212 .= ':'.$desk_end2_minutes;
						}

					if (isset($ss12)){
						echo '<td class="shift">';
						echo '<div class="screen">';
						echo $ss12 . '-' . $se12;
							if (isset($ds12)){
								echo ' <span class="desk">('.$ds12.'-'.$de12;
								if (isset($ds212)){
									echo ', '.$ds212.'-'.$de212;
									}
								echo ')</span>';
							}
						echo '</div>';
						echo '<div class="mobile">';
						echo $ss12 . '-' . $se12;
							if (isset($ds12)){
								echo '<br/><span class="desk">'.$ds12.'-'.$de12;
								if (isset($ds212)){
									echo ',<br/>'.$ds212.'-'.$de212;
									}
								echo '</span>';
							}
						echo '</div>';
						echo '</td>';
						}
					else{
						echo '<td class="shift"></td>';
						}
					//Calculate Hour Totals
					if ($shift_start_minutes != '00') {
						$shift_start += dec_minutes($shift_start_minutes);
						}
					if ($shift_end_minutes != '00') {
						$shift_end += dec_minutes($shift_end_minutes);
						}
					if ($lunch_start_minutes != '00') {
						$lunch_start += dec_minutes($lunch_start_minutes);
						}
					if ($lunch_end_minutes != '00') {
						$lunch_end += dec_minutes($lunch_end_minutes);
						}
					$shift_total = $shift_end - $shift_start;
					$lunch_total = $lunch_end - $lunch_start;
					$hr_total += $shift_total;
					$hr_total -= $lunch_total;
					}
				else{
					echo '<td class="shift"></td>';
					}
				}
			echo '<td class="hr_total">'.$hr_total.'</td>';
			echo '</tr>'."\n";
			}
		echo '</table></div>';
		}


	echo '</div></div>';
	}
	?>
