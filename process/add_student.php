<?php
// check if the user is logged in
include("../database/connection.php");
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit();
}

// check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // get the form data    $student_no = $_POST["student_no"];
    $student_no = $_POST["student_no"];
    $academic_status = $_POST["academic_status"];
    $last_name = $_POST["last_name"];
    $first_name = $_POST["first_name"];
    $mi = $_POST["mi"];
    $gender = $_POST["gender"];
    $birthday = $_POST["birthday"];
    $year_level = $_POST["year_level"];
    $section = $_POST["section"];
    $academic = $_POST["academic_year"];
    $semester = $_POST["semester"];
    $student_classification = $_POST["student_classification"];
    $address = $_POST["address"];
    $city = $_POST["city"];
    $province = $_POST["province"];

    // print all
    //echo "Student No: $student_no<br>";
    //echo "Academic Status: $academic_status<br>";
    //echo "Last Name: $last_name<br>";
    //echo "First Name: $first_name<br>";
    //echo "Middle Initial: $mi<br>";
    //echo "Gender: $gender<br>";
    //echo "Birthday: $birthday<br>";
    //echo "Year Level: $year_level<br>";
    //echo "Section: $section<br>";
    //echo "Academic: $academic<br>";
    //echo "Semester: $semester<br>";
    //echo "Student Classification: $student_classification<br>";
    //echo "Address: $address<br>";
    //echo "City: $city<br>";
    //echo "Province: $province<br>";

    // Prepare the SQL query
    $query = "INSERT INTO students (
         student_no, academic_status, last_name, first_name, mi, 
         gender, birthday, year_level, section, academic, 
         semester, student_classification, address, city, province
     ) VALUES (
         '$student_no', '$academic_status', '$last_name', '$first_name', '$mi',
         '$gender', '$birthday', $year_level, '$section', '$academic',
         '$semester', '$student_classification', '$address', '$city', '$province'
     )";

    // Execute the query
    if (mysqli_query($conn, $query)) {
        // Redirect back to students page with success message
        $_SESSION["success"] = "Student added successfully";
        header("Location: ../index.php?section=students");
        //echo "Student added successfully";
    } else {
        // Redirect back with error message
        $_SESSION["error"] = "Error adding student: " . mysqli_error($conn);
        header("Location: ../index.php?section=students");
        //echo "Error: " . mysqli_error($conn);
    }
    exit();
}
?>