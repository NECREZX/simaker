<?php
$pageTitle = 'QR Attendance';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/activity_logger.php';

requireRole('Tenaga Medis');

$userId = $_SESSION['user_id'];
$today = date('Y-m-d');

// Get user's shift today (if any)
$todayShift = fetchOne("SELECT s.* FROM shifts s 
    WHERE TIME(NOW()) BETWEEN s.start_time AND s.end_time
    LIMIT 1");

// Check if already checked in today
$todayAttendance = fetchOne("SELECT * FROM qr_attendance 
    WHERE user_id = :user_id AND DATE(scanned_at) = :today",
    ['user_id' => $userId, 'today' => $today]);

// Process check-in
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_in'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        setFlashMessage('Invalid request.', 'danger');
        redirect(APP_URL . '/modules/medis/qr-attendance.php');
    }
    
    if ($todayAttendance) {
        setFlashMessage('Anda sudah melakukan check-in hari ini.', 'warning');
    } elseif (!$todayShift) {
        setFlashMessage('Tidak ada shift aktif saat ini.', 'warning');
    } else {
        // Generate QR code
        $qrCode = 'QR-' . date('Ymd') . '-' . str_pad($userId, 3, '0', STR_PAD_LEFT) . '-' . $todayShift['id'];
        
        $data = [
            'user_id' => $userId,
            'shift_id' => $todayShift['id'],
            'attendance_date' => $today,
            'qr_code' => $qrCode,
            'scanned_at' => date('Y-m-d H:i:s'),
            'status' => 'on_time'
        ];
        
        $attendanceId = insert('qr_attendance', $data);
        
        if ($attendanceId) {
            logActivity($userId, 'CREATE', 'qr_attendance', $attendanceId, 'Check-in attendance');
            setFlashMessage('Check-in berhasil!', 'success');
            redirect(APP_URL . '/modules/medis/qr-attendance.php');
        }
    }
}

// Get attendance history (last 30 days)
$attendances = fetchAll("SELECT a.*, s.shift_name, s.start_time, s.end_time
    FROM qr_attendance a
    JOIN shifts s ON a.shift_id = s.id
    WHERE a.user_id = :user_id 
    ORDER BY a.scanned_at DESC
    LIMIT 30", ['user_id' => $userId]);

require_once __DIR__ . '/../../layouts/header.php';
require_once __DIR__ . '/../../layouts/sidebar-medis.php';
?>

<div class="row">
    <!-- QR Check-in Card -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-qr-code"></i> QR Code Attendance</h5>
            </div>
            <div class="card-body text-center">
                <?php if ($todayAttendance): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle fs-1"></i>
                        <h4 class="mt-3">Sudah Check-in!</h4>
                        <p>Waktu: <?= formatDateTime($todayAttendance['scanned_at']) ?></p>
                        <p class="mb-0">Shift: <?= $todayShift ? escapeHtml($todayShift['shift_name']) : '-' ?></p>
                    </div>
                <?php elseif (!$todayShift): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-clock fs-1"></i>
                        <h4 class="mt-3">Tidak Ada Shift Aktif</h4>
                        <p class="mb-0">Silakan check-in saat jam shift Anda.</p>
                    </div>
                <?php else: ?>
                    <!-- Generate QR Code -->
                    <?php
                    $qrData = base64_encode(json_encode([
                        'user_id' => $userId,
                        'date' => $today,
                        'shift_id' => $todayShift['id'],
                        'token' => md5($userId . $today . APP_SECRET)
                    ]));
                    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($qrData);
                    ?>
                    
                    <div class="mb-4">
                        <img src="<?= $qrCodeUrl ?>" alt="QR Code" class="img-fluid">
                    </div>
                    
                    <h5>Shift Aktif: <?= escapeHtml($todayShift['shift_name']) ?></h5>
                    <p class="text-muted">
                        <?= date('H:i', strtotime($todayShift['start_time'])) ?> - 
                        <?= date('H:i', strtotime($todayShift['end_time'])) ?>
                    </p>
                    
                    <form method="POST" class="mt-4">
                        <?= csrfField() ?>
                        <button type="submit" name="check_in" class="btn btn-emerald btn-lg">
                            <i class="bi bi-check-circle"></i> Check-In Sekarang
                        </button>
                    </form>
                    
                    <small class="text-muted mt-3 d-block">
                        Atau scan QR Code di atas dengan aplikasi mobile
                    </small>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Attendance Stats -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Statistik Kehadiran</h5>
            </div>
            <div class="card-body">
                <?php
                // Calculate stats for current month
                $monthStart = date('Y-m-01');
                $monthEnd = date('Y-m-t');
                
                $monthStats = fetchOne("SELECT 
                    COUNT(*) as total_days,
                    SUM(CASE WHEN scanned_at = 'on_time' THEN 1 ELSE 0 END) as on_time,
                    SUM(CASE WHEN scanned_at = 'late' THEN 1 ELSE 0 END) as late
                    FROM qr_attendance
                    WHERE user_id = :user_id 
                    AND DATE(scanned_at) BETWEEN :start AND :end",
                    ['user_id' => $userId, 'start' => $monthStart, 'end' => $monthEnd]);
                
                $total = $monthStats['total_days'] ?? 0;
                $onTime = $monthStats['on_time'] ?? 0;
                $late = $monthStats['late'] ?? 0;
                $onTimePercent = $total > 0 ? round(($onTime / $total) * 100) : 0;
                ?>
                
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="p-3 bg-light rounded">
                            <h2 class="text-primary mb-0"><?= $total ?></h2>
                            <small class="text-muted">Total Hari Hadir</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="p-3 bg-light rounded">
                            <h2 class="text-success mb-0"><?= $onTime ?></h2>
                            <small class="text-muted">Tepat Waktu</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="p-3 bg-light rounded">
                            <h2 class="text-warning mb-0"><?= $late ?></h2>
                            <small class="text-muted">Terlambat</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="p-3 bg-light rounded">
                            <h2 class="text-info mb-0"><?= $onTimePercent ?>%</h2>
                            <small class="text-muted">Ketepatan</small>
                        </div>
                    </div>
                </div>
                
                <div class="progress mt-3" style="height: 25px;">
                    <div class="progress-bar bg-success" style="width: <?= $onTimePercent ?>%">
                        <?= $onTimePercent ?>% Tepat Waktu
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance History -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Riwayat Kehadiran (30 Hari Terakhir)</h5>
    </div>
    <div class="card-body">
        <?php if (empty($attendances)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Belum ada riwayat kehadiran.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Check-In</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendances as $att): ?>
                        <tr>
                            <td><?= formatDate(substr($att['scanned_at'], 0, 10)) ?></td>
                            <td><?= escapeHtml($att['shift_name']) ?></td>
                            <td><?= date('H:i:s', strtotime($att['scanned_at'])) ?></td>
                            <td>
                                <?php
                                $statusConfig = [
                                    'on_time' => ['class' => 'bg-success', 'text' => 'Tepat Waktu'],
                                    'late' => ['class' => 'bg-warning', 'text' => 'Terlambat'],
                                    'absent' => ['class' => 'bg-danger', 'text' => 'Tidak Hadir']
                                ];
                                $status = $statusConfig[$att['scanned_at']] ?? ['class' => 'bg-secondary', 'text' => $att['scanned_at']];
                                ?>
                                <span class="badge <?= $status['class'] ?>"><?= $status['text'] ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

</div> <!-- End main-content -->

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
