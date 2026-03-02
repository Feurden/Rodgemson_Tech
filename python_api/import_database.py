#!/usr/bin/env python3
"""Direct SQL import using mysql executable."""

import subprocess
import sys
from pathlib import Path

SCRIPT_DIR = Path(__file__).parent.parent
SQL_FILE = SCRIPT_DIR / "rodgemson_database.sql"

# Read and modify SQL to add IF NOT EXISTS and IF EXISTS for dropping
with open(SQL_FILE, 'r', encoding='utf-8') as f:
    sql = f.read()

# Modify CREATE TABLE to CREATE TABLE IF NOT EXISTS
modified_sql = sql.replace('CREATE TABLE `', 'CREATE TABLE IF NOT EXISTS `')

# Write to temp file
temp_sql = SCRIPT_DIR / "temp_import.sql"
with open(temp_sql, 'w', encoding='utf-8') as f:
    f.write(modified_sql)

print(f"📁 Created temporary SQL: {temp_sql}")
print(f"📝 Modified: {len(modified_sql)} bytes")

# Run mysql import
try:
    result = subprocess.run(
        [
            'C:\\xampp\\mysql\\bin\\mysql.exe',
            '-u', 'root',
            'rodgemson_database'
        ],
        stdin=open(temp_sql, 'r'),
        capture_output=True,
        text=True,
        timeout=30
    )
    
    if result.returncode == 0:
        print("\n✅ Database import successful!")
    else:
        print(f"\n⚠️  Import completed with warnings:")
        if result.stderr:
            print(result.stderr)
    
    # Verify tables
    result = subprocess.run(
        ['C:\\xampp\\mysql\\bin\\mysql.exe', '-u', 'root', 'rodgemson_database', '-e', 'SHOW TABLES;'],
        capture_output=True,
        text=True,
        timeout=10
    )
    
    if result.returncode == 0:
        print("\n📋 Tables in rodgemson_database:")
        print(result.stdout)
    
    # Clean up temp file
    temp_sql.unlink()
    print("🧹 Cleaned up temporary file")
    
except Exception as e:
    print(f"❌ Error: {e}")
    sys.exit(1)
