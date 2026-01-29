<?php
$pageTitle = 'Log Aktivitas';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/activity_logger.php';

requireRole('Admin');

// Get filter parameters
$filterAction = $_GET['action'] ?? 'all';
$filterUser = $_GET['user'] ?? '';
$filterDate = $_GET['date'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build query
$where = "1=1";
$params = [];

if ($filterAction !== 'all') {
    $where .= " AND al.action = :action";
    $params['action'] = $filterAction;
}

if (!empty($filterUser)) {
    $where .= " AND al.user_id = :user_id";
    $params['user_id'] = $filterUser;
}

if (!empty($filterDate)) {
    $where .= " AND DATE(al.created_at) = :date";
    $params['date'] = $filterDate;
}

// Get total count
$totalSql = "SELECT COUNT(*) as count FROM activity_logs al WHERE $where";
$totalResult = fetchOne($totalSql, $params);
$totalRecords = $totalResult['count'] ?? 0;
$totalPages = ceil($totalRecords / $perPage);

// Get logs
$sql = "SELECT al.*, u.full_name, u.username
        FROM activity_logs al
        JOIN users u ON al.user_id = u.id
        WHERE $where
        ORDER BY al.created_at DESC
        LIMIT :limit OFFSET :offset";

$stmt = db()->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue(':' . $key, $value);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll();

// Get users for filter
$users = fetchAll("SELECT id, full_name FROM users ORDER BY full_name");

require_once __DIR__ . '/../../layouts/header.php';
require_once __DIR__ . '/../../layouts/sidebar-admin.php';
?>

<!-- Stats -->
<div class="row mb-4">
    <?php
    $stats = fetchOne("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN action = 'CREATE' THEN 1 ELSE 0 END) as creates,
        SUM(CASE WHEN action = 'UPDATE' THEN 1 ELSE 0 END) as updates,
        SUM(CASE WHEN action = 'DELETE' THEN 1 ELSE 0 END) as deletes
        FROM activity_logs");
    ?>
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="text-white-50">Total Aktivitas</h6>
                <h2><?= $stats['total'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="text-white-50">Create</h6>
                <h2><?= $stats['creates'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="text-white-50">Update</h6>
                <h2><?= $stats['updates'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h6 class="text-white-50">Delete</h6>
                <h2><?= $stats['deletes'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row align-items-end">
            <div class="col-md-3 mb-2">
                <label class="form-label">Aksi</label>
                <select class="form-select" name="action">
                    <option value="all" <?= $filterAction === 'all' ? 'selected' : '' ?>>Semua Aksi</option>
                    <option value="CREATE" <?= $filterAction === 'CREATE' ? 'selected' : '' ?>>CREATE</option>
                    <option value="UPDATE" <?= $filterAction === 'UPDATE' ? 'selected' : '' ?>>UPDATE</option>
                    <option value="DELETE" <?= $filterAction === 'DELETE' ? 'selected' : '' ?>>DELETE</option>
                    <option value="LOGIN" <?= $filterAction === 'LOGIN' ? 'selected' : '' ?>>LOGIN</option>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">User</label>
                <select class="form-select" name="user">
                    <option value="">Semua User</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>" <?= $filterUser == $user['id'] ? 'selected' : '' ?>>
                            <?= escapeHtml($user['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">Tanggal</label>
                <input type="date" class="form-control" name="date" value="<?= escapeHtml($filterDate) ?>">
            </div>
            <div class="col-md-3 mb-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Logs Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-activity"></i> Log Aktivitas Sistem (<?= $totalRecords ?> Records)</h5>
    </div>
    <div class="card-body">
        <?php if (empty($logs)): ?>
            <div class="alert alert-info">Tidak ada log aktivitas.</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>User</th>
                        <th>Aksi</th>
                        <th>Tabel</th>
                        <th>Deskripsi</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><small><?= formatDateTime($log['created_at']) ?></small></td>
                        <td><?= escapeHtml($log['full_name']) ?></td>
                        <td>
                            <?php
                            $badgeClass = [
                                'CREATE' => 'success',
                                'UPDATE' => 'primary',
                                'DELETE' => 'danger',
                                'LOGIN' => 'info'
                            ][$log['action']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $badgeClass ?>"><?= escapeHtml($log['action']) ?></span>
                        </td>
                        <td><code><?= escapeHtml($log['table_name'] ?? '-') ?></code></td>
                        <td><?= escapeHtml($log['description'] ?? '-') ?></td>
                        <td><small class="text-muted"><?= escapeHtml($log['ip_address']) ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav class="mt-3">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&action=<?= $filterAction ?>&user=<?= $filterUser ?>&date=<?= $filterDate ?>">Previous</a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&action=<?= $filterAction ?>&user=<?= $filterUser ?>&date=<?= $filterDate ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&action=<?= $filterAction ?>&user=<?= $filterUser ?>&date=<?= $filterDate ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
