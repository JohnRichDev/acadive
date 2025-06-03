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
    }    if ($password == $row["password"]) {
        $_SESSION["username"] = $username;
        $_SESSION["user_id"] = $row["id"];
        header("Location: ../index.php");
        exit;
    }

    $query = "UPDATE users SET tries = tries + 1 WHERE username='$username'";
    mysqli_query($conn, $query);

    if (++$row["tries"] >= 3) {
        $query = "UPDATE users SET tries = 0 WHERE username='$username'";
        mysqli_query($conn, $query);
        $query = "UPDATE users SET locked = 'Y' WHERE username='$username'";
        mysqli_query($conn, $query);
        $_SESSION["error"] = "Your account is locked!";
        header("Location: ../login.php");
    } else {
        $remaining_tries = 3 - $row["tries"];
        if ($remaining_tries == 1) {
            $_SESSION["error"] = "Incorrect password! You have $remaining_tries try left.";
        } else {
            $_SESSION["error"] = "Incorrect password! You have $remaining_tries tries left.";
        }
        header("Location: ../login.php");
    }
} else {
    $_SESSION["error"] = "Username not found!";
    header("Location: ../login.php");
}
exit;
?>