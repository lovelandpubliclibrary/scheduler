<?php #supersubmenu.php

echo "<div class=\"submenuhead\"><h5>Supervisors</h5></div>\n";
?>
<div id="accordion" class="links">
	<h3 class="ui-corner-flat">Schedules</h3>
	<div class="ui-corner-flat">
	<a href="/scheduler2/add_schedule">Add New Schedule</a><br/>
	<a href="/scheduler2/edit_schedule">Edit Schedule</a>
	</div>
	<h3 class="ui-corner-flat">Subs</h3>
	<div class="ui-corner-flat">
	<a href="/scheduler2/sub_needs">Request Sub</a>
	</div>
	<h3 class="ui-corner-flat">Timeoff</h3>
	<div class="ui-corner-flat">
	<a href="/scheduler2/add_timeoff">Add Timeoff</a><br/>
	<a href="/scheduler2/view_timeoff">View All Timeoff</a><br/>
	<a href="/scheduler2/view_past">Edit Past Days</a>
	</div>
	<h3 class="ui-corner-flat">Coverage</h3>
	<div class="ui-corner-flat">
	<a href="/scheduler2/add_coverage">Add Coverage</a><br/>
	<a href="/scheduler2/view_coverage">View All Coverage</a><br/>
	<a href="/scheduler2/view_past">Edit Past Days</a>
	</div>
	<h3 class="ui-corner-flat">Employee Tools</h3>
	<div class="ui-corner-flat">
	<a href="/scheduler2/timesheet">Confirm Timesheet</a><br/>
	<a href="/scheduler2/change_password">Change Password</a>
	</div>
	<h3 class="ui-corner-flat">Admin Tools</h3>
	<div class="ui-corner-flat">
	<a href="/scheduler2/approve_timesheets">Approve Timesheets</a><br/>
	<a href="/scheduler2/payperiod_csv">Generate Pay Period CSV</a><br/>
	<a href="/scheduler2/add_payperiods">Add Yearly Pay Periods</a><br/>
	<a href="/scheduler2/add_closures">Add Library Closures</a>
	</div>
	<h3 class="ui-corner-flat">Employees</h3>
	<div class="ui-corner-flat">
	<a href="/scheduler2/add_employee">Add Employee</a><br/>
	<a href="/scheduler2/view_employees">View & Edit Employees</a><br/>
	<a href="/scheduler2/replace_employee">Replace Employee</a>
	</div>
	<h3 class="ui-corner-flat">PIC</h3>
	<div class="ui-corner-flat">
	<a href="/scheduler2/add_pic">Add PIC Schedule</a><br/>
	<a href="/scheduler2/add_pic_coverage">Add PIC Coverage</a><br/>
	<a href="/scheduler2/view_pic_coverage">View PIC Coverage</a>
	</div>
</div>