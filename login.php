<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/activity_logger.php';
require_once __DIR__ . '/includes/functions.php';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid request. Please try again.', 'danger');
        redirect(APP_URL . '/login-page.php');
    }
    
    // Sanitize inputs
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate inputs
    if (empty($username) || empty($password)) {
        setFlashMessage('Username dan password harus diisi.', 'danger');
        redirect(APP_URL . '/login-page.php');
    }
    
    // Attempt login
    if (login($username, $password)) {
        setFlashMessage('Login berhasil! Selamat datang, ' . $_SESSION['full_name'] . '.', 'success');
        redirectToDashboard();
    } else {
        setFlashMessage('Username atau password salah.', 'danger');
        redirect(APP_URL . '/login-page.php');
    }
} else {
    // If not POST request, redirect to login page
    redirect(APP_URL . '/login-page.php');
}
