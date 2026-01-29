<?php
$pageTitle = 'Tambah Shift';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/security.php';
require_once __DIR__ . '/../../../includes/activity_logger.php';

requireRole('Admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid request.', 'danger');
        redirect(APP_URL . '/modules/admin/shifts/create.php');
    }
    
    $shiftName = sanitizeInput($_POST['shift_name'] ?? '');
    $startTime = $_POST['start_time'] ?? '';
    $endTime = $_POST['end_time'] ?? '';
    
    $errors = [];
    
    if (empty($shiftName)) $errors[] = 'Nama shift harus diisi.';
    if (empty($startTime)) $errors[] = 'Jam mulai harus diisi.';
    if (empty($endTime)) $errors[] = 'Jam selesai harus diisi.';
    
    // Validate time format
    if (!empty($startTime) && !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $startTime)) {
        $errors[] = 'Format jam mulai tidak valid.';
    }
    if (!empty($endTime) && !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $endTime)) {
        $errors[] = 'Format jam selesai tidak valid.';
    }
    
    if (empty($errors)) {
        $data = [
            'shift_name' => $shiftName,
            'start_time' => $startTime . ':00',
            'end_time' => $endTime . ':00'
        ];
        
        $shiftId = insert('shifts', $data);
        
        if ($shiftId) {
            logActivity($_SESSION['user_id'], 'CREATE', 'shifts', $shiftId, 'Menambah shift: ' . $shiftName);
            setFlashMessage('Shift berhasil ditambahkan!', 'success');
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
        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Form Tambah Shift</h5>
    </div>
    <div class="card-body">
        <form method="POST" class="needs-validation" novalidate>
            <?= csrfField() ?>
            
            <div class="mb-3">
                <label for="shift_name" class="form-label">Nama Shift <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="shift_name" name="shift_name" required>
                <small class="text-muted">Contoh: Shift Pagi, Shift Siang, Shift Malam</small>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="start_time" class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                    <input type="time" class="form-control" id="start_time" name="start_time" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="end_time" class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                    <input type="time" class="form-control" id="end_time" name="end_time" required>
                </div>
            </div>
            
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> <strong>Contoh Shift:</strong>
                <ul class="mb-0 mt-2">
                    <li>Shift Pagi: 07:00 - 14:00</li>
                    <li>Shift Siang: 14:00 - 21:00</li>
                    <li>Shift Malam: 21:00 - 07:00</li>
                </ul>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-emerald">
                    <i class="bi bi-save"></i> Simpan
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
