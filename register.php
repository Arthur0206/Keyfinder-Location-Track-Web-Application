<?php
$connection = mysql_connect("localhost", "root", "sosoman1984") or die("Couldn't connect to the server!");
mysql_select_db("location_service", $connection) or die("Couldn't connect to the database!");

error_reporting(0);

if (isset($_POST['register'])) {
	if (isset($_POST['username']) && isset($_POST['password'])) {
		$username = mysql_real_escape_string($_POST['username']);
		$password = mysql_real_escape_string(hash("sha512", $_POST['password']));
		$email = mysql_real_escape_string(strip_tags($_POST['email']));

		$check = mysql_fetch_array(mysql_query("SELECT * FROM `user` WHERE `Username`='$username'"));
		if ($check != '0') {
			die("That username <i>$username already exist! Please try another one. <a href='register.php'>&larr; Back</a>");
		}
		if (!ctype_alnum($username)) {
			die("Username contains special characters! Only numbers and letters are permitted! <a href='register.php'>&larr; Back</a>");
		}
		if (strlen($username) > 20) {
			die("Username must not contain more than 20 characters!  <a href='register.php'>&larr; Back</a>");
		}
		$salt = hash("sha512", rand() . rand() . rand());
		mysql_query("INSERT INTO `user` (`Username`, `Password`, `Email`, `Salt`) VALUES ('$username', '$password', '$email', '$salt')");
		setcookie("c_user", hash("sha512", $username), time() + 24 * 60 * 60, "/");
		setcookie("c_salt", $salt, time() + 24 * 60 * 60, "/");
		die("Your account has been created and you are now loggeed in.");
	}
}
?>

<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
	<body style='font-family: verdana, sans-serif;'>
		<div style='width: 80%; padding: 10px; border: 1px solid #e3e3e3; background-color: #fff; color: #000; margin-left: 10'>
			<h1>Register</h1>
			<br />
			<form action='' method='post'>
				<table>
					<tr>
						<td>
							<b>Username:</b>
						</td>
						<td>
							<input type='text' name='username' style='padding: 4px;' />
						</td>
					</tr>
					<tr>
						<td>
							<b>Password:</b>
						</td>
						<td>
							<input type='password' name='password' style='padding: 4px;' />
						</td>
					</tr>
					<tr>
						<td>
							<b>Email:</b>
						</td>
						<td>
							<input type='email' name='email' style='padding: 4px;' />
						</td>
					</tr>
					<tr>
						<td>
							<input type='submit' value='Register' name='register' />
						</td>
					</tr>
				</table>
			</form>
			<br />
			<h6>
				No account? <a href='register.php'>Register!</a>
			</h6>
		</div>
	</body>
</html>


