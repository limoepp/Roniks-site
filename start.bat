@echo off
chcp 65001 >nul
cd /d "%~dp0"
set PHP=C:\php\php.exe
if not exist "%PHP%" (
  echo Не найден %PHP%. Распакуйте PHP в C:\php
  pause
  exit /b 1
)
echo Сайт: http://localhost:8000   Админка: http://localhost:8000/admin
echo Чтобы остановить сервер, закройте это окно.
start "" http://localhost:8000
"%PHP%" -c "%~dp0php-local.ini" -S localhost:8000 router.php
pause
