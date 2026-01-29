<?php
$pageTitle = 'Data Pengguna';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/functions.php';

requireRole('Admin');

$users = fetchAll(
    "SELECT u.*, r.role_name, un.unit_name
FROM users u
JOIN roles r ON u.role_id = r.id
LEFT JOIN units un ON u.unit_id = un.id
ORDER BY r.role_name ASC, COALESCE(un.unit_name, '') ASC, u.full_name ASC
"
);

require_once __DIR__ . '/../../../layouts/header.php';
require_once __DIR__ . '/../../../layouts/sidebar-admin.php';
?>

<div class="row mb-3">
    <div class="col-12">
        <a href="<?= APP_URL ?>/modules/admin/users/create.php" class="btn btn-emerald">
            <i class="bi bi-plus-circle"></i> Tambah Pengguna
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-people"></i> Daftar Pengguna</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Unit</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= escapeHtml($user['username']) ?></td>
                        <td><?= escapeHtml($user['full_name']) ?></td>
                        <td><?= escapeHtml($user['email']) ?></td>
                        <td><span class="badge bg-primary"><?= escapeHtml($user['role_name']) ?></span></td>
                        <td><?= escapeHtml($user['unit_name'] ?? '-') ?></td>
                        <td>
                            <?php if ($user['is_active']): ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= APP_URL ?>/modules/admin/users/edit.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
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
