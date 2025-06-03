<?php

require_once "../database/connection.php";

function addStudent($studentData, $fileData = null, $adviser_id = null) {
    global $conn;
    
    $student_no = mysqli_real_escape_string($conn, $studentData["student_no"]);
    $academic_status = mysqli_real_escape_string($conn, $studentData["academic_status"]);
    $last_name = mysqli_real_escape_string($conn, $studentData["last_name"]);
    $first_name = mysqli_real_escape_string($conn, $studentData["first_name"]);
    $mi = mysqli_real_escape_string($conn, $studentData["mi"]);
    $gender = mysqli_real_escape_string($conn, $studentData["gender"]);
    $birthday = mysqli_real_escape_string($conn, $studentData["birthday"]);
    $year_level = mysqli_real_escape_string($conn, $studentData["year_level"]);
    $section = mysqli_real_escape_string($conn, $studentData["section"]);
    $academic = mysqli_real_escape_string($conn, $studentData["academic_year"]);
    $semester = mysqli_real_escape_string($conn, $studentData["semester"]);
    $student_classification = mysqli_real_escape_string($conn, $studentData["student_classification"]);
    $address = mysqli_real_escape_string($conn, $studentData["address"]);
    $city = mysqli_real_escape_string($conn, $studentData["city"]);
    $province = mysqli_real_escape_string($conn, $studentData["province"]);
    
    $query = "INSERT INTO students (
         student_no, academic_status, last_name, first_name, mi, profile_image,
         gender, birthday, year_level, section, academic, 
         semester, student_classification, address, city, province, adviser_id
     ) VALUES (
         '$student_no', '$academic_status', '$last_name', '$first_name', '$mi',
         NULL,
         '$gender', '$birthday', $year_level, '$section', '$academic',
         '$semester', '$student_classification', '$address', '$city', '$province', " . ($adviser_id ? $adviser_id : "NULL") . "
     )";
    
    if (mysqli_query($conn, $query)) {
        $new_student_id = mysqli_insert_id($conn);
        
        if ($fileData && $fileData['error'] === UPLOAD_ERR_OK) {
            $profileImagePath = handleProfileImageUpload($fileData, $student_no, $adviser_id);
        }
        
        return [
            'status' => 200,
            'message' => 'Student added successfully',
            'student_id' => $new_student_id
        ];
    } else {
        return [
            'status' => 500,
            'message' => 'Error adding student: ' . mysqli_error($conn)
        ];
    }
}

function updateStudent($student_id, $studentData, $fileData = null, $adviser_id = null) {
    global $conn;
    
    if (!$student_id) {
        return [
            'status' => 400,
            'message' => 'Error: Student ID is missing.'
        ];
    }
    
    $student_no = mysqli_real_escape_string($conn, $studentData["student_no"]);
    $academic_status = mysqli_real_escape_string($conn, $studentData["academic_status"]);
    $last_name = mysqli_real_escape_string($conn, $studentData["last_name"]);
    $first_name = mysqli_real_escape_string($conn, $studentData["first_name"]);
    $mi = mysqli_real_escape_string($conn, $studentData["mi"]);
    $gender = mysqli_real_escape_string($conn, $studentData["gender"]);
    $birthday = mysqli_real_escape_string($conn, $studentData["birthday"]);
    $year_level = mysqli_real_escape_string($conn, $studentData["year_level"]);
    $section_code = mysqli_real_escape_string($conn, $studentData["section"]);
    $academic = mysqli_real_escape_string($conn, $studentData["academic_year"]);
    $semester = mysqli_real_escape_string($conn, $studentData["semester"]);
    $student_classification = mysqli_real_escape_string($conn, $studentData["student_classification"]);
    $address = mysqli_real_escape_string($conn, $studentData["address"]);
    $city = mysqli_real_escape_string($conn, $studentData["city"]);
    $province = mysqli_real_escape_string($conn, $studentData["province"]);
    
    $original_student_no = getOriginalStudentNo($student_id);
    
    if ($original_student_no != $student_no && $original_student_no != "" && $adviser_id) {
        renameStudentProfileImage($original_student_no, $student_no, $adviser_id);
    }
    
    if ($fileData && $fileData['error'] === UPLOAD_ERR_OK) {
        handleProfileImageUpload($fileData, $student_no, $adviser_id);
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
        return [
            'status' => 200,
            'message' => 'Student record updated successfully.'
        ];
    } else {
        return [
            'status' => 500,
            'message' => 'Error updating student record: ' . mysqli_error($conn)
        ];
    }
}

function getOriginalStudentNo($student_id) {
    global $conn;
    
    $original_student_no = "";
    if ($student_id) {
        $query = "SELECT student_no FROM students WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $student_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $original_student_no);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
    }
    
    return $original_student_no;
}

function renameStudentProfileImage($original_student_no, $new_student_no, $adviser_id) {
    $uploadFileDir = '../img/student_1x1/';
    $clean_original_student_no = str_replace('-', '', $original_student_no);
    $clean_new_student_no = str_replace('-', '', $new_student_no);

    $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    foreach ($extensions as $ext) {
        $oldFilePath = $uploadFileDir . $adviser_id . '_' . $clean_original_student_no . '.' . $ext;
        if (file_exists($oldFilePath)) {
            $newFilePath = $uploadFileDir . $adviser_id . '_' . $clean_new_student_no . '.' . $ext;
            return rename($oldFilePath, $newFilePath);
            break;
        }
    }
    
    return false;
}

function handleProfileImageUpload($fileData, $student_no, $adviser_id) {
    $fileTmpPath = $fileData['tmp_name'];
    $fileName = $fileData['name'];
    $fileNameCmps = explode('.', $fileName);
    $fileExtension = strtolower(end($fileNameCmps));
    $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array($fileExtension, $allowedfileExtensions)) {
        $uploadFileDir = '../img/student_1x1/';
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0777, true);
        }

        $clean_student_no = str_replace('-', '', $student_no);
        $newFileName = $adviser_id . '_' . $clean_student_no . '.' . $fileExtension;
        $dest_path = $uploadFileDir . $newFileName;

        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        foreach ($extensions as $ext) {
            $oldFile = $uploadFileDir . $adviser_id . '_' . $clean_student_no . '.' . $ext;
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            return 'img/student_1x1/' . $newFileName;
        }
    }
    
    return null;
}

function authenticateUser($username, $password) {
    global $conn;
    
    $query = "SELECT * FROM users WHERE username=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        if ($row["locked"] == "Y") {
            return [
                'status' => 403,
                'message' => 'Your account is locked!'
            ];
        }

        if ($password == $row["password"]) {
            return [
                'status' => 200,
                'message' => 'Login successful',
                'user_data' => [
                    'user_id' => $row["id"],
                    'username' => $username
                ]
            ];
        }

        $query = "UPDATE users SET tries = tries + 1 WHERE username=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);

        $tries = $row["tries"] + 1;
        if ($tries >= 3) {
            $query = "UPDATE users SET tries = 0, locked = 'Y' WHERE username=?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            
            return [
                'status' => 403,
                'message' => 'Your account is locked!'
            ];
        } else {
            $remaining_tries = 3 - $tries;
            $message = "Incorrect password! You have $remaining_tries " . ($remaining_tries == 1 ? "try" : "tries") . " left.";
            
            return [
                'status' => 401,
                'message' => $message
            ];
        }
    } else {
        return [
            'status' => 404,
            'message' => 'Username not found!'
        ];
    }
}

?>
