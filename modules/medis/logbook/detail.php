<?php
$pageTitle = 'Detail Logbook';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/security.php';

requireRole('Tenaga Medis');

$userId = $_SESSION['user_id'];
$logbookId = (int)($_GET['id'] ?? 0);

if ($logbookId === 0) {
    setFlashMessage('Logbook tidak ditemukan.', 'danger');
    redirect(APP_URL . '/modules/medis/logbook/index.php');
}

// Get logbook detail
$sql = "SELECT l.*, s.shift_name, s.start_time, s.end_time,
               u.unit_name, u.unit_code,
               creator.full_name as creator_name, creator.email as creator_email,
               verifier.full_name as verifier_name
        FROM logbooks l
        JOIN shifts s ON l.shift_id = s.id
        JOIN units u ON l.unit_id = u.id
        JOIN users creator ON l.user_id = creator.id
        LEFT JOIN users verifier ON l.verified_by = verifier.id
        WHERE l.id = :id AND l.user_id = :user_id";

$logbook = fetchOne($sql, ['id' => $logbookId, 'user_id' => $userId]);

if (!$logbook) {
    setFlashMessage('Logbook tidak ditemukan atau Anda tidak memiliki akses.', 'danger');
    redirect(APP_URL . '/modules/medis/logbook/index.php');
}

require_once __DIR__ . '/../../../layouts/header.php';
require_once __DIR__ . '/../../../layouts/sidebar-medis.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-file-text"></i> Detail Logbook</h5>
            </div>
            <div class="card-body">
                <!-- Status Badge -->
                <div class="mb-4">
                    <?php
                    $statusConfig = [
                        'pending' => ['class' => 'bg-warning', 'icon' => 'clock-history', 'text' => 'Menunggu Verifikasi'],
                        'approved' => ['class' => 'bg-success', 'icon' => 'check-circle', 'text' => 'Disetujui'],
                        'rejected' => ['class' => 'bg-danger', 'icon' => 'x-circle', 'text' => 'Ditolak']
                    ];
                    $status = $statusConfig[$logbook['verification_status']] ?? ['class' => 'bg-secondary', 'icon' => 'question', 'text' => 'Unknown'];
                    ?>
                    <span class="badge <?= $status['class'] ?> p-3" style="font-size: 1rem;">
                        <i class="bi bi-<?= $status['icon'] ?>"></i> Status: <?= $status['text'] ?>
                    </span>
                </div>
                
                <!-- Logbook Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Informasi Logbook</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Tanggal</strong></td>
                                <td><?= formatDate($logbook['logbook_date']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Shift</strong></td>
                                <td><?= escapeHtml($logbook['shift_name']) ?> 
                                    (<?= date('H:i', strtotime($logbook['start_time'])) ?> - <?= date('H:i', strtotime($logbook['end_time'])) ?>)
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Unit</strong></td>
                                <td><?= escapeHtml($logbook['unit_name']) ?> 
                                    <span class="badge bg-secondary"><?= escapeHtml($logbook['unit_code']) ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Jumlah Pasien</strong></td>
                                <td><span class="badge bg-info"><?= $logbook['patient_count'] ?> Pasien</span></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Informasi Verifikasi</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Status Verifikasi</strong></td>
                                <td><span class="badge <?= $status['class'] ?>"><?= $status['text'] ?></span></td>
                            </tr>
                            <?php if ($logbook['verified_by']): ?>
                            <tr>
                                <td><strong>Diverifikasi Oleh</strong></td>
                                <td><?= escapeHtml($logbook['verifier_name']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Verifikasi</strong></td>
                                <td><?= $logbook['verified_at'] ? formatDateTime($logbook['verified_at']) : '-' ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td><strong>Dibuat</strong></td>
                                <td><?= formatDateTime($logbook['created_at']) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <hr>
                
                <!-- Activity Details -->
                <div class="mb-4">
                    <h6 class="text-muted mb-3">Detail Aktivitas</h6>
                    <div class="mb-3">
                        <label class="form-label"><strong>Judul Aktivitas</strong></label>
                        <p class="form-control-plaintext"><?= escapeHtml($logbook['activity_title']) ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><strong>Deskripsi Aktivitas</strong></label>
                        <div class="border rounded p-3 bg-light">
                            <?= nl2br(escapeHtml($logbook['activity_description'])) ?>
                        </div>
                    </div>
                </div>
                
                <!-- Evidence File -->
                <?php if ($logbook['evidence_file']): ?>
                <hr>
                <div class="mb-4">
                    <h6 class="text-muted mb-3">Bukti/Dokumentasi</h6>
                    <?php
                    $filePath = UPLOAD_PATH . '/logbooks/' . $logbook['evidence_file'];
                    $fileUrl = UPLOAD_URL . '/logbooks/' . $logbook['evidence_file'];
                    $fileExt = strtolower(pathinfo($logbook['evidence_file'], PATHINFO_EXTENSION));
                    ?>
                    <?php if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                        <div class="text-center">
                            <img src="<?= $fileUrl ?>" alt="Evidence" class="img-fluid rounded shadow" style="max-height: 400px;">
                            <br>
                            <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-primary mt-2">
                                <i class="bi bi-download"></i> Download
                            </a>
                        </div>
                    <?php elseif ($fileExt === 'pdf'): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-file-pdf"></i> File PDF tersedia
                            <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-primary ms-2">
                                <i class="bi bi-download"></i> Download PDF
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="bi bi-file-earmark"></i> File: <?= escapeHtml($logbook['evidence_file']) ?>
                            <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-sm btn-primary ms-2">
                                <i class="bi bi-download"></i> Download
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- Verifier Notes -->
                <?php if ($logbook['verifier_notes']): ?>
                <hr>
                <div class="mb-4">
                    <h6 class="text-muted mb-3">Catatan Supervisor</h6>
                    <div class="alert alert-<?= $logbook['verification_status'] === 'approved' ? 'success' : 'danger' ?>">
                        <i class="bi bi-chat-left-quote"></i>
                        <strong><?= escapeHtml($logbook['verifier_name']) ?>:</strong>
                        <p class="mb-0 mt-2"><?= nl2br(escapeHtml($logbook['verifier_notes'])) ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Actions -->
                <div class="d-flex gap-2 mt-4">
                    <a href="<?= APP_URL ?>/modules/medis/logbook/index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                    </a>
                    <?php if ($logbook['verification_status'] === 'pending'): ?>
                        <a href="<?= APP_URL ?>/modules/medis/logbook/edit.php?id=<?= $logbook['id'] ?>" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Edit Logbook
                        </a>
                        <a href="<?= APP_URL ?>/modules/medis/logbook/delete.php?id=<?= $logbook['id'] ?>" 
                           class="btn btn-danger"
                           onclick="return confirm('Yakin ingin menghapus logbook ini?')">
                            <i class="bi bi-trash"></i> Hapus
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

</div> <!-- End main-content -->

<?php require_once __DIR__ . '/../../../layouts/footer.php'; ?>
