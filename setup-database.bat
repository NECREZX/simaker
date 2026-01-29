@echo off
echo ========================================
echo SIMAKER Database Setup
echo ========================================
echo.

REM Change to project directory
cd /d "c:\xampp\htdocs\simaker"

echo [1/4] Checking MySQL service...
net start | find /i "mysql" > nul
if errorlevel 1 (
    echo ERROR: MySQL is not running!
    echo Please start MySQL from XAMPP Control Panel
    pause
    exit /b 1
)
echo OK: MySQL is running
echo.

echo [2/4] Creating database...
"c:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS simaker;"
if errorlevel 1 (
    echo ERROR: Failed to create database
    pause
    exit /b 1
)
echo OK: Database created/exists
echo.

echo [3/4] Importing schema...
"c:\xampp\mysql\bin\mysql.exe" -u root simaker < "database\schema.sql"
if errorlevel 1 (
    echo ERROR: Failed to import schema
    pause
    exit /b 1
)
echo OK: Schema imported
echo.

echo [4/4] Importing seed data...
"c:\xampp\mysql\bin\mysql.exe" -u root simaker < "database\seeds.sql"
if errorlevel 1 (
    echo ERROR: Failed to import seed data
    pause
    exit /b 1
)
echo OK: Seed data imported
echo.

echo ========================================
echo Database setup completed successfully!
echo ========================================
echo.
echo You can now login with:
echo Username: admin
echo Password: password123
echo.
echo Test database: http://localhost/simaker/test-db.php
echo Login page: http://localhost/simaker/login-page.php
echo.
pause
