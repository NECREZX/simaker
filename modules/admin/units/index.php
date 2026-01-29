<?php
$pageTitle = 'Manajemen Unit';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/functions.php';

requireRole('Admin');

$units = fetchAll("SELECT * FROM units ORDER BY id ASC");

require_once __DIR__ . '/../../../layouts/header.php';
require_once __DIR__ . '/../../../layouts/sidebar-admin.php';
?>

<div class="row mb-3">
    <div class="col-12">
        <a href="<?= APP_URL ?>/modules/admin/units/create.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Unit
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-building"></i> Daftar Unit/Ruangan</h5>
    </div>
    <div class="card-body">
        <?php if (empty($units)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Belum ada data unit.
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kode Unit</th>
                        <th>Nama Unit</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th>Tanggal Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($units as $unit): ?>
                    <tr>
                        <td><?= $unit['id'] ?></td>
                        <td><span class="badge bg-secondary"><?= escapeHtml($unit['unit_code']) ?></span></td>
                        <td><strong><?= escapeHtml($unit['unit_name']) ?></strong></td>
                        <td><?= escapeHtml($unit['description'] ?? '-') ?></td>
                        <td>
                            <?php if ($unit['is_active']): ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td><?= formatDate(substr($unit['created_at'], 0, 10)) ?></td>
                        <td>
                            <a href="<?= APP_URL ?>/modules/admin/units/edit.php?id=<?= $unit['id'] ?>" 
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
