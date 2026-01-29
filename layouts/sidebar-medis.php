<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar for Medical Staff -->
<div class="sidebar">
    <div class="logo">
        <i class="bi bi-clipboard-pulse"></i> SIMAKER
    </div>
    
    <nav class="nav flex-column">
        <a class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/modules/medis/dashboard.php">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        
        <div class="nav-section-title text-white-50 px-3 mt-3 mb-2 small">LOGBOOK</div>
        
        <a class="nav-link <?= $currentPage === 'create.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/modules/medis/logbook/create.php">
            <i class="bi bi-plus-circle"></i> Tambah Logbook
        </a>
        
        <a class="nav-link <?= strpos($currentPage, 'index.php') !== false && strpos($_SERVER['PHP_SELF'], 'logbook') !== false ? 'active' : '' ?>" href="<?= APP_URL ?>/modules/medis/logbook/index.php">
            <i class="bi bi-journal-text"></i> Riwayat Logbook
        </a>
        
        <div class="nav-section-title text-white-50 px-3 mt-3 mb-2 small">ABSENSI</div>
        
        <a class="nav-link <?= $currentPage === 'qr-attendance.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/modules/medis/qr-attendance.php">
            <i class="bi bi-qr-code"></i> QR Attendance
        </a>
        
        <div class="nav-section-title text-white-50 px-3 mt-3 mb-2 small">AKUN</div>
        
        <a class="nav-link <?= $currentPage === 'profile.php' ? 'active' : '' ?>" href="<?= APP_URL ?>/modules/medis/profile.php">
            <i class="bi bi-person"></i> Profil Saya
        </a>
        
        <a class="nav-link" href="<?= APP_URL ?>/logout.php">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </nav>
</div>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay"></div>

<!-- Main Content Wrapper -->
<div class="main-content">
    <div class="top-navbar">
        <h1 class="page-title"><?= isset($pageTitle) ? escapeHtml($pageTitle) : 'Dashboard' ?></h1>
        
        <div class="navbar-actions">
            <!-- Notification Bell -->
            <div class="dropdown">
                <button class="btn notification-bell" id="notificationBell" data-bs-toggle="dropdown">
                    <i class="bi bi-bell"></i>
                    <?php if ($unreadCount > 0): ?>
                    <span class="badge bg-danger"><?= $unreadCount > 99 ? '99+' : $unreadCount ?></span>
                    <?php endif; ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" id="notificationDropdown" style="min-width: 300px; max-height: 400px; overflow-y: auto;">
                    <li><a class="dropdown-item text-center" href="#">Loading...</a></li>
                </ul>
            </div>
            
            <!-- Dark Mode Toggle -->
            <button class="dark-mode-toggle" id="darkModeToggle">
                <i class="bi bi-moon-stars"></i>
            </button>
            
            <!-- User Dropdown -->
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i> <?= escapeHtml($current_user['full_name']) ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= APP_URL ?>/modules/medis/profile.php"><i class="bi bi-person"></i> Profil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= APP_URL ?>/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
