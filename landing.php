<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirectToDashboard();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMAKER - <?= APP_FULL_NAME ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            background: linear-gradient(135deg, #e9d49fff 0%, #e9d49fff 100%);
        }

        /* Navbar */
        .navbar-landing {
            background: rgba(43, 56, 87, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(16, 185, 129, 0.1);
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .navbar-landing.scrolled {
            background: rgba(15, 23, 42, 0.98);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .navbar-brand {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            color: #10b981 !important;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-brand i {
            font-size: 2rem;
        }

        .nav-link {
            color: #cbd5e1 !important;
            font-weight: 500;
            margin: 0 0.5rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: #10b981 !important;
            transform: translateY(-2px);
        }

        .btn-login {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-left: 1rem;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
            color: white;
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding-top: 80px;
        }

        .hero-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(0, 0, 0, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(0, 0, 0, 0.1) 0%, transparent 50%);
            z-index: 0;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 4rem;
            font-weight: 800;
            color: black;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            animation: fadeInUp 1s ease;
        }

        .hero .emerald-text {
            background: linear-gradient(135deg, #10b981 0%, #0b6c7eff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            font-size: 1.3rem;
            color: #000000ff;
            margin-bottom: 2rem;
            animation: fadeInUp 1s ease 0.2s both;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            animation: fadeInUp 1s ease 0.4s both;
        }

        .btn-hero-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 1rem 2.5rem;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 40px rgba(16, 185, 129, 0.4);
            color: white;
        }

        .btn-hero-secondary {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            color: black;
            border: 2px solid rgba(0, 0, 0, 0.3);
            padding: 1rem 2.5rem;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .btn-hero-secondary:hover {
            background: rgba(16, 185, 129, 0.1);
            border-color: #000000ff;
            transform: translateY(-3px);
            color:black;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        .hero-image {
            animation: float 6s ease-in-out infinite;
        }

        /* Features Section */
        .features {
            padding: 6rem 0;
            position: relative;
            background: linear-gradient(135deg, #e9d49fff 0%, #e9d49fff 100%);
        }

        .section-title {
            font-family: 'Poppins', sans-serif;
            font-size: 2.8rem;
            font-weight: 700;
            color: black;
            text-align: center;
            margin-bottom: 1rem;
        }

        .section-subtitle {
            color: black;
            text-align: center;
            font-size: 1.2rem;
            margin-bottom: 4rem;
        }

        .feature-card {
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(16, 185, 129, 0.1);
            border-radius: 20px;
            padding: 2.5rem;
            height: 100%;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, transparent 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            border-color: #10b981;
            box-shadow: 0 20px 60px rgba(16, 185, 129, 0.2);
        }

        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .feature-card h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }

        .feature-card p {
            color: #cbd5e1;
            line-height: 1.7;
            position: relative;
            z-index: 1;
        }

        /* Roles Section */
        .roles {
            padding: 6rem 0;
            background: linear-gradient(135deg, #e9d49fff 0%, #e9d49fff 100%);;
        }

        .role-card {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.8) 0%, rgba(30, 41, 59, 0.4) 100%);
            backdrop-filter: blur(10px);
            border: 2px solid transparent;
            border-radius: 20px;
            padding: 2.5rem;
            text-align: center;
            transition: all 0.4s ease;
            height: 100%;
        }

        .role-card:hover {
            border-color: #10b981;
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(16, 185, 129, 0.3);
        }

        .role-icon {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            margin: 0 auto 1.5rem;
        }

        .role-card h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
        }

        .role-card ul {
            list-style: none;
            padding: 0;
            margin: 1.5rem 0;
            text-align: left;
        }

        .role-card li {
            color: #cbd5e1;
            padding: 0.5rem 0;
            padding-left: 1.5rem;
            position: relative;
        }

        .role-card li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #10b981;
            font-weight: bold;
        }

        /* Stats Section */
        .stats {
            padding: 5rem 0;
            background: linear-gradient(135deg, #e9d49fff 0%, #e9d49fff 100%);
        }

        .stat-item {
            text-align: center;
            color: black;
        }

        .stat-number {
            font-family: 'Poppins', sans-serif;
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        /* CTA Section */
        .cta {
            padding: 6rem 0;
            background: linear-gradient(135deg, #e9d49fff 0%, #e9d49fff 100%);
            text-align: center;
        }

        .cta h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 3rem;
            font-weight: 700;
            color: black;
            margin-bottom: 1.5rem;
        }

        .cta p {
            font-size: 1.3rem;
            color: black;
            margin-bottom: 2.5rem;
        }

        /* Scroll to Top Button */
        .scroll-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 1.5rem;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .scroll-to-top.show {
            opacity: 1;
            visibility: visible;
        }

        .scroll-to-top:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.5);
        }

        /* Footer */
        .footer {
            background: #0f172a;
            border-top: 1px solid rgba(16, 185, 129, 0.1);
            padding: 2rem 0;
            text-align: center;
            color: #94a3b8;
        }

        /* Responsive - Tablet & Mobile */
        @media (max-width: 992px) {
            .navbar-brand {
                font-size: 1.3rem;
            }
            
            .navbar-brand i {
                font-size: 1.5rem;
            }
            
            .btn-login {
                margin-left: 0;
                margin-top: 0.5rem;
                width: 100%;
            }
            
            .hero {
                padding-top: 100px;
                min-height: auto;
                padding-bottom: 3rem;
            }
            
            .hero h1 {
                font-size: 2.8rem;
            }
            
            .hero p {
                font-size: 1.15rem;
            }
            
            .section-title {
                font-size: 2.2rem;
            }
            
            .section-subtitle {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 768px) {
            /* Navbar Mobile */
            .navbar-landing {
                padding: 0.75rem 0;
            }
            
            .navbar-collapse {
                background: rgba(15, 23, 42, 0.98);
                border-radius: 12px;
                margin-top: 1rem;
                padding: 1rem;
            }
            
            .nav-link {
                padding: 0.75rem 1rem !important;
                margin: 0.25rem 0;
                border-radius: 8px;
            }
            
            .nav-link:hover {
                background: rgba(16, 185, 129, 0.1);
            }
            
            /* Hero Mobile */
            .hero {
                padding-top: 80px;
                padding-bottom: 2rem;
            }
            
            .hero h1 {
                font-size: 2rem;
                line-height: 1.3;
                margin-bottom: 1rem;
            }

            .hero p {
                font-size: 1rem;
                margin-bottom: 1.5rem;
            }

            .hero-buttons {
                flex-direction: column;
                gap: 0.75rem;
            }

            .btn-hero-primary, 
            .btn-hero-secondary {
                width: 100%;
                justify-content: center;
                padding: 0.875rem 1.5rem;
                font-size: 1rem;
            }
            
            /* Sections Mobile */
            .features,
            .roles,
            .stats,
            .cta {
                padding: 3rem 0;
            }

            .section-title {
                font-size: 1.75rem;
                margin-bottom: 0.75rem;
            }
            
            .section-subtitle {
                font-size: 1rem;
                margin-bottom: 2rem;
            }
            
            /* Feature Cards Mobile */
            .feature-card {
                padding: 1.75rem;
                margin-bottom: 1rem;
            }
            
            .feature-icon {
                width: 60px;
                height: 60px;
                font-size: 1.75rem;
                margin-bottom: 1rem;
            }
            
            .feature-card h3 {
                font-size: 1.25rem;
                margin-bottom: 0.75rem;
            }
            
            .feature-card p {
                font-size: 0.9rem;
            }
            
            /* Role Cards Mobile */
            .role-card {
                padding: 1.75rem;
                margin-bottom: 1rem;
            }
            
            .role-icon {
                width: 70px;
                height: 70px;
                font-size: 2rem;
                margin-bottom: 1rem;
            }
            
            .role-card h3 {
                font-size: 1.5rem;
            }
            
            .role-card ul {
                margin: 1rem 0;
            }
            
            .role-card li {
                font-size: 0.9rem;
                padding: 0.4rem 0;
            }

            /* Stats Mobile */
            .stat-number {
                font-size: 2rem;
                margin-bottom: 0.35rem;
            }
            
            .stat-label {
                font-size: 1rem;
            }

            /* CTA Mobile */
            .cta h2 {
                font-size: 1.75rem;
                margin-bottom: 1rem;
            }
            
            .cta p {
                font-size: 1rem;
                margin-bottom: 1.5rem;
            }
            
            /* Footer Mobile */
            .footer {
                padding: 1.5rem 0;
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 576px) {
            /* Extra Small Mobile */
            .hero h1 {
                font-size: 1.65rem;
            }
            
            .hero p {
                font-size: 0.95rem;
            }
            
            .btn-hero-primary,
            .btn-hero-secondary {
                padding: 0.75rem 1.25rem;
                font-size: 0.95rem;
            }
            
            .section-title {
                font-size: 1.5rem;
            }
            
            .section-subtitle {
                font-size: 0.95rem;
            }
            
            .feature-card,
            .role-card {
                padding: 1.5rem;
            }
            
            .stat-number {
                font-size: 1.75rem;
            }
            
            .stat-label {
                font-size: 0.9rem;
            }
            
            .cta h2 {
                font-size: 1.5rem;
            }
            
            .cta p {
                font-size: 0.95rem;
            }
        }

        /* Smooth Scroll */
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body>
    
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-landing">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-clipboard-pulse"></i>
                SIMAKER
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" style="border-color: #10b981;">
                <i class="bi bi-list" style="color: #10b981; font-size: 1.5rem;"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#roles">Pengguna</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#stats">Tentang</a>
                    </li>
                    <li class="nav-item">
                        <a href="login-page.php" class="btn btn-login">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-bg"></div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <h1>
                        Sistem Informasi<br>
                        <span class="emerald-text">Monitoring Aktivitas</span><br>
                        Kerja Rumah Sakit
                    </h1>
                    <p>
                        Logbook Medis Digital | Efisien, Terintegrasi, Praktis.
                    </p>
                    <div class="hero-buttons">
                        <a href="login-page.php" class="btn btn-hero-primary">
                            <i class="bi bi-rocket-takeoff"></i> Mulai Sekarang
                        </a>
                        <a href="#features" class="btn btn-hero-secondary">
                            Pelajari Lebih Lanjut
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <h2 class="section-title">Fitur Unggulan</h2>
            <p class="section-subtitle">Solusi lengkap untuk manajemen logbook rumah sakit</p>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <h3>Logbook Digital</h3>
                        <p>Catat aktivitas harian tenaga medis secara digital dengan mudah. Upload bukti dokumentasi dan kelola data dengan rapi.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h3>Verifikasi Otomatis</h3>
                        <p>Sistem verifikasi berjenjang dengan notifikasi real-time. Supervisor dapat menyetujui atau menolak logbook dengan mudah.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-qr-code"></i>
                        </div>
                        <h3>QR Code Attendance</h3>
                        <p>Scan QR code untuk absensi shift dengan cepat. Teknologi modern untuk tracking kehadiran yang akurat.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <h3>Laporan & Analitik</h3>
                        <p>Dashboard interaktif dengan grafik statistik. Generate laporan harian dan bulanan dalam format PDF atau Excel.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-bell"></i>
                        </div>
                        <h3>Notifikasi Real-time</h3>
                        <p>Dapatkan notifikasi instan untuk setiap perubahan status logbook. Tidak ada lagi informasi yang terlewat.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-phone"></i>
                        </div>
                        <h3>Mobile Responsive</h3>
                        <p>Akses sistem dari perangkat apapun. Desain responsive yang optimal untuk desktop, tablet, dan smartphone.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Roles Section -->
    <section class="roles" id="roles">
        <div class="container">
            <h2 class="section-title">Tiga Peran Utama</h2>
            <p class="section-subtitle">Dirancang untuk memenuhi kebutuhan setiap pengguna</p>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="role-card">
                        <div class="role-icon">
                            <i class="bi bi-person-gear"></i>
                        </div>
                        <h3>Admin</h3>
                        <p style="color: #94a3b8; margin-bottom: 1.5rem;">Kelola seluruh sistem</p>
                        <ul>
                            <li>Manajemen pengguna</li>
                            <li>Kelola unit & shift</li>
                            <li>Monitor aktivitas sistem</li>
                            <li>Pengaturan konfigurasi</li>
                            <li>Activity logs lengkap</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="role-card">
                        <div class="role-icon">
                            <i class="bi bi-clipboard-heart"></i>
                        </div>
                        <h3>Tenaga Medis</h3>
                        <p style="color: #94a3b8; margin-bottom: 1.5rem;">Input logbook harian</p>
                        <ul>
                            <li>Buat logbook baru</li>
                            <li>Upload bukti dokumentasi</li>
                            <li>Tracking status verifikasi</li>
                            <li>QR code attendance</li>
                            <li>Lihat history aktivitas</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="role-card">
                        <div class="role-icon">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                        <h3>Supervisor</h3>
                        <p style="color: #94a3b8; margin-bottom: 1.5rem;">Verifikasi & laporan</p>
                        <ul>
                            <li>Review logbook staff</li>
                            <li>Approve/reject logbook</li>
                            <li>Generate laporan</li>
                            <li>Analitik & grafik</li>
                            <li>Export PDF & Excel</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats" id="stats">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6 mb-4">
                    <div class="stat-item">
                        <div class="stat-number">100%</div>
                        <div class="stat-label">Digital</div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="stat-item">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Akses Online</div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="stat-item">
                        <div class="stat-number">3</div>
                        <div class="stat-label">Role Pengguna</div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-4">
                    <div class="stat-item">
                        <div class="stat-number">∞</div>
                        <div class="stat-label">Kemudahan</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Scroll to Top Button -->
    <button id="scrollToTop" class="scroll-to-top" aria-label="Scroll to top">
        <i class="bi bi-arrow-up"></i>
    </button>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p class="mb-0">
                &copy; 2026 SIMAKER - Sistem Informasi Monitoring Aktivitas Kerja. All Rights Reserved.
            </p>
            <p class="mt-2 mb-0">
                <i class="bi bi-shield-check"></i> Sistem informasi aman dan terpercaya
            </p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-landing');
            const scrollToTop = document.getElementById('scrollToTop');
            
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
            
            // Show/hide scroll to top button
            if (window.scrollY > 300) {
                scrollToTop.classList.add('show');
            } else {
                scrollToTop.classList.remove('show');
            }
        });

        // Scroll to top button click
        document.getElementById('scrollToTop').addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
