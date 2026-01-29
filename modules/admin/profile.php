<?php
$pageTitle = 'Profil Saya';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/activity_logger.php';

requireRole('Admin');

$userId = $_SESSION['user_id'];
$user = getCurrentUser();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid request.', 'danger');
        redirect(APP_URL . '/modules/admin/profile.php');
    }
    
    $fullName = sanitizeInput($_POST['full_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    
    if (empty($fullName) || empty($email)) {
        setFlashMessage('Nama dan email harus diisi.', 'danger');
    } else {
        $data = [
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone
        ];
        
        $updated = update('users', $data, 'id = :id', ['id' => $userId]);
        
        if ($updated) {
            $_SESSION['full_name'] = $fullName;
            logActivity($userId, 'UPDATE', 'users', $userId, 'Update profil');
            setFlashMessage('Profil berhasil diperbarui!', 'success');
            redirect(APP_URL . '/modules/admin/profile.php');
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid request.', 'danger');
        redirect(APP_URL . '/modules/admin/profile.php');
    }
    
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Verify current password
    if (!verifyPassword($currentPassword, $user['password'])) {
        setFlashMessage('Password saat ini salah.', 'danger');
    } elseif (strlen($newPassword) < 6) {
        setFlashMessage('Password baru minimal 6 karakter.', 'danger');
    } elseif ($newPassword !== $confirmPassword) {
        setFlashMessage('Konfirmasi password tidak cocok.', 'danger');
    } else {
        $hashedPassword = hashPassword($newPassword);
        $updated = update('users', ['password' => $hashedPassword], 'id = :id', ['id' => $userId]);
        
        if ($updated) {
            logActivity($userId, 'UPDATE', 'users', $userId, 'Mengubah password');
            setFlashMessage('Password berhasil diubah!', 'success');
            redirect(APP_URL . '/modules/admin/profile.php');
        }
    }
}

require_once __DIR__ . '/../../layouts/header.php';
require_once __DIR__ . '/../../layouts/sidebar-admin.php';
?>

<div class="row">
    <!-- Profile Information -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-circle"></i> Informasi Profil</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?= csrfField() ?>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?= escapeHtml($user['username']) ?>" disabled>
                        <small class="text-muted">Username tidak dapat diubah</small>
                   </div>
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                               value="<?= escapeHtml($user['full_name']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= escapeHtml($user['email']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">No. Telepon</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?= escapeHtml($user['phone'] ?? '') ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <input type="text" class="form-control" value="<?= escapeHtml($user['role_name']) ?>" disabled>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Change Password -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-key"></i> Ubah Password</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?= csrfField() ?>
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Password Saat Ini <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Password Baru <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="new_password" name="new_password" 
                               minlength="6" required>
                        <small class="text-muted">Minimal 6 karakter</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn btn-warning">
                        <i class="bi bi-shield-lock"></i> Ubah Password
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Profile Summary -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="bi bi-person-circle" style="font-size: 5rem; color: var(--primary);"></i>
                </div>
                <h4><?= escapeHtml($user['full_name']) ?></h4>
                <p class="text-muted"><?= escapeHtml($user['role_name']) ?></p>
                <hr>
                <div class="text-start">
                    <p><strong>Username:</strong><br><?= escapeHtml($user['username']) ?></p>
                    <p><strong>Email:</strong><br><?= escapeHtml($user['email']) ?></p>
                    <p><strong>Bergabung:</strong><br><?= formatDate(substr($user['created_at'], 0, 10)) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

</div> <!-- End main-content -->

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
