#!/usr/bin/env python3
"""
export_feedback_data.py
-----------------------
Analyzes and exports repair diagnosis feedback for model improvement.

Usage:
    python export_feedback_data.py
    
    Make sure to update DATABASE_CONFIG with your actual credentials
"""

from pathlib import Path
import pandas as pd
import mysql.connector
from datetime import datetime
import json

# Database configuration - UPDATE THESE
DATABASE_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'cakephp_db'  # Change to your actual database name
}

def get_feedback_data():
    """Fetch all diagnosis feedback from database"""
    try:
        conn = mysql.connector.connect(**DATABASE_CONFIG)
        query = """
            SELECT 
                job_id,
                device,
                ai_diagnosis,
                actual_diagnosis,
                actual_root_cause,
                parts_replaced,
                diagnosis_correct,
                technician_notes,
                completed_at
            FROM repair_diagnoses
            WHERE diagnosis_correct IS NOT NULL
            ORDER BY completed_at DESC
        """
        df = pd.read_sql(query, con=conn)
        conn.close()
        return df
    except Exception as e:
        print(f"Error connecting to database: {e}")
        return None

def calculate_metrics(df):
    """Calculate accuracy and performance metrics"""
    if df is None or len(df) == 0:
        print("❌ No feedback data available yet.")
        return None
    
    total = len(df)
    correct = (df['diagnosis_correct'] == True).sum()
    incorrect = (df['diagnosis_correct'] == False).sum()
    accuracy = (correct / total * 100) if total > 0 else 0
    
    return {
        'total_diagnoses': total,
        'correct': correct,
        'incorrect': incorrect,
        'accuracy_percent': round(accuracy, 2),
    }

def show_summary():
    """Display summary of feedback data"""
    df = get_feedback_data()
    
    if df is None:
        return
    
    metrics = calculate_metrics(df)
    
    print("\n" + "="*60)
    print("📊 DIAGNOSIS FEEDBACK ANALYSIS")
    print("="*60)
    
    if metrics['total_diagnoses'] == 0:
        print("❌ No feedback collected yet. Start collecting data!")
        print("\nWorkflow:")
        print("  1. Create repair job")
        print("  2. Run AI diagnosis")
        print("  3. Complete repair")
        print("  4. Click 'Feedback' button")
        print("  5. Confirm if diagnosis was correct")
        return
    
    print(f"\n✓ Total Diagnoses: {metrics['total_diagnoses']}")
    print(f"✓ Correct:        {metrics['correct']} ({metrics['accuracy_percent']}%)")
    print(f"✗ Incorrect:      {metrics['incorrect']}")
    
    threshold = 50
    if metrics['total_diagnoses'] < threshold:
        needed = threshold - metrics['total_diagnoses']
        print(f"\n⏳ Collect {needed} more diagnoses before model retraining")
    else:
        print(f"\n✅ Ready for model retraining! (Have {metrics['total_diagnoses']} records)")
    
    # Show misclassifications
    mistakes = df[df['diagnosis_correct'] == False]
    if len(mistakes) > 0:
        print("\n" + "-"*60)
        print("❌ MOST COMMON MISCLASSIFICATIONS")
        print("-"*60)
        
        wrong_diagnoses = mistakes['ai_diagnosis'].value_counts()
        for diagnosis, count in wrong_diagnoses.head(5).items():
            pct = (count / len(mistakes) * 100)
            print(f"  • {diagnosis}: {count} times ({pct:.1f}%)")
        
        # Show a few examples
        print("\n📋 Recent Examples of Wrong Diagnoses:")
        for idx, row in mistakes.head(3).iterrows():
            print(f"\n  Job ID: {row['job_id']}")
            print(f"  AI said:    {row['ai_diagnosis']}")
            print(f"  Actually:   {row['actual_diagnosis']}")
            if row['actual_root_cause']:
                print(f"  Root cause: {row['actual_root_cause'][:80]}...")
    
    print("\n" + "="*60 + "\n")

def export_mistakes():
    """Export misclassifications to CSV for review"""
    df = get_feedback_data()
    
    if df is None:
        return
    
    mistakes = df[df['diagnosis_correct'] == False]
    
    if len(mistakes) == 0:
        print("✓ No misclassifications to export!")
        return
    
    # Clean up for export
    export_df = mistakes[[
        'job_id', 
        'device', 
        'ai_diagnosis', 
        'actual_diagnosis', 
        'actual_root_cause',
        'parts_replaced',
        'technician_notes',
        'completed_at'
    ]].copy()
    
    filename = f"misclassified_repairs_{datetime.now().strftime('%Y%m%d')}.csv"
    export_df.to_csv(filename, index=False)
    print(f"✓ Exported {len(mistakes)} misclassifications to '{filename}'")

def export_training_dataset():
    """Export all feedback as training dataset for model retraining"""
    df = get_feedback_data()
    
    if df is None or len(df) < 50:
        print("⏳ Need at least 50 feedback records before exporting training data")
        return
    
    # Prepare dataset
    export_df = df[[
        'device',
        'ai_diagnosis',
        'actual_diagnosis',
        'diagnosis_correct',
        'completed_at'
    ]].copy()
    
    filename = f"real_world_training_data_{datetime.now().strftime('%Y%m%d')}.csv"
    export_df.to_csv(filename, index=False)
    print(f"✓ Exported {len(df)} real-world records to '{filename}'")
    print(f"  Use this file to retrain the model with actual repair data")

if __name__ == '__main__':
    import sys
    
    print("\n🔧 Cellphone Repair AI - Feedback Analysis Tool\n")
    
    if len(sys.argv) > 1 and sys.argv[1] == '--export-mistakes':
        export_mistakes()
    elif len(sys.argv) > 1 and sys.argv[1] == '--export-training':
        export_training_dataset()
    else:
        show_summary()
        
        df = get_feedback_data()
        if df is not None and len(df) > 0:
            print("Usage:")
            print(f"  python export_feedback_data.py              # Show this summary")
            print(f"  python export_feedback_data.py --export-mistakes   # Export wrong diagnoses")
            if len(df) >= 50:
                print(f"  python export_feedback_data.py --export-training  # Export for model retraining")
            print()
