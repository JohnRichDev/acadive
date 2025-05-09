<?php
session_start();
if (isset($_SESSION["username"])) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    include("../database/connection.php");
    $username = $_POST["username"];
    $password = $_POST["password"];

    $query = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION["error"] = "Username already exists";
        header("Location: ../register.php");
        exit;
    }

    if ($password != $_POST["confirm_password"]) {
        $_SESSION["error"] = "Passwords do not match";
        header("Location: ../register.php");
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $query = "INSERT INTO users (username, password) VALUES ('$username', '$hashed_password')";

    if (mysqli_query($conn, $query)) {
        $_SESSION["username"] = $username;
        // $_SESSION["success"] = "Registration successful! You can now log in.";
        header("Location: ../index.php");
        exit;
    } else {
        $_SESSION["error"] = "Error: " . mysqli_error($conn);
        header("Location: ../register.php");
        exit;
    }
}
?>