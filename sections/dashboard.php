<?php
include("database/connection.php");

$whereClause = "WHERE 1=1";
$params = [];

if (isset($_GET['academic_year']) && !empty($_GET['academic_year'])) {
    $whereClause .= " AND academic = ?";
    $params[] = $_GET['academic_year'];
}

if (isset($_GET['semester']) && !empty($_GET['semester'])) {
    $whereClause .= " AND semester = ?";
    $params[] = $_GET['semester'];
}

$ageGroups = [
    ['min' => 17, 'max' => 18],
    ['min' => 19, 'max' => 20],
    ['min' => 21, 'max' => 22],
    ['min' => 23, 'max' => 24],
    ['min' => 25, 'max' => 100],
];
$ageLabels = ['17-18', '19-20', '21-22', '23-24', '25+'];
$ageCounts = [];

foreach ($ageGroups as $group) {
    $min = $group['min'];
    $max = $group['max'];

    if ($max >= 100) {
        $query = "SELECT COUNT(*) as total FROM students $whereClause AND TIMESTAMPDIFF(YEAR, birthday, CURDATE()) >= ?";
        $queryParams = array_merge($params, [$min]);
    } else {
        $query = "SELECT COUNT(*) as total FROM students $whereClause AND TIMESTAMPDIFF(YEAR, birthday, CURDATE()) >= ? AND TIMESTAMPDIFF(YEAR, birthday, CURDATE()) <= ?";
        $queryParams = array_merge($params, [$min, $max]);
    }

    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        if (!empty($queryParams)) {
            $types = str_repeat('s', count($params)) . str_repeat('i', count($queryParams) - count($params));
            mysqli_stmt_bind_param($stmt, $types, ...$queryParams);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $ageCounts[] = (int) $row['total'];
        mysqli_stmt_close($stmt);
    }
}

$genderLabels = [];
$genderCounts = [];
$queryGender = "SELECT sex, COUNT(*) as total FROM students $whereClause AND sex IS NOT NULL AND sex != '' GROUP BY sex ORDER BY total DESC";

$stmt = mysqli_prepare($conn, $queryGender);
if ($stmt) {
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $genderLabels[] = $row['sex'];
        $genderCounts[] = (int) $row['total'];
    }
    mysqli_stmt_close($stmt);
}

$years = [1, 2, 3, 4, 5];
$yearLabels = ['1st', '2nd', '3rd', '4th', '5th'];
$regularCounts = [];
$irregularCounts = [];

foreach ($years as $year) {
    $queryReg = "SELECT COUNT(*) as total FROM students $whereClause AND year_level = ? AND academic_status = ?";
    $queryParams = array_merge($params, [$year, 'Regular']);

    $stmt = mysqli_prepare($conn, $queryReg);
    if ($stmt) {
        $types = str_repeat('s', count($params)) . 'is';
        mysqli_stmt_bind_param($stmt, $types, ...$queryParams);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $regularCounts[] = (int) $row['total'];
        mysqli_stmt_close($stmt);
    }

    $queryIrreg = "SELECT COUNT(*) as total FROM students $whereClause AND year_level = ? AND academic_status = ?";
    $queryParams = array_merge($params, [$year, 'Irregular']);

    $stmt = mysqli_prepare($conn, $queryIrreg);
    if ($stmt) {
        $types = str_repeat('s', count($params)) . 'is';
        mysqli_stmt_bind_param($stmt, $types, ...$queryParams);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $irregularCounts[] = (int) $row['total'];
        mysqli_stmt_close($stmt);
    }
}

$cityCounts = [];
$cityLabels = [];
$queryCity = "SELECT city, COUNT(*) as total FROM students $whereClause AND city IS NOT NULL AND city != '' GROUP BY city ORDER BY total DESC";

$stmt = mysqli_prepare($conn, $queryCity);
if ($stmt) {
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $cityLimit = 5;
    $cityIndex = 0;
    $othersCity = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        if ($cityIndex < $cityLimit) {
            $cityLabels[] = $row['city'];
            $cityCounts[] = (int) $row['total'];
        } else {
            $othersCity += (int) $row['total'];
        }
        $cityIndex++;
    }

    if ($othersCity > 0) {
        $cityLabels[] = 'Others';
        $cityCounts[] = $othersCity;
    }
    mysqli_stmt_close($stmt);
}

$provinceCounts = [];
$provinceLabels = [];
$queryProv = "SELECT province, COUNT(*) as total FROM students $whereClause AND province IS NOT NULL AND province != '' GROUP BY province ORDER BY total DESC";

$stmt = mysqli_prepare($conn, $queryProv);
if ($stmt) {
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $provinceLimit = 4;
    $provIndex = 0;
    $othersProv = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        if ($provIndex < $provinceLimit) {
            $provinceLabels[] = $row['province'];
            $provinceCounts[] = (int) $row['total'];
        } else {
            $othersProv += (int) $row['total'];
        }
        $provIndex++;
    }

    if ($othersProv > 0) {
        $provinceLabels[] = 'Others';
        $provinceCounts[] = $othersProv;
    }
    mysqli_stmt_close($stmt);
}

$sectionCounts = [];
$sectionLabels = [];
$yearSectionTotals = [];
$yearLabelsForTotal = ['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'];

foreach ($yearLabelsForTotal as $yearLabel) {
    $yearSectionTotals[$yearLabel] = 0;
}

$querySection = "SELECT CONCAT(year_level, '-', section) as year_section, COUNT(*) as total 
                FROM students $whereClause AND section IS NOT NULL AND section != '' 
                GROUP BY year_level, section 
                ORDER BY year_level, section";

$stmt = mysqli_prepare($conn, $querySection);
if ($stmt) {
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $sectionLimit = 15;
    $sectionIndex = 0;
    $othersSection = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $yearSection = explode('-', $row['year_section']);
        $yearLevel = isset($yearSection[0]) ? (int) $yearSection[0] : 0;
        $yearLabel = $yearLevel . (
            $yearLevel == 1 ? 'st Year' :
            ($yearLevel == 2 ? 'nd Year' :
                ($yearLevel == 3 ? 'rd Year' : 'th Year'))
        );

        if (isset($yearSectionTotals[$yearLabel])) {
            $yearSectionTotals[$yearLabel] += (int) $row['total'];
        }

        if ($sectionIndex < $sectionLimit) {
            $sectionLabels[] = $row['year_section'];
            $sectionCounts[] = (int) $row['total'];
        } else {
            $othersSection += (int) $row['total'];
        }
        $sectionIndex++;
    }

    if ($othersSection > 0 && $sectionIndex > $sectionLimit) {
        $sectionLabels[] = 'Others';
        $sectionCounts[] = $othersSection;
    }
    mysqli_stmt_close($stmt);
}

$yearTotalLabels = array_keys($yearSectionTotals);
$yearTotalCounts = array_values($yearSectionTotals);

$genderSectionData = [];
$queryGenderSection = "SELECT CONCAT(year_level, '-', section) as year_section, sex, COUNT(*) as total 
                      FROM students $whereClause AND section IS NOT NULL AND section != '' AND sex IS NOT NULL AND sex != ''
                      GROUP BY year_level, section, sex 
                      ORDER BY year_level, section, sex";

$stmt = mysqli_prepare($conn, $queryGenderSection);
if ($stmt) {
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $section = $row['year_section'];
        $gender = $row['sex'];
        $count = (int) $row['total'];

        if (!isset($genderSectionData[$section])) {
            $genderSectionData[$section] = [];
        }
        $genderSectionData[$section][$gender] = $count;
    }
    mysqli_stmt_close($stmt);
}

?>
<style>
    .chart-flex-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-top: 20px;
    }

    .chart-row {
        display: flex;
        flex-wrap: wrap;
        width: 100%;
        gap: 20px;
        margin-bottom: 20px;
    }

    .chart-col {
        flex: 1;
        min-width: 30%;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .stats-card {
        text-align: center;
        padding: 25px 15px;
        border-radius: 12px;
        position: relative;
        overflow: hidden;
        border-bottom: 4px solid transparent;
    }

    .stats-card:nth-child(1) {
        border-bottom-color: #4e73df;
    }

    .stats-card:nth-child(2) {
        border-bottom-color: #1cc88a;
    }

    .stats-card:nth-child(3) {
        border-bottom-color: #e74a3b;
    }

    .stats-card h3 {
        font-size: 1rem;
        margin-top: 0;
        margin-bottom: 15px;
        color: #555;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stats-card h3 i {
        margin-right: 8px;
        color: #0a1f44;
        font-size: 1.2rem;
    }

    .stats-card .count {
        font-size: 2.5rem;
        font-weight: 700;
        color: #0a1f44;
        margin: 10px 0 5px;
        text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.05);
    }

    .stats-card .count-label {
        color: #666;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .stats-card:hover {
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
        transform: translateY(-5px);
    }

    .stats-card:before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(to right, transparent, rgba(78, 115, 223, 0.2), transparent);
    }

    .stats-card:nth-child(2):before {
        background: linear-gradient(to right, transparent, rgba(28, 200, 138, 0.2), transparent);
    }

    .stats-card:nth-child(3):before {
        background: linear-gradient(to right, transparent, rgba(231, 74, 59, 0.2), transparent);
    }

    .chart-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        transition: all 0.3s ease;
        width: 100%;
        height: 500px;
        display: flex;
        flex-direction: column;
    }

    .chart-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .chart-header {
        padding: 15px 20px;
        border-bottom: 1px solid #f0f0f0;
        background-color: #fcfcfc;
        flex-shrink: 0;
    }

    .chart-header h4 {
        margin: 0;
        color: #0a1f44;
        font-size: 1.1rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: space-between;
        line-height: 1.4;
    }

    .chart-header h4 i {
        margin-right: 8px;
        color: #1a73e8;
        font-size: 1rem;
    }

    .chart-body {
        padding: 15px;
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
        flex: 1;
        min-height: 250px;
    }

    .chart-stats {
        margin-top: 15px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        flex-shrink: 0;
        max-height: 100px;
        overflow-y: auto;
    }

    .stat-badge {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
        min-width: 100px;
    }

    .stat-label {
        font-weight: 500;
    }

    .stat-value {
        font-weight: 700;
    }

    .status-bars-container {
        display: flex;
        flex-direction: column;
        gap: 15px;
        flex: 1;
        overflow-y: auto;
    }

    .status-bar {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .status-bar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.9rem;
    }

    .status-bar-title {
        font-weight: 600;
    }

    .status-bar-total {
        color: #666;
    }

    .status-bar-graph {
        display: flex;
        height: 28px;
        border-radius: 14px;
        overflow: hidden;
        background: #f1f1f1;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .status-bar-segment {
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.85rem;
        font-weight: 700;
        transition: all 0.3s ease;
    }

    .status-bar-segment.regular {
        background: #1cc88a;
        background-image: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(0, 0, 0, 0.1) 100%);
    }

    .status-bar-segment.irregular {
        background: #e74a3b;
        background-image: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(0, 0, 0, 0.1) 100%);
    }

    .status-bar-legend {
        display: flex;
        justify-content: space-between;
        font-size: 0.85rem;
    }

    .status-legend-item {
        display: flex;
        align-items: center;
    }

    .status-legend-item.regular {
        color: #1cc88a;
    }

    .status-legend-item.irregular {
        color: #e74a3b;
    }

    .status-legend-item:before {
        content: '';
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 5px;
    }

    .status-legend-item.regular:before {
        background: #1cc88a;
    }

    .status-legend-item.irregular:before {
        background: #e74a3b;
    }

    .address-chart-container {
        display: flex;
        flex-direction: column;
        gap: 20px;
        height: 100%;
        flex: 1;
    }

    .address-table-container {
        width: 100%;
        border: 1px solid #f0f0f0;
        border-radius: 8px;
        overflow: hidden;
        flex: 1;
        max-height: 220px;
        overflow-y: auto;
    }

    .address-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }

    .address-table th,
    .address-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #f0f0f0;
    }

    .address-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #0a1f44;
        border-bottom: 2px solid #e9ecef;
    }

    .address-table tr:hover {
        background-color: #f8f9fa;
    }

    .address-table tr:last-child td {
        border-bottom: none;
    }

    .address-table td:last-child,
    .address-table th:last-child {
        text-align: right;
    }

    .address-color-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 8px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        flex-shrink: 0;
        display: inline-block;
    }

    .address-label-cell {
        display: flex;
        align-items: center;
        font-weight: 500;
    }

    .pie-chart-container {
        width: 220px;
        height: 220px;
        position: relative;
        margin: 0 auto;
        flex-shrink: 0;
    }

    .toggle-btn {
        border: none;
        background: #f0f4f9;
        color: #1a73e8;
        padding: 6px 12px;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .toggle-btn:hover {
        background: #e8f0fe;
        color: #1967d2;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
    }

    .toggle-btn i {
        font-size: 0.75rem;
    }

    .toggle-active {
        transform: scale(0.95);
        background: #e8f0fe;
    }

    .section-view-container {
        display: flex;
        flex-direction: column;
        gap: 20px;
        align-items: stretch;
        height: 100%;
        flex: 1;
    }

    .section-chart-container {
        width: 100%;
        position: relative;
        height: 220px;
        order: 2;
        flex-shrink: 0;
    }

    .section-table-container {
        width: 100%;
        max-height: 200px;
        overflow-y: auto;
        margin-top: 0;
        scrollbar-width: thin;
        scrollbar-color: rgba(0, 0, 0, 0.2) transparent;
        border: 1px solid #f0f0f0;
        border-radius: 8px;
        order: 1;
        flex: 1;
    }

    .section-table-container::-webkit-scrollbar {
        width: 6px;
    }

    .section-table-container::-webkit-scrollbar-track {
        background: transparent;
    }

    .section-table-container::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, 0.2);
        border-radius: 3px;
    }

    .section-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
    }

    .section-table th,
    .section-table td {
        padding: 8px 12px;
        text-align: left;
        border-bottom: 1px solid #f0f0f0;
    }

    .section-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #0a1f44;
        position: sticky;
        top: 0;
        z-index: 10;
        box-shadow: 0 1px 0 #f0f0f0;
    }

    .section-table tr:hover {
        background-color: #f8f9fa;
        cursor: pointer;
    }

    .section-table tr.highlighted-section {
        background-color: #e8f0fe;
        animation: highlight-pulse 1.5s ease-in-out;
    }

    @keyframes highlight-pulse {
        0% {
            background-color: #e8f0fe;
        }

        50% {
            background-color: #c7dbfc;
        }

        100% {
            background-color: #e8f0fe;
        }
    }

    .section-table td:last-child,
    .section-table th:last-child {
        text-align: right;
    }

    .section-year-group {
        background-color: #f0f4f9;
        font-weight: 600;
        position: relative;
    }

    .section-year-group td {
        padding: 10px 12px;
    }

    .gender-view-container {
        display: flex;
        flex-direction: column;
        gap: 20px;
        align-items: stretch;
        height: 100%;
        flex: 1;
    }

    .gender-chart-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 15px;
        flex-shrink: 0;
    }

    .gender-legend {
        display: flex;
        justify-content: center;
        gap: 20px;
        flex-wrap: wrap;
        font-size: 0.9rem;
    }

    .gender-legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
    }

    .gender-legend-color {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .gender-table-container {
        width: 100%;
        max-height: 200px;
        overflow-y: auto;
        margin-top: 0;
        scrollbar-width: thin;
        scrollbar-color: rgba(0, 0, 0, 0.2) transparent;
        border: 1px solid #f0f0f0;
        border-radius: 8px;
        flex: 1;
    }

    .gender-table-container::-webkit-scrollbar {
        width: 6px;
    }

    .gender-table-container::-webkit-scrollbar-track {
        background: transparent;
    }

    .gender-table-container::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, 0.2);
        border-radius: 3px;
    }

    .gender-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
    }

    .gender-table th,
    .gender-table td {
        padding: 8px 10px;
        text-align: left;
        border-bottom: 1px solid #f0f0f0;
    }

    .gender-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #0a1f44;
        position: sticky;
        top: 0;
        z-index: 10;
        box-shadow: 0 1px 0 #f0f0f0;
        font-size: 0.8rem;
    }

    .gender-table tr:hover {
        background-color: #f8f9fa;
        cursor: pointer;
    }

    .gender-table td:nth-child(2),
    .gender-table td:nth-child(3),
    .gender-table td:nth-child(4),
    .gender-table td:nth-child(5),
    .gender-table td:nth-child(6),
    .gender-table th:nth-child(2),
    .gender-table th:nth-child(3),
    .gender-table th:nth-child(4),
    .gender-table th:nth-child(5),
    .gender-table th:nth-child(6) {
        text-align: center;
    }
</style>
<div class="filters-bar">
    <div class="search-filter">
        <form id="filterForm" method="GET" action=""
            style="display: flex; gap: 15px; width: 100%; align-items: center;">
            <label for="academic_year" style="min-width: 110px;">Academic Year</label>
            <select id="academic_year" name="academic_year" onchange="document.getElementById('filterForm').submit();"
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
            <label for="semester" style="min-width: 80px;">Semester</label>
            <select id="semester" name="semester" onchange="document.getElementById('filterForm').submit();"
                style="min-width: 150px;">
                <option value="">All Semesters</option>
                <?php $semesters = [
                    '1st Semester' => '1st Semester',
                    '2nd Semester' => '2nd Semester'
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
<div class="card dashboard-main">
    <div class="stats-grid">
        <div class="card stats-card hov">
            <h3><i class="fas fa-users"></i> Total Students</h3>
            <div class="count">
                <?php
                $query = "SELECT COUNT(*) as total FROM students $whereClause";
                $stmt = mysqli_prepare($conn, $query);
                if ($stmt) {
                    if (!empty($params)) {
                        $types = str_repeat('s', count($params));
                        mysqli_stmt_bind_param($stmt, $types, ...$params);
                    }
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $row = mysqli_fetch_assoc($result);
                    echo $row['total'];
                    mysqli_stmt_close($stmt);
                }
                ?>
            </div>
            <div class="count-label">Enrolled Students</div>
        </div>
        <div class="card stats-card hov">
            <h3><i class="fas fa-male"></i> Total Male</h3>
            <div class="count">
                <?php
                $query = "SELECT COUNT(*) as total FROM students $whereClause AND sex = ?";
                $queryParams = array_merge($params, ['Male']);
                $stmt = mysqli_prepare($conn, $query);
                if ($stmt) {
                    $types = str_repeat('s', count($queryParams));
                    mysqli_stmt_bind_param($stmt, $types, ...$queryParams);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $row = mysqli_fetch_assoc($result);
                    echo $row['total'];
                    mysqli_stmt_close($stmt);
                }
                ?>
            </div>
            <div class="count-label">Male Students</div>
        </div>
        <div class="card stats-card hov">
            <h3><i class="fas fa-female"></i> Total Female</h3>
            <div class="count">
                <?php
                $query = "SELECT COUNT(*) as total FROM students $whereClause AND sex = ?";
                $queryParams = array_merge($params, ['Female']);
                $stmt = mysqli_prepare($conn, $query);
                if ($stmt) {
                    $types = str_repeat('s', count($queryParams));
                    mysqli_stmt_bind_param($stmt, $types, ...$queryParams);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $row = mysqli_fetch_assoc($result);
                    echo $row['total'];
                    mysqli_stmt_close($stmt);
                }
                ?>
            </div>
            <div class="count-label">Female Students</div>
        </div>
    </div>
    <div class="chart-flex-container">
        <div class="chart-row">
            <div class="chart-col">
                <div class="chart-card">
                    <div class="chart-header">
                        <h4><i class="fas fa-chart-bar"></i> Student Demographics (Age)</h4>
                    </div>
                    <div class="chart-body">
                        <div class="chart-container">
                            <canvas id="ageBarChart"></canvas>
                        </div>
                        <div id="agePercentages" class="chart-stats"></div>
                    </div>
                </div>
            </div>
            <div class="chart-col">
                <div class="chart-card">
                    <div class="chart-header">
                        <h4><i class="fas fa-venus-mars"></i> Gender Distribution</h4>
                    </div>
                    <div class="chart-body">
                        <div class="gender-view-container">
                            <div class="gender-chart-container">
                                <div class="pie-chart-container">
                                    <canvas id="genderPieChart"></canvas>
                                </div>
                                <div id="genderLegend" class="gender-legend"></div>
                            </div>
                            <div class="gender-table-container">
                                <table class="gender-table">
                                    <thead>
                                        <tr>
                                            <th>Section</th>
                                            <th>Male</th>
                                            <th>Female</th>
                                            <th>Total</th>
                                            <th>M%</th>
                                            <th>F%</th>
                                        </tr>
                                    </thead>
                                    <tbody id="genderTableBody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="chart-col">
                <div class="chart-card">
                    <div class="chart-header">
                        <h4><i class="fas fa-graduation-cap"></i> Student Status by Year Level</h4>
                    </div>
                    <div class="chart-body">
                        <div id="statusBarsContainer" class="status-bars-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="chart-row">
            <div class="chart-col">
                <div class="chart-card">
                    <div class="chart-header">
                        <h4>
                            <div><i class="fas fa-map-marker-alt"></i> Student Address</div>
                            <button id="toggleAddressType" class="toggle-btn">
                                <span id="toggleBtnText">By Province</span>
                                <i class="fas fa-exchange-alt"></i>
                            </button>
                        </h4>
                    </div>
                    <div class="chart-body address-chart-container">
                        <div class="address-table-container">
                            <table class="address-table" id="addressTable">
                                <thead>
                                    <tr>
                                        <th id="addressTypeHeader">City</th>
                                        <th>Students</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody id="addressTableBody">
                                </tbody>
                            </table>
                        </div>
                        <div class="pie-chart-container">
                            <canvas id="addressPieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="chart-col">
                <div class="chart-card">
                    <div class="chart-header">
                        <h4><i class="fas fa-users"></i> Students per Year Level</h4>
                    </div>
                    <div class="chart-body">
                        <div class="chart-container">
                            <canvas id="yearTotalChart"></canvas>
                        </div>
                        <div id="yearTotalPercentages" class="chart-stats"></div>
                    </div>
                </div>
            </div>
            <div class="chart-col">
                <div class="chart-card">
                    <div class="chart-header">
                        <h4>
                            <i class="fas fa-layer-group"></i> Students per Section
                        </h4>
                    </div>
                    <div class="chart-body">
                        <div class="section-view-container">
                            <div class="section-chart-container">
                                <canvas id="sectionDonutChart"></canvas>
                            </div>
                            <div class="section-table-container">
                                <table class="section-table">
                                    <thead>
                                        <tr>
                                            <th>Year & Section</th>
                                            <th>Total Students</th>
                                            <th>Percentage</th>
                                        </tr>
                                    </thead>
                                    <tbody id="sectionTableBody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>    const ageData = {
        labels: <?php echo json_encode($ageLabels); ?>,
        counts: <?php echo json_encode($ageCounts); ?>
    };
    const genderData = {
        labels: <?php echo json_encode($genderLabels); ?>,
        counts: <?php echo json_encode($genderCounts); ?>
    };
    const statusData = {
        regular: <?php echo json_encode($regularCounts); ?>,
        irregular: <?php echo json_encode($irregularCounts); ?>,
        years: <?php echo json_encode($yearLabels); ?>
    };
    const addressData = {
        city: { labels: <?php echo json_encode($cityLabels); ?>, counts: <?php echo json_encode($cityCounts); ?> },
        province: { labels: <?php echo json_encode($provinceLabels); ?>, counts: <?php echo json_encode($provinceCounts); ?> }
    };
    const sectionData = {
        labels: <?php echo json_encode($sectionLabels); ?>,
        counts: <?php echo json_encode($sectionCounts); ?>
    }; const yearTotalData = {
        labels: <?php echo json_encode($yearTotalLabels); ?>,
        counts: <?php echo json_encode($yearTotalCounts); ?>
    }; const genderSectionData = <?php echo json_encode($genderSectionData); ?>;
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script>
    Chart.register(ChartDataLabels);

    Chart.defaults.font.family = "'Arial', sans-serif";
    Chart.defaults.color = '#555';
    Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(10, 31, 68, 0.8)';
    Chart.defaults.plugins.tooltip.titleFont = { weight: 'bold' };
    Chart.defaults.plugins.tooltip.padding = 10;
    Chart.defaults.plugins.tooltip.cornerRadius = 6;
    Chart.defaults.plugins.tooltip.displayColors = true;
    Chart.defaults.plugins.tooltip.boxPadding = 6;

    const colorPalette = {
        primary: ['#1a73e8', '#4285f4', '#5e97f6', '#7baaf7', '#a0c3ff'],
        accent: ['#1cc88a', '#36b9cc', '#4e73df', '#f6c23e', '#e74a3b'],
        regular: '#1cc88a',
        irregular: '#e74a3b',
        yearColors: ['#4e73df', '#1cc88a', '#f6c23e', '#e74a3b', '#36b9cc'],
        background: 'rgba(10, 31, 68, 0.05)',
        border: 'rgba(10, 31, 68, 0.8)'
    };

    const ageBarChart = new Chart(document.getElementById('ageBarChart'), {
        type: 'bar',
        data: {
            labels: ageData.labels,
            datasets: [{
                label: 'Students',
                data: ageData.counts,
                backgroundColor: colorPalette.primary,
                borderColor: colorPalette.border,
                borderWidth: 1,
                borderRadius: 6,
                hoverBackgroundColor: 'rgba(26, 115, 232, 0.8)',
                barPercentage: 0.6,
                categoryPercentage: 0.8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const label = context.dataset.label || '';
                            const value = context.parsed.y || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? (value / total * 100).toFixed(1) + '%' : '0%';
                            return `${label}: ${value} (${percentage})`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        },
                        padding: 10
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        },
                        padding: 5
                    }
                }
            }
        }
    }); const totalAge = ageData.counts.reduce((a, b) => a + b, 0);
    if (totalAge > 0) {
        document.getElementById('agePercentages').innerHTML = ageData.labels.map((label, i) => {
            const percent = ((ageData.counts[i] / totalAge) * 100).toFixed(1);
            const color = colorPalette.primary[i % colorPalette.primary.length];
            return `<div class="stat-badge" style="background-color: ${color}20; border-left: 3px solid ${color}">
                <span class="stat-label">${label}</span>
                <span class="stat-value">${percent}%</span>
            </div>`;
        }).join('');
    } const genderPieChart = new Chart(document.getElementById('genderPieChart'), {
        type: 'doughnut',
        data: {
            labels: genderData.labels,
            datasets: [{
                data: genderData.counts,
                backgroundColor: function (context) {
                    const label = context.chart.data.labels[context.dataIndex];
                    if (label === 'Male') return '#4e73df';
                    if (label === 'Female') return '#e74a3b';
                    return '#1cc88a';
                },
                borderColor: '#fff',
                borderWidth: 2,
                hoverBorderWidth: 0,
                hoverOffset: 10,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            layout: {
                padding: {
                    top: 10,
                    bottom: 10,
                    left: 10,
                    right: 10
                }
            },
            animation: {
                animateRotate: true,
                animateScale: true,
                duration: 800,
                easing: 'easeOutCirc'
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? (value / total * 100).toFixed(1) + '%' : '0%';
                            return `${label}: ${value} (${percentage})`;
                        }
                    }
                },
                datalabels: {
                    formatter: (value, ctx) => {
                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = total > 0 ? (value / total * 100).toFixed(1) + '%' : '0%';
                        const label = ctx.chart.data.labels[ctx.dataIndex];
                        return `${label}\n${percentage}`;
                    },
                    color: '#fff',
                    font: {
                        weight: 'bold',
                        size: 12
                    },
                    textAlign: 'center',
                    textStrokeColor: '#000000',
                    textStrokeWidth: 2,
                    textShadowBlur: 4,
                    textShadowColor: 'rgba(0, 0, 0, 0.7)'
                }
            }
        }
    });

    function createGenderLegend() {
        const legendContainer = document.getElementById('genderLegend');
        legendContainer.innerHTML = '';

        const totalGender = genderData.counts.reduce((a, b) => a + b, 0);

        genderData.labels.forEach((label, i) => {
            const count = genderData.counts[i];
            const percent = totalGender > 0 ? ((count / totalGender) * 100).toFixed(1) : 0;
            let color = '#1cc88a';
            if (label === 'Male') color = '#4e73df';
            if (label === 'Female') color = '#e74a3b';

            const legendItem = document.createElement('div');
            legendItem.className = 'gender-legend-item';
            legendItem.innerHTML = `
                <div class="gender-legend-color" style="background-color: ${color}"></div>
                <span>${label}: ${count} (${percent}%)</span>
            `;
            legendContainer.appendChild(legendItem);
        });
    }

    function updateGenderTable() {
        const tableBody = document.getElementById('genderTableBody');
        tableBody.innerHTML = '';

        Object.keys(genderSectionData).forEach(section => {
            const maleCount = genderSectionData[section]['Male'] || 0;
            const femaleCount = genderSectionData[section]['Female'] || 0;
            const totalCount = maleCount + femaleCount;
            const malePercent = totalCount > 0 ? ((maleCount / totalCount) * 100).toFixed(1) : '0';
            const femalePercent = totalCount > 0 ? ((femaleCount / totalCount) * 100).toFixed(1) : '0';

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${section}</td>
                <td>${maleCount}</td>
                <td>${femaleCount}</td>
                <td>${totalCount}</td>
                <td>${malePercent}%</td>
                <td>${femalePercent}%</td>
            `;
            tableBody.appendChild(row);
        });
    }

    createGenderLegend();
    updateGenderTable();

    function createStatusBars() {
        const container = document.getElementById('statusBarsContainer');
        container.innerHTML = '';

        statusData.years.forEach((year, index) => {
            const regular = statusData.regular[index];
            const irregular = statusData.irregular[index];
            const total = regular + irregular;

            if (total > 0) {
                const regularPercent = (regular / total) * 100;
                const irregularPercent = (irregular / total) * 100;

                const barDiv = document.createElement('div');
                barDiv.className = 'status-bar';
                barDiv.innerHTML = `
                    <div class="status-bar-header">
                        <span class="status-bar-title">${year} Year</span>
                        <span class="status-bar-total">Total: ${total}</span>
                    </div>
                    <div class="status-bar-graph">
                        <div class="status-bar-segment regular" style="width: ${regularPercent}%">
                            ${regular > 0 && regularPercent > 10 ? `${regularPercent.toFixed(1)}%` : ''}
                        </div>
                        <div class="status-bar-segment irregular" style="width: ${irregularPercent}%">
                            ${irregular > 0 && irregularPercent > 10 ? `${irregularPercent.toFixed(1)}%` : ''}
                        </div>
                    </div>
                    <div class="status-bar-legend">
                        <span class="status-legend-item regular">Regular: ${regular}</span>
                        <span class="status-legend-item irregular">Irregular: ${irregular}</span>
                    </div>
                `;
                container.appendChild(barDiv);
            }
        });
    }
    createStatusBars();

    let showCity = true;

    const addressPieChart = new Chart(document.getElementById('addressPieChart'), {
        type: 'doughnut',
        data: {
            labels: addressData.city.labels,
            datasets: [{
                data: addressData.city.counts,
                backgroundColor: colorPalette.accent,
                borderColor: '#fff',
                borderWidth: 2,
                hoverBorderWidth: 0,
                hoverOffset: 10
            }]
        }, options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '50%',
            animation: {
                animateRotate: true,
                animateScale: true,
                duration: 800,
                easing: 'easeOutCirc'
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? (value / total * 100).toFixed(1) + '%' : '0%';
                            return `${label}: ${value} (${percentage})`;
                        }
                    }
                }, datalabels: {
                    formatter: (value, ctx) => {
                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = total > 0 ? (value / total * 100).toFixed(1) + '%' : '0%';
                        const label = ctx.chart.data.labels[ctx.dataIndex];

                        if (value / total > 0.08) {
                            return label.length > 8 ? label.substring(0, 8) + '...\n' + percentage : label + '\n' + percentage;
                        } else {
                            return percentage;
                        }
                    },
                    color: '#ffffff',
                    font: {
                        weight: 'bold',
                        size: 10
                    },
                    textAlign: 'center',
                    textStrokeColor: '#000000',
                    textStrokeWidth: 2,
                    textShadowBlur: 4,
                    textShadowColor: 'rgba(0, 0, 0, 0.8)',
                    anchor: 'center',
                    align: 'center',
                    offset: 0,
                    display: function (ctx) {
                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ctx.dataset.data[ctx.dataIndex] / total;
                        return percentage > 0.05;
                    }
                }
            }
        }
    }); function updateAddressList() {
        const data = showCity ? addressData.city : addressData.province;
        const total = data.counts.reduce((a, b) => a + b, 0);
        const tableBody = document.getElementById('addressTableBody');
        const header = document.getElementById('addressTypeHeader');

        header.textContent = showCity ? 'City' : 'Province';

        tableBody.innerHTML = '';

        data.labels.forEach((label, i) => {
            const count = data.counts[i];
            const percent = total > 0 ? ((count / total) * 100).toFixed(1) : 0;
            const color = colorPalette.accent[i % colorPalette.accent.length];

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <div class="address-label-cell">
                        <div class="address-color-indicator" style="background-color: ${color}"></div>
                        ${label}
                    </div>
                </td>
                <td>${count}</td>
                <td>${percent}%</td>
            `;
            tableBody.appendChild(row);
        });
    }

    function updateAddressChart() {
        const data = showCity ? addressData.city : addressData.province;
        addressPieChart.data.labels = data.labels;
        addressPieChart.data.datasets[0].data = data.counts;
        addressPieChart.update();
        updateAddressList();
        document.getElementById('toggleBtnText').innerText = showCity ? 'By Province' : 'By City';
    }

    document.getElementById('toggleAddressType').onclick = function () {
        this.classList.add('toggle-active');
        setTimeout(() => this.classList.remove('toggle-active'), 300);
        showCity = !showCity;
        updateAddressChart();
    };

    updateAddressChart();

    const yearTotalChart = new Chart(document.getElementById('yearTotalChart'), {
        type: 'bar',
        data: {
            labels: yearTotalData.labels,
            datasets: [{
                label: 'Students',
                data: yearTotalData.counts,
                backgroundColor: colorPalette.yearColors,
                borderColor: colorPalette.border,
                borderWidth: 1,
                borderRadius: 6,
                hoverBackgroundColor: 'rgba(26, 115, 232, 0.8)',
                barPercentage: 0.6,
                categoryPercentage: 0.8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const label = context.dataset.label || '';
                            const value = context.parsed.y || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? (value / total * 100).toFixed(1) + '%' : '0%';
                            return `${label}: ${value} (${percentage})`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        },
                        padding: 10
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        },
                        padding: 5
                    }
                }
            }
        }
    });

    const totalYearStudents = yearTotalData.counts.reduce((a, b) => a + b, 0);
    if (totalYearStudents > 0) {
        document.getElementById('yearTotalPercentages').innerHTML = yearTotalData.labels.map((label, i) => {
            const percent = ((yearTotalData.counts[i] / totalYearStudents) * 100).toFixed(1);
            const color = colorPalette.yearColors[i % colorPalette.yearColors.length];
            return `<div class="stat-badge" style="background-color: ${color}20; border-left: 3px solid ${color}">
                <span class="stat-label">${label}</span>
                <span class="stat-value">${percent}%</span>
            </div>`;
        }).join('');
    }
    const sectionDonutChart = new Chart(document.getElementById('sectionDonutChart'), {
        type: 'doughnut',
        data: {
            labels: sectionData.labels,
            datasets: [{
                data: sectionData.counts,
                backgroundColor: function (context) {
                    const index = context.dataIndex;
                    const label = context.chart.data.labels[index];
                    if (label === 'Others') return '#999';

                    const year = parseInt(label.split('-')[0]);
                    return colorPalette.yearColors[(year - 1) % colorPalette.yearColors.length];
                },
                borderColor: '#fff',
                borderWidth: 2,
                hoverBorderWidth: 0,
                hoverOffset: 12
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '50%',
            layout: {
                padding: {
                    top: 15,
                    bottom: 15,
                    left: 20,
                    right: 20
                }
            },
            animation: {
                animateRotate: true,
                animateScale: true,
                duration: 800,
                easing: 'easeOutCirc'
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? (value / total * 100).toFixed(1) + '%' : '0%';
                            return `${label}: ${value} (${percentage})`;
                        }
                    }
                },
                datalabels: {
                    formatter: (value, ctx) => {
                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = total > 0 ? (value / total * 100).toFixed(1) + '%' : '0%';
                        const label = ctx.chart.data.labels[ctx.dataIndex];

                        const parts = label.split('-');
                        const year = parts[0];
                        const section = parts.length > 1 ? parts[1] : '';

                        if (label === 'Others') {
                            return 'Others\n' + percentage;
                        } else {
                            return `${year}-${section}\n${percentage}`;
                        }
                    },
                    color: '#333',
                    font: {
                        weight: 'bold',
                        size: 11
                    },
                    textAlign: 'center',
                    textStrokeColor: '#fff',
                    textStrokeWidth: 2,
                    textShadowBlur: 6,
                    textShadowColor: 'rgba(255, 255, 255, 0.75)',
                    borderRadius: 4,
                    borderWidth: 1,
                    padding: 4,
                    anchor: function (context) {
                        const value = context.dataset.data[context.dataIndex];
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percent = value / total;

                        return percent < 0.03 ? 'center' : 'end';
                    },
                    align: function (context) {
                        const index = context.dataIndex;
                        const count = context.dataset.data.length;
                        const value = context.dataset.data[context.dataIndex];
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percent = value / total;

                        if (percent < 0.03) return 'end';

                        return 'center';
                    },
                    offset: function (context) {
                        const value = context.dataset.data[context.dataIndex];
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percent = value / total;

                        return percent < 0.05 ? 15 : 8;
                    },
                    display: function (ctx) {
                        return ctx.dataset.data[ctx.dataIndex] / ctx.dataset.data.reduce((a, b) => a + b, 0) > 0.02;
                    },
                    listeners: {
                        click: function (context) {
                            const label = context.chart.data.labels[context.dataIndex];
                            highlightSectionInTable(label);
                        }
                    }
                }
            }
        }
    }); window.addEventListener('load', function () {
        createSectionTable();
    });

    function createSectionTable() {
        const tableBody = document.getElementById('sectionTableBody');
        tableBody.innerHTML = '';

        const total = sectionData.counts.reduce((a, b) => a + b, 0);

        const yearGroups = {};
        sectionData.labels.forEach((label, i) => {
            if (label === 'Others') {
                if (!yearGroups['Others']) yearGroups['Others'] = [];
                yearGroups['Others'].push({ label, count: sectionData.counts[i] });
                return;
            }

            const parts = label.split('-');
            const year = parts[0];
            const yearKey = year + (
                year == 1 ? 'st Year' :
                    (year == 2 ? 'nd Year' :
                        (year == 3 ? 'rd Year' : 'th Year'))
            );

            if (!yearGroups[yearKey]) yearGroups[yearKey] = [];
            yearGroups[yearKey].push({ label, count: sectionData.counts[i] });
        });

        Object.keys(yearGroups).forEach(yearKey => {
            const yearTotalCount = yearGroups[yearKey].reduce((sum, item) => sum + item.count, 0);
            const yearRow = document.createElement('tr');
            yearRow.className = 'section-year-group';
            yearRow.innerHTML = `
                <td colspan="2">${yearKey}</td>
                <td>${((yearTotalCount / total) * 100).toFixed(1)}%</td>
            `;
            tableBody.appendChild(yearRow);

            yearGroups[yearKey].forEach(item => {
                const sectionRow = document.createElement('tr');
                sectionRow.dataset.section = item.label;
                let color = '#999';
                if (item.label !== 'Others') {
                    const year = parseInt(item.label.split('-')[0]);
                    color = colorPalette.yearColors[(year - 1) % colorPalette.yearColors.length];
                }

                sectionRow.innerHTML = `
                    <td>
                        <div style="display: flex; align-items: center;">
                            <div style="width: 10px; height: 10px; border-radius: 50%; background-color: ${color}; margin-right: 8px;"></div>
                            ${item.label}
                        </div>
                    </td>
                    <td>${item.count}</td>
                    <td>${((item.count / total) * 100).toFixed(1)}%</td>
                `;

                sectionRow.addEventListener('click', function () {
                    highlightSectionInChart(item.label);
                });

                tableBody.appendChild(sectionRow);
            });
        });
    }

    function highlightSectionInTable(sectionLabel) {
        const rows = document.querySelectorAll('#sectionTableBody tr');
        rows.forEach(row => {
            row.classList.remove('highlighted-section');
        });

        const targetRow = document.querySelector(`#sectionTableBody tr[data-section="${sectionLabel}"]`);
        if (targetRow) {
            targetRow.classList.add('highlighted-section');

            targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function highlightSectionInChart(sectionLabel) {
        const chart = sectionDonutChart;
        const activeElements = chart.getActiveElements();

        if (activeElements.length > 0) {
            chart.setActiveElements([]);
        }

        const index = chart.data.labels.indexOf(sectionLabel);
        if (index !== -1) {
            chart.setActiveElements([{
                datasetIndex: 0,
                index: index
            }]);

            chart.update();
        }
    }

    const genderSectionChart = new Chart(document.getElementById('genderSectionChart'), {
        type: 'bar',
        data: {
            labels: Object.keys(genderSectionData),
            datasets: [
                {
                    label: 'Male Students',
                    data: Object.keys(genderSectionData).map(section => genderSectionData[section]['Male'] || 0),
                    backgroundColor: '#4e73df',
                    borderColor: '#fff',
                    borderWidth: 2,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                },
                {
                    label: 'Female Students',
                    data: Object.keys(genderSectionData).map(section => genderSectionData[section]['Female'] || 0),
                    backgroundColor: '#e74a3b',
                    borderColor: '#fff',
                    borderWidth: 2,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'start',
                    labels: {
                        boxWidth: 12,
                        padding: 15,
                        color: '#333',
                        font: {
                            weight: 'bold',
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const label = context.dataset.label || '';
                            const value = context.parsed.y || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? (value / total * 100).toFixed(1) + '%' : '0%';
                            return `${label}: ${value} (${percentage})`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        },
                        padding: 10
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        },
                        padding: 5
                    }
                }
            }
        }
    });
</script>