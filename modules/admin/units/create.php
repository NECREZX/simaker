<?php
$pageTitle = 'Tambah Unit';
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
        redirect(APP_URL . '/modules/admin/units/create.php');
    }
    
    $unitCode = strtoupper(sanitizeInput($_POST['unit_code'] ?? ''));
    $unitName = sanitizeInput($_POST['unit_name'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    
    $errors = [];
    
    if (empty($unitCode)) $errors[] = 'Kode unit harus diisi.';
    if (empty($unitName)) $errors[] = 'Nama unit harus diisi.';
    
    // Check if code exists
    $existing = fetchOne("SELECT id FROM units WHERE unit_code = :code", ['code' => $unitCode]);
    if ($existing) $errors[] = 'Kode unit sudah digunakan.';
    
    if (empty($errors)) {
        $data = [
            'unit_code' => $unitCode,
            'unit_name' => $unitName,
            'description' => $description,
            'is_active' => 1
        ];
        
        $unitId = insert('units', $data);
        
        if ($unitId) {
            logActivity($_SESSION['user_id'], 'CREATE', 'units', $unitId, 'Menambah unit: ' . $unitName);
            setFlashMessage('Unit berhasil ditambahkan!', 'success');
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
        <h5 class="mb-0"><i class="bi bi-building-add"></i> Form Tambah Unit</h5>
    </div>
    <div class="card-body">
        <form method="POST" class="needs-validation" novalidate>
            <?= csrfField() ?>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="unit_code" class="form-label">Kode Unit <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="unit_code" name="unit_code" 
                           required maxlength="20" style="text-transform: uppercase;">
                    <small class="text-muted">Contoh: IGD, ICU, RALAN</small>
                </div>
                
                <div class="col-md-8 mb-3">
                    <label for="unit_name" class="form-label">Nama Unit <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="unit_name" name="unit_name" required>
                    <small class="text-muted">Contoh: Instalasi Gawat Darurat</small>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan
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
