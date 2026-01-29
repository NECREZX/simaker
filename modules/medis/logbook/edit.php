<?php
$pageTitle = 'Edit Logbook';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/security.php';
require_once __DIR__ . '/../../../includes/activity_logger.php';

requireRole('Tenaga Medis');

$userId = $_SESSION['user_id'];
$logbookId = (int)($_GET['id'] ?? 0);

if ($logbookId === 0) {
    setFlashMessage('Logbook tidak ditemukan.', 'danger');
    redirect(APP_URL . '/modules/medis/logbook/index.php');
}

// Get logbook
$logbook = fetchOne("SELECT * FROM logbooks WHERE id = :id AND user_id = :user_id", 
    ['id' => $logbookId, 'user_id' => $userId]);

if (!$logbook) {
    setFlashMessage('Logbook tidak ditemukan atau Anda tidak memiliki akses.', 'danger');
    redirect(APP_URL . '/modules/medis/logbook/index.php');
}

// Check if logbook can be edited (only pending status)
if ($logbook['verification_status'] !== 'pending') {
    setFlashMessage('Hanya logbook dengan status Pending yang dapat diedit.', 'danger');
    redirect(APP_URL . '/modules/medis/logbook/detail.php?id=' . $logbookId);
}

// Get shifts and units
$shifts = fetchAll("SELECT * FROM shifts ORDER BY start_time");
$units = fetchAll("SELECT * FROM units WHERE is_active = 1 ORDER BY unit_name");

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid request.', 'danger');
        redirect(APP_URL . '/modules/medis/logbook/edit.php?id=' . $logbookId);
    }
    
    $logbookDate = sanitizeInput($_POST['logbook_date'] ?? '');
    $shiftId = (int)($_POST['shift_id'] ?? 0);
    $selectedUnitId = (int)($_POST['unit_id'] ?? 0);
    $activityTitle = sanitizeInput($_POST['activity_title'] ?? '');
    $activityDescription = sanitizeInput($_POST['activity_description'] ?? '');
    $patientCount = (int)($_POST['patient_count'] ?? 0);
    
    $errors = [];
    
    if (empty($logbookDate)) $errors[] = 'Tanggal logbook harus diisi.';
    if ($shiftId === 0) $errors[] = 'Shift harus dipilih.';
    if ($selectedUnitId === 0) $errors[] = 'Unit harus dipilih.';
    if (empty($activityTitle)) $errors[] = 'Judul aktivitas harus diisi.';
    if (empty($activityDescription)) $errors[] = 'Deskripsi aktivitas harus diisi.';
    if ($patientCount < 0) $errors[] = 'Jumlah pasien tidak valid.';
    
    $evidenceFile = $logbook['evidence_file']; // Keep old file by default
    
    // Handle new file upload
    if (isset($_FILES['evidence']) && $_FILES['evidence']['error'] === UPLOAD_ERR_OK) {
        // Delete old file
        if ($evidenceFile) {
            $oldFilePath = UPLOAD_PATH . '/logbooks/' . $evidenceFile;
            if (file_exists($oldFilePath)) {
                @unlink($oldFilePath);
            }
        }
        
        $uploadResult = uploadFile($_FILES['evidence'], 'logbook_');
        if ($uploadResult['success']) {
            $evidenceFile = $uploadResult['filename'];
        } else {
            $errors = array_merge($errors, $uploadResult['errors']);
        }
    }
    
    if (empty($errors)) {
        $data = [
            'logbook_date' => $logbookDate,
            'shift_id' => $shiftId,
            'unit_id' => $selectedUnitId,
            'activity_title' => $activityTitle,
            'activity_description' => $activityDescription,
            'patient_count' => $patientCount,
            'evidence_file' => $evidenceFile
        ];
        
        $updated = update('logbooks', $data, 'id = :id AND user_id = :user_id', 
            ['id' => $logbookId, 'user_id' => $userId]);
        
        if ($updated) {
            logActivity($userId, 'UPDATE', 'logbooks', $logbookId, 'Mengupdate logbook: ' . $activityTitle);
            setFlashMessage('Logbook berhasil diperbarui!', 'success');
            redirect(APP_URL . '/modules/medis/logbook/detail.php?id=' . $logbookId);
        } else {
            $errors[] = 'Gagal memperbarui logbook.';
        }
    }
    
    if (!empty($errors)) {
        foreach ($errors as $error) {
            setFlashMessage($error, 'danger');
        }
    }
}

require_once __DIR__ . '/../../../layouts/header.php';
require_once __DIR__ . '/../../../layouts/sidebar-medis.php';
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Edit Logbook</h5>
    </div>
    <div class="card-body">
        <form action="" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            <?= csrfField() ?>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="logbook_date" class="form-label">Tanggal Logbook <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="logbook_date" name="logbook_date" 
                           value="<?= escapeHtml($logbook['logbook_date']) ?>" max="<?= date('Y-m-d') ?>" required>
                    <div class="invalid-feedback">Tanggal harus diisi.</div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="shift_id" class="form-label">Shift <span class="text-danger">*</span></label>
                    <select class="form-select" id="shift_id" name="shift_id" required>
                        <option value="">Pilih Shift...</option>
                        <?php foreach ($shifts as $shift): ?>
                            <option value="<?= $shift['id'] ?>" <?= $shift['id'] == $logbook['shift_id'] ? 'selected' : '' ?>>
                                <?= escapeHtml($shift['shift_name']) ?> 
                                (<?= date('H:i', strtotime($shift['start_time'])) ?> - <?= date('H:i', strtotime($shift['end_time'])) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Shift harus dipilih.</div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="unit_id" class="form-label">Unit <span class="text-danger">*</span></label>
                <select class="form-select" id="unit_id" name="unit_id" required>
                    <option value="">Pilih Unit...</option>
                    <?php foreach ($units as $unit): ?>
                        <option value="<?= $unit['id'] ?>" <?= $unit['id'] == $logbook['unit_id'] ? 'selected' : '' ?>>
                            <?= escapeHtml($unit['unit_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">Unit harus dipilih.</div>
            </div>
            
            <div class="mb-3">
                <label for="activity_title" class="form-label">Judul Aktivitas <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="activity_title" name="activity_title" 
                       value="<?= escapeHtml($logbook['activity_title']) ?>" required maxlength="200">
                <div class="invalid-feedback">Judul aktivitas harus diisi.</div>
            </div>
            
            <div class="mb-3">
                <label for="activity_description" class="form-label">Deskripsi Aktivitas <span class="text-danger">*</span></label>
                <textarea class="form-control" id="activity_description" name="activity_description" 
                          rows="5" required><?= escapeHtml($logbook['activity_description']) ?></textarea>
                <div class="invalid-feedback">Deskripsi aktivitas harus diisi.</div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="patient_count" class="form-label">Jumlah Pasien <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="patient_count" name="patient_count" 
                           min="0" value="<?= $logbook['patient_count'] ?>" required>
                    <div class="invalid-feedback">Jumlah pasien harus diisi.</div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="evidence" class="form-label">Ganti Bukti/Foto (Opsional)</label>
                    <?php if ($logbook['evidence_file']): ?>
                        <div class="mb-2">
                            <small class="text-muted">File saat ini: <?= escapeHtml($logbook['evidence_file']) ?></small>
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" id="evidence" name="evidence" 
                           accept="image/jpeg,image/png,image/jpg,application/pdf" data-preview="preview-evidence">
                    <small class="text-muted">Kosongkan jika tidak ingin mengubah file</small>
                    <div id="preview-evidence"></div>
                </div>
            </div>
            
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> <strong>Perhatian:</strong> 
                Pastikan semua perubahan sudah benar sebelum menyimpan.
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-emerald">
                    <i class="bi bi-save"></i> Simpan Perubahan
                </button>
                <a href="<?= APP_URL ?>/modules/medis/logbook/detail.php?id=<?= $logbookId ?>" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

</div> <!-- End main-content -->

<?php require_once __DIR__ . '/../../../layouts/footer.php'; ?>
