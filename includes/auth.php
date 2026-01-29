<?php
/**
 * Authentication Functions
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/activity_logger.php';

/**
 * Login user
 */
function login($username, $password) {
    $sql = "SELECT u.*, r.role_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE u.username = :username AND u.is_active = 1";
    
    $user = fetchOne($sql, ['username' => $username]);
    
    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['role_name'] = $user['role_name'];
        $_SESSION['unit_id'] = $user['unit_id'];
        $_SESSION['is_logged_in'] = true;
        
        // Log activity
        logActivity($user['id'], 'LOGIN', 'users', $user['id'], 'User logged in');
        
        return true;
    }
    
    return false;
}

/**
 * Logout user
 */
function logout() {
    if (isset($_SESSION['user_id'])) {
        logActivity($_SESSION['user_id'], 'LOGOUT', 'users', $_SESSION['user_id'], 'User logged out');
    }
    
    session_unset();
    session_destroy();
    
    // Start new session for messages
    session_start();
}

/**
 * Check if user is authenticated
 */
function isLoggedIn() {
    return isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
}

/**
 * Require authentication
 */
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/index.php');
        exit();
    }
}

/**
 * Check if user has specific role
 */
function hasRole($roleName) {
    if (!isLoggedIn()) {
        return false;
    }
    
    if (is_array($roleName)) {
        return in_array($_SESSION['role_name'], $roleName);
    }
    
    return $_SESSION['role_name'] === $roleName;
}

/**
 * Require specific role
 */
function requireRole($roleName) {
    requireAuth();
    
    if (!hasRole($roleName)) {
        header('HTTP/1.0 403 Forbidden');
        die('Access denied. You do not have permission to access this page.');
    }
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $sql = "SELECT u.*, r.role_name, un.unit_name, un.unit_code
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            LEFT JOIN units un ON u.unit_id = un.id
            WHERE u.id = :id";
    
    return fetchOne($sql, ['id' => $_SESSION['user_id']]);
}

/**
 * Get dashboard URL based on role
 */
function getDashboardUrl() {
    if (!isLoggedIn()) {
        return APP_URL . '/index.php';
    }
    
    $role = $_SESSION['role_name'];
    
    switch ($role) {
        case 'Admin':
            return APP_URL . '/modules/admin/dashboard.php';
        case 'Tenaga Medis':
            return APP_URL . '/modules/medis/dashboard.php';
        case 'Supervisor':
            return APP_URL . '/modules/supervisor/dashboard.php';
        default:
            return APP_URL . '/index.php';
    }
}

/**
 * Redirect to dashboard
 */
function redirectToDashboard() {
    header('Location: ' . getDashboardUrl());
    exit();
}
