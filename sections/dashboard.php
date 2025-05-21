<?php

?>
<div class="section-content">
    <div class="filters-bar">
        <div class="search-filter">
            <form id="filterForm" method="GET" action="" style="display: flex; gap: 15px; width: 100%;">
                <input type="hidden" name="section" value="students">
                <select name="academic_year" onchange="document.getElementById('filterForm').submit();"
                    style="min-width: 170px;">
                    <option value="">All Academic Years</option>
                    <?php
                    $currentYear = 2025;
                    for ($i = 0; $i < 5; $i++) {
                        $year = $currentYear - $i;
                        $academicYear = ($year - 1) . "-" . $year;
                        $selected = (isset($_GET['academic_year']) && $_GET['academic_year'] == $academicYear) ? 'selected' : '';
                        echo "<option value=\"$academicYear\" $selected>$academicYear</option>";
                    }
                    ?>
                </select>
                <select name="semester" onchange="document.getElementById('filterForm').submit();"
                    style="min-width: 150px;">
                    <option value="">All Semesters</option>
                    <?php $semesters = [
                        '1st' => '1st Semester',
                        '2nd' => '2nd Semester'
                    ];
                    foreach ($semesters as $value => $label) {
                        $selected = (isset($_GET['semester']) && $_GET['semester'] == $value) ? 'selected' : '';
                        echo "<option value=\"$value\" $selected>$label</option>";
                    }
                    ?>
                </select>
            </form>
        </div>
    </div>
    <h2>Dashboard Overview</h2>
    <div class="grid">
        <div class="card stats-card hov">
            <h3><i class="fas fa-users"></i> Total Students</h3>
            <div class="count">
                <?php
                include("database/connection.php");
                $query = "SELECT COUNT(*) as total FROM students";
                $result = mysqli_query($conn, $query);
                $row = mysqli_fetch_assoc($result);
                echo $row['total'];
                ?>
            </div>
        </div>
        <div class="card stats-card hov">
            <h3><i class="fas fa-male"></i> Total Male</h3>
            <div class="count">
                <?php
                $query = "SELECT COUNT(*) as total FROM students WHERE gender = 'Male'";
                $result = mysqli_query($conn, $query);
                $row = mysqli_fetch_assoc($result);
                echo $row['total'];
                ?>
            </div>
        </div>
        <div class="card stats-card hov">
            <h3><i class="fas fa-female"></i> Total Female</h3>
            <div class="count">
                <?php
                $query = "SELECT COUNT(*) as total FROM students WHERE gender = 'Female'";
                $result = mysqli_query($conn, $query);
                $row = mysqli_fetch_assoc($result);
                echo $row['total'];
                ?>
            </div>
        </div>
    </div>

    <div class="card">
        <h3><i class="fas fa-chart-pie"></i> Statistics</h3>
    </div>
</div>