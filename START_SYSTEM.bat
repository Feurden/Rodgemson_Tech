@echo off
title Rodgemson Repair Shop - FULL AUTO START

echo =====================================
echo   Starting Rodgemson Repair System
echo =====================================

REM -----------------------------
REM START XAMPP (Apache + MySQL)
REM -----------------------------
echo.
echo Opening XAMPP Control Panel...
start "" "C:\xampp\xampp-control.exe"

timeout /t 8 >nul

REM -----------------------------
REM START FASTAPI
REM -----------------------------
echo.
echo Starting FastAPI Server...

start /b cmd /c "cd /d "C:\xampp\htdocs\Rodgemson_Tech\python_api" && py -m uvicorn main:app --reload > "C:\xampp\htdocs\Rodgemson_Tech\python_api\fastapi.log" 2>&1"

timeout /t 3 >nul

REM -----------------------------
REM START CAKEPHP
REM -----------------------------
echo.
echo Starting CakePHP Server...

start /b cmd /c "cd /d "C:\xampp\htdocs\Rodgemson_Tech\UI" && php bin\cake.php server -p 8765 > "C:\xampp\htdocs\Rodgemson_Tech\UI\cakephp.log" 2>&1"

timeout /t 5 >nul

REM -----------------------------
REM OPEN BROWSER
REM -----------------------------
echo.
echo Opening Dashboard...

start http://localhost:8765/dashboard/repairs

echo.
echo =====================================
echo System Started Successfully!
echo =====================================

pause