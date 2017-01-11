<?php #change_password.php
include('./includes/sessionstart.php');
include('./includes/allsessionvariables.php');

$page_title = "Change Password";

include ('./includes/header.html');
include ('./includes/sidebar.html');
include ('./display_functions.php');

?>
<script>
function validatePassword() {
	var old_pw=document.change_password.old_pw.value;
	var new_pw1=document.change_password.new_pw1.value;
	var new_pw2=document.change_password.new_pw2.value;
	
	if (old_pw==null || old_pw==''){
		alert('Please enter your old password.');
		document.change_password.old_pw.focus();
		return false;
		}
	if (new_pw1==null || new_pw1==''){
		alert('Please enter your new password.');
		document.change_password.new_pw1.focus();
		return false;
		}
	if (new_pw2==null || new_pw2==''){
		alert('Please enter your new password again.');
		document.change_password.new_pw2.focus();
		return false;
		}
	
	if (new_pw1 !== new_pw2){
		alert('Passwords do not match!');
		document.change_password.new_pw2.focus();
		return false;
		}
	if (new_pw1.length < 8) {
		alert("Password must contain at least eight characters");
		document.change_password.new_pw1.focus();
		return false;
		}
	re = /[0-9A-Z]/;
	if (!re.test(new_pw1)) {
		alert("Password must contain at least one digit (0-9) or one capital letter");
		document.change_password.new_pw1.focus();
		return false;
		}
	re = /[a-z]/;
	if	(!re.test(new_pw1)) {
		alert("Password must contain at least one lowercase letter (a-z)");
		document.change_password.new_pw1.focus();
		return false;
		}
	}
</script>
<div class="mobilewrapper_outer">
	<div class="mobilewrapper_inner">
		<span class="date"><h1><?php echo $page_title;?></h1></span>
<?php 
if (isset($_POST['submitted'])){
	$old_pw = $_POST['old_pw'];
	$query = "SELECT * from logins WHERE emp_id='$this_emp_id' and password=sha('$old_pw')";
	$result = mysqli_query($dbc, $query);
	if (mysqli_num_rows($result) == 1) {
		if($_POST['new_pw1'] == $_POST['new_pw2']){
			$password = escape_data($_POST['new_pw1']);
			$query1 = "UPDATE logins set password = sha('$password') WHERE emp_id='$this_emp_id'";
			$result1 = mysqli_query($dbc, $query1);
			if($result1){
				echo '<div class="message"><b>Your password has been updated successfully!</b></div>';
				}
			else{
				$error = '<div class="errormessage"><h3>System Error</h3>
					The employee could not be added due to a system error.
					We apologize for the inconvenience.</div>';
				}
			}
		else{
			$error = '<div class="errormessage"><b>Passwords do not match!</b> Please try again.</div>';
			}
		}
	else{
		$error = '<div class="errormessage"><b>Old password incorrect!</b> Please try again.</div>';
		}
	}
	
if(isset($error)){
	echo $error;
	}
?>
		<div class="coverform">
		<form action="/scheduler2/change_password" method="post" name="change_password" onsubmit="return validatePassword();">
			<div class="label">Old Password:</div>
			<input type="password" name="old_pw" size="30"/><br/><br/>
			<div class="label">New Password:</div>
			<input type="password" name="new_pw1" size="30"/>
			<div class="label">Confirm New Password:</div>
			<input type="password" name="new_pw2" size="30"/><br/>
			<div class="help">Password should be 8+ characters long and include:<br/>- at least one lower-case letter<br/>- at least one digit (0-9) or one upper-case letter.</div>
			<p><input type="submit" name="submit" value="Submit" /></p>
			<input type="hidden" name="submitted" value="TRUE" />
		</form>
		</div>
	</div>
</div>
<?php
include ('./includes/footer.html');
?>