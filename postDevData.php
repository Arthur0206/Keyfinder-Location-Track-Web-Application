<?php
include "connection.php";
error_reporting(0);

function logSysErrMsg($msg) {
	// print error message and _POST[] to syslog/error.txt
	$error_msg = $msg.': '.$_POST['username'].' '.$_POST['password'].' '.$_POST['devid']
	.' '.$_POST['opcode'].' '.$_POST['devtype'].' '.$_POST['longtitude'].' '.$_POST['latitude'].' '.$_POST['date'].' '.$_POST['time']."\n";
	file_put_contents('syslog/error.txt', $error_msg, FILE_APPEND);
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
	$user = mysql_fetch_array(mysql_query("SELECT * FROM `user` WHERE `Username`='$username'"));
	// check user name
	if ($user == '0') {
		logSysErrMsg("postDevData.php - username doesn't exist");
		unset($_POST);
        header("Location: ./postDevData.php");
		die;
	}
	// check password
	if ($user['Password'] != $password) {
		logSysErrMsg("postDevData.php - incorrect password");
		unset($_POST);
        header("Location: ./postDevData.php");
		die;
	}
	
    // if form fields are empty, outputs message, else, gets their data
    if($_POST['opcode'] == '0') {
        // add device operation
		
		// if missing devtype input
		if ($_POST['devtype'] == '') {
			logSysErrMsg("postDevData.php - missing devtype for add device operation");
			unset($_POST);
			header("Location: ./postDevData.php");
			die;
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
				unset($_POST);
				header("Location: ./postDevData.php");
				die;
			}
		}
		
		// add a line for the new device to the dev_list.txt jason file
		$formdata = array(
			'devid'=> $_POST['devid'],
			'devtype'=> $_POST['devtype'],
			'devdescript'=> $_POST['devdescript'],
			'lastdate'=> '',
		);
		// appends the array with new form data
		$arr_data[] = $formdata;
		// encodes the array into a string in JSON format (JSON_PRETTY_PRINT - uses whitespace in json-string, for human readable)
		$jsondata = json_encode($arr_data, JSON_PRETTY_PRINT);
		
		// saves the json string in "dev_list.txt"
		if (file_put_contents("users/".$_POST['username']."/dev_list.txt", $jsondata)) {
			echo 'Data successfully saved';
		} else {
			// outputs error message if data cannot be saved
			logSysErrMsg("postDevData.php - can't add new dev to dev_list.txt");
			unset($_POST);
			header("Location: ./postDevData.php");
			die;
		}
    } else if ($_POST['opcode'] == '1') {
		// add location data operation
		
		// if missing longtitude/latitude/datetime input
		if (!isset($_POST['longtitude']) || !isset($_POST['latitude']) || !isset($_POST['datetime'])) {
			logSysErrMsg("postDevData.php - no longtitude/latitude/datetime for adding location data");
			unset($_POST);
			header("Location: ./postDevData.php");
			die;
		}
		// if invalid datetime
		if (!isValidDateTime($_POST['datetime'])) {
			logSysErrMsg("postDevData.php - invalid datetime");
			unset($_POST);
			header("Location: ./postDevData.php");
			die;
		}
		
		// gets json-data from dev_list.txt file and check if device id is in there
		$arr_data = array(); 
		$jsondata = file_get_contents("users/".$_POST['username'].'/'."dev_list.txt");
		$arr_data = json_decode($jsondata, true);
		$i = 0, $size = count($arr_data), $devtype = 0, $lastdate = '';
		for(; $i < $size; ++$i) {
			if ($arr_data[$i]['devid'] == $_POST['devid']) {
				$devtype = $arr_data[$i]['devtype'];
				$lastdate = $arr_data[$i]['lastdate'];
			}
		}
		// if device not exist or device type is not keyfinder
		if ($i == $size || $devtype != 0) {
			logSysErrMsg("postDevData.php - device not exist or dev type is not keyfinder");
			unset($_POST);
			header("Location: ./postDevData.php");
			die;
		}
		// if dir doesn't exist, create dir users/tester/140aaab0a9 for new device
		checkAndCreateFile("users/".$_POST['username']."/".$_POST['devid'], null);
/*
		$line = 
		file_put_contents("users/$_POST['username']"."/".$image, $contents_data);

        // adds form data into an array
        $formdata = array(
            'youname'=> $_POST['youname'],
            'youemail'=> $_POST['youemail'],
            'studies'=> $_POST['studies'],
            'civilstate'=> $_POST['civilstate']
        );
        
        // encodes the array into a string in JSON format (JSON_PRETTY_PRINT - uses whitespace in json-string, for human readable)
        $jsondata = json_encode($formdata, JSON_PRETTY_PRINT);
        
        // saves the json string in "formdata.txt" (in "dirdata" folder)
        // outputs error message if data cannot be saved
        if(file_put_contents('dirdata/formdata.txt', $jsondata)) echo 'Data successfully saved';
        else echo 'Unable to save data in "dirdata/formdata.txt"';
*/
    }
}
?>

<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
	<form action='' method='post'>
		<b>*username:</b><input type="text" name="username"/>
		<b>*password:</b><input type="password" name="password"/>
		<b>*device id:</b><input type="text" name="devid"/>
		<b>*opcode:</b><input type="text" name="opcode"/>               <!-- 0: add device, 1: location service-->
		<b>device type:</b><input type="text" name="devtype"/>
		<b>device description:</b><input type="text" name="devdescript"/>
		<b>longtitude:</b><input type="text" name="longtitude"/>
		<b>latitude:</b><input type="text" name="latitude"/>
		<b>datetime:</b><input type="text" name="datetime"/>
		<input type='submit' value='Post Device Data' name='postDevData' />
	</form>
</html>