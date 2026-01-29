<?php
$pageTitle = 'Edit Shift';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/security.php';
require_once __DIR__ . '/../../../includes/activity_logger.php';

requireRole('Admin');

$shiftId = (int)($_GET['id'] ?? 0);

if ($shiftId === 0) {
    setFlashMessage('Shift tidak ditemukan.', 'danger');
    redirect(APP_URL . '/modules/admin/shifts/index.php');
}

$shift = fetchOne("SELECT * FROM shifts WHERE id = :id", ['id' => $shiftId]);

if (!$shift) {
    setFlashMessage('Shift tidak ditemukan.', 'danger');
    redirect(APP_URL . '/modules/admin/shifts/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid request.', 'danger');
        redirect(APP_URL . '/modules/admin/shifts/edit.php?id=' . $shiftId);
    }
    
    $shiftName = sanitizeInput($_POST['shift_name'] ?? '');
    $startTime = $_POST['start_time'] ?? '';
    $endTime = $_POST['end_time'] ?? '';
    
    $errors = [];
    
    if (empty($shiftName)) $errors[] = 'Nama shift harus diisi.';
    if (empty($startTime)) $errors[] = 'Jam mulai harus diisi.';
    if (empty($endTime)) $errors[] = 'Jam selesai harus diisi.';
    
    if (empty($errors)) {
        $data = [
            'shift_name' => $shiftName,
            'start_time' => $startTime . ':00',
            'end_time' => $endTime . ':00'
        ];
        
        $updated = update('shifts', $data, 'id = :id', ['id' => $shiftId]);
        
        if ($updated) {
            logActivity($_SESSION['user_id'], 'UPDATE', 'shifts', $shiftId, 'Mengupdate shift: ' . $shiftName);
            setFlashMessage('Shift berhasil diperbarui!', 'success');
            redirect(APP_URL . '/modules/admin/shifts/index.php');
        } else {
            $errors[] = 'Gagal menyimpan data.';
        }
    }
    
    if (!empty($errors)) {
        foreach ($errors as $error) {
            setFlashMessage($error, 'danger');
        }
    }
}

require_once __DIR__ . '/../../../layouts/header.php';
require_once __DIR__ . '/../../../layouts/sidebar-admin.php';
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Edit Shift: <?= escapeHtml($shift['shift_name']) ?></h5>
    </div>
    <div class="card-body">
        <form method="POST" class="needs-validation" novalidate>
            <?= csrfField() ?>
            
            <div class="mb-3">
                <label for="shift_name" class="form-label">Nama Shift <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="shift_name" name="shift_name" 
                       value="<?= escapeHtml($shift['shift_name']) ?>" required>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="start_time" class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                    <input type="time" class="form-control" id="start_time" name="start_time" 
                           value="<?= date('H:i', strtotime($shift['start_time'])) ?>" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="end_time" class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                    <input type="time" class="form-control" id="end_time" name="end_time" 
                           value="<?= date('H:i', strtotime($shift['end_time'])) ?>" required>
                </div>
            </div>
            
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> <strong>Perhatian:</strong>
                Mengubah jam shift dapat mempengaruhi data kehadiran dan logbook yang sudah ada.
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-emerald">
                    <i class="bi bi-save"></i> Simpan Perubahan
                </button>
                <a href="<?= APP_URL ?>/modules/admin/shifts/index.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

</div>

<?php require_once __DIR__ . '/../../../layouts/footer.php'; ?>
