<?php
/**
 * Helper Functions
 * 
 */
require_once __DIR__ . '/security.php';

/**
 * Redirect and exit
 */
function redirect($url) {
    header('Location: ' . $url);
    exit();
}

/**
 * Set flash message
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        
        return ['message' => $message, 'type' => $type];
    }
    
    return null;
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'd M Y') {
    if (!$date) return '-';
    return date($format, strtotime($date));
}

/**
 * Format datetime for display
 */
function formatDateTime($datetime, $format = 'd M Y H:i') {
    if (!$datetime) return '-';
    return date($format, strtotime($datetime));
}

/**
 * Get date in Indonesian format
 */
function formatDateIndo($date) {
    if (!$date) return '-';
    
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $timestamp = strtotime($date);
    $day = date('d', $timestamp);
    $month = $bulan[(int)date('n', $timestamp)];
    $year = date('Y', $timestamp);
    
    return "$day $month $year";
}

/**
 * Get status badge HTML
 */
function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge bg-warning"><i class="bi bi-clock"></i> Pending</span>',
        'approved' => '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Disetujui</span>',
        'rejected' => '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Ditolak</span>',
    ];
    
    return $badges[$status] ?? '<span class="badge bg-secondary">' . $status . '</span>';
}

/**
 * Paginate results
 */
function paginate($sql, $params = [], $page = 1, $perPage = RECORDS_PER_PAGE) {
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM ($sql) as count_table";
    $countResult = fetchOne($countSql, $params);
    $total = $countResult['total'];
    
    // Calculate pagination
    $totalPages = ceil($total / $perPage);
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;
    
    // Get paginated results
    $paginatedSql = $sql . " LIMIT :limit OFFSET :offset";
    $params['limit'] = $perPage;
    $params['offset'] = $offset;
    
    $results = fetchAll($paginatedSql, $params);
    
    return [
        'data' => $results,
        'current_page' => $page,
        'total_pages' => $totalPages,
        'total_records' => $total,
        'per_page' => $perPage
    ];
}

/**
 * Create notification
 */
function createNotification($userId, $title, $message, $type = 'info') {
    $data = [
        'user_id' => $userId,
        'title' => $title,
        'message' => $message,
        'type' => $type,
        'is_read' => 0
    ];
    
    return insert('notifications', $data);
}

/**
 * Get unread notifications count
 */
function getUnreadNotificationsCount($userId) {
    return countRecords('notifications', 'user_id = :user_id AND is_read = 0', ['user_id' => $userId]);
}

/**
 * Get user notifications
 */
function getUserNotifications($userId, $limit = 10) {
    $sql = "SELECT * FROM notifications 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC 
            LIMIT :limit";
    
    return fetchAll($sql, ['user_id' => $userId, 'limit' => $limit]);
}

/**
 * Mark notification as read
 */
function markNotificationAsRead($notificationId) {
    return update('notifications', ['is_read' => 1], 'id = :id', ['id' => $notificationId]);
}

/**
 * Generate QR code string
 */
function generateQRCode($userId, $shiftId, $date) {
    return 'QR-' . date('Ymd', strtotime($date)) . '-' . str_pad($shiftId, 3, '0', STR_PAD_LEFT) . '-' . $userId;
}

/**
 * Time ago format
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if ($difference < 60) {
        return 'Baru saja';
    } elseif ($difference < 3600) {
        $minutes = floor($difference / 60);
        return $minutes . ' menit yang lalu';
    } elseif ($difference < 86400) {
        $hours = floor($difference / 3600);
        return $hours . ' jam yang lalu';
    } elseif ($difference < 604800) {
        $days = floor($difference / 86400);
        return $days . ' hari yang lalu';
    } else {
        return formatDateIndo($datetime);
    }
}

/**
 * Truncate text
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $suffix;
}


