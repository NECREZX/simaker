<img width="1910" height="905" alt="Screenshot 2026-01-29 114311" src="https://github.com/user-attachments/assets/d7b5f6fa-78a2-4167-8649-de8a563a37e7" />
<img width="1910" height="915" alt="Screenshot 2026-01-29 110350" src="https://github.com/user-attachments/assets/b740475a-edee-4d76-b9b7-ff466e050dc6" />
<img width="1911" height="917" alt="Screenshot 2026-01-29 120344" src="https://github.com/user-attachments/assets/89f54cff-a6a4-40a8-b243-e404823febd0" />
<img width="1919" height="917" alt="Screenshot 2026-01-29 120404" src="https://github.com/user-attachments/assets/4742d9d4-f72c-45dd-a650-47242a6812f0" />
<img width="1912" height="916" alt="Screenshot 2026-01-29 110428" src="https://github.com/user-attachments/assets/f1a7dc39-5685-4cab-95cc-64b60d2c024d" />
<img width="1897" height="929" alt="Screenshot 2026-01-29 121843" src="https://github.com/user-attachments/assets/e5088ff6-b97d-4962-a889-75a091fa12c3" />

# SIMAKER - Sistem Informasi Monitoring Aktivitas Kerja

Sistem informasi logbook rumah sakit berbasis web untuk mencatat aktiv itas harian tenaga medis secara digital dengan fitur verifikasi dan laporan terpusat.

## 🚀 Teknologi

- **Backend**: PHP Native (tanpa framework)
- **Database**: MySQL
- **Frontend**: Bootstrap 5
- **Design**: Emerald Green Theme, Mobile Responsive

## ✨ Fitur Utama

### Role Pengguna
1. **Admin**: Manage pengguna, unit, shift, dan monitoring sistem
2. **Tenaga Medis**: Input dan manage logbook harian
3. **Supervisor**: Verifikasi logbook dan generate laporan

### Fitur Lengkap
- ✅ Authentication & Role-based Access Control
- ✅ Dashboard dengan statistik
- ✅ CRUD Logbook dengan upload bukti
- ✅ Workflow verifikasi logbook
- ✅ Notifikasi real-time
- ✅ QR Code untuk absensi shift
- ✅ Laporan harian & bulanan
- ✅ Export PDF & Excel
- ✅ Grafik statistik aktivitas
- ✅ Dark Mode
- ✅ Mobile Friendly

## 📋 Persyaratan Sistem

- PHP >= 7.4
- MySQL >= 5.7
- XAMPP/WAMP/LAMP/MAMP
- Browser modern (Chrome, Firefox, Edge, Safari)

## 🔧 Instalasi

### 1. Clone atau Download Project

```bash
# Tempatkan di folder htdocs (untuk XAMPP)
cd c:/xampp/htdocs/simaker
```

### 2. Import Database

```bash
# Buat database
mysql -u root -p
CREATE DATABASE simaker;
exit;

# Import schema
mysql -u root -p simaker < database/schema.sql

# Import data sample
mysql -u root -p simaker < database/seeds.sql
```

### 3. Konfigurasi Database

Edit file `config/config.php` jika perlu mengubah kredensial database:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'simaker');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 4. Set Permissions

Pastikan folder `uploads/` memiliki permission write:

```bash
chmod -R 755 uploads/
```

### 5. Akses Aplikasi

Buka browser dan akses:
```
http://localhost/simaker
```

## 👤 Demo Akun

### Admin
- Username: `admin`
- Password: `password123`

### Tenaga Medis
- Username: `medis.andi`
- Password: `password123`

### Supervisor
- Username: `supervisor.igd`
- Password: `password123`

## 📁 Struktur Folder

```
simaker/
├── assets/              # CSS, JS, Images
├── config/              # Konfigurasi aplikasi
├── includes/            # Helper functions
├── layouts/             # Template layouts
├── modules/             # Modul aplikasi
│   ├── admin/          # Admin module
│   ├── medis/          # Medical staff module
│   └── supervisor/     # Supervisor module
├── uploads/             # Uploaded files
├── database/            # SQL files
├── api/                 # AJAX endpoints
├── index.php            # Login page
├── login.php            # Login handler
└── logout.php           # Logout handler
```

## 🔒 Keamanan

- Password hashing menggunakan bcrypt
- CSRF protection pada semua form
- SQL injection prevention dengan prepared statements
- XSS protection dengan input sanitization
- File upload validation
- Session timeout (30 menit)
- Activity logging

## 📱 Responsive Design

Aplikasi dioptimalkan untuk berbagai ukuran layar:
- Desktop (> 1200px)
- Tablet (768px - 1199px)
- Mobile (< 768px)

## 🎨 Tema

### Light Mode
- Primary Color: #10b981 (Emerald Green)
- Modern and clean design
- Easy to read

### Dark Mode
- Toggle available di navbar
- Emerald accent maintained
- Eye-friendly for night use
- Preference saved in localStorage

## 📊 Fitur Tambahan

### QR Code Attendance
- Generate QR code untuk setiap shift
- Scan QR menggunakan kamera
- Record kehadiran otomatis

### Notifikasi
- Real-time notification untuk perubahan status logbook
- Notification bell dengan counter
- Mark as read functionality

### Laporan
- Daily dan monthly reports
- Filter berdasarkan unit, tanggal, staff
- Export ke PDF (menggunakan TCPDF/mPDF)
- Export ke Excel (menggunakan PHPSpreadsheet)

### Charts & Analytics
- Bar chart: logbook per hari
- Line chart: trend jumlah pasien
- Pie chart: distribusi aktivitas
- Menggunakan Chart.js

## 🐛 Troubleshooting

### Database Connection Error
- Pastikan MySQL sudah running
- Cek kredensial database di `config/config.php`
- Pastikan database `simaker` sudah dibuat

### Upload Error
- Pastikan folder `uploads/` exists dan writable
- Cek `upload_max_filesize` di php.ini (minimum 5MB)

### Session Timeout
- Default timeout 30 menit
- Ubah di `config/config.php`: `SESSION_TIMEOUT`

## 📝 Development Notes

### Menambah User Baru via PHP
```php
$password = hashPassword('password_baru');
insert('users', [
    'username' => 'username_baru',
    'email' => 'email@example.com',
    'password' => $password,
    'role_id' => 2, // 1=Admin, 2=Tenaga Medis, 3=Supervisor
    'unit_id' => 1,
    'full_name' => 'Nama Lengkap',
    'phone' => '081234567890',
    'is_active' => 1
]);
```

### Logging Aktivitas
```php
logActivity(
    $userId,
    'CREATE', // CREATE, UPDATE, DELETE, LOGIN, LOGOUT
    'tablename',
    $recordId,
    'Deskripsi aktivitas'
);
```

### Membuat Notifikasi
```php
createNotification(
    $userId,
    'Judul Notifikasi',
    'Pesan notifikasi',
    'success' // info, success, warning, error
);
```

## 📞 Support

Jika ada pertanyaan atau issue, silakan hubungi:
- Email: admin@simaker.com
- GitHub Issues: (jika ada repository)

## 📄 License

Copyright © 2026 SIMAKER. All rights reserved.

---

**Dibuat dengan ❤️ menggunakan PHP Native & MySQL**
