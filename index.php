<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirectToDashboard();
    exit();
}

// Redirect to landing page
header('Location: landing.php');
exit();
