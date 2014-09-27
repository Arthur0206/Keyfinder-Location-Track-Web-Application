<?php
include "devDataParser.php";

$request_body = file_get_contents('php://input');
$jsonObj = json_decode($request_body, true);

if ($response["success"] == 0) {
    logSysErrMsg($response["message"]);
}

// reply back to the phone
echo json_encode(devDataParser($jsonObj));
?>
