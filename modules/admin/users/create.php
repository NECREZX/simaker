<?php
$pageTitle = 'Tambah Pengguna';
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/security.php';
require_once __DIR__ . '/../../../includes/activity_logger.php';

requireRole('Admin');

// Get roles and units
$roles = fetchAll("SELECT * FROM roles ORDER BY role_name");
$units = fetchAll("SELECT * FROM units WHERE is_active = 1 ORDER BY unit_name");

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid request.', 'danger');
        redirect(APP_URL . '/modules/admin/users/create.php');
    }
    
    $username = sanitizeInput($_POST['username'] ?? '');
    $fullName = sanitizeInput($_POST['full_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $roleId = (int)($_POST['role_id'] ?? 0);
    $unitId = (int)($_POST['unit_id'] ?? 0);
    
    $errors = [];
    
    // Validation
    if (empty($username)) $errors[] = 'Username harus diisi.';
    if (strlen($username) < 3) $errors[] = 'Username minimal 3 karakter.';
    if (empty($fullName)) $errors[] = 'Nama lengkap harus diisi.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
    if (empty($password)) $errors[] = 'Password harus diisi.';
    if (strlen($password) < 6) $errors[] = 'Password minimal 6 karakter.';
    if ($password !== $confirmPassword) $errors[] = 'Konfirmasi password tidak cocok.';
    if ($roleId === 0) $errors[] = 'Role harus dipilih.';
    
    // Check if username exists
    $existing = fetchOne("SELECT id FROM users WHERE username = :username", ['username' => $username]);
    if ($existing) $errors[] = 'Username sudah digunakan.';
    
    // Check if email exists
    $existingEmail = fetchOne("SELECT id FROM users WHERE email = :email", ['email' => $email]);
    if ($existingEmail) $errors[] = 'Email sudah digunakan.';
    
    if (empty($errors)) {
        $data = [
            'username' => $username,
            'password' => hashPassword($password),
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'role_id' => $roleId,
            'unit_id' => $unitId > 0 ? $unitId : null,
            'is_active' => 1
        ];
        
        $userId = insert('users', $data);
        
        if ($userId) {
            logActivity($_SESSION['user_id'], 'CREATE', 'users', $userId, 'Menambah user: ' . $username);
            
            // Send notification to new user
            require_once __DIR__ . '/../../../includes/notification_helper.php';
            $role = fetchOne("SELECT role_name FROM roles WHERE id = :id", ['id' => $roleId]);
            notifyNewAccount($userId, $username, $role['role_name']);
            
            setFlashMessage('Pengguna berhasil ditambahkan!', 'success');
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
        <h5 class="mb-0"><i class="bi bi-person-plus"></i> Form Tambah Pengguna</h5>
    </div>
    <div class="card-body">
        <form method="POST" class="needs-validation" novalidate>
            <?= csrfField() ?>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="username" name="username" required minlength="3">
                    <small class="text-muted">Minimal 3 karakter, tanpa spasi</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="full_name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">No. Telepon</label>
                    <input type="tel" class="form-control" id="phone" name="phone">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="password" name="password" required minlength="6">
                    <small class="text-muted">Minimal 6 karakter</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="confirm_password" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                    <select class="form-select" id="role_id" name="role_id" required>
                        <option value="">Pilih Role...</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role['id'] ?>"><?= escapeHtml($role['role_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="unit_id" class="form-label">Unit</label>
                    <select class="form-select" id="unit_id" name="unit_id">
                        <option value="">Pilih Unit (Opsional)...</option>
                        <?php foreach ($units as $unit): ?>
                            <option value="<?= $unit['id'] ?>"><?= escapeHtml($unit['unit_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Wajib untuk Tenaga Medis & Supervisor</small>
                </div>
            </div>
            
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> <strong>Catatan:</strong>
                <ul class="mb-0 mt-2">
                    <li>Username harus unik dan tidak boleh diubah setelah dibuat</li>
                    <li>Password akan di-hash secara aman</li>
                    <li>User baru akan langsung aktif dan menerima notifikasi</li>
                </ul>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan
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
