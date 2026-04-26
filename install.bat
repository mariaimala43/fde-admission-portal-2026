@echo off
setlocal enabledelayedexpansion
title FDE Admission Portal 2026 — Installer

echo.
echo  ================================================================
echo   FDE Admission Portal 2026 — One-Click Installer
echo  ================================================================
echo.

:: ── 1. Check PHP ────────────────────────────────────────────────────
php --version >nul 2>&1
if errorlevel 1 (
    echo  [ERROR] PHP is not found in PATH.
    echo          Make sure Laragon is running and PHP is in your PATH.
    echo          Typical path: C:\laragon\bin\php\phpX.X.X
    echo.
    pause
    exit /b 1
)
echo  [OK] PHP found.

:: ── 2. Check Composer ───────────────────────────────────────────────
composer --version >nul 2>&1
if errorlevel 1 (
    echo  [ERROR] Composer is not found in PATH.
    echo          Download from https://getcomposer.org/download/
    echo.
    pause
    exit /b 1
)
echo  [OK] Composer found.

:: ── 3. Check Node.js ────────────────────────────────────────────────
node --version >nul 2>&1
if errorlevel 1 (
    echo  [ERROR] Node.js is not found in PATH.
    echo          Download from https://nodejs.org/
    echo.
    pause
    exit /b 1
)
echo  [OK] Node.js found.

echo.
echo  ── Step 1 of 4: Installing PHP dependencies ──────────────────
echo.
composer install --no-dev --optimize-autoloader
if errorlevel 1 (
    echo.
    echo  [ERROR] composer install failed. Check the error above.
    pause
    exit /b 1
)

echo.
echo  ── Step 2 of 4: Running setup wizard ─────────────────────────
echo.
php artisan app:install
if errorlevel 1 (
    echo.
    echo  [ERROR] Setup wizard failed. Check the error above.
    pause
    exit /b 1
)

echo.
echo  ── Step 3 of 4: Installing frontend dependencies ─────────────
echo.
call npm install
if errorlevel 1 (
    echo.
    echo  [ERROR] npm install failed. Check the error above.
    pause
    exit /b 1
)

echo.
echo  ── Step 4 of 4: Building frontend assets ─────────────────────
echo.
call npm run build
if errorlevel 1 (
    echo.
    echo  [ERROR] npm run build failed. Check the error above.
    pause
    exit /b 1
)

echo.
echo  ================================================================
echo   Installation complete!
echo   Open your browser at the URL you configured + /login
echo  ================================================================
echo.
pause
