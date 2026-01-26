@echo off
echo ========================================
echo Starting Casa Vera Furniture Backend
echo ========================================
echo.

REM Check if PHP is available
php --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: PHP is not installed or not in PATH
    echo Please install PHP and add it to your system PATH
    pause
    exit /b 1
)

REM Change to backend directory
cd /d "%~dp0"

echo Checking database connection...
php verify-backend.php
if errorlevel 1 (
    echo.
    echo ERROR: Database connection failed!
    echo Please check your database configuration in .env
    pause
    exit /b 1
)

echo.
echo ========================================
echo Starting Laravel Development Server
echo ========================================
echo Server will be available at: http://localhost:8000
echo Press Ctrl+C to stop the server
echo.

php artisan serve
