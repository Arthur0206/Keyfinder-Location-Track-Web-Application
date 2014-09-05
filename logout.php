<?php
include "algor.php";

session_destroy();
$logged = false;
setcookie("c_salt", "", time()-3600, "/");
setcookie("c_user", "", time()-3600, "/");
header('Location: index.php');exit();
return true;
?>