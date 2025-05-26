<?php
// acadive/sections/students.php
?>
<style>
    .stud-avatar img {
        width: 48px;
        height: 48px;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid #e0e0e0;
        background: #f5f5f5;
    }
</style>
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
            <form id="filterForm" method="GET" action="" style="display: flex; gap: 15px; width: 100%;">
                <input type="hidden" name="section" value="students">
                <div style="position: relative; display: flex; align-items: center;">
                    <input type="text" name="search" placeholder="Search Students..."
                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                        style="padding-right: 40px;">
                    <button type="submit"
                        style="position: absolute; right: 5px; background: none; border: none; cursor: pointer; padding: 5px;">
                        <i class="fas fa-search" style="color: #666;"></i>
                    </button>
                </div> <select name="academic_year" onchange="document.getElementById('filterForm').submit();"
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
    <h2>List of Students (A.Y <?php
                                $displayAcademic = isset($_GET['academic_year']) && !empty($_GET['academic_year']) ? $_GET['academic_year'] : '2024-2025';
                                $displaySemester = isset($_GET['semester']) && !empty($_GET['semester']) ?
                                    ($semesters[$_GET['semester']] ?? '2nd Semester')
                                    : '2nd Semester';
                                echo $displayAcademic . ' ' . $displaySemester;
                                ?>)</h2>
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <div style="display: flex; align-items: center;"> <span style="margin-right: 10px;">Sort by:</span>
                <div style="position: relative; display: inline-block;"> <select name="sort" form="filterForm"
                        onchange="document.getElementById('filterForm').submit();"
                        style="padding: 8px 30px 8px 10px; border-radius: 4px; border: 1px solid #ddd; appearance: none;">
                        <?php
                        $currentSort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';
                        $sortOptions = [
                            'name_asc' => 'Name (A-Z)',
                            'name_desc' => 'Name (Z-A)',
                            'year_asc' => 'Year (Low to High)',
                            'year_desc' => 'Year (High to Low)',
                            'section_asc' => 'Section (A-Z)',
                            'section_desc' => 'Section (Z-A)'
                        ];
                        foreach ($sortOptions as $value => $label) {
                            $selected = ($currentSort == $value) ? 'selected' : '';
                            echo "<option value=\"$value\" $selected>$label</option>";
                        }
                        ?>
                    </select>
                    <i class="fas fa-chevron-down"
                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #666;"></i>
                </div>
            </div>
            <a href="index.php?section=students&showModal=addStudent" class="btn btn-primary"
                style="padding: 8px 15px; display: flex; align-items: center; gap: 5px; border-radius: 4px; cursor: pointer; text-decoration: none;">
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
                    include("database/connection.php"); //

                    $query = "SELECT * FROM students WHERE 1=1"; //
                    $params = []; //

                    if (isset($_GET['search']) && !empty($_GET['search'])) {
                        $search = '%' . $_GET['search'] . '%';
                        $query .= " AND (student_no LIKE ? OR last_name LIKE ? OR first_name LIKE ? OR mi LIKE ?)"; //
                        $params[] = $search; //
                        $params[] = $search; //
                        $params[] = $search; //
                        $params[] = $search; //
                    }
                    if (isset($_GET['academic_year']) && !empty($_GET['academic_year'])) {
                        $query .= " AND academic = ?"; //
                        $params[] = $_GET['academic_year']; //
                    }

                    if (isset($_GET['semester']) && !empty($_GET['semester'])) {
                        $query .= " AND semester = ?"; //
                        $params[] = $_GET['semester']; //
                    }

                    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'year_asc'; //
                    switch ($sort) { //
                        case 'name_asc': //
                            $query .= " ORDER BY last_name ASC, first_name ASC"; //
                            break; //
                        case 'name_desc': //
                            $query .= " ORDER BY last_name DESC, first_name DESC"; //
                            break; //
                        case 'year_asc': //
                            $query .= " ORDER BY year_level ASC"; //
                            break; //
                        case 'year_desc': //
                            $query .= " ORDER BY year_level DESC"; //
                            break; //
                        case 'section_asc': //
                            $query .= " ORDER BY section ASC"; //
                            break; //
                        case 'section_desc': //
                            $query .= " ORDER BY section DESC"; //
                            break; //
                        default: //
                            $query .= " ORDER BY last_name ASC, first_name ASC"; //
                    }

                    $stmt = mysqli_prepare($conn, $query); //
                    if (!empty($params)) { //
                        $types = str_repeat('s', count($params)); //
                        mysqli_stmt_bind_param($stmt, $types, ...$params); //
                    }
                    mysqli_stmt_execute($stmt); //
                    $result = mysqli_stmt_get_result($stmt); //

                    if (mysqli_num_rows($result) > 0) { //
                        $counter = 1; //
                        while ($row = mysqli_fetch_assoc($result)) { //
                            echo "<tr>"; //
                            echo "<td>" . $counter . "</td>"; //

                            $adviser_id = $_SESSION['user_id']; //
                            $student_no = $row['student_no']; //
                            $student_no = str_replace('-', '', $student_no); //
                            $imgDir = 'img/student_1x1/'; //
                            $imgBase = $adviser_id . '_' . $student_no; //
                            $imgSrc = ''; //
                            $found = false; //
                            $extensions = ['png', 'jpg', 'jpeg', 'webp', 'gif']; //
                            foreach ($extensions as $ext) { //
                                $tryPath = $imgDir . $imgBase . '.' . $ext; //
                                if (file_exists($tryPath)) { //
                                    $imgSrc = $tryPath; //
                                    $found = true; //
                                    break; //
                                }
                            }
                            if (!$found) { //
                                $imgSrc = 'img/person.png'; //
                            }
                            echo "<td>\n    <div class='stud-avatar'>\n        <img src='" . $imgSrc . "' alt='Student Photo'>\n    </div>\n</td>"; //
                            echo "<td>" . htmlspecialchars($row['student_no']) . "</td>"; //
                            echo "<td>" . htmlspecialchars($row['last_name']) . "</td>"; //
                            echo "<td>" . htmlspecialchars($row['first_name']) . "</td>"; //
                            echo "<td>" . htmlspecialchars($row['mi']) . "</td>"; //
                            echo "<td>" . htmlspecialchars($row['year_level']) . "</td>"; //
                            echo "<td>" . htmlspecialchars($row['section']) . "</td>"; //
                            echo "<td>" . htmlspecialchars($row['address'] . ", " . $row['city'] . ", " . $row['province']) . "</td>"; //
                            // **MODIFIED LINE BELOW**
                            echo "<td>
                                    <a href='index.php?section=students&showModal=editStudent&student_id=" . $row['id'] . "' class='action-btn'>
                                        <i class='fas fa-edit'></i>
                                    </a>
                                </td>";
                            echo "</tr>"; //
                            $counter++; //
                        }
                    } else {
                        echo "<tr><td colspan='10' style='text-align: center; padding: 20px;'>
                                <i class='fas fa-info-circle' style='margin-right: 10px; color: #666;'></i>No results were found
                              </td></tr>"; //
                    }
                    mysqli_stmt_close($stmt); //
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