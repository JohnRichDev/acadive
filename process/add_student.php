<?php
include("../database/connection.php");
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit();
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {

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
    $adviser_id = null;
    if (isset($_SESSION['user_id'])) {
        $adviser_id = $_SESSION['user_id'];
    }

    $profile_image = null;
    $new_student_id = null;
      $query = "INSERT INTO students (
         student_no, academic_status, last_name, first_name, mi,
         sex, birthday, year_level, section, academic, 
         semester, student_classification, address, city, province, advisor_id
     ) VALUES (
         '$student_no', '$academic_status', '$last_name', '$first_name', '$mi',
         '$gender', '$birthday', $year_level, '$section', '$academic',
         '$semester', '$student_classification', '$address', '$city', '$province', " . ($adviser_id ? $adviser_id : "NULL") . "
     )";
    if (mysqli_query($conn, $query)) {
        $new_student_id = mysqli_insert_id($conn);
        
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile_image']['tmp_name'];
            $fileName = $_FILES['profile_image']['name'];
            $fileNameCmps = explode('.', $fileName);
            $fileExtension = strtolower(end($fileNameCmps));
            $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($fileExtension, $allowedfileExtensions)) {
                $uploadFileDir = '../img/student_1x1/';
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0777, true);
                }
                $newFileName = $adviser_id . '_' . $student_no . '.' . $fileExtension;
                $dest_path = $uploadFileDir . $newFileName;
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $profile_image = 'img/student_1x1/' . $newFileName;
                }
            }
        }
        $_SESSION["success"] = "Student added successfully";
        header("Location: ../index.php?section=students");
    } else {
        $_SESSION["error"] = "Error adding student: " . mysqli_error($conn);
        header("Location: ../index.php?section=students");
    }
    exit();
}
?>