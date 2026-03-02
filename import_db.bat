@echo off
cd /d "c:\xampp\htdocs\Rodgemson Cellphone Repair Shop"
"C:\xampp\mysql\bin\mysql.exe" -u root rodgemson_database < rodgemson_database.sql
if errorlevel 1 (
    echo Database import failed!
    pause
) else (
    echo Database imported successfully!
    pause
)
