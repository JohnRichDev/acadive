<?php
// include("database.php");
$username = $_POST["username"];
$password = $_POST["password"];
$confirm_password = $_POST["confirm_password"];
if ($password !== $confirm_password) {
    echo "Passwords do not match!";
    exit;
}
echo $username . "<br>";
echo $password . "<br>";
?>