"""
fix_categories.py - Clean up confusing categories and improve model accuracy
Add this to your data processing pipeline
"""

import pandas as pd
import numpy as np

def clean_fault_categories(df):
    """
    Clean and consolidate fault categories for better model performance
    """
    print("="*60)
    print("🔧 CLEANING FAULT CATEGORIES")
    print("="*60)
    
    # Show original distribution
    print("\n📊 Original distribution:")
    print(df['fault'].value_counts())
    
    # Make a copy
    df_clean = df.copy()
    
    # 1️⃣ REMOVE "Other Issue" (only 2 samples - causes more harm than good)
    other_count = len(df_clean[df_clean['fault'] == 'Other Issue'])
    df_clean = df_clean[df_clean['fault'] != 'Other Issue']
    print(f"\n✅ Removed {other_count} 'Other Issue' samples")
    
    # 2️⃣ FIX DISPLAY CONFUSION - Merge Display Issue and Display IC Issue
    # Display IC Issue (56% F1) is too similar to Display Issue (92% F1)
    display_count = len(df_clean[df_clean['fault'].isin(['Display Issue', 'Display IC Issue'])])
    df_clean['fault'] = df_clean['fault'].replace({
        'Display IC Issue': 'Display Issue'
    })
    print(f"✅ Merged 'Display IC Issue' into 'Display Issue' ({display_count} total samples)")
    
    # 3️⃣ FIX TOUCH CONTROLLER - Often confused with Display
    # Touch Controller (60% F1) needs stronger separation
    touch_count = len(df_clean[df_clean['fault'] == 'Touch Controller Issue'])
    print(f"✅ Keeping 'Touch Controller Issue' separate ({touch_count} samples)")
    
    # 4️⃣ CHECK MAINBOARD - Ensure no mislabeled cameras/back glass
    # Let's see what descriptions are labeled as Mainboard
    mainboard_samples = df_clean[df_clean['fault'] == 'Mainboard Issue']
    print(f"\n🔍 Checking Mainboard Issue samples ({len(mainboard_samples)} total):")
    
    # Check for camera-related in Mainboard
    camera_in_mainboard = mainboard_samples[
        mainboard_samples['description'].str.contains('camera|rear cam|front cam', case=False, na=False)
    ]
    if len(camera_in_mainboard) > 0:
        print(f"⚠️ Found {len(camera_in_mainboard)} camera-related issues mislabeled as Mainboard")
        # Fix them
        df_clean.loc[camera_in_mainboard.index, 'fault'] = 'Camera Issue'
        print(f"✅ Fixed {len(camera_in_mainboard)} to 'Camera Issue'")
    
    # Check for back glass in Mainboard
    glass_in_mainboard = mainboard_samples[
        mainboard_samples['description'].str.contains('back glass|rear glass', case=False, na=False)
    ]
    if len(glass_in_mainboard) > 0:
        print(f"⚠️ Found {len(glass_in_mainboard)} back glass issues mislabeled as Mainboard")
        # Fix them
        df_clean.loc[glass_in_mainboard.index, 'fault'] = 'Back Glass Issue'
        print(f"✅ Fixed {len(glass_in_mainboard)} to 'Back Glass Issue'")
    
    # 5️⃣ COMBINE SIM-RELATED ISSUES
    sim_count = len(df_clean[df_clean['fault'].str.contains('SIM', case=False, na=False)])
    # Keep as is - they're performing well (91% F1)
    print(f"\n✅ SIM-related issues: {sim_count} samples (91% F1 - good)")
    
    # 6️⃣ SHOW FINAL DISTRIBUTION
    print("\n📊 Cleaned distribution:")
    print(df_clean['fault'].value_counts())
    
    # 7️⃣ CHECK CLASS BALANCE
    min_class = df_clean['fault'].value_counts().min()
    max_class = df_clean['fault'].value_counts().max()
    print(f"\n📈 Class balance:")
    print(f"   Smallest class: {min_class} samples")
    print(f"   Largest class: {max_class} samples")
    print(f"   Ratio: {max_class/min_class:.1f}:1")
    
    return df_clean

# Optional: Further simplification for better performance
def simplify_to_broad_categories(df):
    """
    Alternative: Group into broader categories for even better accuracy
    Use this if you want higher accuracy over specific diagnoses
    """
    df_broad = df.copy()
    
    category_map = {
        # Power Related
        'Battery Issue': 'Power System',
        'Charging IC Issue': 'Power System',
        'Charging Port Issue': 'Power System',
        'Power IC Issue': 'Power System',
        
        # Display Related
        'Display Issue': 'Display/Touch',
        'Touch Controller Issue': 'Display/Touch',
        
        # Audio Related
        'Speaker Issue': 'Audio System',
        'Microphone Issue': 'Audio System',
        
        # Connectivity
        'Antenna Issue': 'Connectivity',
        'Baseband Issue': 'Connectivity',
        'SIM IC Issue': 'Connectivity',
        'WiFi Issue': 'Connectivity',
        'Bluetooth Issue': 'Connectivity',
        
        # Mainboard
        'Mainboard Issue': 'Mainboard',
        
        # Software
        'Software/OS Issue': 'Software',
        
        # Physical
        'Back Glass Issue': 'Physical Damage',
        'Camera Issue': 'Camera System'
    }
    
    df_broad['fault'] = df_broad['fault'].map(category_map).fillna(df_broad['fault'])
    
    print("\n📊 Broad categories distribution:")
    print(df_broad['fault'].value_counts())
    
    return df_broad

# Main execution
if __name__ == "__main__":
    # Load your combined dataset
    df = pd.read_csv('combined_dataset.csv')
    
    # Apply cleaning
    df_cleaned = clean_fault_categories(df)
    
    # Save cleaned version
    df_cleaned.to_csv('combined_dataset_cleaned.csv', index=False)
    print("\n💾 Saved cleaned dataset to 'combined_dataset_cleaned.csv'")
    
    # Optional: Also create broad categories version
    df_broad = simplify_to_broad_categories(df_cleaned)
    df_broad.to_csv('combined_dataset_broad.csv', index=False)
    print("💾 Saved broad categories to 'combined_dataset_broad.csv'")