<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

logout();
setFlashMessage('Anda telah berhasil logout.', 'success');
redirect(APP_URL . '/index.php');
