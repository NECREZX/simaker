<?php
$pageTitle = 'Dashboard Admin';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/activity_logger.php';

// Require Admin role
requireRole('Admin');

// Get statistics
$totalUsers = countRecords('users', 'is_active = 1');
$totalUnits = countRecords('units', 'is_active = 1');
$totalShifts = countRecords('shifts');
$totalLogbooks = countRecords('logbooks');
$pendingLogbooks = countRecords('logbooks', 'verification_status = :status', ['status' => 'pending']);

// Get recent activity logs
$recentLogs = getActivityLogs(10);

require_once __DIR__ . '/../../layouts/header.php';
require_once __DIR__ . '/../../layouts/sidebar-admin.php';
?>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="card stat-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-2">Total Pengguna</h6>
                        <h2 class="mb-0"><?= $totalUsers ?></h2>
                    </div>
                    <div class="icon">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="card stat-card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-2">Total Unit</h6>
                        <h2 class="mb-0"><?= $totalUnits ?></h2>
                    </div>
                    <div class="icon">
                        <i class="bi bi-building"></i>
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
        <div class="card stat-card" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-2">Pending Verifikasi</h6>
                        <h2 class="mb-0"><?= $pendingLogbooks ?></h2>
                    </div>
                    <div class="icon">
                        <i class="bi bi-clock-history"></i>
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
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="<?= APP_URL ?>/modules/admin/users/create.php" class="btn btn-emerald w-100">
                            <i class="bi bi-person-plus"></i> Tambah Pengguna
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="<?= APP_URL ?>/modules/admin/units/create.php" class="btn btn-primary w-100">
                            <i class="bi bi-building-add"></i> Tambah Unit
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="<?= APP_URL ?>/modules/admin/shifts/create.php" class="btn btn-info w-100">
                            <i class="bi bi-clock-history"></i> Tambah Shift
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="<?= APP_URL ?>/modules/admin/activity-logs.php" class="btn btn-secondary w-100">
                            <i class="bi bi-list-ul"></i> Lihat Log
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity Logs -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-activity"></i> Aktivitas Terkini</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recentLogs)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> Belum ada aktivitas tercatat.
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Pengguna</th>
                                <th>Aksi</th>
                                <th>Tabel</th>
                                <th>Deskripsi</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentLogs as $log): ?>
                            <tr>
                                <td><?= formatDateTime($log['created_at']) ?></td>
                                <td><?= escapeHtml($log['full_name']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $log['action'] === 'CREATE' ? 'success' : ($log['action'] === 'DELETE' ? 'danger' : 'primary') ?>">
                                        <?= escapeHtml($log['action']) ?>
                                    </span>
                                </td>
                                <td><code><?= escapeHtml($log['table_name'] ?? '-') ?></code></td>
                                <td><?= escapeHtml($log['description'] ?? '-') ?></td>
                                <td><small class="text-muted"><?= escapeHtml($log['ip_address']) ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="<?= APP_URL ?>/modules/admin/activity-logs.php" class="btn btn-outline-primary">
                        Lihat Semua Aktivitas <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</div> <!-- End main-content -->

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
