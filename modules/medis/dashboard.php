<?php
$pageTitle = 'Dashboard Tenaga Medis';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Require Tenaga Medis role
requireRole('Tenaga Medis');

$userId = $_SESSION['user_id'];

// Get statistics
$totalLogbooks = countRecords('logbooks', 'user_id = :user_id', ['user_id' => $userId]);
$pendingLogbooks = countRecords('logbooks', 'user_id = :user_id AND verification_status = :status', [
    'user_id' => $userId,
    'status' => 'pending'
]);
$approvedLogbooks = countRecords('logbooks', 'user_id = :user_id AND verification_status = :status', [
    'user_id' => $userId,
    'status' => 'approved'
]);
$rejectedLogbooks = countRecords('logbooks', 'user_id = :user_id AND verification_status = :status', [
    'user_id' => $userId,
    'status' => 'rejected'
]);

// Get recent logbooks
$recentLogbooks = fetchAll(
    "SELECT l.*, s.shift_name, u.unit_name 
     FROM logbooks l
     JOIN shifts s ON l.shift_id = s.id
     JOIN units u ON l.unit_id = u.id
     WHERE l.user_id = :user_id
     ORDER BY l.logbook_date DESC, l.created_at DESC
     LIMIT 5",
    ['user_id' => $userId]
);

require_once __DIR__ . '/../../layouts/header.php';
require_once __DIR__ . '/../../layouts/sidebar-medis.php';
?>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="card stat-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-2">Total Logbook</h6>
                        <h2 class="mb-0"><?= $totalLogbooks ?></h2>
                    </div>
                    <div class="icon">
                        <i class="bi bi-journal-text"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="card stat-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-2">Pending</h6>
                        <h2 class="mb-0"><?= $pendingLogbooks ?></h2>
                    </div>
                    <div class="icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="card stat-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-2">Disetujui</h6>
                        <h2 class="mb-0"><?= $approvedLogbooks ?></h2>
                    </div>
                    <div class="icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="card stat-card" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-2">Ditolak</h6>
                        <h2 class="mb-0"><?= $rejectedLogbooks ?></h2>
                    </div>
                    <div class="icon">
                        <i class="bi bi-x-circle"></i>
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
                    <div class="col-md-4 mb-3">
                        <a href="<?= APP_URL ?>/modules/medis/logbook/create.php" class="btn btn-emerald w-100 py-3">
                            <i class="bi bi-plus-circle"></i> Tambah Logbook Baru
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="<?= APP_URL ?>/modules/medis/logbook/index.php" class="btn btn-primary w-100 py-3">
                            <i class="bi bi-journal-text"></i> Lihat Semua Logbook
                        </a>
                    </div>
                    <div class="col-md-4 mb-3">
                        <a href="<?= APP_URL ?>/modules/medis/qr-attendance.php" class="btn btn-info w-100 py-3">
                            <i class="bi bi-qr-code"></i> QR Attendance
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Logbooks -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Logbook Terkini</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recentLogbooks)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> Belum ada logbook. Silakan buat logbook baru.
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Shift</th>
                                <th>Unit</th>
                                <th>Aktivitas</th>
                                <th>Pasien</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentLogbooks as $log): ?>
                            <tr>
                                <td><?= formatDate($log['logbook_date']) ?></td>
                                <td><span class="badge bg-secondary"><?= escapeHtml($log['shift_name']) ?></span></td>
                                <td><?= escapeHtml($log['unit_name']) ?></td>
                                <td><?= escapeHtml($log['activity_title']) ?></td>
                                <td><?= $log['patient_count'] ?></td>
                                <td><?= getStatusBadge($log['verification_status']) ?></td>
                                <td>
                                    <a href="<?= APP_URL ?>/modules/medis/logbook/detail.php?id=<?= $log['id'] ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="<?= APP_URL ?>/modules/medis/logbook/index.php" class="btn btn-outline-primary">
                        Lihat Semua Logbook <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</div> <!-- End main-content -->

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
