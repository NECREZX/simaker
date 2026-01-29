<?php
/**
 * Activity Logger Functions
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/security.php';

/**
 * Log user activity
 */
function logActivity($userId, $action, $tableName = null, $recordId = null, $description = null) {
    try {
        $data = [
            'user_id' => $userId,
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'description' => $description,
            'ip_address' => getClientIP()
        ];
        
        insert('activity_logs', $data);
    } catch (Exception $e) {
        // Log error but don't break the application
        error_log('Activity log error: ' . $e->getMessage());
    }
}

/**
 * Get activity logs
 */
function getActivityLogs($limit = 50, $userId = null, $action = null) {
    $where = [];
    $params = [];
    
    if ($userId) {
        $where[] = 'al.user_id = :user_id';
        $params['user_id'] = $userId;
    }
    
    if ($action) {
        $where[] = 'al.action = :action';
        $params['action'] = $action;
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $sql = "SELECT al.*, u.full_name, u.username
            FROM activity_logs al
            JOIN users u ON al.user_id = u.id
            $whereClause
            ORDER BY al.created_at DESC
            LIMIT :limit";
    
    $params['limit'] = $limit;
    
    return fetchAll($sql, $params);
}
