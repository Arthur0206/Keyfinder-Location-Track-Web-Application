<?php
$connection = mysql_connect("localhost", "root", "sosoman1984") or die("Couldn't connect to the server!");
mysql_select_db("location_service", $connection) or die("Couldn't connect to the database!");
?>