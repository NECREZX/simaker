<?php
$pageTitle = 'Grafik & Analitik';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireRole('Supervisor');

$unitId = $_SESSION['unit_id'];
$days = 30; // Last 30 days

// Get daily logbook count
$dailyLogbooks = fetchAll("SELECT 
    DATE(logbook_date) as date,
    COUNT(*) as count
    FROM logbooks
    WHERE unit_id = :unit_id 
    AND logbook_date >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
    GROUP BY DATE(logbook_date)
    ORDER BY date",
    ['unit_id' => $unitId, 'days' => $days]);

// Get verification status distribution
$statusDist = fetchAll("SELECT 
    verification_status as status,
    COUNT(*) as count
    FROM logbooks
    WHERE unit_id = :unit_id
    AND logbook_date >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
    GROUP BY verification_status",
    ['unit_id' => $unitId, 'days' => $days]);

// Get patient count trend
$patientTrend = fetchAll("SELECT 
    DATE(logbook_date) as date,
    SUM(patient_count) as patients
    FROM logbooks
    WHERE unit_id = :unit_id
    AND logbook_date >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
    GROUP BY DATE(logbook_date)
    ORDER BY date",
    ['unit_id' => $unitId, 'days' => $days]);

// Prepare data for Chart.js
$dates = array_column($dailyLogbooks, 'date');
$counts = array_column($dailyLogbooks, 'count');
$patients = array_column($patientTrend, 'patients');

$statusLabels = array_map('ucfirst', array_column($statusDist, 'status'));
$statusCounts = array_column($statusDist, 'count');

require_once __DIR__ . '/../../layouts/header.php';
require_once __DIR__ . '/../../layouts/sidebar-supervisor.php';
?>

<div class="row">
    <!-- Logbook Trend Chart -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Rekap Logbook Bulanan</h5>
            </div>
            <div class="card-body">
                <canvas id="logbookTrendChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Status Distribution -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Distribusi Status</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Patient Count Trend -->
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-people"></i> Trend Jumlah Pasien</h5>
            </div>
            <div class="card-body">
                <canvas id="patientTrendChart"></canvas>
            </div>
        </div>
    </div>
</div>

</div> <!-- End main-content -->

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Logbook Trend Chart
const logbookCtx = document.getElementById('logbookTrendChart').getContext('2d');
new Chart(logbookCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_map(fn($d) => date('d M', strtotime($d)), $dates)) ?>,
        datasets: [{
            label: 'Jumlah Logbook',
            data: <?= json_encode($counts) ?>,
            backgroundColor: 'rgba(16, 185, 129, 0.6)',
            borderColor: 'rgba(16, 185, 129, 1)',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        },
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        }
    }
});

// Status Distribution Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'pie',
    data: {
        labels: <?= json_encode($statusLabels) ?>,
        datasets: [{
            data: <?= json_encode($statusCounts) ?>,
            backgroundColor: [
                'rgba(16, 185, 129, 0.8)',   // approved - green
                'rgba(251, 191, 36, 0.8)',   // pending - yellow
                'rgba(239, 68, 68, 0.8)'     // rejected - red
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Patient Trend Chart
const patientCtx = document.getElementById('patientTrendChart').getContext('2d');
new Chart(patientCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_map(fn($d) => date('d M', strtotime($d)), $dates)) ?>,
        datasets: [{
            label: 'Jumlah Pasien',
            data: <?= json_encode($patients) ?>,
            fill: true,
            backgroundColor: 'rgba(59, 130, 246, 0.2)',
            borderColor: 'rgba(59, 130, 246, 1)',
            borderWidth: 3,
            tension: 0.4,
            pointRadius: 4,
            pointHoverRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                beginAtZero: true
            }
        },
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        }
    }
});
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
