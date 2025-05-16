<?php

?>
<div class="section-content">
    <?php
    if (isset($_SESSION["success"])) {
        echo '<div class="alert alert-success" style="display: flex; align-items: center; background-color: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #c3e6cb;">
            <i class="fas fa-check-circle" style="margin-right: 10px; font-size: 1.2em;"></i>
            <span style="flex: 1;">' . htmlspecialchars($_SESSION["success"]) . '</span>
        </div>';
        unset($_SESSION["success"]);
    }
    if (isset($_SESSION["error"])) {
        echo '<div class="alert alert-error" style="display: flex; align-items: center; background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #f5c6cb;">
            <i class="fas fa-exclamation-circle" style="margin-right: 10px; font-size: 1.2em;"></i>
            <span style="flex: 1;">' . htmlspecialchars($_SESSION["error"]) . '</span>
        </div>';
        unset($_SESSION["error"]);
    }
    ?>
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

    <h2>List of Students (A.Y 2024-2025 2nd Semester)</h2>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <div style="display: flex; align-items: center;">
                <span style="margin-right: 10px;">Sort by:</span>
                <div style="position: relative; display: inline-block;">
                    <select style="padding: 8px 30px 8px 10px; border-radius: 4px; border: 1px solid #ddd; appearance: none;">
                        <option>Year</option>
                        <option>Name</option>
                        <option>Section</option>
                        <option>Student No</option>
                    </select>
                    <i class="fas fa-chevron-down" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #666;"></i>
                </div>
            </div>
            <a href="index.php?section=students&showModal=addStudent" class="btn btn-primary" style="padding: 8px 15px; display: flex; align-items: center; gap: 5px; border-radius: 4px; cursor: pointer; text-decoration: none;">
                <i class="fas fa-plus"></i> New Student Record
            </a>
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
                    <?php
                    include("database/connection.php");

                    $query = "SELECT * FROM students ORDER BY last_name ASC";
                    $result = mysqli_query($conn, $query);

                    if (mysqli_num_rows($result) > 0) {
                        $counter = 1;
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>" . $counter . "</td>";
                            echo "<td>
                                    <div class='stud-avatar'>
                                        <img src='img/person.png' alt='Student Photo'>
                                    </div>
                                </td>";
                            echo "<td>" . $row['student_no'] . "</td>";
                            echo "<td>" . $row['last_name'] . "</td>";
                            echo "<td>" . $row['first_name'] . "</td>";
                            echo "<td>" . $row['mi'] . "</td>";
                            echo "<td>" . $row['year_level'] . "</td>";
                            echo "<td>" . $row['section'] . "</td>";
                            echo "<td>" . $row['address'] . ", " . $row['city'] . ", " . $row['province'] . "</td>";
                            echo "<td>
                                    <button class='action-btn'>
                                        <i class='fas fa-edit'></i>
                                    </button>
                                </td>";
                            echo "</tr>";
                            $counter++;
                        }
                    } else {
                        echo "<tr><td colspan='10' style='text-align: center; padding: 20px;'>
                                <i class='fas fa-info-circle' style='margin-right: 10px; color: #666;'></i>No results were found
                              </td></tr>";
                    }
                    ?>
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
                <button style="padding: 6px 12px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;">Previous</button>
                <button style="padding: 6px 12px; background: #1c3d7a; color: white; border: none; border-radius: 4px; cursor: pointer;">1</button>
                <button style="padding: 6px 12px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;">2</button>
                <button style="padding: 6px 12px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;">3</button>
                <button style="padding: 6px 12px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;">Next</button>
            </div>
        </div>
    </div>
</div>
