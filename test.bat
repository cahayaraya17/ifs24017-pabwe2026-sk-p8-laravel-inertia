@echo off
REM Menjalankan seluruh pengujian + laporan coverage (target 100% pada app/)
REM XDEBUG_MODE=coverage wajib agar coverage terukur
set XDEBUG_MODE=coverage
php artisan config:clear
php artisan test --coverage --min=100 > coverage.txt 2>&1
type coverage.txt
echo.
echo Selesai. Ringkasan tersimpan di coverage.txt
