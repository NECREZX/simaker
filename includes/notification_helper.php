<?php
/**
 * Notification Helper Functions
 * Comprehensive notification system for all user activities
 */

/**
 * Send notification to user(s)
 * 
 * @param int|array $userIds Single user ID or array of user IDs
 * @param string $title Notification title
 * @param string $message Notification message
 * @param string $type Notification type: info, success, warning, error, verification
 * @return bool Success status
 */
function sendNotification($userIds, $title, $message, $type = 'info') {
    // Ensure userIds is an array
    if (!is_array($userIds)) {
        $userIds = [$userIds];
    }
    
    $success = true;
    foreach ($userIds as $userId) {
        $data = [
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        if (!insert('notifications', $data)) {
            $success = false;
        }
    }
    
    return $success;
}

/**
 * Notify about logbook verification
 */
function notifyLogbookVerification($logbookId, $status, $verifierName, $notes = '') {
    $logbook = fetchOne("SELECT l.*, u.id as user_id, u.full_name 
        FROM logbooks l 
        JOIN users u ON l.user_id = u.id 
        WHERE l.id = :id", ['id' => $logbookId]);
    
    if (!$logbook) return false;
    
    $isApproved = ($status === 'approved');
    $title = $isApproved ? '✅ Logbook Disetujui' : '❌ Logbook Ditolak';
    $message = sprintf(
        'Logbook "%s" tanggal %s telah %s oleh %s.%s',
        $logbook['activity_title'],
        formatDate($logbook['logbook_date']),
        $isApproved ? 'disetujui' : 'ditolak',
        $verifierName,
        $notes ? "\n\nCatatan: $notes" : ''
    );
    
    return sendNotification($logbook['user_id'], $title, $message, 'verification');
}

/**
 * Notify about new logbook submission (to supervisors)
 */
function notifyNewLogbook($logbookId, $staffName) {
    $logbook = fetchOne("SELECT * FROM logbooks WHERE id = :id", ['id' => $logbookId]);
    if (!$logbook) return false;
    
    // Get all active supervisors
    $supervisors = fetchAll("SELECT id FROM users u 
        JOIN roles r ON u.role_id = r.id 
        WHERE r.role_name = 'Supervisor' AND u.is_active = 1");
    
    if (empty($supervisors)) return false;
    
    $supervisorIds = array_column($supervisors, 'id');
    $title = '📝 Logbook Baru Menunggu Verifikasi';
    $message = sprintf(
        '%s telah mengirimkan logbook baru "%s" untuk tanggal %s. Segera lakukan verifikasi.',
        $staffName,
        $logbook['activity_title'],
        formatDate($logbook['logbook_date'])
    );
    
    return sendNotification($supervisorIds, $title, $message, 'info');
}

/**
 * Notify about user account creation (to new user)
 */
function notifyNewAccount($userId, $username, $role) {
    $title = '🎉 Akun Berhasil Dibuat';
    $message = sprintf(
        'Selamat datang di SIMAKER! Akun Anda dengan username "%s" sebagai %s telah berhasil dibuat. Silakan login untuk mulai menggunakan sistem.',
        $username,
        $role
    );
    
    return sendNotification($userId, $title, $message, 'success');
}

/**
 * Notify about account update (to user)
 */
function notifyAccountUpdate($userId) {
    $title = '🔔 Data Akun Diperbarui';
    $message = 'Informasi akun Anda telah diperbarui oleh administrator. Jika ada perubahan yang tidak Anda kenali, segera hubungi admin.';
    
    return sendNotification($userId, $title, $message, 'info');
}

/**
 * Notify about account deactivation (to user)
 */
function notifyAccountDeactivation($userId, $reason = '') {
    $title = '⚠️ Akun Dinonaktifkan';
    $message = 'Akun Anda telah dinonaktifkan oleh administrator.' . 
        ($reason ? " Alasan: $reason" : ' Silakan hubungi admin untuk informasi lebih lanjut.');
    
    return sendNotification($userId, $title, $message, 'warning');
}

/**
 * Notify supervisors about pending verifications count
 */
function notifyPendingVerifications() {
    $count = countRecords('logbooks', "verification_status = 'pending'");
    
    if ($count == 0) return true;
    
    // Get all active supervisors
    $supervisors = fetchAll("SELECT id FROM users u 
        JOIN roles r ON u.role_id = r.id 
        WHERE r.role_name = 'Supervisor' AND u.is_active = 1");
    
    if (empty($supervisors)) return false;
    
    $supervisorIds = array_column($supervisors, 'id');
    $title = '⏰ Logbook Menunggu Verifikasi';
    $message = sprintf(
        'Terdapat %d logbook yang menunggu verifikasi. Mohon segera lakukan verifikasi agar karyawan dapat melanjutkan pekerjaan.',
        $count
    );
    
    return sendNotification($supervisorIds, $title, $message, 'warning');
}

/**
 * Notify about system maintenance (to all users)
 */
function notifySystemMaintenance($startTime, $endTime, $description = '') {
    // Get all active users
    $users = fetchAll("SELECT id FROM users WHERE is_active = 1");
    if (empty($users)) return false;
    
    $userIds = array_column($users, 'id');
    $title = '🔧 Pemeliharaan Sistem';
    $message = sprintf(
        'Sistem akan menjalani pemeliharaan dari %s hingga %s.%s',
        $startTime,
        $endTime,
        $description ? "\n\n$description" : ' Mohon maaf atas ketidaknyamanannya.'
    );
    
    return sendNotification($userIds, $title, $message, 'warning');
}

/**
 * Notify admins about critical activities
 */
function notifyAdmins($title, $message, $type = 'info') {
    // Get all active admins
    $admins = fetchAll("SELECT id FROM users u 
        JOIN roles r ON u.role_id = r.id 
        WHERE r.role_name = 'Admin' AND u.is_active = 1");
    
    if (empty($admins)) return false;
    
    $adminIds = array_column($admins, 'id');
    return sendNotification($adminIds, $title, $message, $type);
}

/**
 * Get unread notifications count
 */
function getUnreadNotificationsCount($userId) {
    return (int)countRecords('notifications', "user_id = :user_id AND is_read = 0", ['user_id' => $userId]);
}

/**
 * Mark all notifications as read for a user
 */
function markAllNotificationsAsRead($userId) {
    return update('notifications', ['is_read' => 1], 'user_id = :user_id AND is_read = 0', ['user_id' => $userId]);
}

/**
 * Delete old notifications (older than X days)
 */
function deleteOldNotifications($days = 30) {
    $date = date('Y-m-d H:i:s', strtotime("-$days days"));
    return db()->prepare("DELETE FROM notifications WHERE created_at < :date AND is_read = 1")
        ->execute(['date' => $date]);
}
