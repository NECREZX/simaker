-- SIMAKER Seed Data
-- Sample data for testing

USE simaker;

-- Insert roles
INSERT INTO roles (role_name, description) VALUES
('Admin', 'Administrator dengan akses penuh ke sistem'),
('Tenaga Medis', 'Tenaga medis yang mengisi logbook harian'),
('Supervisor', 'Supervisor/Kepala Ruangan yang melakukan verifikasi');

-- Insert units
INSERT INTO units (unit_code, unit_name, description, is_active) VALUES
('IGD', 'Instalasi Gawat Darurat', 'Unit pelayanan gawat darurat 24 jam', 1),
('ICU', 'Intensive Care Unit', 'Unit perawatan intensif', 1),
('RINAP', 'Rawat Inap', 'Unit rawat inap umum', 1),
('RAJAL', 'Rawat Jalan', 'Unit rawat jalan poliklinik', 1),
('OK', 'Kamar Operasi', 'Unit kamar operasi', 1),
('LAB', 'Laboratorium', 'Unit laboratorium', 1),
('RAD', 'Radiologi', 'Unit radiologi', 1),
('FARM', 'Farmasi', 'Unit farmasi rumah sakit', 1);

-- Insert shifts
INSERT INTO shifts (shift_name, start_time, end_time, description) VALUES
('Pagi', '07:00:00', '14:00:00', 'Shift pagi'),
('Siang', '14:00:00', '21:00:00', 'Shift siang'),
('Malam', '21:00:00', '07:00:00', 'Shift malam');

-- Insert users
-- Password untuk semua user: password123 (hashed dengan bcrypt)
INSERT INTO users (username, email, password, role_id, unit_id, full_name, phone, is_active) VALUES
-- Admin users
('admin', 'admin@simaker.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NULL, 'Administrator SIMAKER', '081234567890', 1),

-- Supervisor users
('supervisor.igd', 'supervisor.igd@simaker.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 1, 'Dr. Budi Santoso, Sp.EM', '081234567891', 1),
('supervisor.icu', 'supervisor.icu@simaker.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 2, 'Dr. Siti Nurhaliza, Sp.An', '081234567892', 1),
('supervisor.rinap', 'supervisor.rinap@simaker.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 3, 'Ns. Ahmad Hidayat, S.Kep', '081234567893', 1),

-- Medical staff users
('medis.andi', 'andi@simaker.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 1, 'Ns. Andi Prasetyo, S.Kep', '081234567894', 1),
('medis.dewi', 'dewi@simaker.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 1, 'Ns. Dewi Lestari, AMK', '081234567895', 1),
('medis.eka', 'eka@simaker.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 2, 'Ns. Eka Putri, S.Kep', '081234567896', 1),
('medis.fajar', 'fajar@simaker.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 3, 'Ns. Fajar Rahman, AMK', '081234567897', 1),
('medis.gita', 'gita@simaker.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 3, 'Ns. Gita Savitri, S.Kep', '081234567898', 1);

-- Insert sample logbooks (with various statuses)
INSERT INTO logbooks (user_id, logbook_date, shift_id, unit_id, activity_title, activity_description, patient_count, verification_status) VALUES
-- Approved logbooks
(5, '2026-01-28', 1, 1, 'Perawatan Pasien Trauma', 'Melakukan perawatan dan monitoring pasien trauma kepala akibat kecelakaan lalu lintas. Vital signs stabil.', 3, 'approved'),
(6, '2026-01-28', 2, 1, 'Triase Pasien IGD', 'Melakukan triase untuk 12 pasien yang datang ke IGD. 2 pasien prioritas merah, 5 kuning, 5 hijau.', 12, 'approved'),
(7, '2026-01-28', 1, 2, 'Monitoring ICU', 'Monitoring pasien ICU dengan ventilator. Melakukan suctioning dan turning position setiap 2 jam.', 5, 'approved'),

-- Pending logbooks (need verification)
(5, '2026-01-29', 1, 1, 'Pemasangan Infus & Kateter', 'Memasang infus line pada 4 pasien baru dan kateter urine pada 2 pasien. Semua tindakan berjalan lancar.', 6, 'pending'),
(8, '2026-01-29', 2, 3, 'Pemberian Obat Oral', 'Memberikan obat oral sesuai jadwal untuk 15 pasien rawat inap. Tidak ada reaksi alergi.', 15, 'pending'),
(9, '2026-01-29', 1, 3, 'Perawatan Luka Post Operasi', 'Melakukan perawatan luka pada 8 pasien post operasi. Semua luka bersih, tidak ada tanda infeksi.', 8, 'pending'),

-- Rejected logbook (example)
(6, '2026-01-27', 3, 1, 'Observasi Pasien', 'Observasi pasien rutin.', 2, 'rejected');

-- Insert verifications for approved/rejected logbooks
INSERT INTO verifications (logbook_id, verifier_id, status, notes, verified_at) VALUES
(1, 2, 'approved', 'Dokumentasi lengkap dan sesuai prosedur. Good job!', '2026-01-28 15:30:00'),
(2, 2, 'approved', 'Triase dilakukan dengan baik sesuai protokol.', '2026-01-28 22:45:00'),
(3, 3, 'approved', 'Perawatan ICU terdokumentasi dengan baik.', '2026-01-28 16:00:00'),
(7, 2, 'rejected', 'Deskripsi terlalu singkat. Mohon tambahkan detail tindakan observasi yang dilakukan.', '2026-01-28 08:30:00');

-- Insert sample notifications
INSERT INTO notifications (user_id, title, message, type, is_read) VALUES
(5, 'Logbook Disetujui', 'Logbook Anda tanggal 28 Jan 2026 untuk "Perawatan Pasien Trauma" telah disetujui oleh Dr. Budi Santoso.', 'success', 1),
(6, 'Logbook Ditolak', 'Logbook Anda tanggal 27 Jan 2026 untuk "Observasi Pasien" ditolak. Catatan: Deskripsi terlalu singkat. Mohon tambahkan detail tindakan observasi yang dilakukan.', 'warning', 0),
(5, 'Reminder', 'Jangan lupa mengisi logbook untuk shift hari ini!', 'info', 0);

-- Insert sample QR attendance
INSERT INTO qr_attendance (user_id, shift_id, attendance_date, qr_code, scanned_at) VALUES
(5, 1, '2026-01-29', 'QR-20260129-001-5', '2026-01-29 07:05:00'),
(6, 2, '2026-01-28', 'QR-20260128-002-6', '2026-01-28 14:02:00'),
(7, 1, '2026-01-29', 'QR-20260129-001-7', '2026-01-29 07:03:00');

-- Insert sample activity logs
INSERT INTO activity_logs (user_id, action, table_name, record_id, description, ip_address) VALUES
(1, 'CREATE', 'users', 9, 'Membuat user baru: Ns. Gita Savitri', '127.0.0.1'),
(5, 'CREATE', 'logbooks', 1, 'Membuat logbook: Perawatan Pasien Trauma', '127.0.0.1'),
(2, 'UPDATE', 'logbooks', 1, 'Menyetujui logbook ID 1', '127.0.0.1'),
(1, 'CREATE', 'units', 8, 'Membuat unit baru: Farmasi', '127.0.0.1');
