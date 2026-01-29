<?php
$pageTitle = 'Dashboard Supervisor';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Require Supervisor role
requireRole('Supervisor');

$userId = $_SESSION['user_id'];
$unitId = $_SESSION['unit_id'];

// Get statistics
$totalLogbooks = countRecords('logbooks', 'unit_id = :unit_id', ['unit_id' => $unitId]);
$pendingVerification = countRecords('logbooks', 'unit_id = :unit_id AND verification_status = :status', [
    'unit_id' => $unitId,
    'status' => 'pending'
]);
$todayLogbooks = countRecords('logbooks', 'unit_id = :unit_id AND logbook_date = :date', [
    'unit_id' => $unitId,
    'date' => date('Y-m-d')
]);

// Get pending logbooks for verification
$pendingLogbooks = fetchAll(
    "SELECT l.*, u.full_name, s.shift_name, un.unit_name
     FROM logbooks l
     JOIN users u ON l.user_id = u.id
     JOIN shifts s ON l.shift_id = s.id
     JOIN units un ON l.unit_id = un.id
     WHERE l.unit_id = :unit_id AND l.verification_status = 'pending'
     ORDER BY l.logbook_date DESC, l.created_at DESC
     LIMIT 10",
    ['unit_id' => $unitId]
);

require_once __DIR__ . '/../../layouts/header.php';
require_once __DIR__ . '/../../layouts/sidebar-supervisor.php';
?>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-md-4 col-sm-6 mb-4">
        <div class="card stat-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-2">Total Logbook Unit</h6>
                        <h2 class="mb-0"><?= $totalLogbooks ?></h2>
                    </div>
                    <div class="icon">
                        <i class="bi bi-journal-text"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 col-sm-6 mb-4">
        <div class="card stat-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-2">Pending Verifikasi</h6>
                        <h2 class="mb-0"><?= $pendingVerification ?></h2>
                    </div>
                    <div class="icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 col-sm-6 mb-4">
        <div class="card stat-card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-2">Logbook Hari Ini</h6>
                        <h2 class="mb-0"><?= $todayLogbooks ?></h2>
                    </div>
                    <div class="icon">
                        <i class="bi bi-calendar-day"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> Aksi Cepat</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="<?= APP_URL ?>/modules/supervisor/verification/index.php" class="btn btn-emerald w-100">
                            <i class="bi bi-check2-square"></i> Verifikasi Logbook
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= APP_URL ?>/modules/supervisor/reports/daily.php" class="btn btn-primary w-100">
                            <i class="bi bi-calendar-day"></i> Laporan Harian
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= APP_URL ?>/modules/supervisor/reports/monthly.php" class="btn btn-info w-100">
                            <i class="bi bi-calendar-month"></i> Laporan Bulanan
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= APP_URL ?>/modules/supervisor/charts.php" class="btn btn-secondary w-100">
                            <i class="bi bi-bar-chart"></i> Grafik
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Verification -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Logbook Menunggu Verifikasi</h5>
            </div>
            <div class="card-body">
                <?php if (empty($pendingLogbooks)): ?>
                    <div class="alert alert-success mb-0">
                        <i class="bi bi-check-circle"></i> Semua logbook sudah terverifikasi!
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Petugas</th>
                                <th>Shift</th>
                                <th>Aktivitas</th>
                                <th>Pasien</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingLogbooks as $log): ?>
                            <tr>
                                <td><?= formatDate($log['logbook_date']) ?></td>
                                <td><?= escapeHtml($log['full_name']) ?></td>
                                <td><span class="badge bg-secondary"><?= escapeHtml($log['shift_name']) ?></span></td>
                                <td><?= escapeHtml(truncate($log['activity_title'], 50)) ?></td>
                                <td><?= $log['patient_count'] ?></td>
                                <td>
                                    <a href="<?= APP_URL ?>/modules/supervisor/verification/process.php?id=<?= $log['id'] ?>" class="btn btn-sm btn-emerald">
                                        <i class="bi bi-check-square"></i> Verifikasi
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="<?= APP_URL ?>/modules/supervisor/verification/index.php" class="btn btn-outline-primary">
                        Lihat Semua <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</div> <!-- End main-content -->

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
