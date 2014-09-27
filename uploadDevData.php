<?php

function clearPostRedirectDie() {
    // clear $_POST, redirect and die
    unset($_POST);
    header("Location: ./uploadDevData.html");
    die;
}

include "devDataParser.php";

$response = devDataParser($_POST);
if ($response["success"] == 0) {
    logSysErrMsg($response["message"]);
    clearPostRedirectDie();
    echo $response["message"];
} else {
    echo "success post";
}

?>
