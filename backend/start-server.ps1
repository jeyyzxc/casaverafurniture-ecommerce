# Casa Vera Furniture Backend Server Startup Script
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Starting Casa Vera Furniture Backend" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if PHP is available
try {
    $phpVersion = php --version 2>&1
    Write-Host "PHP Version: $($phpVersion -split "`n" | Select-Object -First 1)" -ForegroundColor Green
} catch {
    Write-Host "ERROR: PHP is not installed or not in PATH" -ForegroundColor Red
    Write-Host "Please install PHP and add it to your system PATH" -ForegroundColor Red
    exit 1
}

# Change to backend directory
Set-Location $PSScriptRoot

Write-Host "Checking database connection..." -ForegroundColor Yellow
php verify-backend.php
if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "ERROR: Database connection failed!" -ForegroundColor Red
    Write-Host "Please check your database configuration in .env" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Starting Laravel Development Server" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Server will be available at: http://localhost:8000" -ForegroundColor Green
Write-Host "Press Ctrl+C to stop the server" -ForegroundColor Yellow
Write-Host ""

php artisan serve
