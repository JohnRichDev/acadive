<?php
include("../database/connection.php");
session_start();

$username = $_POST["username"];
$password = $_POST["password"];

$query = "SELECT * FROM users WHERE username='$username'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);

    if ($row["locked"] == "Y") {
        $_SESSION["error"] = "Your account is locked!";
        header("Location: ../login.php");
        exit;
    }

    if (password_verify($password, $row["password"])) {
        $_SESSION["username"] = $username;
        header("Location: ../index.php");
        exit;
    }

    $_SESSION["error"] = "Incorrect password!";
    header("Location: ../login.php");

    $query = "UPDATE users SET tries = tries + 1 WHERE username='$username'";
    mysqli_query($conn, $query);

    if (++$row["tries"] >= 3) {
        $query = "UPDATE users SET locked = 'Y' WHERE username='$username'";
        mysqli_query($conn, $query);
        $_SESSION["error"] = "Your account is locked!";
        header("Location: ../login.php");
    }
} else {
    $_SESSION["error"] = "Username not found!";
    header("Location: ../login.php");
}
exit;
?>