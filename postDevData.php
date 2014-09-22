<?php

// include db connect class
require_once 'db_connect.php';
// connecting to db
$db = new DB_CONNECT();

function logSysErrMsg($msg) {
	// print error message and _POST[] to syslog/error.txt
	$error_msg = $msg.': '.$_POST['username'].' '.$_POST['password'].' '.$_POST['devid']
	.' '.$_POST['opcode'].' '.$_POST['devtype'].' '.$_POST['latitude'].' '.$_POST['longtitude'].' '.$_POST['datetime']."\n";
	file_put_contents('syslog/error.txt', $error_msg, FILE_APPEND);
}

function clearPostRedirectDie() {
    // clear $_POST, redirect and die
    unset($_POST);
    header("Location: ./postDevData.php");
    die;
}

function isValidDateTime($dateTime)
{
    if (preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $dateTime, $matches)) {
        if (checkdate($matches[2], $matches[3], $matches[1])) {
            return true;
        }
    }
    return false;
}

function checkAndCreateFile($dirPath, $fileName) {
	// if dir doesn't exist, make it
	if (!is_dir($dirPath)) {
		mkdir($dirPath);
		logSysErrMsg("create $dirPath in checkAndCreateFile()");
	}
	
	$newFile = $dirPath."/".$fileName;
	if ($fileName != null && !file_exists($newFile)) {
		fopen($newFile, 'w') or die("can't open file");
		// Read and write for owner, read for everybody else
		chmod($newFile, 0644);
		logSysErrMsg("create $newFile in checkAndCreateFile()");
	} 
}

// check if all form data are submited, else output error message
if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['devid']) && isset($_POST['opcode'])) {
	$username = mysql_real_escape_string($_POST['username']);
	$password = mysql_real_escape_string(hash("sha512", $_POST['password']));
	$user = mysql_fetch_array(mysql_query("SELECT * FROM `UserTable` WHERE `Username`='$username'"));
	// check user name
	if ($user == '0') {
		logSysErrMsg("postDevData.php - username doesn't exist");
		clearPostRedirectDie();
	}
	// check password
	if ($user['Password'] != $password) {
		logSysErrMsg("postDevData.php - incorrect password");
		clearPostRedirectDie();
	}
	
    // if form fields are empty, outputs message, else, gets their data
    if($_POST['opcode'] == '0') {
        // add device operation
		
		// if missing devtype input
		if ($_POST['devtype'] == '') {
			logSysErrMsg("postDevData.php - missing devtype for add device operation");
			clearPostRedirectDie();
		}
		
		// create dir users/tester/140aaab0a9 for new device
		checkAndCreateFile("users/".$_POST['username']."/".$_POST['devid'], null);
		
		// create dev_list.txt if not there
		checkAndCreateFile("users/".$_POST['username'], "dev_list.txt");
		// use $arr_data to stores the json decoded data
		$arr_data = array(); 
		// gets json-data from dev_list.txt file
		$jsondata = file_get_contents("users/".$_POST['username'].'/'."dev_list.txt");
		// converts json string into array
		$arr_data = json_decode($jsondata, true);
		
		// if the newly added device is duplicated, ignore it
		for($i = 0, $size = count($arr_data); $i < $size; ++$i) {
			if ($arr_data[$i]['devid'] == $_POST['devid']) {
				logSysErrMsg("postDevData.php - devid already exist for add new device operation");
				clearPostRedirectDie();
			}
		}
		
		// add a line for the new device to the dev_list.txt jason file
		$now = date("Y-m-d H:i:s"); 
		$formdata = array(
			'devid'=> $_POST['devid'],
			'devtype'=> $_POST['devtype'],
			'devdescript'=> $_POST['devdescript'],
			'lasttime'=> $now,
		);
		// appends the array with new form data
		$arr_data[] = $formdata;
		// encodes the array into a string in JSON format (JSON_PRETTY_PRINT - uses whitespace in json-string, for human readable)
		$jsondata = json_encode($arr_data);
                // saves the json string in "dev_list.txt"
		if (file_put_contents("users/".$_POST['username']."/dev_list.txt", $jsondata)) {
                    echo $jsondata;
			echo 'Data successfully saved';
		} else {
                    // outputs error message if data cannot be saved
                    echo $jsondata;
			logSysErrMsg("postDevData.php - can't add new dev to dev_list.txt");
			clearPostRedirectDie();
		}
    } else if ($_POST['opcode'] == '1') {
		// add location data operation
		
		// if missing latitude/longtitude/datetime input
		if (!isset($_POST['latitude']) || !isset($_POST['longtitude']) || !isset($_POST['datetime'])) {
			logSysErrMsg("postDevData.php - no latitude/longtitude/datetime for adding location data");
			clearPostRedirectDie();
		}
		// if invalid datetime
		if (!isValidDateTime($_POST['datetime'])) {
			logSysErrMsg("postDevData.php - invalid datetime");
			clearPostRedirectDie();
		}
		
		// gets json-data from dev_list.txt file and check if device id is in there
		$arr_data = array();
		$jsondata = file_get_contents("users/".$_POST['username'].'/'."dev_list.txt");
		$arr_data = json_decode($jsondata, true);
		$idx = 0; $size = count($arr_data); $devtype = 0; $lasttime = '';
		for($i = 0; $i < $size; ++$i) {
			if ($arr_data[$i]['devid'] == $_POST['devid']) {
				$devtype = $arr_data[$i]['devtype'];
				$lasttime = $arr_data[$i]['lasttime'];
				$idx = $i;
			}
		}
		// if device not exist or device type is not keyfinder
		if ($idx == $size || $devtype != 0) {
			logSysErrMsg("postDevData.php - device not exist or dev type is not keyfinder");
			clearPostRedirectDie();
		}

		$devid_path = "users/".$_POST['username']."/".$_POST['devid'];
		// if dir doesn't exist, create dir users/username/deviceID for new device
		checkAndCreateFile($devid_path, null);

		// check if $_POST['datetime'] is bigger than lasttime and smaller than tomorrow (today + 86400 sec)
		// theoretically datetime shouldn't be larger than now, but currently we allow 1 day time shift (due to smart phone's inaccurate time setting)
		$now = date("Y-m-d H:i:s");
		if (strtotime($_POST['datetime']) < strtotime($lasttime) || strtotime($_POST['datetime']) > strtotime($now) + 86400) {
			logSysErrMsg("postDevData.php - datetime smaller than lasttime or bigger than tomorrow (now + 86400 sec)");
			clearPostRedirectDie();
		}

		// open or create the file of the day corresponding to $_POST['datetime']
		$date_and_time = explode(" ", $_POST['datetime']);
		checkAndCreateFile($devid_path, $date_and_time[0].'.txt');
		// append the current latitude/longtitude/datetime to the last line
		file_put_contents($devid_path."/".$date_and_time[0].'.txt', $_POST['latitude'].' '.$_POST['longtitude'].' '.$_POST['datetime']."\n", FILE_APPEND);
		
		// update lasttime in dev_list.txt
		$arr_data[$idx]['lasttime'] = $_POST['datetime'];
		// encodes the array into a string in JSON format, and saves the json string in "dev_list.txt"
		$jsondata = json_encode($arr_data);
		if (file_put_contents("users/".$_POST['username']."/dev_list.txt", $jsondata)) {
                    echo $jsondata;
			echo 'Data successfully saved';
		} else {
                    echo $jsondata;
			// outputs error message if data cannot be saved
			logSysErrMsg("postDevData.php - can't add new dev to dev_list.txt");
			clearPostRedirectDie();
		}
		
		// in the future, we might want to remove a file if it is already 365 ago 
    }
}
?>

<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
	<form action='' method='post'>
		<div><b>*username:</b><input type="text" name="username"/></div>
		<div><b>*password:</b><input type="password" name="password"/></div>
		<div><b>*device id:</b><input type="text" name="devid"/></div>
		<div><b>*opcode:</b><input type="text" name="opcode"/></div>            <!-- 0: add device, 1: location service-->
		<div><b>device type:</b><input type="text" name="devtype"/></div>
		<div><b>device description:</b><input type="text" name="devdescript"/></div>
		<div><b>latitude:</b><input type="text" name="latitude"/></div>
		<div><b>longtitude:</b><input type="text" name="longtitude"/></div>
		<div><b>datetime:</b><input type="text" name="datetime"/></div>
		<div><input type='submit' value='Post Device Data' name='postDevData' /></div>
	</form>
</html>
