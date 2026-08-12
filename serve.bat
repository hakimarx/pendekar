@echo off
title PENDEKAR - Server Launcher
echo ========================================================
echo PENDEKAR - Portal Integrasi Data Pesantren Jawa Timur
echo ========================================================
echo.
echo Starting Laravel Web Server on http://localhost:9000 ...
echo Press Ctrl+C to stop the server.
echo.

php artisan serve --port=9000 || php -S 127.0.0.1:9000 -t public
pause
