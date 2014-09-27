<?php

function logSysErrMsg($msg) {
    // print error message to syslog/error.txt
    $nowtime = date("Y-m-d H:i:s");
    $error_msg = "$nowtime  $msg \n";
    //$error_msg = $nowtime.'  '.$msg.': '.$_POST['username'].' '.$_POST['password'].' '.$_POST['devid']
    //    .' '.$_POST['opcode'].' '.$_POST['devtype'].' '.$_POST['latitude'].' '.$_POST['longtitude'].' '.$_POST['datetime']."\n";
    file_put_contents('syslog/error.txt', $error_msg, FILE_APPEND);
}

function isValidDateTime($dateTime) {
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

function devDataParser($body) {
    // include db connect class
    require_once 'db_connect.php';
    // connecting to db
    $db = new DB_CONNECT();
    
    // array for JSON response
    $response = array();
    $response["success"] = 0;
    $response["message"] = " ";

    // check if all form data are submited, else output error message
    if (isset($body['username']) && isset($body['password']) && isset($body['devid']) && isset($body['opcode'])) {
    	$username = mysql_real_escape_string($body['username']);
    	$password = mysql_real_escape_string(hash("sha512", $body['password']));
    	$user = mysql_fetch_array(mysql_query("SELECT * FROM `UserTable` WHERE `Username`='$username'"));
    	// check user name
        if ($user == '0') {
            $response["message"] = "postDevData.php - username doesn't exist";
            return $response;
    	}
    	// check password
    	if ($user['Password'] != $password) {
            $response["message"] = "postDevData.php - incorrect password";
            return $response;
    	}
    	
        // if form fields are empty, outputs message, else, gets their data
        if($body['opcode'] == '0') {
            // add device operation
    		
    	    // if missing devtype input
    	    if ($body['devtype'] == '') {
                $response["message"] = "postDevData.php - missing devtype for add device operation";
                return $response;
            }
    		
            // create dir users/tester/140aaab0a9 for new device
            checkAndCreateFile("users/".$body['username']."/".$body['devid'], null);
    		
            // create dev_list.txt if not there
            checkAndCreateFile("users/".$body['username'], "dev_list.txt");
            // use $arr_data to stores the json decoded data
            $arr_data = array(); 
            // gets json-data from dev_list.txt file
            $jsondata = file_get_contents("users/".$body['username'].'/'."dev_list.txt");
            // converts json string into array
            $arr_data = json_decode($jsondata, true);

            // if the newly added device is duplicated, ignore it
            for($i = 0, $size = count($arr_data); $i < $size; ++$i) {
                if ($arr_data[$i]['devid'] == $body['devid']) {
                    $response["message"] = "postDevData.php - devid already exist for add new device operation";
                    return $response;
                }
            }
    		
            // add a line for the new device to the dev_list.txt jason file
            $now = date("Y-m-d H:i:s"); 
            $formdata = array(
                'devid'=> $body['devid'],
                'devtype'=> $body['devtype'],
                'devdescript'=> $body['devdescript'],
                'lasttime'=> $now,
            );
            // appends the array with new form data
            $arr_data[] = $formdata;
            // encodes the array into a string in JSON format (JSON_PRETTY_PRINT - uses whitespace in json-string, for human readable)
            $jsondata = json_encode($arr_data);
            // saves the json string in "dev_list.txt"
            if (file_put_contents("users/".$body['username']."/dev_list.txt", $jsondata)) {
                $response["success"] = 1;
                $response["message"] = "postDevData.php - data success added";
                return $response;
            } else {
                // outputs error message if data cannot be saved
                $response["message"] = "postDevData.php - can't add new dev to dev_list.txt";
                return $response;
            }
        } else if ($body['opcode'] == '1') {
            // add location data operation
    		
            // if missing latitude/longtitude/datetime input
            if (!isset($body['latitude']) || !isset($body['longtitude']) || !isset($body['datetime'])) {
                $response["message"] = "postDevData.php - no latitude/longtitude/datetime for adding location data";
                return $response;
            }
            
            // if invalid datetime
            if (!isValidDateTime($body['datetime'])) {
                $response["message"] = "postDevData.php - invalid datetime";
                return $response;
            }
    		
            // gets json-data from dev_list.txt file and check if device id is in there
            $arr_data = array();
            $jsondata = file_get_contents("users/".$body['username'].'/'."dev_list.txt");
            $arr_data = json_decode($jsondata, true);
            $idx = 0; $size = count($arr_data); $devtype = 0; $lasttime = '';
            for($i = 0; $i < $size; ++$i) {
                if ($arr_data[$i]['devid'] == $body['devid']) {
                    $devtype = $arr_data[$i]['devtype'];
                    $lasttime = $arr_data[$i]['lasttime'];
                    $idx = $i;
                }
            }
            // if device not exist or device type is not keyfinder
            if ($idx == $size || $devtype != 0) {
                $response["message"] = "postDevData.php - device not exist or dev type is not keyfinder";
                return $response;
            }
    
            $devid_path = "users/".$body['username']."/".$body['devid'];
            // if dir doesn't exist, create dir users/username/deviceID for new device
            checkAndCreateFile($devid_path, null);
    
            // check if $body['datetime'] is bigger than lasttime and smaller than tomorrow (today + 86400 sec)
            // theoretically datetime shouldn't be larger than now, but currently we allow 1 day time shift (due to smart phone's inaccurate time setting)
            $now = date("Y-m-d H:i:s");
            if (strtotime($body['datetime']) < strtotime($lasttime) || strtotime($body['datetime']) > strtotime($now) + 86400) {
                $response["message"] = "postDevData.php - datetime smaller than lasttime or bigger than tomorrow (now + 86400 sec)";
                return $response;
            }

            // open or create the file of the day corresponding to $body['datetime']
            $date_and_time = explode(" ", $body['datetime']);
            checkAndCreateFile($devid_path, $date_and_time[0].'.txt');
            // append the current latitude/longtitude/datetime to the last line
            file_put_contents($devid_path."/".$date_and_time[0].'.txt', $body['latitude'].' '.$body['longtitude'].' '.$body['datetime']."\n", FILE_APPEND);

            // update lasttime in dev_list.txt
            $arr_data[$idx]['lasttime'] = $body['datetime'];
            // encodes the array into a string in JSON format, and saves the json string in "dev_list.txt"
            $jsondata = json_encode($arr_data);
            if (file_put_contents("users/".$body['username']."/dev_list.txt", $jsondata)) {
                $response["success"] = 1;
                $response["message"] = "postDevData.php - data success added";
                return $response;
            } else {
                // outputs error message if data cannot be saved
                $response["message"] = "postDevData.php - can't add new dev to dev_list.txt";
                return $response;
            }
            // in the future, we might want to remove a file if it is already 365 ago 
        } else {
            $response["message"] = "postDevData.php - opcode != 0 or 1";
            return $response;
        }
    } else {
        $response["message"] = "postDevData.php - body is not fufill";
        return $response;
    }
}

?>
