"""
generate_dataset.py
-------------------
Generates a clean, diverse, balanced dataset for the cellphone diagnosis model.

IMPORTANT — Why we expanded the symptom features:
  The original 9 features only produce 511 non-zero symptom vectors total,
  split unevenly across 13 classes. That leaves some classes with as few as
  3–9 unique rows — not enough for reliable ML training.

  By adding 5 more symptoms (14 features total), we get 2^14 - 1 = 16,383
  possible vectors, giving every class hundreds of unique rows to draw from.

New symptoms added:
  - screen_flickering  : display flickers or has artifacts
  - wifi_not_working   : cannot connect to WiFi
  - bluetooth_issue    : Bluetooth won't pair or drops
  - phone_freezing     : phone hangs or reboots randomly
  - water_damage       : liquid exposure indicator

Run this script first, then run train_model.py.
"""

import itertools
import random
from collections import defaultdict
from pathlib import Path

import pandas as pd

random.seed(42)

SYMPTOMS = [
    # Original 9
    "not_charging",
    "overheating",
    "no_signal",
    "battery_drains_fast",
    "stuck_on_logo",
    "screen_black",
    "touch_not_working",
    "speaker_no_sound",
    "mic_not_work",
    # New 5 — expand the feature space
    "screen_flickering",
    "wifi_not_working",
    "bluetooth_issue",
    "phone_freezing",
    "water_damage",
]

# Core signatures: the MINIMUM set of symptoms that define each class.
# Listed from most-specific to least-specific — higher-priority classes
# get first pick of ambiguous symptom vectors.
CLASS_CORES = [
    ("Mainboard Issue",        frozenset(["touch_not_working", "speaker_no_sound", "mic_not_work"])),
    ("Power IC Issue",         frozenset(["not_charging", "overheating", "battery_drains_fast"])),
    ("Baseband Issue",         frozenset(["no_signal", "mic_not_work"])),
    ("Charging IC Issue",      frozenset(["not_charging", "screen_black"])),
    ("Display IC Issue",       frozenset(["screen_black", "screen_flickering"])),
    ("Antenna Issue",          frozenset(["no_signal", "wifi_not_working"])),
    ("Battery Issue",          frozenset(["battery_drains_fast", "overheating"])),
    ("Charging Port Issue",    frozenset(["not_charging"])),
    ("SIM IC Issue",           frozenset(["no_signal"])),
    ("Touch Controller Issue", frozenset(["touch_not_working"])),
    ("Speaker Issue",          frozenset(["speaker_no_sound"])),
    ("Microphone Issue",       frozenset(["mic_not_work"])),
    ("Software/OS Issue",      frozenset(["stuck_on_logo", "phone_freezing"])),
]

TARGET_PER_CLASS = 50
OUTPUT_PATH = Path("dataset.csv")


def assign_all_vectors() -> dict:
    """
    Enumerate all non-zero symptom vectors and assign each to exactly
    one class (first matching core in priority order wins).

    Note: We only enumerate 'relevant' vectors where at least one core
    symptom is active, to keep this tractable (14 features = 16383 combos).
    """
    print(f"Enumerating vectors over {len(SYMPTOMS)} features "
          f"({2**len(SYMPTOMS)-1} possible non-zero combinations)...")

    label_to_vectors: dict[str, list] = defaultdict(list)

    for bits in itertools.product([0, 1], repeat=len(SYMPTOMS)):
        if not any(bits):
            continue  # skip all-zero

        active = frozenset(s for s, b in zip(SYMPTOMS, bits) if b == 1)

        for label, core in CLASS_CORES:
            if core.issubset(active):
                label_to_vectors[label].append(bits)
                break  # first match wins

    return dict(label_to_vectors)


def build_dataset(label_to_vectors: dict) -> pd.DataFrame:
    rows = []
    print("\nSampling rows per class...")

    for label, core in CLASS_CORES:
        vectors = label_to_vectors.get(label, [])
        random.shuffle(vectors)
        selected = vectors[:TARGET_PER_CLASS]

        status = "✓" if len(selected) >= TARGET_PER_CLASS else "⚠"
        print(f"  {status}  {label:<28} {len(selected):>3} rows "
              f"({len(vectors)} available)")

        for bits in selected:
            row = dict(zip(SYMPTOMS, bits))
            row["diagnosis"] = label
            rows.append(row)

    df = pd.DataFrame(rows).sample(frac=1, random_state=42).reset_index(drop=True)
    return df


def validate_dataset(df: pd.DataFrame) -> None:
    """Assert data quality. Raises AssertionError on any violation."""
    print("\nValidating dataset quality...")
    feature_cols = [c for c in df.columns if c != "diagnosis"]

    dups = df.duplicated(subset=feature_cols).sum()
    assert dups == 0, f"❌ {dups} duplicate symptom vectors found!"
    print("  ✓ No duplicate rows")

    zero_rows = (df[feature_cols].sum(axis=1) == 0).sum()
    assert zero_rows == 0, f"❌ {zero_rows} all-zero symptom rows found!"
    print("  ✓ No all-zero symptom rows")

    counts = df["diagnosis"].value_counts()
    min_count = counts.min()
    assert min_count >= 20, (
        f"❌ '{counts.idxmin()}' has only {min_count} rows (need ≥ 20). "
        f"Consider lowering TARGET_PER_CLASS or adjusting class cores."
    )
    print(f"  ✓ Min class size    : {min_count} rows")
    print(f"  ✓ Total rows        : {len(df)}")
    print(f"  ✓ Number of classes : {df['diagnosis'].nunique()}")


def print_summary(df: pd.DataFrame) -> None:
    print("\nFinal class distribution:")
    counts = df["diagnosis"].value_counts()
    max_count = counts.max()
    for label, count in counts.items():
        bar = "█" * int(count / max_count * 25)
        print(f"  {label:<28} {count:>3}  {bar}")


if __name__ == "__main__":
    label_to_vectors = assign_all_vectors()
    df = build_dataset(label_to_vectors)
    validate_dataset(df)
    print_summary(df)
    df.to_csv(OUTPUT_PATH, index=False)
    print(f"\n✅ Saved {len(df)} rows to {OUTPUT_PATH}")
    print("   → Now run: python train_model.py")