<?php
$pageTitle = 'Laporan Bulanan';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/functions.php';

requireRole('Supervisor');

$unitId = $_SESSION['unit_id'];
$selectedMonth = $_GET['month'] ?? date('Y-m');
list($year, $month) = explode('-', $selectedMonth);

// Get monthly data
$monthStart = "$selectedMonth-01";
$monthEnd = date('Y-m-t', strtotime($monthStart));

$stats = fetchOne("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN verification_status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN verification_status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN verification_status = 'rejected' THEN 1 ELSE 0 END) as rejected,
    SUM(patient_count) as total_patients
    FROM logbooks
    WHERE unit_id = :unit_id AND logbook_date BETWEEN :start AND :end",
    ['unit_id' => $unitId, 'start' => $monthStart, 'end' => $monthEnd]);

// Get daily breakdown
$dailyData = fetchAll("SELECT 
    DATE(logbook_date) as date,
    COUNT(*) as total,
    SUM(patient_count) as patients
    FROM logbooks
    WHERE unit_id = :unit_id AND logbook_date BETWEEN :start AND :end
    GROUP BY DATE(logbook_date)
    ORDER BY date",
    ['unit_id' => $unitId, 'start' => $monthStart, 'end' => $monthEnd]);

// Get staff performance
$staffPerf = fetchAll("SELECT 
    u.full_name,
    COUNT(*) as total_logbooks,
    SUM(l.patient_count) as total_patients,
    SUM(CASE WHEN l.verification_status = 'approved' THEN 1 ELSE 0 END) as approved
    FROM logbooks l
    JOIN users u ON l.user_id = u.id
    WHERE l.unit_id = :unit_id AND l.logbook_date BETWEEN :start AND :end
    GROUP BY u.id
    ORDER BY total_logbooks DESC",
    ['unit_id' => $unitId, 'start' => $monthStart, 'end' => $monthEnd]);

require_once __DIR__ . '/../../../layouts/header.php';
require_once __DIR__ . '/../../../layouts/sidebar-supervisor.php';
?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-calendar3"></i> Laporan Bulanan</h5>
        <button onclick="window.print()" class="btn btn-secondary btn-sm">
            <i class="bi bi-printer"></i> Print
        </button>
    </div>
    <div class="card-body">
        <form method="GET" class="row align-items-end mb-4">
            <div class="col-md-4">
                <label class="form-label">Pilih Bulan</label>
                <input type="month" class="form-control" name="month" value="<?= $selectedMonth ?>" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Tampilkan
                </button>
            </div>
        </form>
        
        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Logbook</h6>
                        <h2><?= $stats['total'] ?? 0 ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Disetujui</h6>
                        <h2><?= $stats['approved'] ?? 0 ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Pending</h6>
                        <h2><?= $stats['pending'] ?? 0 ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Pasien</h6>
                        <h2><?= $stats['total_patients'] ?? 0 ?></h2>
                    </div>
                </div>
            </div>
        </div>
        
        <h5>Periode: <?= date('F Y', strtotime($monthStart)) ?></h5>
        <hr>
        
        <!-- Daily Breakdown -->
        <h6 class="mt-4">Rekap Harian</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Total Logbook</th>
                        <th>Total Pasien</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dailyData as $day): ?>
                    <tr>
                        <td><?= formatDate($day['date']) ?></td>
                        <td><?= $day['total'] ?></td>
                        <td><?= $day['patients'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Staff Performance -->
        <h6 class="mt-4">Performa Staff</h6>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Nama Staff</th>
                        <th>Total Logbook</th>
                        <th>Disetujui</th>
                        <th>Total Pasien</th>
                        <th>Tingkat Approval</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($staffPerf as $staff): ?>
                    <tr>
                        <td><?= escapeHtml($staff['full_name']) ?></td>
                        <td><?= $staff['total_logbooks'] ?></td>
                        <td><?= $staff['approved'] ?></td>
                        <td><?= $staff['total_patients'] ?></td>
                        <td>
                            <?php
                            $approvalRate = $staff['total_logbooks'] > 0 
                                ? round(($staff['approved'] / $staff['total_logbooks']) * 100) 
                                : 0;
                            ?>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: <?= $approvalRate ?>%">
                                    <?= $approvalRate ?>%
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>

<?php require_once __DIR__ . '/../../../layouts/footer.php'; ?>
