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
    $password = $_POST["confirm_password"];

    $query = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        session_start();
        $_SESSION["error"] = "Username already exists";
        header("Location: ../register.php");
        exit;
    } else {
        if ($password != $_POST["confirm_password"]) {
            session_start();
            $_SESSION["error"] = "Passwords do not match";
            header("Location: ../register.php");
            exit;
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (username, password) VALUES ('$username', '$hashed_password')";
            if (mysqli_query($conn, $query)) {
                session_start();
                // $_SESSION["success"] = "Registration successful! You can now log in.";
                $_SESSION["username"] = $username;
                header("Location: ../index.php");
                exit;
            } else {
                session_start();
                $_SESSION["error"] = "Error: " . mysqli_error($conn);
                header("Location: ../register.php");
                exit;
            }
        }
    }
}
?>