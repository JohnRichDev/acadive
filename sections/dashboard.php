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

?>
<div class="section-content">
    <div class="filters-bar">
        <div class="search-filter">
            <form id="filterForm" method="GET" action=""
                style="display: flex; gap: 15px; width: 100%; align-items: center;">
                <label for="academic_year" style="min-width: 110px;">Academic Year</label>
                <select id="academic_year" name="academic_year"
                    onchange="document.getElementById('filterForm').submit();" style="min-width: 170px;">
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

    <div class="card" style="max-height: 600px; overflow-y: auto; display: flex; flex-direction: column;">
        <div class="grid">
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
            </div>
        </div>
        <div style="display: flex; flex: 1; gap: 30px; align-items: flex-end;">
            <div style="flex: 1; min-width: 280px; max-width: 400px;">
                <h4>Student Demographics (Age)</h4>
                <div style="position: relative; height: 250px; width: 100%;">
                    <canvas id="ageBarChart"></canvas>
                </div>
                <div id="agePercentages" style="margin-top: 10px; font-size: 0.9em;"></div>
            </div>

            <div style="flex: 1; min-width: 280px; max-width: 400px;">
                <h4>Student Status by Year Level</h4>
                <div id="statusBarsContainer"></div>
            </div>

            <div style="flex: 1; min-width: 280px; display: flex; gap: 20px;">
                <div style="flex: 1;">
                    <h4>Student Address
                        <button id="toggleAddressType"
                            style="margin-left: 10px; padding: 2px 10px; font-size: 0.8em;">By Province</button>
                    </h4>
                    <div id="addressList" style="font-size: 0.9em; line-height: 1.6;"></div>
                </div>
                <div style="width: 180px; height: 180px; position: relative;">
                    <canvas id="addressPieChart"></canvas>
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
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ageBarChart = new Chart(document.getElementById('ageBarChart'), {
        type: 'bar',
        data: {
            labels: ageData.labels,
            datasets: [{
                label: 'Students',
                data: ageData.counts,
                backgroundColor: '#4e73df',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    const totalAge = ageData.counts.reduce((a, b) => a + b, 0);
    if (totalAge > 0) {
        document.getElementById('agePercentages').innerHTML = ageData.labels.map((label, i) => {
            const percent = ((ageData.counts[i] / totalAge) * 100).toFixed(1);
            return `<span style='margin-right:15px;'>${label}: <b>${percent}%</b></span>`;
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
                barDiv.style.marginBottom = '15px';
                barDiv.innerHTML = `
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 0.9em;">
                        <span><strong>${year} Year</strong></span>
                        <span>Total: ${total}</span>
                    </div>
                    <div style="display: flex; height: 25px; border-radius: 12px; overflow: hidden; background: #f1f1f1;">
                        <div style="width: ${regularPercent}%; background: #1cc88a; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.8em; font-weight: bold;">
                            ${regular > 0 ? `${regularPercent.toFixed(1)}%` : ''}
                        </div>
                        <div style="width: ${irregularPercent}%; background: #e74a3b; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.8em; font-weight: bold;">
                            ${irregular > 0 ? `${irregularPercent.toFixed(1)}%` : ''}
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 3px; font-size: 0.8em;">
                        <span style="color: #1cc88a;">Regular: ${regular}</span>
                        <span style="color: #e74a3b;">Irregular: ${irregular}</span>
                    </div>
                `;
                container.appendChild(barDiv);
            }
        });
    }
    createStatusBars();

    let showCity = true;
    const addressPieChart = new Chart(document.getElementById('addressPieChart'), {
        type: 'pie',
        data: {
            labels: addressData.city.labels,
            datasets: [{
                data: addressData.city.counts,
                backgroundColor: ['#36b9cc', '#f6c23e', '#1cc88a', '#e74a3b', '#858796', '#5a5c69'],
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    });

    function updateAddressList() {
        const data = showCity ? addressData.city : addressData.province;
        const total = data.counts.reduce((a, b) => a + b, 0);

        const listHtml = data.labels.map((label, i) => {
            const count = data.counts[i];
            const percent = total > 0 ? ((count / total) * 100).toFixed(1) : 0;
            return `<div style="display: flex; justify-content: space-between; margin-bottom: 8px; padding: 5px; background: #f8f9fa; border-radius: 5px;">
                <span><strong>${label}</strong></span>
                <span>${count} (${percent}%)</span>
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
        document.getElementById('toggleAddressType').innerText = showCity ? 'By Province' : 'By City';
    }

    document.getElementById('toggleAddressType').onclick = function () {
        showCity = !showCity;
        updateAddressChart();
    };

    updateAddressChart();
</script>