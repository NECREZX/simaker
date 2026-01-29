<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Require authentication
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];

// Handle POST requests (mark as read)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'mark_read') {
        $notificationId = (int)($_POST['id'] ?? 0);
        
        if ($notificationId > 0) {
            // Verify notification belongs to user
            $notification = fetchOne('SELECT * FROM notifications WHERE id = :id AND user_id = :user_id', [
                'id' => $notificationId,
                'user_id' => $userId
            ]);
            
            if ($notification) {
                markNotificationAsRead($notificationId);
                echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Notification not found']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}

// Handle GET requests (fetch notifications)
$limit = (int)($_GET['limit'] ?? 10);
$notifications = getUserNotifications($userId, $limit);
$unreadCount = getUnreadNotificationsCount($userId);

// Add time ago to each notification
foreach ($notifications as &$notification) {
    $notification['time_ago'] = timeAgo($notification['created_at']);
}

echo json_encode([
    'success' => true,
    'count' => $unreadCount,
    'notifications' => $notifications
]);
