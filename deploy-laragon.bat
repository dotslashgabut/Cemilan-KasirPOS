@echo off
echo ========================================
echo  Deploy to Laragon WWW Root
echo ========================================
echo.

REM Check if dist folder exists, if not build first
if not exist "dist\" (
    echo [1/4] Building React Frontend...
    call npm run build
    if errorlevel 1 (
        echo.
        echo ❌ Build failed!
        pause
        exit /b 1
    )
) else (
    echo [1/4] Using existing build in dist folder...
)

echo.
echo [2/4] Copying Frontend files to Laragon WWW root...
xcopy /E /Y "dist\*" "C:\laragon\www\" >nul 2>&1
if errorlevel 1 (
    echo ❌ Failed to copy frontend files!
    echo Make sure C:\laragon\www\ is accessible
    pause
    exit /b 1
)
echo ✅ Frontend files copied to C:\laragon\www\

echo.
echo [3/4] Copying PHP Backend to Laragon WWW root...
xcopy /E /I /Y "php_server\*" "C:\laragon\www\php_server\" >nul 2>&1
if errorlevel 1 (
    echo ❌ Failed to copy backend files!
    pause
    exit /b 1
)
echo ✅ Backend files copied to C:\laragon\www\php_server\

echo.
echo [4/4] Copying root .htaccess...
copy /Y ".htaccess" "C:\laragon\www\.htaccess" >nul 2>&1
echo ✅ .htaccess copied to C:\laragon\www\.htaccess

echo.
echo ========================================
echo  ✅ Deployment Complete!
echo ========================================
echo.
echo Folder Structure:
echo   C:\laragon\www\
echo   ├── index.html
echo   ├── .htaccess
echo   ├── assets\
echo   └── php_server\
echo.
echo URLs:
echo   Frontend: http://localhost/
echo   Backend:  http://localhost/php_server/
echo.
echo Don't forget to:
echo  1. Start Laragon (Apache + MySQL)
echo  2. Import database if not done yet
echo  3. Check php_server/.env configuration
echo.
pause
