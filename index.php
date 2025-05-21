<!DOCTYPE html>
<html lang="en">

<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit;
}
$currentSection = isset($_GET['section']) ? $_GET['section'] : 'dashboard';
?>

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
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
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
        }

        .btn-primary {
            background-color: #1c3d7a;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .alert {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            display: none;
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
                    class="<?php echo ($currentSection === 'dashboard') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </button>
            </a>
            <a href="?section=students" style="text-decoration:none;">
                <button type="button" style="width:100%;text-align:left;"
                    class="<?php echo ($currentSection === 'students') ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Student
                </button>
            </a>
            <a href="?section=account" style="text-decoration:none;">
                <button type="button" style="width:100%;text-align:left;"
                    class="<?php echo ($currentSection === 'account') ? 'active' : ''; ?>">
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
                            echo $_SESSION["username"];
                        } else {
                            header("Location: login.php");
                        }
                        ?></b>!</span>
            </div>
            <div class="right-header" style="flex-direction: row; align-items: center; gap: 10px;">
                <div style="text-align: right;">
                    <span style="font-size: large;"><b>
                            <?php
                            $username = $_SESSION["username"];
                            echo strtoupper($username);
                            ?>
                        </b></span><br>
                    Administrator
                </div>
                <div class="avatar">
                    <img src="img/person.png" style="height: 30px; width: auto;" alt="Student Photo">
                </div>
            </div>
        </div>

        <div class="section active">
            <?php
            include './sections/' . $currentSection . '.php';
            ?>
        </div>
    </div>

    <div id="addStudentModal" class="modal"
        style="<?php echo (isset($_GET['showModal']) && $_GET['showModal'] === 'addStudent') ? 'display:block;' : 'display:none;'; ?>">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Student Record</h2>
            </div>
            <div id="alertMessage" class="alert"></div>
            <form id="addStudentForm" action="process/add_student.php" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="student_no">Student Number</label>
                        <input type="text" id="student_no" name="student_no" required>
                    </div>
                    <div class="form-group">
                        <label for="academic_status">Academic Status</label>
                        <select id="academic_status" name="academic_status" required>
                            <option value="Officially enrolled">Officially enrolled</option>
                            <option value="Dropped">Dropped</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="mi">Middle Initial</label>
                        <input type="text" id="mi" name="mi" maxlength="1">
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender</label>
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
                            <option value="5">5rd Year</option>
                            <option value="6">6th Year</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="section">Section</label>
                        <select id="section" name="section" required>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                            <option value="E">E</option>
                            <option value="F">F</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="academic">Academic Year</label>
                        <select name="academic_year" id="academic" required>
                            <option value="2024-2025">2024-2025</option>
                            <option value="2023-2024">2023-2024</option>
                            <option value="2022-2023">2022-2023</option>
                            <option value="2021-2022">2021-2022</option>
                            <option value="2020-2021">2020-2021</option>
                            <option value="2019-2020">2019-2020</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="semester">Semester</label>
                        <select id="semester" name="semester" required>
                            <option value="1st">1st Semester</option>
                            <option value="2nd">2nd Semester</option>
                            <option value="Mid-Year">Mid-Year</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="student_classification">Student Classification</label>
                        <select id="student_classification" name="student_classification" required>
                            <option value="New">New Student</option>
                            <option value="Regular">Regular Student</option>
                            <option value="Transferee">Transferee</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" required>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" required>
                    </div>
                    <div class="form-group">
                        <label for="province">Province</label>
                        <input type="text" id="province" name="province" required>
                    </div>
                </div>
                <div class="form-buttons" style="text-align: center;">
                    <a href="?section=<?php echo $currentSection; ?>" class="btn btn-secondary"
                        style="text-decoration:none;">Cancel</a>
                    <input type="submit" class="btn btn-primary" value="Save Student">
                </div>
            </form>
        </div>
    </div>

</body>

</html>