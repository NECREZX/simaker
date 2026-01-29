<?php
$pageTitle = 'Manajemen Shift';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/functions.php';

requireRole('Admin');

$shifts = fetchAll("SELECT * FROM shifts ORDER BY start_time ASC");

require_once __DIR__ . '/../../../layouts/header.php';
require_once __DIR__ . '/../../../layouts/sidebar-admin.php';
?>

<div class="row mb-3">
    <div class="col-12">
        <a href="<?= APP_URL ?>/modules/admin/shifts/create.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Shift
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Daftar Shift Kerja</h5>
    </div>
    <div class="card-body">
        <?php if (empty($shifts)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Belum ada data shift.
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Shift</th>
                        <th>Jam Mulai</th>
                        <th>Jam Selesai</th>
                        <th>Durasi</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($shifts as $shift): ?>
                    <tr>
                        <td><?= $shift['id'] ?></td>
                        <td><strong><?= escapeHtml($shift['shift_name']) ?></strong></td>
                        <td><?= date('H:i', strtotime($shift['start_time'])) ?></td>
                        <td><?= date('H:i', strtotime($shift['end_time'])) ?></td>
                        <td>
                            <?php
                            $start = strtotime($shift['start_time']);
                            $end = strtotime($shift['end_time']);
                            $duration = ($end - $start) / 3600;
                            ?>
                            <span class="badge bg-info"><?= $duration ?> Jam</span>
                        </td>
                        <td><?= escapeHtml($shift['description'] ?? '-') ?></td>
                        <td>
                            <a href="<?= APP_URL ?>/modules/admin/shifts/edit.php?id=<?= $shift['id'] ?>" 
                               class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
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
