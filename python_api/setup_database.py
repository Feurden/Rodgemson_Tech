#!/usr/bin/env python3
"""Setup the rodgemson_database by importing SQL schema."""

import mysql.connector
from pathlib import Path

# Database connection parameters
DB_CONFIG = {
    "host": "localhost",
    "user": "root",
    "password": "",  # XAMPP default has no password
    "database": "rodgemson_database"
}

# Path to SQL file
SCRIPT_DIR = Path(__file__).parent.parent
SQL_FILE = SCRIPT_DIR / "rodgemson_database.sql"

def main():
    print(f"📁 SQL File: {SQL_FILE}")
    print(f"   Exists: {SQL_FILE.exists()}")
    
    if not SQL_FILE.exists():
        print("❌ SQL file not found!")
        return False
    
    try:
        # Read SQL file
        print("\n📖 Reading SQL file...")
        with open(SQL_FILE, 'r', encoding='utf-8') as f:
            sql_content = f.read()
        
        print(f"   ✓ Loaded {len(sql_content)} bytes")
        
        # Connect to MySQL
        print("\n🔗 Connecting to database...")
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        print("   ✓ Connected to rodgemson_database")
        
        # Drop existing tables to start fresh (optional - comment out to preserve data)
        print("\n🗑️  Dropping existing tables...")
        tables_to_drop = [
            'repair_parts', 'repair_diagnoses', 'orders', 'notifications',
            'users', 'devices', 'parts', 'customers'  # Order matters (FK constraints)
        ]
        for table in tables_to_drop:
            try:
                cursor.execute(f"DROP TABLE IF EXISTS `{table}`;")
                print(f"   ✓ Dropped {table}")
            except Exception as e:
                print(f"   ⚠️  Could not drop {table}: {e}")
        
        conn.commit()
        
        # Execute SQL statements
        print("\n⚙️  Creating tables...")
        statements = sql_content.split(';')
        for i, statement in enumerate(statements, 1):
            statement = statement.strip()
            if statement and not statement.startswith('--') and 'SET' not in statement and '!' not in statement:
                try:
                    cursor.execute(statement)
                    conn.commit()
                except mysql.connector.Error as e:
                    if "already exists" in str(e):
                        print(f"   ℹ️  Statement {i}: Table already exists (skipping)")
                    else:
                        print(f"   ⚠️  Statement {i}: {e}")
        
        # Verify tables
        print("\n✅ Verifying tables...")
        cursor.execute("SHOW TABLES;")
        tables = cursor.fetchall()
        for (table,) in tables:
            cursor.execute(f"SELECT COUNT(*) FROM `{table}`;")
            count = cursor.fetchone()[0]
            print(f"   ✓ {table}: {count} rows")
        
        cursor.close()
        conn.close()
        
        print("\n✅ Database setup complete!")
        return True
        
    except mysql.connector.Error as e:
        print(f"\n❌ Database error: {e}")
        return False
    except Exception as e:
        print(f"\n❌ Error: {e}")
        import traceback
        traceback.print_exc()
        return False

if __name__ == "__main__":
    success = main()
    exit(0 if success else 1)
