<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/functions.php';

// Require supervisor role
requireRole('Supervisor');

$pageTitle = 'Detail Verifikasi Logbook';
$current_user = getCurrentUser();

// Get logbook ID from URL
$logbookId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$logbookId) {
    setFlashMessage('error', 'ID logbook tidak valid');
    header('Location: ' . APP_URL . '/modules/supervisor/verification/index.php');
    exit;
}

// Get logbook details with user info
$logbook = fetchOne("SELECT l.*, 
    u.full_name as user_name, 
    u.email as user_email,
    un.unit_name,
    s.shift_name 
    FROM logbooks l
    LEFT JOIN users u ON l.user_id = u.id
    LEFT JOIN units un ON l.unit_id = un.id
    LEFT JOIN shifts s ON l.shift_id = s.id
    WHERE l.id = :id",
    ['id' => $logbookId]);

if (!$logbook) {
    setFlashMessage('error', 'Logbook tidak ditemukan');
    header('Location: ' . APP_URL . '/modules/supervisor/verification/index.php');
    exit;
}

require_once __DIR__ . '/../../../layouts/header.php';
require_once __DIR__ . '/../../../layouts/sidebar-supervisor.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <a href="<?= APP_URL ?>/modules/supervisor/verification/index.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Logbook Detail Card -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-text"></i> Informasi Logbook</h5>
                    <?php
                    $statusConfig = [
                        'pending' => ['class' => 'warning', 'icon' => 'clock-history', 'text' => 'Menunggu Verifikasi'],
                        'approved' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Disetujui'],
                        'rejected' => ['class' => 'danger', 'icon' => 'x-circle', 'text' => 'Ditolak']
                    ];
                    $status = $statusConfig[$logbook['verification_status']] ?? ['class' => 'secondary', 'icon' => 'question-circle', 'text' => $logbook['verification_status']];
                    ?>
                    <span class="badge bg-<?= $status['class'] ?> fs-6">
                        <i class="bi bi-<?= $status['icon'] ?>"></i> <?= $status['text'] ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Tanggal Kegiatan</label>
                            <p class="mb-0 fw-bold"><?= formatDate($logbook['logbook_date']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Shift</label>
                            <p class="mb-0 fw-bold"><?= $logbook['shift_name'] ? escapeHtml($logbook['shift_name']) : '-' ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Unit/Ruangan</label>
                            <p class="mb-0 fw-bold"><?= $logbook['unit_name'] ? escapeHtml($logbook['unit_name']) : '-' ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Jumlah Pasien</label>
                            <p class="mb-0 fw-bold"><?= $logbook['patient_count'] ?> Pasien</p>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="text-muted small">Deskripsi Kegiatan</label>
                        <p class="mb-0"><?= nl2br(escapeHtml($logbook['activity_description'])) ?></p>
                    </div>

                    <?php if ($logbook['verifier_notes']): ?>
                    <div class="mb-3">
                        <label class="text-muted small">Catatan Tambahan</label>
                        <p class="mb-0 text-muted"><?= nl2br(escapeHtml($logbook['verifier_notes'])) ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if ($logbook['evidence_file']): ?>
                    <hr>
                    <div class="mb-3">
                        <label class="text-muted small">Bukti Dokumentasi</label>
                        <div class="mt-2">
                            <?php
                            $evidencePath = UPLOAD_PATH . '/' . $logbook['evidence_file'];
                            $fileExt = strtolower(pathinfo($logbook['evidence_file'], PATHINFO_EXTENSION));
                            $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                            ?>
                            
                            <?php if (in_array($fileExt, $imageExts)): ?>
                                <div class="text-center">
                                    <img src="<?= $evidencePath ?>" 
                                         alt="Evidence" 
                                         class="img-fluid rounded border"
                                         style="max-height: 400px; cursor: pointer;"
                                         onclick="window.open('<?= $evidencePath ?>', '_blank')">
                                    <p class="text-muted small mt-2">Klik gambar untuk memperbesar</p>
                                </div>
                            <?php else: ?>
                                <a href="<?= $evidencePath ?>" target="_blank" class="btn btn-outline-primary">
                                    <i class="bi bi-download"></i> Download File (<?= strtoupper($fileExt) ?>)
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($logbook['verifier_notes']): ?>
                    <hr>
                    <div class="alert alert-<?= $logbook['verification_status'] === 'approved' ? 'success' : 'danger' ?>">
                        <label class="small fw-bold">Catatan Verifikasi:</label>
                        <p class="mb-0"><?= nl2br(escapeHtml($logbook['verifier_notes'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- User Info Card -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-person"></i> Informasi Karyawan</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="text-muted small">Nama Lengkap</label>
                        <p class="mb-0 fw-bold"><?= escapeHtml($logbook['user_name']) ?></p>
                    </div>
                    <div class="mb-2">
                        <label class="text-muted small">Email</label>
                        <p class="mb-0"><?= escapeHtml($logbook['user_email']) ?></p>
                    </div>
                </div>
            </div>

            <!-- Verification Action Card -->
            <?php if ($logbook['verification_status'] === 'pending'): ?>
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="bi bi-check-square"></i> Aksi Verifikasi</h6>
                </div>
                <div class="card-body">
                    <form action="<?= APP_URL ?>/modules/supervisor/verification/process.php" method="POST">
                        <?= csrfField() ?>
                        <input type="hidden" name="logbook_id" value="<?= $logbook['id'] ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Catatan Verifikasi <span class="text-muted">(Opsional)</span></label>
                            <textarea name="notes" class="form-control" rows="4" placeholder="Tambahkan catatan untuk karyawan..."></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="action" value="approve" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Setujui Logbook
                            </button>
                            <button type="submit" name="action" value="reject" class="btn btn-danger">
                                <i class="bi bi-x-circle"></i> Tolak Logbook
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Status</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-<?= $status['class'] ?> mb-0">
                        <i class="bi bi-<?= $status['icon'] ?>"></i> 
                        Logbook ini sudah <strong><?= $status['text'] ?></strong>
                        <?php if ($logbook['verified_at']): ?>
                        <br><small>pada <?= formatDateTime($logbook['verified_at']) ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Timestamps -->
            <div class="card mt-3">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">
                        <i class="bi bi-calendar-plus"></i> Dibuat: <?= formatDateTime($logbook['created_at']) ?>
                    </small>
                    <?php if ($logbook['updated_at']): ?>
                    <small class="text-muted d-block">
                        <i class="bi bi-calendar-check"></i> Diupdate: <?= formatDateTime($logbook['updated_at']) ?>
                    </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../../layouts/footer.php'; ?>
