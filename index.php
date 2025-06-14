<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit;
}
$currentSection = isset($_GET['section']) ? $_GET['section'] : 'dashboard';

$studentToEdit = null;
if (isset($_GET['showModal']) && $_GET['showModal'] === 'editStudent' && isset($_GET['student_id'])) {
    include './database/connection.php';
    $student_id_raw = $_GET['student_id'];    if (filter_var($student_id_raw, FILTER_VALIDATE_INT) === false) {
        $_SESSION["error"] = "Invalid student ID format.";
        
        $redirectUrl = "index.php?section=students";
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
        exit;
    }
    $student_id = mysqli_real_escape_string($conn, $student_id_raw);

    $query = "SELECT * FROM students WHERE id = '$student_id'";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $studentToEdit = mysqli_fetch_assoc($result);    } else {
        $_SESSION["error"] = "Student record not found (ID: " . htmlspecialchars($student_id_raw) . ").";
        
        $redirectUrl = "index.php?section=students";
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
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Acadive</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            margin: 0;
            background-color: #f4f6f8;
            overflow: hidden;
        }

        #preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #0a1f44;
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: all 0.25s ease-out;
        }

        .preloader-logo {
            width: 300px;
            margin-bottom: 30px;
            animation: pulse 1s ease-in-out infinite alternate;
        }

        @keyframes pulse {
            from {
                transform: scale(1);
                opacity: 1;
            }

            to {
                transform: scale(1.05);
                opacity: 0.8;
            }
        }

        .logo {
            height: 65px;
            -moz-user-select: none;
            -webkit-user-select: none;
            user-select: none;
            margin-top: 20px;
        }

        #sidebar {
            width: 220px;
            background-color: #0a1f44;
            color: white;
            padding: 20px;
            height: 100vh;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        #sidebar button {
            font-family: Verdana;
            display: block;
            width: 100%;
            margin: 10px 0;
            margin-top: 20px;
            padding: 10px 15px;
            background: none;
            border: none;
            color: white;
            text-align: left;
            font-size: 1rem;
            cursor: pointer;
            border-radius: 4px;
            font-size: large;
        }

        #sidebar button:hover {
            background-color: #1c3d7a;
        }

        #sidebar a button.active-section {
            background-color: #1c3d7a;
            font-weight: bold;
        }


        #content {
            padding: 20px;
            flex-grow: 1;
            background-color: #f2f2f2;
            display: flex;
            flex-direction: column;
        }

        .section {
            display: none;
            flex-direction: column;
            flex-grow: 1;
        }

        .active {
            display: flex;
        }

        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            color: white;
            background-color: #0a1f44;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .welcome-message {
            font-size: 1.2rem;
            font-weight: 500;
        }

        .right-header {
            display: flex;
            gap: 10px;
        }

        .filters-bar {
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background-color: #ffffff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .search-filter {
            flex: 1;
            max-width: 500px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .search-filter input,
        .search-filter select {
            padding: 8px 10px;
            border-radius: 5px;
            border: 1px solid #e3e3e3;
            font-size: 0.9rem;
        }

        .search-filter select {
            min-width: 120px;
        }

        .search-filter input {
            flex-grow: 1;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .action-buttons button {
            padding: 8px 15px;
            background-color: #1c3d7a;
            border: none;
            color: white;
            font-size: 0.9rem;
            cursor: pointer;
            border-radius: 4px;
        }

        .action-buttons button:hover {
            background-color: #163b65;
        }

        .section-content {
            flex-grow: 1;
            overflow-y: auto;
        }

        .card {
            background-color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .hov:hover {
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        .card h3 {
            margin-top: 0;
            color: #0a1f44;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
        }

        .card h3 i {
            margin-right: 8px;
            color: #1c3d7a;
        }

        .stats-card {
            text-align: center;
            padding: 25px 15px;
        }

        .stats-card .count {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1c3d7a;
            margin: 10px 0;
        }

        .stats-card .label {
            color: #666;
            font-size: 0.9rem;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .chart-container {
            height: 300px;
            margin-top: 20px;
        }

        .graph-tabs {
            display: flex;
            margin-bottom: 15px;
            border-bottom: 1px solid #e3e3e3;
        }

        .graph-tabs button {
            background: none;
            border: none;
            padding: 10px 15px;
            font-size: 0.9rem;
            cursor: pointer;
            border-bottom: 3px solid transparent;
        }

        .graph-tabs button.active {
            border-bottom: 3px solid #1c3d7a;
            color: #1c3d7a;
            font-weight: 600;
        }

        .student-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .student-table th {
            background-color: #f5f7fa;
            color: #0a1f44;
            font-weight: 600;
            padding: 12px 15px;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            border-bottom: 1px solid #e3e3e3;
            text-align: center;
        }

        .student-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e3e3e3;
            color: #333;
            font-size: 0.95rem;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .student-table tr:hover {
            background-color: #f5f9ff;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            padding: 1px;
            margin-left: 5px;
            border: 1px solid rgb(187, 187, 187);
            background-color: #d8d8d8;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .avatar img {
            width: 100%;
            height: auto;
        }

        .student-table .stud-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #ebebeb;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .student-table .stud-avatar img {
            width: 70%;
            height: auto;
        }

        .action-btn {
            background-color: transparent;
            color: #666666;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            padding: 0;
            font-size: large;
            text-decoration: none;
        }

        .action-btn i {
            margin: 0;
        }

        .action-btn:hover {
            color: #414141;
        }

        .fas {
            margin-right: 5px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 30px;
            border-radius: 8px;
            width: 80%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;

        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .modal-header h2 {
            margin: 0;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            position: absolute;
            top: 15px;
            right: 25px;
        }

        .close:hover {
            color: #000;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            box-sizing: border-box;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #1c3d7a;
            outline: none;
        }

        .form-buttons {
            margin-top: 20px;
            text-align: center;
        }

        .btn {
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 10px;
            text-decoration: none;
        }

        .btn-primary {
            background-color: #1c3d7a;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
            border: 1px solid #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }

        .alert {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        #content {
            padding: 20px;
            flex-grow: 1;
            background-color: #f2f2f2;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        .section-content {
            flex-grow: 1;
            overflow-y: auto;
            padding-right: 10px;
        }

        .dashboard-graphs-container {
            max-height: 600px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            padding-right: 10px;
        }

        .students-table-container {
            max-height: 70vh;
            overflow-y: auto;
            border: 1px solid #e3e3e3;
            border-radius: 8px;
            margin-top: 15px;
        }

        .students-table-container .student-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .students-table-container .student-table thead {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: #f5f7fa;
        }

        .students-table-container .student-table th {
            background-color: #f5f7fa;
            color: #0a1f44;
            font-weight: 600;
            padding: 12px 15px;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            border-bottom: 2px solid #e3e3e3;
            text-align: center;
            position: sticky;
            top: 0;
        }

        .section-content::-webkit-scrollbar,
        .dashboard-graphs-container::-webkit-scrollbar,
        .students-table-container::-webkit-scrollbar {
            width: 8px;
        }

        .section-content::-webkit-scrollbar-track,
        .dashboard-graphs-container::-webkit-scrollbar-track,
        .students-table-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .section-content::-webkit-scrollbar-thumb,
        .dashboard-graphs-container::-webkit-scrollbar-thumb,
        .students-table-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .section-content::-webkit-scrollbar-thumb:hover,
        .dashboard-graphs-container::-webkit-scrollbar-thumb:hover,
        .students-table-container::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        .card.dashboard-main {
            max-height: calc(100vh - 250px);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .dashboard-main .chart-flex-container {
            display: flex;
            flex: 1;
            gap: 30px;
            align-items: flex-start;
            overflow-y: auto;
            padding-right: 10px;
            min-height: 300px;
        }

        .confirmation-modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(3px);
            animation: fadeIn 0.3s ease-out;
        }

        .confirmation-modal-content {
            background-color: #ffffff;
            margin: 15% auto;
            padding: 0;
            border: none;
            border-radius: 12px;
            width: 90%;
            max-width: 480px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.3s ease-out;
            overflow: hidden;
        }

        .confirmation-modal-header {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            padding: 20px 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .confirmation-modal-header .icon {
            font-size: 24px;
            color: #fff;
        }

        .confirmation-modal-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }

        .confirmation-modal-body {
            padding: 25px;
            text-align: left;
        }

        .confirmation-modal-body .warning-text {
            font-size: 16px;
            color: #333;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .confirmation-modal-body .consequences {
            background-color: #f8f9fa;
            border-left: 4px solid #dc3545;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }

        .confirmation-modal-body .consequences h4 {
            margin: 0 0 10px 0;
            color: #dc3545;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .confirmation-modal-body .consequences ul {
            margin: 0;
            padding-left: 20px;
            color: #666;
        }

        .confirmation-modal-body .consequences li {
            margin-bottom: 5px;
            font-size: 14px;
        }

        .confirmation-modal-footer {
            padding: 20px 25px;
            background-color: #f8f9fa;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            border-top: 1px solid #e9ecef;
        }

        .confirmation-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 80px;
        }

        .confirmation-btn-cancel {
            background-color: #6c757d;
            color: white;
        }

        .confirmation-btn-cancel:hover {
            background-color: #5a6268;
            transform: translateY(-1px);
        }

        .confirmation-btn-confirm {
            background-color: #dc3545;
            color: white;
        }

        .confirmation-btn-confirm:hover {
            background-color: #c82333;
            transform: translateY(-1px);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.9);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
    </style>
    </style>
</head>

<body>
    <div id="preloader" style="display:none;">
        <img src="img/logo_invert.svg" alt="Acadive Logo" class="preloader-logo">
    </div>

    <div id="sidebar">
        <img class="logo" draggable="false" src="img/logo_invert.svg" alt="Acadive Logo" />
        <div style="margin-top: 20px;">
            <a href="?section=dashboard" style="text-decoration:none;">
                <button type="button" style="width:100%;text-align:left;"
                    class="<?php echo ($currentSection === 'dashboard') ? 'active-section' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </button>
            </a>
            <a href="?section=students" style="text-decoration:none;">
                <button type="button" style="width:100%;text-align:left;"
                    class="<?php echo ($currentSection === 'students') ? 'active-section' : ''; ?>">
                    <i class="fas fa-users"></i> Student
                </button>
            </a>
            <a href="?section=account" style="text-decoration:none;">
                <button type="button" style="width:100%;text-align:left;"
                    class="<?php echo ($currentSection === 'account') ? 'active-section' : ''; ?>">
                    <i class="fas fa-cogs"></i> Account
                </button>
            </a>
        </div>
        <hr
            style="margin-top: 20px; margin-bottom: 20px; background-color:rgb(105, 105, 105); height: 2px; border: none;">
        <button onclick="window.location.href='process/logout.php'">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>
    </div>

    <div id="content">
        <div class="top-header">
            <div class="welcome-message">
                <span style="font-size: x-large;">Welcome, <b>
                        <?php
                        if (isset($_SESSION["username"])) {
                            echo htmlspecialchars($_SESSION["username"]);
                        } else {
                            header("Location: login.php");
                            exit;
                        }
                        ?></b>!</span>
            </div>
            <div class="right-header" style="flex-direction: row; align-items: center; gap: 10px;">
                <div style="text-align: right;">
                    <span style="font-size: large;"><b>
                            <?php
                            echo strtoupper(htmlspecialchars($_SESSION["username"]));
                            ?>
                        </b></span><br>
                    Administrator
                </div>
                <div class="avatar">
                    <img src="img/person.png" style="height: 30px; width: auto;" alt="User Avatar">
                </div>
            </div>
        </div>

        <div class="section active">
            <?php
            $allowed_sections = ['dashboard', 'students', 'account'];
            if (in_array($currentSection, $allowed_sections) && file_exists('./sections/' . $currentSection . '.php')) {
                include './sections/' . $currentSection . '.php';
            } else {
                include './sections/dashboard.php';
            }
            ?>
        </div>
    </div>

    <div id="addStudentModal" class="modal"
        style="<?php echo (isset($_GET['showModal']) && $_GET['showModal'] === 'addStudent') ? 'display:block;' : 'display:none;'; ?>">
        <div class="modal-content">            <div class="modal-header">
                <h2>Add New Student Record</h2>
                <?php
                $closeUrl = "?section=" . htmlspecialchars($currentSection);
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
                    $closeUrl .= "&" . implode("&", $filterParams);
                }
                ?>
                <a href="<?php echo $closeUrl; ?>" class="close"
                    title="Close">&times;</a>
            </div>
            <div id="alertMessage" class="alert"
                style="<?php echo (isset($_SESSION['modal_error']) || isset($_SESSION['modal_success'])) ? 'display:block;' : 'display:none;'; ?>">
                <?php
                if (isset($_SESSION['modal_error'])) {
                    echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['modal_error']) . '</div>';
                    unset($_SESSION['modal_error']);
                }
                if (isset($_SESSION['modal_success'])) {
                    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['modal_success']) . '</div>';
                    unset($_SESSION['modal_success']);
                }
                ?>
            </div>
            <form id="addStudentForm" action="process/add_student.php" method="POST" enctype="multipart/form-data">
                <!-- Hidden fields to preserve current filters -->
                <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                    <input type="hidden" name="filter_search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                <?php endif; ?>
                <?php if (isset($_GET['academic_year']) && !empty($_GET['academic_year'])): ?>
                    <input type="hidden" name="filter_academic_year" value="<?php echo htmlspecialchars($_GET['academic_year']); ?>">
                <?php endif; ?>
                <?php if (isset($_GET['semester']) && !empty($_GET['semester'])): ?>
                    <input type="hidden" name="filter_semester" value="<?php echo htmlspecialchars($_GET['semester']); ?>">
                <?php endif; ?>
                <?php if (isset($_GET['sort']) && !empty($_GET['sort'])): ?>
                    <input type="hidden" name="filter_sort" value="<?php echo htmlspecialchars($_GET['sort']); ?>">
                <?php endif; ?>
                <?php if (isset($_GET['limit']) && !empty($_GET['limit'])): ?>
                    <input type="hidden" name="filter_limit" value="<?php echo htmlspecialchars($_GET['limit']); ?>">
                <?php endif; ?>
                <?php if (isset($_GET['page']) && !empty($_GET['page'])): ?>
                    <input type="hidden" name="filter_page" value="<?php echo htmlspecialchars($_GET['page']); ?>">
                <?php endif; ?>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="student_no">Student Number</label>
                        <input type="text" id="student_no" name="student_no" required>
                    </div>
                    <div class="form-group">
                        <label for="academic_status">Academic Status</label>
                        <select id="academic_status" name="academic_status" required>
                            <option value="Regular">Regular</option>
                            <option value="Irregular">Irregular</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>                    <div class="form-group">
                        <label for="mi">Middle Initial</label>
                        <input type="text" id="mi" name="mi" maxlength="5">
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="gender">Sex</label>
                        <select id="gender" name="gender" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="birthday">Birthday</label>
                        <input type="date" id="birthday" name="birthday" required>
                    </div>
                    <div class="form-group">
                        <label for="year_level">Year Level</label>
                        <select id="year_level" name="year_level" required>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                            <option value="5">5th Year</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="section_form">Section</label> <select id="section_form" name="section" required>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                            <option value="E">E</option>
                            <option value="F">F</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="academic_year_form">Academic Year</label> <select name="academic_year"
                            id="academic_year_form" required>
                            <?php
                            $startYear = date("Y") + 1;
                            for ($i = 0; $i < 6; $i++) {
                                $endY = $startYear - $i;
                                $acadY = ($endY - 1) . "-" . $endY;
                                echo "<option value=\"$acadY\">$acadY</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="semester_form">Semester</label> <select id="semester_form" name="semester" required>
                            <option value="1st">1st Semester</option>
                            <option value="2nd">2nd Semester</option>
                            <option value="Mid-Year">Mid-Year</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="student_classification">Student Classification</label>
                        <select id="student_classification" name="student_classification" required>
                            <option value="Enrolled">Officially Enrolled</option>
                            <option value="Dropped">Dropped</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="address">Address (Street, Barangay)</label>
                    <input type="text" id="address" name="address" required>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="city">City / Municipality</label>
                        <input type="text" id="city" name="city" required>
                    </div>
                    <div class="form-group">
                        <label for="province">Province</label>
                        <input type="text" id="province" name="province" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="profile_image">Profile Image (Optional)</label>
                    <input type="file" id="profile_image" name="profile_image"
                        accept="image/png, image/jpeg, image/gif, image/webp">
                </div>                <div class="form-buttons" style="text-align: center;">
                    <?php
                    $cancelUrl = "?section=" . htmlspecialchars($currentSection);
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
                        $cancelUrl .= "&" . implode("&", $filterParams);
                    }
                    ?>
                    <a href="<?php echo $cancelUrl; ?>" class="btn btn-secondary"
                        style="text-decoration:none;">Cancel</a>
                    <input type="submit" class="btn btn-primary" value="Save Student">
                </div>
            </form>
        </div>
    </div>

    <div id="editStudentModal" class="modal"
        style="<?php echo (isset($_GET['showModal']) && $_GET['showModal'] === 'editStudent' && $studentToEdit) ? 'display:block;' : 'display:none;'; ?>">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Student Record</h2>
                <a href="?section=students" class="close" title="Close">&times;</a>
            </div>
            <div id="editAlertMessage" class="alert"
                style="<?php echo (isset($_SESSION['edit_modal_error']) || isset($_SESSION['edit_modal_success'])) ? 'display:block;' : 'display:none;'; ?>">
                <?php
                if (isset($_SESSION['edit_modal_error'])) {
                    echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['edit_modal_error']) . '</div>';
                    unset($_SESSION['edit_modal_error']);
                }
                if (isset($_SESSION['edit_modal_success'])) {
                    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['edit_modal_success']) . '</div>';
                    unset($_SESSION['edit_modal_success']);
                }
                ?>
            </div>
            <?php if ($studentToEdit): ?>                <form id="editStudentForm" action="process/edit_student.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($studentToEdit['id']); ?>">
                    
                    <!-- Hidden fields to preserve current filters -->
                    <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                        <input type="hidden" name="filter_search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                    <?php endif; ?>
                    <?php if (isset($_GET['academic_year']) && !empty($_GET['academic_year'])): ?>
                        <input type="hidden" name="filter_academic_year" value="<?php echo htmlspecialchars($_GET['academic_year']); ?>">
                    <?php endif; ?>
                    <?php if (isset($_GET['semester']) && !empty($_GET['semester'])): ?>
                        <input type="hidden" name="filter_semester" value="<?php echo htmlspecialchars($_GET['semester']); ?>">
                    <?php endif; ?>
                    <?php if (isset($_GET['sort']) && !empty($_GET['sort'])): ?>
                        <input type="hidden" name="filter_sort" value="<?php echo htmlspecialchars($_GET['sort']); ?>">
                    <?php endif; ?>
                    <?php if (isset($_GET['limit']) && !empty($_GET['limit'])): ?>
                        <input type="hidden" name="filter_limit" value="<?php echo htmlspecialchars($_GET['limit']); ?>">
                    <?php endif; ?>
                    <?php if (isset($_GET['page']) && !empty($_GET['page'])): ?>
                        <input type="hidden" name="filter_page" value="<?php echo htmlspecialchars($_GET['page']); ?>">
                    <?php endif; ?>>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="edit_student_no">Student Number</label>
                            <input type="text" id="edit_student_no" name="student_no"
                                value="<?php echo htmlspecialchars($studentToEdit['student_no']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_academic_status">Academic Status</label>
                            <select id="edit_academic_status" name="academic_status" required>
                                <option value="Regular" <?php echo ($studentToEdit['academic_status'] === 'Regular') ? 'selected' : ''; ?>>Regular</option>
                                <option value="Irregular" <?php echo ($studentToEdit['academic_status'] === 'Irregular') ? 'selected' : ''; ?>>Irregular</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_last_name">Last Name</label>
                            <input type="text" id="edit_last_name" name="last_name"
                                value="<?php echo htmlspecialchars($studentToEdit['last_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_first_name">First Name</label>
                            <input type="text" id="edit_first_name" name="first_name"
                                value="<?php echo htmlspecialchars($studentToEdit['first_name']); ?>" required>
                        </div>                        <div class="form-group">
                            <label for="edit_mi">Middle Initial</label>
                            <input type="text" id="edit_mi" name="mi" maxlength="5"
                                value="<?php echo htmlspecialchars($studentToEdit['mi']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="edit_email">Email Address</label>
                            <input type="email" id="edit_email" name="email"
                                value="<?php echo htmlspecialchars($studentToEdit['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_gender">Gender</label>
                            <select id="edit_gender" name="gender" required>
                                <option value="Male" <?php echo ($studentToEdit['sex'] === 'Male') ? 'selected' : ''; ?>>Male
                                </option>
                                <option value="Female" <?php echo ($studentToEdit['sex'] === 'Female') ? 'selected' : ''; ?>>
                                    Female</option>
                                <option value="Other" <?php echo ($studentToEdit['sex'] === 'Other') ? 'selected' : ''; ?>>
                                    Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_birthday">Birthday</label>
                            <input type="date" id="edit_birthday" name="birthday"
                                value="<?php echo htmlspecialchars($studentToEdit['birthday']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_year_level">Year Level</label>
                            <select id="edit_year_level" name="year_level" required>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo ($studentToEdit['year_level'] == $i) ? 'selected' : ''; ?>>
                                        <?php echo $i;
                                        echo ($i == 1) ? 'st' : (($i == 2) ? 'nd' : (($i == 3) ? 'rd' : 'th')); ?>
                                        Year</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_section_form">Section</label> <select id="edit_section_form" name="section"
                                required>
                                <?php $sections = ['A', 'B', 'C', 'D', 'E', 'F']; ?>
                                <?php foreach ($sections as $sec): ?>
                                    <option value="<?php echo $sec; ?>" <?php echo ($studentToEdit['section'] === $sec) ? 'selected' : ''; ?>><?php echo $sec; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_academic_year_form">Academic Year</label> <select name="academic_year"
                                id="edit_academic_year_form" required>
                                <?php
                                $startYear = date("Y") + 1;
                                for ($k = 0; $k < 6; $k++) {
                                    $endY_edit = $startYear - $k;
                                    $acadY_edit = ($endY_edit - 1) . "-" . $endY_edit;
                                    $selected_edit = ($studentToEdit['academic'] === $acadY_edit) ? 'selected' : '';
                                    echo "<option value=\"$acadY_edit\" $selected_edit>$acadY_edit</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_semester_form">Semester</label> <select id="edit_semester_form" name="semester"
                                required>
                                <option value="1st" <?php echo ($studentToEdit['semester'] === '1st Semester') ? 'selected' : ''; ?>>
                                    1st Semester</option>
                                <option value="2nd" <?php echo ($studentToEdit['semester'] === '2nd Semester') ? 'selected' : ''; ?>>
                                    2nd Semester</option>
                                <option value="Mid-Year" <?php echo ($studentToEdit['semester'] === 'Mid-Year') ? 'selected' : ''; ?>>Mid-Year</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_student_classification">Student Classification</label>
                            <select id="edit_student_classification" name="student_classification" required>
                                <option value="Enrolled">Officially Enrolled</option>
                                <option value="Dropped">Dropped</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_address">Address (Street, Barangay)</label>
                        <input type="text" id="edit_address" name="address"
                            value="<?php echo htmlspecialchars($studentToEdit['address']); ?>" required>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="edit_city">City / Municipality</label>
                            <input type="text" id="edit_city" name="city"
                                value="<?php echo htmlspecialchars($studentToEdit['city']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_province">Province</label>
                            <input type="text" id="edit_province" name="province"
                                value="<?php echo htmlspecialchars($studentToEdit['province']); ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_profile_image">New Profile Image (Optional - leave blank to keep current)</label>
                        <input type="file" id="edit_profile_image" name="profile_image"
                            accept="image/png, image/jpeg, image/gif, image/webp">
                        <?php
                        if (!empty($studentToEdit['profile_image']) && file_exists($studentToEdit['profile_image'])) {
                            echo '<p style="margin-top: 5px;">Current image: <img src="' . htmlspecialchars($studentToEdit['profile_image']) . '" alt="Current Profile Image" style="max-width: 100px; max-height: 100px; vertical-align: middle; margin-left: 10px;"></p>';
                        } else if (!empty($studentToEdit['profile_image'])) {
                            echo '<p style="margin-top: 5px; color: #777;">Current image path (not found): ' . htmlspecialchars($studentToEdit['profile_image']) . '</p>';
                        }
                        ?>
                    </div>                    <div class="form-buttons"
                        style="text-align: center; display: flex; gap: 10px; justify-content: center;">
                        <?php
                        $cancelUrl = "?section=students";
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
                            $cancelUrl .= "&" . implode("&", $filterParams);
                        }
                        ?>
                        <a href="<?php echo $cancelUrl; ?>" class="btn btn-secondary" style="text-decoration:none;">Cancel</a>
                        <input type="submit" class="btn btn-primary" value="Update Student">
                        <button type="button" class="btn btn-danger"
                            onclick="confirmDeleteStudent(<?php echo htmlspecialchars($studentToEdit['id']); ?>)"
                            style="background-color: #dc3545; color: white; border: 1px solid #dc3545; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px; transition: background-color 0.3s ease;"
                            onmouseover="this.style.backgroundColor='#c82333'"
                            onmouseout="this.style.backgroundColor='#dc3545'">
                            <i class="fas fa-trash-alt" style="margin-right: 5px;"></i>Delete Student
                        </button>
                    </div>
                </form>            <?php else: ?>
                <p>Student data could not be loaded for editing. Please ensure the student ID is correct and try again.</p>
                <div class="form-buttons" style="text-align: center;">
                    <?php
                    $backUrl = "?section=students";
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
                        $backUrl .= "&" . implode("&", $filterParams);
                    }
                    ?>
                    <a href="<?php echo $backUrl; ?>" class="btn btn-secondary" style="text-decoration:none;">Back to List</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="customConfirmModal" class="confirmation-modal">
        <div class="confirmation-modal-content">
            <div class="confirmation-modal-header">
                <i class="fas fa-exclamation-triangle icon"></i>
                <h3>Confirm Deletion</h3>
            </div>
            <div class="confirmation-modal-body">
                <p class="warning-text">Are you sure you want to permanently delete this student record?</p>
                <div class="consequences">
                    <h4>This action will:</h4>
                    <ul>
                        <li>Remove all student information from the database</li>
                        <li>Delete the student's profile image</li>
                        <li>Cannot be undone</li>
                    </ul>
                </div>
            </div>
            <div class="confirmation-modal-footer">
                <button class="confirmation-btn confirmation-btn-cancel" onclick="hideCustomConfirm()">Cancel</button>
                <button class="confirmation-btn confirmation-btn-confirm" onclick="confirmAction()">Delete
                    Student</button>
            </div>
        </div>
    </div>

    <script>        let pendingStudentId = null;

        function showCustomConfirm(studentId) {
            pendingStudentId = studentId;
            document.getElementById('customConfirmModal').style.display = 'block';
        }

        function hideCustomConfirm() {
            document.getElementById('customConfirmModal').style.display = 'none';
            pendingStudentId = null;
        }

        function confirmAction() {
            if (pendingStudentId) {
                deleteStudent(pendingStudentId);
                hideCustomConfirm();
            }
        }        function deleteStudent(studentId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'process/delete_student.php';

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'student_id';
            input.value = studentId;
            form.appendChild(input);
            
            const urlParams = new URLSearchParams(window.location.search);
            
            if (urlParams.get('search')) {
                const searchInput = document.createElement('input');
                searchInput.type = 'hidden';
                searchInput.name = 'filter_search';
                searchInput.value = urlParams.get('search');
                form.appendChild(searchInput);
            }
            
            if (urlParams.get('academic_year')) {
                const academicYearInput = document.createElement('input');
                academicYearInput.type = 'hidden';
                academicYearInput.name = 'filter_academic_year';
                academicYearInput.value = urlParams.get('academic_year');
                form.appendChild(academicYearInput);
            }
            
            if (urlParams.get('semester')) {
                const semesterInput = document.createElement('input');
                semesterInput.type = 'hidden';
                semesterInput.name = 'filter_semester';
                semesterInput.value = urlParams.get('semester');
                form.appendChild(semesterInput);
            }
            
            if (urlParams.get('sort')) {
                const sortInput = document.createElement('input');
                sortInput.type = 'hidden';
                sortInput.name = 'filter_sort';
                sortInput.value = urlParams.get('sort');
                form.appendChild(sortInput);
            }
            
            if (urlParams.get('limit')) {
                const limitInput = document.createElement('input');
                limitInput.type = 'hidden';
                limitInput.name = 'filter_limit';
                limitInput.value = urlParams.get('limit');
                form.appendChild(limitInput);
            }
            
            if (urlParams.get('page')) {
                const pageInput = document.createElement('input');
                pageInput.type = 'hidden';
                pageInput.name = 'filter_page';
                pageInput.value = urlParams.get('page');
                form.appendChild(pageInput);
            }

            document.body.appendChild(form);
            form.submit();
        }

        function confirmDeleteStudent(studentId) {
            showCustomConfirm(studentId);
        } document.addEventListener('DOMContentLoaded', function () {
            const addStudentModal = document.getElementById('addStudentModal');
            const editStudentModal = document.getElementById('editStudentModal');
            const customConfirmModal = document.getElementById('customConfirmModal');            function closeModalAndResetURL(modalElement) {
                if (modalElement && modalElement.style.display === 'block') {
                    const currentUrl = new URL(window.location.href);
                    
                    const filtersToKeep = ['search', 'academic_year', 'semester', 'sort', 'limit', 'page', 'section'];
                    const preservedParams = {};
                    
                    filtersToKeep.forEach(param => {
                        if (currentUrl.searchParams.has(param)) {
                            preservedParams[param] = currentUrl.searchParams.get(param);
                        }
                    });
                    
                    currentUrl.search = '';
                    Object.keys(preservedParams).forEach(key => {
                        currentUrl.searchParams.set(key, preservedParams[key]);
                    });
                    
                    window.history.replaceState({}, '', currentUrl.toString());
                    modalElement.style.display = 'none';
                }
            }

            window.addEventListener('click', function (event) {
                if (event.target === addStudentModal) {
                    closeModalAndResetURL(addStudentModal);
                }
                if (event.target === editStudentModal) {
                    closeModalAndResetURL(editStudentModal);
                }
                if (event.target === customConfirmModal) {
                    hideCustomConfirm();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (customConfirmModal.style.display === 'block') {
                    if (event.key === 'Escape') {
                        hideCustomConfirm();
                    } else if (event.key === 'Enter') {
                        confirmAction();
                    }
                }
            });
        });
    </script>
</body>

</html>