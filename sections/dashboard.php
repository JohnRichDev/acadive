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
    }

    .chart-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .chart-header {
        padding: 15px 20px;
        border-bottom: 1px solid #f0f0f0;
        background-color: #fcfcfc;
    }

    .chart-header h4 {
        margin: 0;
        color: #0a1f44;
        font-size: 1.1rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .chart-header h4 i {
        margin-right: 8px;
        color: #1a73e8;
    }

    .chart-body {
        padding: 15px;
    }

    .chart-container {
        position: relative;
        height: 250px;
        width: 100%;
    }

    .chart-stats {
        margin-top: 15px;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
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
        gap: 15px;
    }

    .address-list-container {
        flex: 1;
    }    .address-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        font-size: 0.9rem;
        max-height: 200px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: rgba(0, 0, 0, 0.2) transparent;
        padding-right: 4px;
    }
    
    .address-list::-webkit-scrollbar {
        width: 6px;
    }
    
    .address-list::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .address-list::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, 0.2);
        border-radius: 3px;
    }

    .address-item {
        display: flex;
        align-items: center;
        padding: 8px 10px;
        background: #f8f9fa;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    .address-item:hover {
        background: #f0f4f8;
        transform: translateX(3px);
    }

    .address-color-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 8px;
    }

    .address-label {
        flex: 1;
        font-weight: 600;
    }

    .address-count {
        color: #555;
    }

    .address-percent {
        font-size: 0.8rem;
        color: #777;
    }

    .pie-chart-container {
        width: 180px;
        height: 180px;
        position: relative;
    }

    .toggle-btn {
        border: none;
        background: #f0f4f9;
        color: #1a73e8;
        padding: 6px 12px;
        border-radius: 50px;
        font-size: 0.8rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }

    .toggle-btn:hover {
        background: #e8f0fe;
        color: #1967d2;
    }

    .toggle-btn i {
        font-size: 0.7rem;
    }

    .toggle-active {
        transform: scale(0.95);
        background: #e8f0fe;
    }

    .section-view-container {
        display: flex;
        flex-direction: row;
        gap: 20px;
    }

    .section-chart-container {
        flex: 1;
        min-width: 45%;
    }    .section-table-container {
        flex: 1;
        min-width: 45%;
        max-height: 250px;
        overflow-y: auto;
        margin-top: 0;
        scrollbar-width: thin;
        scrollbar-color: rgba(0, 0, 0, 0.2) transparent;
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
    }    .section-table th {
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
    }

    .section-table td:last-child,
    .section-table th:last-child {
        text-align: right;
    }    .section-year-group {
        background-color: #f0f4f9;
        font-weight: 600;
        position: relative;
    }
    
    .section-year-group td {
        padding: 10px 12px;
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
                $query = "SELECT COUNT(*) as total FROM students $whereClause AND gender = ?";
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
                $query = "SELECT COUNT(*) as total FROM students $whereClause AND gender = ?";
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
                        <h4><i class="fas fa-graduation-cap"></i> Student Status by Year Level</h4>
                    </div>
                    <div class="chart-body">
                        <div id="statusBarsContainer" class="status-bars-container"></div>
                    </div>
                </div>
            </div>

            <div class="chart-col">
                <div class="chart-card">
                    <div class="chart-header">
                        <h4>
                            <i class="fas fa-map-marker-alt"></i> Student Address
                            <button id="toggleAddressType" class="toggle-btn">
                                <span id="toggleBtnText">By Province</span>
                                <i class="fas fa-exchange-alt"></i>
                            </button>
                        </h4>
                    </div>
                    <div class="chart-body address-chart-container">
                        <div class="address-list-container">
                            <div id="addressList" class="address-list"></div>
                        </div>
                        <div class="pie-chart-container">
                            <canvas id="addressPieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="chart-row">
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
                                <canvas id="sectionBarChart"></canvas>
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

<script>
    const ageData = {
        labels: <?php echo json_encode($ageLabels); ?>,
        counts: <?php echo json_encode($ageCounts); ?>
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
    };
    const yearTotalData = {
        labels: <?php echo json_encode($yearTotalLabels); ?>,
        counts: <?php echo json_encode($yearTotalCounts); ?>
    };
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script>
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
    });

    const totalAge = ageData.counts.reduce((a, b) => a + b, 0);
    if (totalAge > 0) {
        document.getElementById('agePercentages').innerHTML = ageData.labels.map((label, i) => {
            const percent = ((ageData.counts[i] / totalAge) * 100).toFixed(1);
            const color = colorPalette.primary[i % colorPalette.primary.length];
            return `<div class="stat-badge" style="background-color: ${color}20; border-left: 3px solid ${color}">
                <span class="stat-label">${label}</span>
                <span class="stat-value">${percent}%</span>
            </div>`;
        }).join('');
    }

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
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
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
                }
            }
        }
    });

    function updateAddressList() {
        const data = showCity ? addressData.city : addressData.province;
        const total = data.counts.reduce((a, b) => a + b, 0);

        const listHtml = data.labels.map((label, i) => {
            const count = data.counts[i];
            const percent = total > 0 ? ((count / total) * 100).toFixed(1) : 0;
            const color = colorPalette.accent[i % colorPalette.accent.length];

            return `<div class="address-item">
                <div class="address-color-indicator" style="background-color: ${color}"></div>
                <div class="address-label">${label}</div>
                <div class="address-count">${count} <span class="address-percent">(${percent}%)</span></div>
            </div>`;
        }).join('');

        document.getElementById('addressList').innerHTML = listHtml;
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

    const sectionBarChart = new Chart(document.getElementById('sectionBarChart'), {
        type: 'bar',
        data: {
            labels: sectionData.labels,
            datasets: [{
                label: 'Students',
                data: sectionData.counts,
                backgroundColor: function (context) {
                    const index = context.dataIndex;
                    const label = context.chart.data.labels[index];
                    if (label === 'Others') return '#999';

                    const year = parseInt(label.split('-')[0]);
                    return colorPalette.yearColors[(year - 1) % colorPalette.yearColors.length];
                },
                borderColor: colorPalette.border,
                borderWidth: 1,
                borderRadius: 4,
                barPercentage: 0.8,
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
                            size: 10
                        },
                        padding: 5,
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });

    window.addEventListener('load', function () {
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
                sectionRow.innerHTML = `
                    <td>${item.label}</td>
                    <td>${item.count}</td>
                    <td>${((item.count / total) * 100).toFixed(1)}%</td>
                `;
                tableBody.appendChild(sectionRow);
            });
        });
    }
</script>