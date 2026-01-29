<?php
$pageTitle = 'Verifikasi Logbook';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/security.php';

requireRole('Supervisor');

$userId = $_SESSION['user_id'];
$userUnitId = $_SESSION['unit_id'];

// Get filter parameters
$filterUnit = $_GET['unit'] ?? $userUnitId;
$filterDate = $_GET['date'] ?? '';
$filterStaff = $_GET['staff'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Build query - supervisor can see logbooks from their unit only
$where = "l.verification_status = 'pending' AND l.unit_id = :unit_id";
$params = ['unit_id' => $filterUnit];

if (!empty($filterDate)) {
    $where .= " AND DATE(l.logbook_date) = :date";
    $params['date'] = $filterDate;
}

if (!empty($filterStaff)) {
    $where .= " AND l.user_id = :staff_id";
    $params['staff_id'] = $filterStaff;
}

// Get total count
$totalSql = "SELECT COUNT(*) as count FROM logbooks l WHERE $where";
$totalResult = fetchOne($totalSql, $params);
$totalRecords = $totalResult['count'] ?? 0;
$totalPages = ceil($totalRecords / $perPage);

// Get pending logbooks
$sql = "SELECT l.*, s.shift_name, u.unit_name, u.unit_code,
               staff.full_name as staff_name, staff.email as staff_email
        FROM logbooks l
        JOIN shifts s ON l.shift_id = s.id
        JOIN units u ON l.unit_id = u.id
        JOIN users staff ON l.user_id = staff.id
        WHERE $where
        ORDER BY l.logbook_date DESC, l.created_at ASC
        LIMIT :limit OFFSET :offset";

$stmt = db()->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue(':' . $key, $value);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logbooks = $stmt->fetchAll();

// Get staff list for filter (from supervisor's unit)
$staffList = fetchAll("SELECT DISTINCT u.id, u.full_name 
    FROM users u 
    WHERE u.unit_id = :unit_id 
    AND u.role_id = (SELECT id FROM roles WHERE role_name = 'Tenaga Medis')
    AND u.is_active = 1
    ORDER BY u.full_name", ['unit_id' => $userUnitId]);

// Get units if user has access to multiple
$units = fetchAll("SELECT * FROM units WHERE is_active = 1 ORDER BY unit_name");

require_once __DIR__ . '/../../../layouts/header.php';
require_once __DIR__ . '/../../../layouts/sidebar-supervisor.php';
?>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6 class="text-white-50">Menunggu Verifikasi</h6>
                <h2 class="mb-0"><?= $totalRecords ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row align-items-end">
            <div class="col-md-3 mb-2">
                <label class="form-label">Tanggal</label>
                <input type="date" class="form-control" name="date" value="<?= escapeHtml($filterDate) ?>">
            </div>
            <div class="col-md-3 mb-2">
                <label class="form-label">Staff</label>
                <select class="form-select" name="staff">
                    <option value="">Semua Staff</option>
                    <?php foreach ($staffList as $staff): ?>
                        <option value="<?= $staff['id'] ?>" <?= $filterStaff == $staff['id'] ? 'selected' : '' ?>>
                            <?= escapeHtml($staff['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
            <div class="col-md-3 mb-2">
                <a href="?" class="btn btn-secondary w-100">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Logbooks Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Logbook Menunggu Verifikasi</h5>
    </div>
    <div class="card-body">
        <?php if (empty($logbooks)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Tidak ada logbook yang menunggu verifikasi.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Staff</th>
                            <th>Shift</th>
                            <th>Judul Aktivitas</th>
                            <th>Pasien</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logbooks as $log): ?>
                        <tr>
                            <td><?= formatDate($log['logbook_date']) ?></td>
                            <td>
                                <strong><?= escapeHtml($log['staff_name']) ?></strong>
                                <br><small class="text-muted"><?= escapeHtml($log['staff_email']) ?></small>
                            </td>
                            <td><?= escapeHtml($log['shift_name']) ?></td>
                            <td><?= escapeHtml($log['activity_title']) ?></td>
                            <td><span class="badge bg-info"><?= $log['patient_count'] ?></span></td>
                            <td><?= formatDateTime($log['created_at']) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-info" 
                                            onclick="viewDetail(<?= $log['id'] ?>)" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-success" 
                                            onclick="showVerifyModal(<?= $log['id'] ?>, 'approved')" title="Setujui">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger" 
                                            onclick="showVerifyModal(<?= $log['id'] ?>, 'rejected')" title="Tolak">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
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
                            <a class="page-link" href="?page=<?= $page - 1 ?>&unit=<?= $filterUnit ?>&date=<?= $filterDate ?>&staff=<?= $filterStaff ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&unit=<?= $filterUnit ?>&date=<?= $filterDate ?>&staff=<?= $filterStaff ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>&unit=<?= $filterUnit ?>&date=<?= $filterDate ?>&staff=<?= $filterStaff ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Verification Modal -->
<div class="modal fade" id="verifyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="verifyForm" method="POST" action="<?= APP_URL ?>/modules/supervisor/verification/process.php">
                <?= csrfField() ?>
                <input type="hidden" name="logbook_id" id="logbook_id">
                <input type="hidden" name="action" id="verify_action">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Verifikasi Logbook</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="notes" name="notes" rows="4" required 
                                  placeholder="Berikan catatan untuk staff..."></textarea>
                        <small class="text-muted">Catatan ini akan dilihat oleh staff.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

</div> <!-- End main-content -->

<script>
function showVerifyModal(logbookId, action) {
    document.getElementById('logbook_id').value = logbookId;
    document.getElementById('verify_action').value = action;
    
    const modal = new bootstrap.Modal(document.getElementById('verifyModal'));
    const title = action === 'approved' ? 'Setujui Logbook' : 'Tolak Logbook';
    const btnClass = action === 'approved' ? 'btn-success' : 'btn-danger';
    const btnText = action === 'approved' ? 'Setujui' : 'Tolak';
    
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('submitBtn').className = 'btn ' + btnClass;
    document.getElementById('submitBtn').textContent = btnText;
    document.getElementById('notes').value = '';
    
    modal.show();
}

function viewDetail(logbookId) {
    window.open('<?= APP_URL ?>/modules/supervisor/verification/detail.php?id=' + logbookId, '_blank');
}
</script>

<?php require_once __DIR__ . '/../../../layouts/footer.php'; ?>
