<?php
$pageTitle = 'Edit Pengguna';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/security.php';
require_once __DIR__ . '/../../../includes/activity_logger.php';

requireRole('Admin');

$userId = (int)($_GET['id'] ?? 0);

if ($userId === 0 || $userId == $_SESSION['user_id']) {
    setFlashMessage('Tidak dapat mengedit user ini.', 'danger');
    redirect(APP_URL . '/modules/admin/users/index.php');
}

// Get user data
$user = fetchOne("SELECT * FROM users WHERE id = :id", ['id' => $userId]);

if (!$user) {
    setFlashMessage('User tidak ditemukan.', 'danger');
    redirect(APP_URL . '/modules/admin/users/index.php');
}

// Get roles and units
$roles = fetchAll("SELECT * FROM roles ORDER BY role_name");
$units = fetchAll("SELECT * FROM units WHERE is_active = 1 ORDER BY unit_name");

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid request.', 'danger');
        redirect(APP_URL . '/modules/admin/users/edit.php?id=' . $userId);
    }
    
    $fullName = sanitizeInput($_POST['full_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $roleId = (int)($_POST['role_id'] ?? 0);
    $unitId = (int)($_POST['unit_id'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    $errors = [];
    
    if (empty($fullName)) $errors[] = 'Nama lengkap harus diisi.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
    if ($roleId === 0) $errors[] = 'Role harus dipilih.';
    
    // Check email uniqueness (exclude current user)
    $existingEmail = fetchOne("SELECT id FROM users WHERE email = :email AND id != :id", 
        ['email' => $email, 'id' => $userId]);
    if ($existingEmail) $errors[] = 'Email sudah digunakan user lain.';
    
    if (!empty($newPassword) && strlen($newPassword) < 6) {
        $errors[] = 'Password baru minimal 6 karakter.';
    }
    
    if (empty($errors)) {
        $data = [
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'role_id' => $roleId,
            'unit_id' => $unitId > 0 ? $unitId : null,
            'is_active' => $isActive
        ];
        
        // Update password if provided
        if (!empty($newPassword)) {
            $data['password'] = hashPassword($newPassword);
        }
        
        $updated = update('users', $data, 'id = :id', ['id' => $userId]);
        
        if ($updated) {
            logActivity($_SESSION['user_id'], 'UPDATE', 'users', $userId, 'Mengupdate user: ' . $user['username']);
            
            // Send notification to user
            require_once __DIR__ . '/../../../includes/notification_helper.php';
            notifyAccountUpdate($userId);
            
            setFlashMessage('Data pengguna berhasil diperbarui!', 'success');
            redirect(APP_URL . '/modules/admin/users/index.php');
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
        <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Edit Pengguna: <?= escapeHtml($user['username']) ?></h5>
    </div>
    <div class="card-body">
        <form method="POST" class="needs-validation" novalidate>
            <?= csrfField() ?>
            
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" value="<?= escapeHtml($user['username']) ?>" disabled>
                <small class="text-muted">Username tidak dapat diubah</small>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="full_name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="full_name" name="full_name" 
                           value="<?= escapeHtml($user['full_name']) ?>" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?= escapeHtml($user['email']) ?>" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">No. Telepon</label>
                    <input type="tel" class="form-control" id="phone" name="phone" 
                           value="<?= escapeHtml($user['phone'] ?? '') ?>">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="new_password" class="form-label">Password Baru (Opsional)</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" minlength="6">
                    <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                    <select class="form-select" id="role_id" name="role_id" required>
                        <option value="">Pilih Role...</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role['id'] ?>" <?= $role['id'] == $user['role_id'] ? 'selected' : '' ?>>
                                <?= escapeHtml($role['role_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="unit_id" class="form-label">Unit</label>
                    <select class="form-select" id="unit_id" name="unit_id">
                        <option value="">Pilih Unit (Opsional)...</option>
                        <?php foreach ($units as $unit): ?>
                            <option value="<?= $unit['id'] ?>" <?= $unit['id'] == $user['unit_id'] ? 'selected' : '' ?>>
                                <?= escapeHtml($unit['unit_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                           <?= $user['is_active'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_active">
                        <strong>User Aktif</strong>
                    </label>
                    <br><small class="text-muted">Nonaktifkan untuk melarang user login</small>
                </div>
            </div>
            
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> <strong>Perhatian:</strong>
                Mengubah role atau unit dapat mempengaruhi akses user ke sistem. User akan menerima notifikasi update.
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Perubahan
                </button>
                <a href="<?= APP_URL ?>/modules/admin/users/index.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

</div>

<?php require_once __DIR__ . '/../../../layouts/footer.php'; ?>
