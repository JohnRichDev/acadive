<?php
include("../database/connection.php");
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = isset($_POST["student_id"]) ? mysqli_real_escape_string($conn, $_POST["student_id"]) : null;
    
    if (!$student_id) {
        $_SESSION["error"] = "Error: Student ID is missing.";
        header("Location: ../index.php?section=students");
        exit();
    }    $checkQuery = "SELECT student_no FROM students WHERE id = '$student_id'";
    $result = mysqli_query($conn, $checkQuery);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $student_data = mysqli_fetch_assoc($result);
        $student_no = $student_data['student_no'];
        
        $deleteQuery = "DELETE FROM students WHERE id = '$student_id'";
        
        if (mysqli_query($conn, $deleteQuery)) {
            if (isset($_SESSION['user_id'])) {
                $adviser_id = $_SESSION['user_id'];
                $uploadFileDir = '../img/student_1x1/';
                $clean_student_no = str_replace('-', '', $student_no);
                $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                foreach ($extensions as $ext) {
                    $imagePath = $uploadFileDir . $adviser_id . '_' . $clean_student_no . '.' . $ext;
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
            }
            
            $_SESSION["success"] = "Student record deleted successfully.";
        } else {
            $_SESSION["error"] = "Error deleting student record: " . mysqli_error($conn);
        }
    } else {
        $_SESSION["error"] = "Student record not found.";
    }
    
    header("Location: ../index.php?section=students");
    exit();
} else {
    $_SESSION["error"] = "Invalid request method.";
    header("Location: ../index.php?section=students");
    exit();
}
?>
