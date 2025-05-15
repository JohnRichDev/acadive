<!DOCTYPE html>
<html lang="en">

<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit;
}
?>

<head>
    <meta charset="UTF-8">
    <title>Acadive</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <script src="https://kit.fontawesome.com/45304bf22c.js"></script>
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
            display: flex;
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
    </style>
</head>

<body>
    <div id="preloader">
        <img src="img/logo_invert.svg" alt="Acadive Logo" class="preloader-logo">
    </div>

    <div id="sidebar">
        <img class="logo" draggable="false" src="img/logo_invert.svg" alt="Acadive Logo" />
        <div style="margin-top: 20px;">
            <button onclick="navigateToSection('dashboard')">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </button>
            <button onclick="navigateToSection('students')">
                <i class="fas fa-users"></i> Student
            </button>
            <button onclick="navigateToSection('account')">
                <i class="fas fa-cogs"></i> Account
            </button>
        </div>
        <hr style="margin-top: 20px; margin-bottom: 20px; background-color:rgb(105, 105, 105); height: 2px; border: none;">
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

        <div id="dashboard" class="section">
            <div class="section-content">
                <h2>Dashboard Overview</h2>
                <div class="grid">
                    <div class="card stats-card hov">
                        <h3><i class="fas fa-users"></i> Total Students</h3>
                        <div class="count">326</div>
                    </div>
                    <div class="card stats-card hov">
                        <h3><i class="fas fa-male"></i> Total Male</h3>
                        <div class="count">200</div>
                    </div>
                    <div class="card stats-card hov">
                        <h3><i class="fas fa-female"></i> Total Female</h3>
                        <div class="count">126</div>
                    </div>
                </div>

                <div class="card">
                    <h3><i class="fas fa-chart-pie"></i> Statistics</h3>
                </div>
            </div>
        </div>

        <div id="students" class="section">
            <div class="filters-bar">
                <div class="search-filter">
                    <input type="text" placeholder="Search Students...">
                    <select>
                        <option value="all">All Years</option>
                        <option value="1">Year 1</option>
                        <option value="2">Year 2</option>
                        <option value="3">Year 3</option>
                        <option value="4">Year 4</option>
                        <option value="5">Year 5</option>
                        <option value="6">Year 6</option>
                    </select>
                    <select>
                        <option value="all">All Sections</option>
                        <option value="A">Section A</option>
                        <option value="B">Section B</option>
                        <option value="C">Section C</option>
                    </select>
                </div>
            </div>

            <div class="section-content">
                <h2>List of Students (A.Y 2024-2025 2nd Semester)</h2>
                <div class="card">
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <div style="display: flex; align-items: center;">
                            <span style="margin-right: 10px;">Sort by:</span>
                            <div style="position: relative; display: inline-block;">
                                <select
                                    style="padding: 8px 30px 8px 10px; border-radius: 4px; border: 1px solid #ddd; appearance: none;">
                                    <option>Year</option>
                                    <option>Name</option>
                                    <option>Section</option>
                                    <option>Student No</option>
                                </select>
                                <i class="fas fa-chevron-down"
                                    style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #666;"></i>
                            </div>
                        </div>
                        <button class="action-buttons"
                            style="padding: 8px 15px; display: flex; align-items: center; gap: 5px; border-radius: 4px; cursor: pointer;">
                            <i class="fas fa-plus"></i> New Student Record
                        </button>
                    </div>

                    <div style="overflow-x: auto;">
                        <table class="student-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th style="width: 70px;">Pic</th>
                                    <th>Student No</th>
                                    <th>Last Name</th>
                                    <th>First Name</th>
                                    <th style="width: 50px;">MI</th>
                                    <th style="width: 70px;">Year</th>
                                    <th style="width: 70px;">Section</th>
                                    <th>Address</th>
                                    <th style="width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>
                                        <div class="stud-avatar">
                                            <img src="img/person.png" alt="Student Photo">
                                        </div>
                                    </td>
                                    <td>26-123456</td>
                                    <td>Carino</td>
                                    <td>Engelbert</td>
                                    <td>T</td>
                                    <td>2</td>
                                    <td>A</td>
                                    <td>16 Quiling Norte, Batac City, Ilocos Norte</td>
                                    <td>
                                        <button class="action-btn">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span>Show</span>
                            <select style="padding: 6px; border-radius: 4px; border: 1px solid #ddd;">
                                <option>10</option>
                                <option>25</option>
                                <option>50</option>
                                <option>100</option>
                            </select>
                            <span>entries</span>
                        </div>
                        <div style="display: flex; gap: 5px;">
                            <button
                                style="padding: 6px 12px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;">Previous</button>
                            <button
                                style="padding: 6px 12px; background: #1c3d7a; color: white; border: none; border-radius: 4px; cursor: pointer;">1</button>
                            <button
                                style="padding: 6px 12px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;">2</button>
                            <button
                                style="padding: 6px 12px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;">3</button>
                            <button
                                style="padding: 6px 12px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;">Next</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="account" class="section">
            <div class="section-content">
                <h2>Account Settings</h2>
                <div class="card">
                    <p>[User account details, login info, update password, etc]</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const params = new URLSearchParams(window.location.search);
            const section = params.get('section') || 'dashboard';
            showSection(section);

            window.addEventListener('load', function () {
                const preloader = document.getElementById('preloader');
                setTimeout(function () {
                    preloader.style.opacity = '0';
                    // preloader.style.transform = 'translateY(-20px)';
                    setTimeout(function () {
                        preloader.style.display = 'none';
                    }, 500);
                }, 800);
            });
        });

        function navigateToSection(sectionId) {
            showSection(sectionId);
            if (sectionId !== 'dashboard') {
                history.pushState(null, '', '?section=' + sectionId);
            } else {
                history.pushState(null, '', window.location.pathname);
            }
        }

        function showSection(sectionId) {
            const sections = document.querySelectorAll('.section');
            sections.forEach(section => section.classList.remove('active'));
            document.getElementById(sectionId).classList.add('active');
        }
    </script>
</body>

</html>