@echo off
title LogiScan Pro - Laravel 12 Development Server
echo ======================================================================
echo   LOGISCAN PRO - SISTEM SCAN BARCODE DAN GUDANG LOGISTIK (LARAVEL 12)
echo ======================================================================
echo.
echo Menjalankan server aplikasi di: http://127.0.0.1:8000
echo Tekan Ctrl+C untuk menghentikan server.
echo.
"C:\xampp\php\php.exe" artisan serve --host=127.0.0.1 --port=8000
pause
