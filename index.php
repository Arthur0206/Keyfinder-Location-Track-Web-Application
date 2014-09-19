<?php

// include db connect class
require_once 'db_connect.php';
// connecting to db
$db = new DB_CONNECT();

error_reporting(0);

if (isset($_POST['login'])) {
	if (isset($_POST['username']) && isset($_POST['password'])) {
		$username = mysql_real_escape_string($_POST['username']);
		$password = mysql_real_escape_string(hash("sha512", $_POST['password']));
		$user = mysql_fetch_array(mysql_query("SELECT * FROM UserTable WHERE Username='$username'"));
		if ($user == '0') {
			die("The username <i>$username</i> doesn't exist! <a href='index.php'>&larr; Back</a>");
		}
		if ($user['Password'] != $password) {
			die("Incorrect password! <a href='index.php'>&larr; Back</a>");
		}
		// salt is changed and stored into database everytime when the user is successfully logged in
		$salt = hash("sha512", rand() . rand() . rand());
		setcookie("c_user", hash("sha512", $username), time() + 24 * 60 * 60, "/");
		setcookie("c_salt", $salt, time() + 24 * 60 * 60, "/");
		$userID = $user['ID'];
		mysql_query("UPDATE `UserTable` SET `Salt`='$salt' WHERE `ID`='$userID'");
        header("Location: ./userMain.php");
	}
}

// algor.php checks if there is a user which has salt that matches the one in client browser's cookie
include "algor.php";

if ($logged == true) {
	// var_dump($csalt);
	header("Location: ./userMain.php");
}
?>

<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
	<head>
		<link type="text/css" rel="stylesheet" href="css/style.css" />
	</head>
	<body style='font-family: verdana, sans-serif;'>
		<div style='margin-left:auto;margin-right:auto;width:320px;margin-top:12%;font-size:17px'>
			<h1>Login</h1>
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
							<input type='submit' value='Login' name='login' style="margin-top:10px;font-size:16px;font-weight:bold;padding:6px 10px 6px 10px"/>
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


