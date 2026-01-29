<?php
$pageTitle = 'Edit Unit';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/security.php';
require_once __DIR__ . '/../../../includes/activity_logger.php';

requireRole('Admin');

$unitId = (int)($_GET['id'] ?? 0);

if ($unitId === 0) {
    setFlashMessage('Unit tidak ditemukan.', 'danger');
    redirect(APP_URL . '/modules/admin/units/index.php');
}

$unit = fetchOne("SELECT * FROM units WHERE id = :id", ['id' => $unitId]);

if (!$unit) {
    setFlashMessage('Unit tidak ditemukan.', 'danger');
    redirect(APP_URL . '/modules/admin/units/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid request.', 'danger');
        redirect(APP_URL . '/modules/admin/units/edit.php?id=' . $unitId);
    }
    
    $unitName = sanitizeInput($_POST['unit_name'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    $errors = [];
    
    if (empty($unitName)) $errors[] = 'Nama unit harus diisi.';
    
    if (empty($errors)) {
        $data = [
            'unit_name' => $unitName,
            'description' => $description,
            'is_active' => $isActive
        ];
        
        $updated = update('units', $data, 'id = :id', ['id' => $unitId]);
        
        if ($updated) {
            logActivity($_SESSION['user_id'], 'UPDATE', 'units', $unitId, 'Mengupdate unit: ' . $unit['unit_code']);
            setFlashMessage('Unit berhasil diperbarui!', 'success');
            redirect(APP_URL . '/modules/admin/units/index.php');
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
        <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Edit Unit: <?= escapeHtml($unit['unit_code']) ?></h5>
    </div>
    <div class="card-body">
        <form method="POST" class="needs-validation" novalidate>
            <?= csrfField() ?>
            
            <div class="mb-3">
                <label class="form-label">Kode Unit</label>
                <input type="text" class="form-control" value="<?= escapeHtml($unit['unit_code']) ?>" disabled>
                <small class="text-muted">Kode unit tidak dapat diubah</small>
            </div>
            
            <div class="mb-3">
                <label for="unit_name" class="form-label">Nama Unit <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="unit_name" name="unit_name" 
                       value="<?= escapeHtml($unit['unit_name']) ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?= escapeHtml($unit['description'] ?? '') ?></textarea>
            </div>
            
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                           <?= $unit['is_active'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_active">
                        <strong>Unit Aktif</strong>
                    </label>
                    <br><small class="text-muted">Unit nonaktif tidak akan muncul di pilihan</small>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Perubahan
                </button>
                <a href="<?= APP_URL ?>/modules/admin/units/index.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

</div>

<?php require_once __DIR__ . '/../../../layouts/footer.php'; ?>
