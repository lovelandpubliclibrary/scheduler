<?php # login.php
session_save_path("../sess_tmp/");
session_name ('VisitID');
session_start();
if ((isset($_SESSION['came_from']))&&($_SESSION['came_from']!='login')&&($_SESSION['came_from']!='logout')){
	$_SESSION['redirect'] = $_SESSION['came_from'];
	}
if(isset($_SESSION['redirect'])){
	$came_from = $_SESSION['redirect'];
	}
include('./includes/allsessionvariables.php');
if (isset($_SESSION['role'])){
	$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
	if ((substr($url, -1) == '/') OR (substr($url, -1) == '\\')){
		$url = substr($url, 0, -1);
		}
	$url .= '/';
	header("Location: $url");
	exit();
	}
else {
	if (isset($_POST['submitted'])){
		require_once('../mysql_connect.php');
		$errors = array();
		if (empty($_POST['username'])){
			$errors[] = 'You forgot to enter your username';
			}
		else {
			$user = escape_data($_POST['username']);
			}
		if (empty($_POST['password'])){
			$errors[] = 'You forgot to enter your password.';
			}
		else {
			$p = escape_data($_POST['password']);
			}
		if (empty($errors)){
			$query = "SELECT login_id, username, role, emp_id, assignment_id 
				FROM logins WHERE username ='$user' AND password=SHA('$p')";
			$result = @mysqli_query($dbc, $query);
			$row = mysqli_fetch_array($result, MYSQL_NUM);
			
			if ($row){
				$_SESSION['role'] = $row[2];
				$_SESSION['username'] = $row[1];
				$_SESSION['this_emp_id'] = $row[3];
				$_SESSION['this_assignment_id'] = $row[4];
				
				$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
				if ((substr($url, -1) == '/') OR (substr($url, -1) == '\\')){
					$url = substr($url, 0, -1);
					}
				$url .= '';
				if (isset($came_from)){
					$url.= '/'.$came_from;
					}
				header("Location: $url");
				exit();
				}
			else {
				$errors[] = 'The user name and password <br/>do not match those on file.';
				}
			}
		mysqli_close($dbc);
		}
	else {
		$errors = NULL;
		}
	$page_title = 'Login';
	include ('./includes/loginheader.html');
?>
	
	<div class="loginwrapper">
		<div class="loginform">
		<h2>LPL Scheduler Login</h2>
<?php
	if (!empty($errors)){
		echo '<div class="login_errors"><div class="errormessage"><h3>Error!</h3><br/>'."\n".
			'The following error(s) occurred:<p>';
		foreach ($errors as $msg){
			echo " $msg<br/>\n";
			}
		echo '</p></div></div>';
		}
?>
			<form action="login" method="post">
				<p><div class="label">Username:</div><input type="text" name="username" size="20" maxlength="40" tabindex="1" autofocus 
					value="<?php if (isset($_POST['username'])){echo $_POST['username'];} ?>" /></p>
				<p><div class="label">Password:</div><input type="password" name="password" size="20" maxlength="20" tabindex="2" /></p>
				<p><div class="submit"><input type="submit" name="submit" value="Login" tabindex="3" /></div></p>
				<input type="hidden" name="submitted" value="TRUE" />
			</form>
		</div>
	</div>

<?php
	include ('./includes/footer.html');
}
?>