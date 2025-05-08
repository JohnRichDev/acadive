<?php
include("../database/connection.php");
session_start();
$username = $_POST["username"];
$password = $_POST["password"];
$query = "SELECT * FROM users WHERE username='$username'";
$result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    if (password_verify($password, $row["password"])) {
        $_SESSION["username"] = $username;
        header("Location: ../index.php");
        exit;
    } else {
        $_SESSION["error"] = "Invalid password";
        header("Location: ../login.php");
        exit;
    }
} else {
    $_SESSION["error"] = "Username not found";
    header("Location: ../login.php");
    exit;
}
?>