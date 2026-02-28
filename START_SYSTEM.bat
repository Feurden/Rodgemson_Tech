@echo off
title Rodgemson Repair Shop - FULL AUTO START

echo =====================================
echo   Starting Rodgemson Repair System
echo =====================================

REM -----------------------------
REM START XAMPP (Apache + MySQL)
REM -----------------------------
echo.
echo Starting XAMPP Services...

cd /d C:\xampp
xampp_start.exe

timeout /t 5 >nul

REM -----------------------------
REM START FASTAPI
REM -----------------------------
echo.
echo Starting FastAPI Server...

start cmd /k "cd /d C:\xampp\htdocs\Rodgemson Cellphone Repair Shop\python_api && py -m uvicorn main:app --reload"

timeout /t 3 >nul

REM -----------------------------
REM START CAKEPHP
REM -----------------------------
echo.
echo Starting CakePHP Server...

start cmd /k "cd /d C:\xampp\htdocs\Rodgemson Cellphone Repair Shop\UI && bin\cake server -p 8765"

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
