<?php
$pageTitle = 'Laporan Harian';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/functions.php';

requireRole('Supervisor');

$unitId = $_SESSION['unit_id'];
$selectedDate = $_GET['date'] ?? date('Y-m-d');

// Get data for selected date
$logbooks = fetchAll("SELECT l.*, s.shift_name, u.unit_name, staff.full_name as staff_name
    FROM logbooks l
    JOIN shifts s ON l.shift_id = s.id
    JOIN units u ON l.unit_id = u.id
    JOIN users staff ON l.user_id = staff.id
    WHERE l.unit_id = :unit_id AND DATE(l.logbook_date) = :date
    ORDER BY s.start_time, staff.full_name",
    ['unit_id' => $unitId, 'date' => $selectedDate]);

// Calculate stats
$totalLogbooks = count($logbooks);
$approved = count(array_filter($logbooks, fn($l) => $l['verification_status'] === 'approved'));
$pending = count(array_filter($logbooks, fn($l) => $l['verification_status'] === 'pending'));
$rejected = count(array_filter($logbooks, fn($l) => $l['verification_status'] === 'rejected'));
$totalPatients = array_sum(array_column($logbooks, 'patient_count'));

require_once __DIR__ . '/../../../layouts/header.php';
require_once __DIR__ . '/../../../layouts/sidebar-supervisor.php';
?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-calendar-day"></i> Laporan Harian</h5>
        <div>
            <button onclick="window.print()" class="btn btn-secondary btn-sm">
                <i class="bi bi-printer"></i> Print
            </button>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="row align-items-end mb-4">
            <div class="col-md-4">
                <label class="form-label">Pilih Tanggal</label>
                <input type="date" class="form-control" name="date" value="<?= $selectedDate ?>" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Tampilkan
                </button>
            </div>
        </form>
        
        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Logbook</h6>
                        <h2><?= $totalLogbooks ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Disetujui</h6>
                        <h2><?= $approved ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Pending</h6>
                        <h2><?= $pending ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="text-white-50">Total Pasien</h6>
                        <h2><?= $totalPatients ?></h2>
                    </div>
                </div>
            </div>
        </div>
        
        <h5>Tanggal: <?= formatDate($selectedDate) ?></h5>
        <hr>
        
        <?php if (empty($logbooks)): ?>
            <div class="alert alert-info">Tidak ada data logbook untuk tanggal ini.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Staff</th>
                            <th>Shift</th>
                            <th>Aktivitas</th>
                            <th>Pasien</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logbooks as $idx => $log): ?>
                        <tr>
                            <td><?= $idx + 1 ?></td>
                            <td><?= escapeHtml($log['staff_name']) ?></td>
                            <td><?= escapeHtml($log['shift_name']) ?></td>
                            <td><?= escapeHtml($log['activity_title']) ?></td>
                            <td><?= $log['patient_count'] ?></td>
                            <td>
                                <?php
                                $badges = [
                                    'approved' => 'success',
                                    'pending' => 'warning',
                                    'rejected' => 'danger'
                                ];
                                ?>
                                <span class="badge bg-<?= $badges[$log['verification_status']] ?? 'secondary' ?>">
                                    <?= ucfirst($log['verification_status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

</div>

<?php require_once __DIR__ . '/../../../layouts/footer.php'; ?>
