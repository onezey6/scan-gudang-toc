@echo off
:: ==============================================================================
:: LOGISTIK.TOC - Auto Registrar Domain Lokal (logistik.toc)
:: ==============================================================================
TITLE Pemasangan Domain Lokal logistik.toc
color 0B
echo.
echo  ==============================================================
echo    MEMASANG DOMAIN LOKAL: http://logistik.toc
echo  ==============================================================
echo.
echo  Memeriksa hak akses Administrator...

net session >nul 2>&1
if %errorLevel% == 0 (
    goto :execute
) else (
    echo  Meminta izin Administrator (UAC)...
    powershell -Command "Start-Process '%~f0' -Verb RunAs"
    exit /b
)

:execute
echo  Menambahkan domain logistik.toc ke Windows hosts...
set HOSTS=%WINDIR%\System32\drivers\etc\hosts

findstr /I /C:"logistik.toc" "%HOSTS%" >nul
if %errorLevel% == 0 (
    echo  [OK] Domain logistik.toc sudah terdaftar sebelumnya di file hosts.
) else (
    echo. >> "%HOSTS%"
    echo # Domain Lokal Resmi LOGISTIK.TOC >> "%HOSTS%"
    echo 127.0.0.1 logistik.toc >> "%HOSTS%"
    echo 127.0.0.1 www.logistik.toc >> "%HOSTS%"
    echo  [OK] Berhasil menambahkan 127.0.0.1 logistik.toc ke file hosts!
)

echo  Membersihkan cache DNS Windows...
ipconfig /flushdns >nul 2>&1

echo.
echo  ==============================================================
echo    SUKSES! Domain http://logistik.toc siap digunakan!
echo  ==============================================================
echo.
timeout /t 3 >nul
exit /b
