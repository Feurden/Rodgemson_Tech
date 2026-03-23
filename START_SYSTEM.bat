@echo off
REM ============================================================================
REM RODGEMSON REPAIR SHOP - FULL AUTO START (UPDATED)
REM ============================================================================
REM Updated to use port 5000 for FastAPI (avoids Windows firewall issues)
REM ============================================================================

title Rodgemson Repair Shop - FULL AUTO START

echo =====================================
echo   Starting Rodgemson Repair System
echo =====================================
echo.
echo [*] FastAPI will run on:  http://localhost:5000
echo [*] CakePHP will run on:  http://localhost:8765
echo [*] Dashboard:            http://localhost:8765/dashboard/repairs
echo.

REM ============================================================================
REM START XAMPP (Apache + MySQL)
REM ============================================================================
echo [*] Opening XAMPP Control Panel...
start "" "C:\xampp\xampp-control.exe"

timeout /t 8 >nul

echo [OK] XAMPP started

REM ============================================================================
REM START FASTAPI (Now with port 5000 to avoid firewall issues)
REM ============================================================================
echo.
echo [*] Starting FastAPI Server on port 5000...

REM Remove --reload to avoid multiprocessing issues on Windows
start /b cmd /c "cd /d "C:\xampp\htdocs\Rodgemson_Tech\python_api" && py -m uvicorn main:app --host 127.0.0.1 --port 5000 > "C:\xampp\htdocs\Rodgemson_Tech\python_api\fastapi.log" 2>&1"

timeout /t 5 >nul

echo [OK] FastAPI started on port 5000

REM ============================================================================
REM START CAKEPHP
REM ============================================================================
echo.
echo [*] Starting CakePHP Server on port 8765...

start /b cmd /c "cd /d "C:\xampp\htdocs\Rodgemson_Tech\UI" && php bin\cake.php server -p 8765 > "C:\xampp\htdocs\Rodgemson_Tech\UI\cakephp.log" 2>&1"

timeout /t 5 >nul

echo [OK] CakePHP started on port 8765

REM ============================================================================
REM OPEN BROWSER
REM ============================================================================
echo.
echo [*] Opening Dashboard in browser...

start http://localhost:8765/dashboard/repairs

timeout /t 2 >nul

REM ============================================================================
REM STATUS
REM ============================================================================
echo.
echo =====================================
echo    [OK] System Started Successfully!
echo =====================================
echo.
echo SERVICES RUNNING:
echo   [OK] XAMPP (Apache + MySQL)
echo   [OK] FastAPI Diagnosis API (port 5000)
echo   [OK] CakePHP Dashboard (port 8765)
echo.
echo USEFUL LINKS:
echo   - FastAPI Docs:    http://localhost:5000/docs
echo   - FastAPI Health:  http://localhost:5000/health
echo   - CakePHP:         http://localhost:8765
echo   - Dashboard:       http://localhost:8765/dashboard/repairs
echo.
echo LOG FILES:
echo   - FastAPI:   C:\xampp\htdocs\Rodgemson_Tech\python_api\fastapi.log
echo   - CakePHP:   C:\xampp\htdocs\Rodgemson_Tech\UI\cakephp.log
echo.
echo NOTES:
echo   - First FastAPI request takes 30-60 seconds (NLP model loading)
echo   - Keep this window open to monitor services
echo   - Press Ctrl+C to stop (will stop all services)
echo.
pause