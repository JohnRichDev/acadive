<?php
include("../database/connection.php");
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = isset($_POST["student_id"]) ? mysqli_real_escape_string($conn, $_POST["student_id"]) : null;
    $student_no = mysqli_real_escape_string($conn, $_POST["student_no"]);
    $academic_status = mysqli_real_escape_string($conn, $_POST["academic_status"]);    $last_name = mysqli_real_escape_string($conn, $_POST["last_name"]);
    $first_name = mysqli_real_escape_string($conn, $_POST["first_name"]);
    $mi = mysqli_real_escape_string($conn, $_POST["mi"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
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
    $advisor_id = null;
    if (isset($_SESSION['user_id'])) {
        $advisor_id = $_SESSION['user_id'];
    }    if (!$student_id) {
        $_SESSION["error"] = "Error: Student ID is missing.";
        
        // Build redirect URL with preserved filters
        $redirectUrl = "../index.php?section=students";
        $filterParams = [];
        
        if (isset($_POST['filter_search']) && !empty($_POST['filter_search'])) {
            $filterParams[] = "search=" . urlencode($_POST['filter_search']);
        }
        if (isset($_POST['filter_academic_year']) && !empty($_POST['filter_academic_year'])) {
            $filterParams[] = "academic_year=" . urlencode($_POST['filter_academic_year']);
        }
        if (isset($_POST['filter_semester']) && !empty($_POST['filter_semester'])) {
            $filterParams[] = "semester=" . urlencode($_POST['filter_semester']);
        }
        if (isset($_POST['filter_sort']) && !empty($_POST['filter_sort'])) {
            $filterParams[] = "sort=" . urlencode($_POST['filter_sort']);
        }
        if (isset($_POST['filter_limit']) && !empty($_POST['filter_limit'])) {
            $filterParams[] = "limit=" . urlencode($_POST['filter_limit']);
        }
        if (isset($_POST['filter_page']) && !empty($_POST['filter_page'])) {
            $filterParams[] = "page=" . urlencode($_POST['filter_page']);
        }
        
        if (!empty($filterParams)) {
            $redirectUrl .= "&" . implode("&", $filterParams);
        }
        
        header("Location: " . $redirectUrl);
        exit();
    }

    $old_student_no = "";
    $checkQuery = "SELECT student_no FROM students WHERE id = '$student_id'";
    $result = mysqli_query($conn, $checkQuery);
    if ($result && mysqli_num_rows($result) > 0) {
        $student_data = mysqli_fetch_assoc($result);
        $old_student_no = str_replace('-', '', $student_data['student_no']);
    }    if ($student_no != $old_student_no) {
        $duplicateQuery = "SELECT id FROM students WHERE student_no = '$student_no' AND id != '$student_id'";
        $duplicateResult = mysqli_query($conn, $duplicateQuery);        if ($duplicateResult && mysqli_num_rows($duplicateResult) > 0) {
            $_SESSION["error"] = "Error: Student number already exists for another student.";
            
            // Build redirect URL with preserved filters
            $redirectUrl = "../index.php?section=students";
            $filterParams = [];
            
            if (isset($_POST['filter_search']) && !empty($_POST['filter_search'])) {
                $filterParams[] = "search=" . urlencode($_POST['filter_search']);
            }
            if (isset($_POST['filter_academic_year']) && !empty($_POST['filter_academic_year'])) {
                $filterParams[] = "academic_year=" . urlencode($_POST['filter_academic_year']);
            }
            if (isset($_POST['filter_semester']) && !empty($_POST['filter_semester'])) {
                $filterParams[] = "semester=" . urlencode($_POST['filter_semester']);
            }
            if (isset($_POST['filter_sort']) && !empty($_POST['filter_sort'])) {
                $filterParams[] = "sort=" . urlencode($_POST['filter_sort']);
            }
            if (isset($_POST['filter_limit']) && !empty($_POST['filter_limit'])) {
                $filterParams[] = "limit=" . urlencode($_POST['filter_limit']);
            }
            if (isset($_POST['filter_page']) && !empty($_POST['filter_page'])) {
                $filterParams[] = "page=" . urlencode($_POST['filter_page']);
            }
            
            if (!empty($filterParams)) {
                $redirectUrl .= "&" . implode("&", $filterParams);
            }
            
            header("Location: " . $redirectUrl);
            exit();
        }
    }

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
            
            $clean_student_no = str_replace('-', '', $student_no);
            $newFileName = $advisor_id . '_' . $clean_student_no . '.' . $fileExtension;
            $dest_path = $uploadFileDir . $newFileName;
            
            $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            foreach ($extensions as $ext) {
                $oldFile = $uploadFileDir . $advisor_id . '_' . $clean_student_no . '.' . $ext;
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }
            
            move_uploaded_file($fileTmpPath, $dest_path);
        }
    }

    if ($old_student_no !== "" && $old_student_no !== str_replace('-', '', $student_no)) {
        $uploadFileDir = '../img/student_1x1/';
        $clean_student_no = str_replace('-', '', $student_no);
        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        foreach ($extensions as $ext) {
            $oldImagePath = $uploadFileDir . $advisor_id . '_' . $old_student_no . '.' . $ext;
            if (file_exists($oldImagePath)) {
                $newImagePath = $uploadFileDir . $advisor_id . '_' . $clean_student_no . '.' . $ext;
                rename($oldImagePath, $newImagePath);
                break;
            }
        }
    }    $query = "UPDATE students SET
                student_no = '$student_no', 
                academic_status = '$academic_status', 
                last_name = '$last_name', 
                first_name = '$first_name', 
                mi = '$mi',
                email = '$email',
                sex = '$gender', 
                birthday = '$birthday', 
                year_level = '$year_level', 
                section = '$section_code', 
                academic = '$academic', 
                semester = '$semester', 
                student_classification = '$student_classification', 
                address = '$address', 
                city = '$city', 
                province = '$province'
                WHERE id = '$student_id'";    // Build redirect URL with preserved filters
    $redirectUrl = "../index.php?section=students";
    $filterParams = [];
    
    if (isset($_POST['filter_search']) && !empty($_POST['filter_search'])) {
        $filterParams[] = "search=" . urlencode($_POST['filter_search']);
    }
    if (isset($_POST['filter_academic_year']) && !empty($_POST['filter_academic_year'])) {
        $filterParams[] = "academic_year=" . urlencode($_POST['filter_academic_year']);
    }
    if (isset($_POST['filter_semester']) && !empty($_POST['filter_semester'])) {
        $filterParams[] = "semester=" . urlencode($_POST['filter_semester']);
    }
    if (isset($_POST['filter_sort']) && !empty($_POST['filter_sort'])) {
        $filterParams[] = "sort=" . urlencode($_POST['filter_sort']);
    }
    if (isset($_POST['filter_limit']) && !empty($_POST['filter_limit'])) {
        $filterParams[] = "limit=" . urlencode($_POST['filter_limit']);
    }
    if (isset($_POST['filter_page']) && !empty($_POST['filter_page'])) {
        $filterParams[] = "page=" . urlencode($_POST['filter_page']);
    }
    
    if (!empty($filterParams)) {
        $redirectUrl .= "&" . implode("&", $filterParams);
    }

    if (mysqli_query($conn, $query)) {
        $_SESSION["success"] = "Student record updated successfully.";
        header("Location: " . $redirectUrl);
    } else {
        $_SESSION["error"] = "Error updating student record: " . mysqli_error($conn);
        header("Location: " . $redirectUrl);
    }
    exit();
} else {
    $_SESSION["error"] = "Invalid request method.";
    
    // Build redirect URL with preserved filters (if available in GET parameters)
    $redirectUrl = "../index.php?section=students";
    $filterParams = [];
    
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $filterParams[] = "search=" . urlencode($_GET['search']);
    }
    if (isset($_GET['academic_year']) && !empty($_GET['academic_year'])) {
        $filterParams[] = "academic_year=" . urlencode($_GET['academic_year']);
    }
    if (isset($_GET['semester']) && !empty($_GET['semester'])) {
        $filterParams[] = "semester=" . urlencode($_GET['semester']);
    }
    if (isset($_GET['sort']) && !empty($_GET['sort'])) {
        $filterParams[] = "sort=" . urlencode($_GET['sort']);
    }
    if (isset($_GET['limit']) && !empty($_GET['limit'])) {
        $filterParams[] = "limit=" . urlencode($_GET['limit']);
    }
    if (isset($_GET['page']) && !empty($_GET['page'])) {
        $filterParams[] = "page=" . urlencode($_GET['page']);
    }
    
    if (!empty($filterParams)) {
        $redirectUrl .= "&" . implode("&", $filterParams);
    }
    
    header("Location: " . $redirectUrl);
    exit();
}
