<?php
$db_server = "localhost";
$db_username = "root";
$db_password = "";
$db_database = "acadive";
$conn = mysqli_connect($db_server,$db_username,$db_password,$db_database);
if (!$conn) {
    echo "Not connected to database";
}
?>