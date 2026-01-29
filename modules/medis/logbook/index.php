<?php
$pageTitle = 'Daftar Logbook';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/security.php';

requireRole('Tenaga Medis');

$userId = $_SESSION['user_id'];

// Get filter parameters
$filterStatus = $_GET['status'] ?? 'all';
$filterDate = $_GET['date'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Build query
$where = "l.user_id = :user_id";
$params = ['user_id' => $userId];

if ($filterStatus !== 'all') {
    $where .= " AND l.verification_status = :status";
    $params['status'] = $filterStatus;
}

if (!empty($filterDate)) {
    $where .= " AND DATE(l.logbook_date) = :date";
    $params['date'] = $filterDate;
}

if (!empty($search)) {
    $where .= " AND (l.activity_title LIKE :search OR l.activity_description LIKE :search)";
    $params['search'] = '%' . $search . '%';
}

// Get total count
$totalSql = "SELECT COUNT(*) as count FROM logbooks l WHERE $where";
$totalResult = fetchOne($totalSql, $params);
$totalRecords = $totalResult['count'] ?? 0;
$totalPages = ceil($totalRecords / $perPage);

// Get logbooks
$sql = "SELECT l.*, s.shift_name, u.unit_name, u.unit_code,
               sup.full_name as verifier_name
        FROM logbooks l
        JOIN shifts s ON l.shift_id = s.id
        JOIN units u ON l.unit_id = u.id
        LEFT JOIN users sup ON l.verified_by = sup.id
        WHERE $where
        ORDER BY l.logbook_date DESC, l.created_at DESC
        LIMIT :limit OFFSET :offset";

$stmt = db()->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue(':' . $key, $value);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logbooks = $stmt->fetchAll();

// Get statistics
$stats = fetchOne("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN verification_status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN verification_status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN verification_status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM logbooks WHERE user_id = :user_id", ['user_id' => $userId]);

require_once __DIR__ . '/../../../layouts/header.php';
require_once __DIR__ . '/../../../layouts/sidebar-medis.php';
?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="text-white-50">Total Logbook</h6>
                <h2 class="mb-0"><?= $stats['total'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6 class="text-white-50">Menunggu Verifikasi</h6>
                <h2 class="mb-0"><?= $stats['pending'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="text-white-50">Disetujui</h6>
                <h2 class="mb-0"><?= $stats['approved'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h6 class="text-white-50">Ditolak</h6>
                <h2 class="mb-0"><?= $stats['rejected'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Filters & Actions -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-end">
            <div class="col-md-3 mb-2">
                <label class="form-label">Status</label>
                <select class="form-select" id="filterStatus" onchange="applyFilters()">
                    <option value="all" <?= $filterStatus === 'all' ? 'selected' : '' ?>>Semua Status</option>
                    <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= $filterStatus === 'approved' ? 'selected' : '' ?>>Disetujui</option>
                    <option value="rejected" <?= $filterStatus === 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">Tanggal</label>
                <input type="date" class="form-control" id="filterDate" value="<?= escapeHtml($filterDate) ?>" onchange="applyFilters()">
            </div>
            <div class="col-md-4 mb-2">
                <label class="form-label">Pencarian</label>
                <input type="text" class="form-control" id="search" placeholder="Cari judul atau deskripsi..." 
                       value="<?= escapeHtml($search) ?>" onkeyup="if(event.key==='Enter') applyFilters()">
            </div>
            <div class="col-md-2 mb-2">
                <a href="<?= APP_URL ?>/modules/medis/logbook/create.php" class="btn btn-emerald w-100">
                    <i class="bi bi-plus-circle"></i> Tambah Baru
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Logbooks Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-journal-text"></i> Daftar Logbook Saya</h5>
    </div>
    <div class="card-body">
        <?php if (empty($logbooks)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Belum ada logbook. 
                <a href="<?= APP_URL ?>/modules/medis/logbook/create.php">Tambah logbook pertama Anda</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Unit</th>
                            <th>Judul Aktivitas</th>
                            <th>Pasien</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logbooks as $log): ?>
                        <tr>
                            <td><?= formatDate($log['logbook_date']) ?></td>
                            <td><?= escapeHtml($log['shift_name']) ?></td>
                            <td><span class="badge bg-secondary"><?= escapeHtml($log['unit_code']) ?></span></td>
                            <td><?= escapeHtml($log['activity_title']) ?></td>
                            <td><?= $log['patient_count'] ?></td>
                            <td>
                                <?php
                                $badgeClass = [
                                    'pending' => 'bg-warning',
                                    'approved' => 'bg-success',
                                    'rejected' => 'bg-danger'
                                ][$log['verification_status']] ?? 'bg-secondary';
                                
                                $statusText = [
                                    'pending' => 'Pending',
                                    'approved' => 'Disetujui',
                                    'rejected' => 'Ditolak'
                                ][$log['verification_status']] ?? $log['verification_status'];
                                ?>
                                <span class="badge <?= $badgeClass ?>">
                                    <?= $statusText ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= APP_URL ?>/modules/medis/logbook/detail.php?id=<?= $log['id'] ?>" 
                                       class="btn btn-info" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if ($log['verification_status'] === 'pending'): ?>
                                        <a href="<?= APP_URL ?>/modules/medis/logbook/edit.php?id=<?= $log['id'] ?>" 
                                           class="btn btn-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?= APP_URL ?>/modules/medis/logbook/delete.php?id=<?= $log['id'] ?>&token=<?= $_SESSION['csrf_token'] ?>" 
                                           class="btn btn-danger" title="Hapus" 
                                           onclick="return confirm('Yakin ingin menghapus logbook ini?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
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
                            <a class="page-link" href="?page=<?= $page - 1 ?>&status=<?= $filterStatus ?>&date=<?= $filterDate ?>&search=<?= urlencode($search) ?>">
                                Previous
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&status=<?= $filterStatus ?>&date=<?= $filterDate ?>&search=<?= urlencode($search) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>&status=<?= $filterStatus ?>&date=<?= $filterDate ?>&search=<?= urlencode($search) ?>">
                                Next
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

</div> <!-- End main-content -->

<script>
function applyFilters() {
    const status = document.getElementById('filterStatus').value;
    const date = document.getElementById('filterDate').value;
    const search = document.getElementById('search').value;
    
    let url = '<?= APP_URL ?>/modules/medis/logbook/index.php?';
    url += 'status=' + status;
    url += '&date=' + date;
    url += '&search=' + encodeURIComponent(search);
    
    window.location.href = url;
}
</script>

<?php require_once __DIR__ . '/../../../layouts/footer.php'; ?>
