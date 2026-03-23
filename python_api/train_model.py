"""
train_model.py - FIXED v2: Clean data + aligned binary symptom vectors
------------------------------------------------------------------------
Changes from v1:
1. Removes iFixit repair guide contamination (141 rows across 11 classes)
2. Adds 'screen_physically_damaged' feature for Display Issue short-format rows
3. Adds 'battery_issue_natural' feature for Battery Issue short-format rows
4. All features remain binary vectors — stays aligned with main.py
"""

import json
import warnings
from pathlib import Path
from datetime import datetime

import joblib
import numpy as np
import pandas as pd
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import classification_report, f1_score, roc_auc_score
from sklearn.model_selection import StratifiedKFold, train_test_split
from sklearn.preprocessing import LabelEncoder

warnings.filterwarnings("ignore")

SCRIPT_DIR = Path(__file__).parent
DATA_PATH = SCRIPT_DIR / "combined_dataset.csv"
MODELS_DIR = SCRIPT_DIR / "models"
MODEL_PATH = MODELS_DIR / "cellphone_diagnosis_model.pkl"
ENCODER_PATH = MODELS_DIR / "label_encoder.pkl"
REPORT_PATH = SCRIPT_DIR / "evaluation_report.txt"

# ── Symptom features (must stay in sync with main.py) ────────────────────────
# Original 14 structured-format symptoms
STRUCTURED_SYMPTOMS = [
    "not_charging",
    "overheating",
    "no_signal",
    "battery_drains_fast",
    "stuck_on_logo",
    "screen_black",
    "touch_not_working",
    "speaker_no_sound",
    "mic_not_work",
    "screen_flickering",
    "wifi_not_working",
    "bluetooth_issue",
    "phone_freezing",
    "water_damage",
]

# New features for natural-language short-format rows
EXTENDED_SYMPTOMS = STRUCTURED_SYMPTOMS + [
    "screen_physically_damaged",  # Display Issue: cracked, lines, spots, bleed
    "battery_issue_natural",      # Battery Issue: "phone dies", "battery swollen", etc.
]

# Phrase → symptom_id (structured format, as used by the API)
PHRASE_TO_SYMPTOM = {
    "phone not charging":      "not_charging",
    "phone overheating":       "overheating",
    "no mobile signal":        "no_signal",
    "battery drains fast":     "battery_drains_fast",
    "phone stuck on logo":     "stuck_on_logo",
    "screen black":            "screen_black",
    "touch not working":       "touch_not_working",
    "speaker no sound":        "speaker_no_sound",
    "microphone not working":  "mic_not_work",
    "screen flickering":       "screen_flickering",
    "wifi not working":        "wifi_not_working",
    "bluetooth issue":         "bluetooth_issue",
    "phone freezing":          "phone_freezing",
    "water damage symptoms":   "water_damage",
}

# Keywords that signal physical screen damage (natural-language rows)
SCREEN_DAMAGE_KEYWORDS = [
    "black spots", "green lines", "purple lines", "cracked", "dead pixels",
    "half screen", "screen dim", "discoloration", "display glitch",
    "lcd bleeding", "blinking randomly", "screen blinking",
]

# Keywords that signal battery problem in natural language
BATTERY_NATURAL_KEYWORDS = [
    "battery drains very fast", "battery percentage drops", "phone dies after",
    "battery not lasting", "phone shuts down at", "battery overheating",
    "battery swollen", "phone randomly powers off", "battery health",
    "phone dying even with charge", "battery drains overnight",
    "phone turns off without warning",
]


# ── Data cleaning ─────────────────────────────────────────────────────────────
def remove_ifixit_contamination(data: pd.DataFrame) -> pd.DataFrame:
    """Remove iFixit repair guide / teardown rows — they are not symptom descriptions."""
    ifixit_mask = data["description"].str.contains(
        r"Step 1|Steps:|iOpener|Teardown|pentalobe|opening pick|back cover",
        case=False, na=False, regex=True
    )
    removed = ifixit_mask.sum()
    if removed:
        print(f"  Removed {removed} iFixit contamination rows:")
        print(data[ifixit_mask]["fault"].value_counts().to_string())
    return data[~ifixit_mask].copy()


def remove_rare_classes(data: pd.DataFrame, min_samples: int = 5) -> pd.DataFrame:
    """Drop classes with too few samples to train/evaluate meaningfully."""
    counts = data["fault"].value_counts()
    rare = counts[counts < min_samples].index.tolist()
    if rare:
        print(f"  Removing classes with < {min_samples} samples: {rare}")
        data = data[~data["fault"].isin(rare)]
    return data.copy()


# ── Feature engineering ───────────────────────────────────────────────────────
def description_to_vector(description: str) -> dict:
    """
    Convert a description into a binary symptom feature vector.

    Handles two input formats:
    1. Structured: "phone not charging and battery drains fast and ..."
    2. Natural:    "battery drains very fast" / "screen cracked"

    Returns the same feature format that main.py produces at inference.
    """
    desc_lower = description.lower()
    features = {s: 0 for s in EXTENDED_SYMPTOMS}

    # Structured symptom phrases (API inference format)
    for phrase, symptom_id in PHRASE_TO_SYMPTOM.items():
        if phrase in desc_lower:
            features[symptom_id] = 1

    # Physical screen damage (natural-language rows)
    if any(kw in desc_lower for kw in SCREEN_DAMAGE_KEYWORDS):
        features["screen_physically_damaged"] = 1

    # Battery natural-language rows
    if any(kw in desc_lower for kw in BATTERY_NATURAL_KEYWORDS):
        features["battery_issue_natural"] = 1

    return features


def build_feature_matrix(data: pd.DataFrame) -> pd.DataFrame:
    vectors = data["description"].apply(description_to_vector)
    X = pd.DataFrame(list(vectors))
    return X[EXTENDED_SYMPTOMS]


# ── Training ──────────────────────────────────────────────────────────────────
def train(X: pd.DataFrame, y_encoded: np.ndarray, label_encoder: LabelEncoder) -> tuple:
    # Drop classes with < 2 samples (can't stratify-split)
    counts = pd.Series(y_encoded).value_counts()
    valid_classes = counts[counts >= 2].index
    mask = pd.Series(y_encoded).isin(valid_classes).values
    if mask.sum() < len(y_encoded):
        dropped = label_encoder.inverse_transform(counts[counts < 2].index.tolist())
        print(f"  Dropping classes still too small for split: {list(dropped)}")
        X = X[mask]
        y_encoded = y_encoded[mask]

    X_train, X_test, y_train, y_test = train_test_split(
        X, y_encoded, test_size=0.2, random_state=42, stratify=y_encoded
    )

    clf = RandomForestClassifier(
        n_estimators=300,
        max_depth=None,
        min_samples_split=2,
        min_samples_leaf=1,
        class_weight="balanced",
        random_state=42,
        n_jobs=-1,
    )
    clf.fit(X_train, y_train)

    y_pred = clf.predict(X_test)
    y_prob = clf.predict_proba(X_test)

    accuracy = (y_pred == y_test).mean()
    f1_macro = f1_score(y_test, y_pred, average="macro")
    f1_weighted = f1_score(y_test, y_pred, average="weighted")

    try:
        auc = roc_auc_score(y_test, y_prob, multi_class="ovr")
    except Exception:
        auc = None

    present_classes = sorted(set(y_test) | set(y_pred))
    present_names = label_encoder.inverse_transform(present_classes)
    report_dict = classification_report(
        y_test, y_pred,
        labels=present_classes,
        target_names=present_names,
        output_dict=True,
    )

    results = {
        "model": "RandomForestClassifier",
        "input_format": "binary_symptom_vectors_v2",
        "note": (
            "v2: iFixit contamination removed; screen_physically_damaged and "
            "battery_issue_natural features added for natural-language rows. "
            "Model and API are aligned."
        ),
        "accuracy": accuracy,
        "f1_macro": f1_macro,
        "f1_weighted": f1_weighted,
        "auc_score": auc,
        "train_size": len(X_train),
        "test_size": len(X_test),
        "n_classes": len(label_encoder.classes_),
        "features": EXTENDED_SYMPTOMS,
        "timestamp": datetime.now().isoformat(),
        "classification_report": report_dict,
    }

    print(f"\nModel Performance:")
    print(f"  Accuracy:    {accuracy*100:.2f}%")
    print(f"  F1 Macro:    {f1_macro:.4f}")
    print(f"  F1 Weighted: {f1_weighted:.4f}")
    if auc:
        print(f"  AUC:         {auc:.4f}")
    print(f"\nPer-class breakdown:")
    for cls in present_names:
        r = report_dict[cls]
        flag = "  <-- still low" if r["f1-score"] < 0.7 else ""
        print(f"  {cls:<45} P={r['precision']:.2f}  R={r['recall']:.2f}  F1={r['f1-score']:.2f}{flag}")

    return clf, results, present_names


# ── Cross-validation ──────────────────────────────────────────────────────────
def cross_validate(X: pd.DataFrame, y_encoded: np.ndarray, n_splits: int = 5) -> float:
    skf = StratifiedKFold(n_splits=n_splits, shuffle=True, random_state=42)
    scores = []
    for fold, (train_idx, val_idx) in enumerate(skf.split(X, y_encoded), 1):
        clf = RandomForestClassifier(
            n_estimators=200, class_weight="balanced", random_state=42, n_jobs=-1
        )
        clf.fit(X.iloc[train_idx], y_encoded[train_idx])
        preds = clf.predict(X.iloc[val_idx])
        f1 = f1_score(y_encoded[val_idx], preds, average="weighted")
        scores.append(f1)
        print(f"  Fold {fold}: F1={f1:.4f}")
    mean_f1 = float(np.mean(scores))
    print(f"  CV Mean F1: {mean_f1:.4f} +/- {np.std(scores):.4f}")
    return mean_f1


# ── Main ──────────────────────────────────────────────────────────────────────
def main():
    print("=" * 65)
    print("CELLPHONE FAULT DIAGNOSIS - FIXED TRAINING PIPELINE v2")
    print("=" * 65)

    data = pd.read_csv(DATA_PATH, encoding="utf-8-sig")
    data.columns = data.columns.str.strip()
    data = data.dropna(subset=["description", "fault"]).drop_duplicates()
    print(f"Raw data: {len(data)} samples, {data['fault'].nunique()} classes")

    print("\nCleaning data...")
    data = remove_ifixit_contamination(data)
    data = remove_rare_classes(data, min_samples=5)
    print(f"Clean data: {len(data)} samples, {data['fault'].nunique()} classes")
    print(f"Classes remaining: {sorted(data['fault'].unique())}")

    print("\nBuilding feature matrix...")
    X = build_feature_matrix(data)
    print(f"Shape: {X.shape}  |  Features: {list(X.columns)}")

    # Coverage check per feature
    print("\nFeature activation rates:")
    for col in X.columns:
        rate = X[col].mean()
        print(f"  {col:<30} {rate*100:.1f}%")

    label_encoder = LabelEncoder()
    y_encoded = label_encoder.fit_transform(data["fault"])

    print(f"\n5-Fold Cross-Validation:")
    cross_validate(X, y_encoded)

    print("\nTraining final model...")
    clf, results, present_names = train(X, y_encoded, label_encoder)

    MODELS_DIR.mkdir(exist_ok=True)
    joblib.dump(clf, MODEL_PATH)
    joblib.dump(label_encoder, ENCODER_PATH)
    with open(REPORT_PATH, "w", encoding="utf-8") as f:
        json.dump(results, f, indent=2, default=str)

    print(f"\nSaved: {MODEL_PATH}")
    print(f"Saved: {ENCODER_PATH}")
    print(f"Saved: {REPORT_PATH}")

    # Inference alignment test
    print("\nInference alignment test:")
    test_cases = [
        ({"not_charging": 1, "overheating": 1},                    "Charging/Power issue"),
        ({"screen_black": 1, "touch_not_working": 1},              "Display/Touch issue"),
        ({"battery_drains_fast": 1, "phone_freezing": 1},          "Battery/Software issue"),
        ({"no_signal": 1, "bluetooth_issue": 1},                   "Baseband/Antenna issue"),
        ({"screen_physically_damaged": 1},                         "Display Issue (physical)"),
        ({"battery_issue_natural": 1},                             "Battery Issue (natural lang)"),
        ({"water_damage": 1},                                      "Water Damage"),
    ]
    for symptoms_dict, hint in test_cases:
        vec = pd.DataFrame([{s: symptoms_dict.get(s, 0) for s in EXTENDED_SYMPTOMS}])
        pred_idx = clf.predict(vec)[0]
        pred_label = label_encoder.inverse_transform([pred_idx])[0]
        conf = clf.predict_proba(vec)[0].max()
        print(f"  [{hint}]  ->  {pred_label}  ({conf*100:.1f}%)")

    print("\nDone.")


if __name__ == "__main__":
    main()