<?php
// acadive/process/edit_student.php
include("../database/connection.php");
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = isset($_POST["student_id"]) ? mysqli_real_escape_string($conn, $_POST["student_id"]) : null;
    $student_no = mysqli_real_escape_string($conn, $_POST["student_no"]);
    $academic_status = mysqli_real_escape_string($conn, $_POST["academic_status"]);
    $last_name = mysqli_real_escape_string($conn, $_POST["last_name"]);
    $first_name = mysqli_real_escape_string($conn, $_POST["first_name"]);
    $mi = mysqli_real_escape_string($conn, $_POST["mi"]);
    $gender = mysqli_real_escape_string($conn, $_POST["gender"]);
    $birthday = mysqli_real_escape_string($conn, $_POST["birthday"]);
    $year_level = mysqli_real_escape_string($conn, $_POST["year_level"]);
    $section_code = mysqli_real_escape_string($conn, $_POST["section"]);
    $academic = mysqli_real_escape_string($conn, $_POST["academic_year"]);
    $semester = mysqli_real_escape_string($conn, $_POST["semester"]);
    $student_classification = mysqli_real_escape_string($conn, $_POST["student_classification"]);
    $address = mysqli_real_escape_string($conn, $_POST["address"]);
    $city = mysqli_real_escape_string($conn, $_POST["city"]);
    $province = mysqli_real_escape_string($conn, $_POST["province"]);

    if (!$student_id) {
        $_SESSION["error"] = "Error: Student ID is missing.";
        header("Location: ../index.php?section=students");
        exit();
    }


    $query = "UPDATE students SET 
                student_no = '$student_no', 
                academic_status = '$academic_status', 
                last_name = '$last_name', 
                first_name = '$first_name', 
                mi = '$mi',
                gender = '$gender', 
                birthday = '$birthday', 
                year_level = '$year_level', 
                section = '$section_code', 
                academic = '$academic', 
                semester = '$semester', 
                student_classification = '$student_classification', 
                address = '$address', 
                city = '$city', 
                province = '$province'
            WHERE id = '$student_id'";


    if (mysqli_query($conn, $query)) {
        $_SESSION["success"] = "Student record updated successfully.";
        header("Location: ../index.php?section=students");
    } else {
        $_SESSION["error"] = "Error updating student record: " . mysqli_error($conn);
        header("Location: ../index.php?section=students&showModal=editStudent&student_id=" . $student_id);
    }
    exit();
} else {
    $_SESSION["error"] = "Invalid request method.";
    header("Location: ../index.php?section=students");
    exit();
}
