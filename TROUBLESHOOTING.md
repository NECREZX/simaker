# 🔧 Troubleshooting Login Issues - SIMAKER

## ❌ Masalah: "Username/Password Salah"

Jika Anda mendapat error "username atau password salah" padahal sudah menggunakan kredensial yang benar, ikuti langkah-langkah berikut:

---

## 🔍 Langkah 1: Test Database

Buka browser dan akses:
```
http://localhost/simaker/test-db.php
```

Script ini akan mengecek:
- ✅ Koneksi database
- ✅ Tabel yang ada
- ✅ Daftar user
- ✅ Password verification

### Hasil yang Benar:
- Database connected ✅
- Semua tabel ada (users, roles, units, dll) ✅
- Ada minimal 9 users ✅
- Password verification SUCCESS ✅

### Jika Ada Error:
Lanjut ke Langkah 2

---

## 🗄️ Langkah 2: Import Database (Otomatis)

### Metode A: Menggunakan Batch File (MUDAH)

1. Pastikan XAMPP MySQL sudah running
2. Double-click file: `setup-database.bat`
3. Tunggu proses selesai
4. Test lagi di `test-db.php`

### Metode B: Manual via phpMyAdmin

1. Buka phpMyAdmin: `http://localhost/phpmyadmin`
2. Klik "New" → Buat database nama: `simaker`
3. Pilih database `simaker`
4. Klik tab "Import"
5. Upload file: `c:\xampp\htdocs\simaker\database\schema.sql`
6. Klik "Go"
7. Ulangi untuk file: `c:\xampp\htdocs\simaker\database\seeds.sql`

### Metode C: Manual via Command Line

```bash
# Buka Command Prompt
cd c:\xampp\htdocs\simaker

# Create database
c:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS simaker;"

# Import schema
c:\xampp\mysql\bin\mysql.exe -u root simaker < database\schema.sql

# Import seeds
c:\xampp\mysql\bin\mysql.exe -u root simaker < database\seeds.sql
```

---

## 🔐 Langkah 3: Verifikasi Password

Setelah import database, buka lagi:
```
http://localhost/simaker/test-db.php
```

Pastikan di bagian "Password Test" muncul:
```
✅ Password verification SUCCESS!
```

Jika masih FAILED, ada masalah dengan password hash di database.

---

## 🆘 Langkah 4: Reset Password Manual (Jika Perlu)

Jika test-db.php menunjukkan password FAILED, reset manual:

1. Buka phpMyAdmin
2. Pilih database `simaker`
3. Buka tabel `users`
4. Edit user `admin`
5. Ganti kolom `password` dengan hash ini:

```
$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
```

6. Save
7. Coba login lagi

**ATAU** jalankan SQL query ini:

```sql
UPDATE users 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE username = 'admin';
```

---

## ✅ Langkah 5: Test Login

Setelah database berhasil di-setup:

1. Buka: `http://localhost/simaker/`
2. Klik "Login"
3. Username: `admin`
4. Password: `password123`
5. Submit

Seharusnya berhasil masuk ke Dashboard Admin!

---

## 🔑 Kredensial Login yang Benar

| Username | Password | Role |
|----------|----------|------|
| admin | password123 | Admin |
| medis.andi | password123 | Tenaga Medis |
| medis.dewi | password123 | Tenaga Medis |
| medis.eka | password123 | Tenaga Medis |
| medis.fajar | password123 | Tenaga Medis |
| medis.gita | password123 | Tenaga Medis |
| supervisor.igd | password123 | Supervisor (IGD) |
| supervisor.icu | password123 | Supervisor (ICU) |
| supervisor.rinap | password123 | Supervisor (Rinap) |

**⚠️ PENTING:** Semua password adalah `password123` (huruf kecil, no space)

---

## 🐛 Masalah Lain?

### Error: "Connection failed"
- Pastikan MySQL di XAMPP sudah running
- Cek file `config/config.php` untuk DB credentials

### Error: "Table doesn't exist"
- Database belum di-import
- Jalankan `setup-database.bat`

### Error: "Call to undefined function"
- Ada file include yang belum ter-load
- Pastikan semua file di folder `includes/` ada

### Login berhasil tapi redirect ke landing lagi
- Sudah diperbaiki di `login.php`
- Clear browser cache dan cookies

---

## 📞 Quick Links

- **Test Database**: http://localhost/simaker/test-db.php
- **Login Page**: http://localhost/simaker/login-page.php
- **Landing Page**: http://localhost/simaker/
- **phpMyAdmin**: http://localhost/phpmyadmin

---

## ✨ Tips

- Gunakan browser Incognito/Private mode untuk testing
- Clear browser cache jika ada masalah redirect
- Cek Console (F12) untuk error JavaScript
- Cek Network tab untuk error HTTP

---

**Semoga berhasil! 🚀**
