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
    }$checkQuery = "SELECT student_no FROM students WHERE id = '$student_id'";
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
?>
