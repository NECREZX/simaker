<?php
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

// Check if logbook can be deleted (only pending status)
if ($logbook['verification_status'] !== 'pending') {
    setFlashMessage('Hanya logbook dengan status Pending yang dapat dihapus.', 'danger');
    redirect(APP_URL . '/modules/medis/logbook/detail.php?id=' . $logbookId);
}

// Validate CSRF token
if (!validateCSRFToken($_GET['token'] ?? '')) {
    setFlashMessage('Invalid request.', 'danger');
    redirect(APP_URL . '/modules/medis/logbook/index.php');
}

// Delete evidence file if exists
if ($logbook['evidence_file']) {
    $filePath = UPLOAD_PATH . '/logbooks/' . $logbook['evidence_file'];
    if (file_exists($filePath)) {
        @unlink($filePath);
    }
}

// Delete logbook
$deleted = delete('logbooks', 'id = :id AND user_id = :user_id', 
    ['id' => $logbookId, 'user_id' => $userId]);

if ($deleted) {
    logActivity($userId, 'DELETE', 'logbooks', $logbookId, 'Menghapus logbook: ' . $logbook['activity_title']);
    setFlashMessage('Logbook berhasil dihapus.', 'success');
} else {
    setFlashMessage('Gagal menghapus logbook.', 'danger');
}

redirect(APP_URL . '/modules/medis/logbook/index.php');
