<?php

?>
<div class="section-content">
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