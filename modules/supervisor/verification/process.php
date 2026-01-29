<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/security.php';
require_once __DIR__ . '/../../../includes/activity_logger.php';

requireRole('Supervisor');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(APP_URL . '/modules/supervisor/verification/index.php');
}

// Validate CSRF
if (!validateCSRFToken($_POST['csrf_token'])) {
    setFlashMessage('Invalid request.', 'danger');
    redirect(APP_URL . '/modules/supervisor/verification/index.php');
}

$logbookId = (int)($_POST['logbook_id'] ?? 0);
$action = sanitizeInput($_POST['action'] ?? '');
$notes = sanitizeInput($_POST['notes'] ?? '');

// Validate inputs
if ($logbookId === 0 || !in_array($action, ['approved', 'rejected'])) {
    setFlashMessage('Data tidak valid.', 'danger');
    redirect(APP_URL . '/modules/supervisor/verification/index.php');
}


// Get logbook
$logbook = fetchOne("SELECT * FROM logbooks WHERE id = :id", ['id' => $logbookId]);

if (!$logbook) {
    setFlashMessage('Logbook tidak ditemukan.', 'danger');
    redirect(APP_URL . '/modules/supervisor/verification/index.php');
}

// Check if already verified
if ($logbook['verification_status'] !== 'pending') {
    setFlashMessage('Logbook sudah diverifikasi sebelumnya.', 'warning');
    redirect(APP_URL . '/modules/supervisor/verification/index.php');
}

// Update logbook
$data = [
    'verification_status' => $action,
    'verified_by' => $_SESSION['user_id'],
    'verified_at' => date('Y-m-d H:i:s'),
    'verifier_notes' => $notes
];

$updated = update('logbooks', $data, 'id = :id', ['id' => $logbookId]);

if ($updated) {
    // Log activity
    $actionText = $action === 'approved' ? 'Menyetujui' : 'Menolak';
    logActivity($_SESSION['user_id'], 'UPDATE', 'logbooks', $logbookId, 
        $actionText . ' logbook: ' . $logbook['activity_title']);
    
    // Include notification helper
    require_once __DIR__ . '/../../../includes/notification_helper.php';
    
    // Send notification to staff using helper function
    $currentUser = getCurrentUser();
    notifyLogbookVerification($logbookId, $action, $currentUser['full_name'], $notes);
    
    $successMsg = $action === 'approved' 
        ? 'Logbook berhasil disetujui!' 
        : 'Logbook berhasil ditolak.';
    setFlashMessage($successMsg, 'success');
} else {
    setFlashMessage('Gagal memverifikasi logbook.', 'danger');
}

redirect(APP_URL . '/modules/supervisor/verification/index.php');
