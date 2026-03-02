"""
diagnosis.py
------------
Cellphone fault diagnosis tool.
Takes a plain-text description of phone symptoms and returns a diagnosis,
confidence score, and suggested replacement parts.

Usage:
    python diagnosis.py

Bugs fixed in this version:
- Replacement parts was printing symptom names instead of parts
  (root cause: iterating df.columns instead of doing REPLACEMENT_MAP lookup)
- Output was printing twice
  (root cause: main() was being called both inside and at module level)
"""

import re
from pathlib import Path
from typing import Optional

import joblib
import numpy as np
import pandas as pd

# ── Paths ──────────────────────────────────────────────────────────────────────
MODEL_PATH   = Path("cellphone_diagnosis_model.pkl")
ENCODER_PATH = Path("label_encoder.pkl")

# ── Feature list (must match dataset column order exactly) ─────────────────────
SYMPTOMS: list[str] = [
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

# ── Keyword map ────────────────────────────────────────────────────────────────
KEYWORD_MAP: dict[str, str] = {
    # not_charging
    "not charging":           "not_charging",
    "cannot charge":          "not_charging",
    "cant charge":            "not_charging",
    "wont charge":            "not_charging",
    "no charge":              "not_charging",
    # overheating
    "overheating":            "overheating",
    "overheat":               "overheating",
    "getting hot":            "overheating",
    "too hot":                "overheating",
    "feels hot":              "overheating",
    # no_signal
    "no signal":              "no_signal",
    "no network":             "no_signal",
    "no service":             "no_signal",
    "lost signal":            "no_signal",
    "cannot connect network": "no_signal",
    # battery_drains_fast
    "battery draining fast":  "battery_drains_fast",
    "battery draining":       "battery_drains_fast",
    "battery dies fast":      "battery_drains_fast",
    "draining fast":          "battery_drains_fast",
    "drain fast":             "battery_drains_fast",
    "battery drain":          "battery_drains_fast",
    # stuck_on_logo
    "stuck on logo":          "stuck_on_logo",
    "only show logo":         "stuck_on_logo",
    "logo frozen":            "stuck_on_logo",
    "logo stuck":             "stuck_on_logo",
    "stuck logo":             "stuck_on_logo",
    "wont boot":              "stuck_on_logo",
    "wont turn on":           "stuck_on_logo",
    "boot loop":              "stuck_on_logo",
    # screen_black
    "screen is black":        "screen_black",
    "screen black":           "screen_black",
    "black screen":           "screen_black",
    "screen went black":      "screen_black",
    "blank screen":           "screen_black",
    "no display":             "screen_black",
    # touch_not_working
    "touch screen not working": "touch_not_working",
    "touch not working":        "touch_not_working",
    "touchscreen not working":  "touch_not_working",
    "ghost touching":           "touch_not_working",
    "ghost touch":              "touch_not_working",
    "hard touch":               "touch_not_working",
    "cant touch":               "touch_not_working",
    "screen not responding":    "touch_not_working",
    # speaker_no_sound
    "speaker not working":    "speaker_no_sound",
    "speaker problem":        "speaker_no_sound",
    "no sound":               "speaker_no_sound",
    "no audio":               "speaker_no_sound",
    "cant hear":              "speaker_no_sound",
    "no volume":              "speaker_no_sound",
    # mic_not_work  (word-boundary matched in text_to_features)
    "microphone not working": "mic_not_work",
    "mic not working":        "mic_not_work",
    "microphone problem":     "mic_not_work",
    "mic problem":            "mic_not_work",
    "no mic":                 "mic_not_work",
    "mic broken":             "mic_not_work",
    "mic dead":               "mic_not_work",
    "voice not heard":        "mic_not_work",
    "they cant hear me":      "mic_not_work",
    "caller cant hear me":    "mic_not_work",
    "people cant hear me":    "mic_not_work",
    # screen_flickering
    "screen flickering":      "screen_flickering",
    "flickering screen":      "screen_flickering",
    "screen flickers":        "screen_flickering",
    "display flickering":     "screen_flickering",
    "screen flashing":        "screen_flickering",
    "lines on screen":        "screen_flickering",
    "screen glitching":       "screen_flickering",
    # wifi_not_working
    "wifi not working":       "wifi_not_working",
    "wifi problem":           "wifi_not_working",
    "cannot connect wifi":    "wifi_not_working",
    "no wifi":                "wifi_not_working",
    "wifi disconnecting":     "wifi_not_working",
    "wifi keeps dropping":    "wifi_not_working",
    # bluetooth_issue
    "bluetooth not working":  "bluetooth_issue",
    "bluetooth problem":      "bluetooth_issue",
    "cant connect bluetooth": "bluetooth_issue",
    "bluetooth wont pair":    "bluetooth_issue",
    "bluetooth issue":        "bluetooth_issue",
    # phone_freezing
    "phone freezing":         "phone_freezing",
    "phone frozen":           "phone_freezing",
    "phone keeps freezing":   "phone_freezing",
    "phone hanging":          "phone_freezing",
    "phone lags":             "phone_freezing",
    "keeps restarting":       "phone_freezing",
    "random reboot":          "phone_freezing",
    "phone reboots":          "phone_freezing",
    # water_damage
    "water damage":           "water_damage",
    "got wet":                "water_damage",
    "dropped in water":       "water_damage",
    "liquid damage":          "water_damage",
    "water inside":           "water_damage",
    "fell in water":          "water_damage",
}

# Short keywords needing word-boundary protection against partial matches
BOUNDARY_KEYWORDS: set[str] = {"hot", "logo"}

# -- Single-symptom direct diagnosis ------------------------------------------
# When only ONE symptom is detected, skip the ML model and return a direct
# answer. This mirrors real repair shop logic: if a customer only reports
# one problem, diagnose that problem specifically.
SINGLE_SYMPTOM_MAP: dict[str, str] = {
    "not_charging":        "Charging Port Issue",
    "overheating":         "Battery Issue",
    "no_signal":           "SIM IC Issue",
    "battery_drains_fast": "Battery Issue",
    "stuck_on_logo":       "Software/OS Issue",
    "screen_black":        "Display IC Issue",
    "touch_not_working":   "Touch Controller Issue",
    "speaker_no_sound":    "Speaker Issue",
    "mic_not_work":        "Microphone Issue",
    "screen_flickering":   "Display IC Issue",
    "wifi_not_working":    "Antenna Issue",
    "bluetooth_issue":     "Baseband Issue",
    "phone_freezing":      "Software/OS Issue",
    "water_damage":        "Water Damage - Inspect All Components",
}


# ── Replacement parts ──────────────────────────────────────────────────────────
REPLACEMENT_MAP: dict[str, list[str]] = {
    "Mainboard Issue": [
        "Mainboard/Motherboard", "Power IC", "CPU", "RAM", "Baseband IC",
    ],
    "Charging Port Issue": [
        "Charging Port", "USB Connector", "Flex Cable",
    ],
    "Charging IC Issue": [
        "Charging IC Chip", "Power Management IC", "Battery Connector",
    ],
    "Battery Issue": [
        "Battery", "Battery Connector", "Thermal Sensor IC",
    ],
    "SIM IC Issue": [
        "SIM Card Slot", "SIM IC Chip", "Baseband IC",
    ],
    "Touch Controller Issue": [
        "Touch Controller IC", "Digitizer", "Power Management IC", "Thermal Sensor IC",
    ],
    "Display IC Issue": [
        "Display Driver IC", "Backlight IC", "Connector Flex",
    ],
    "Speaker Issue": [
        "Speaker Module", "Audio IC", "Connector Flex",
    ],
    "Microphone Issue": [
        "Microphone Module", "Audio IC", "Connector Flex",
    ],
    "Antenna Issue": [
        "Antenna Module", "RF IC", "Baseband IC",
    ],
    "Baseband Issue": [
        "Baseband IC", "RF IC", "Antenna Module",
    ],
    "Power IC Issue": [
        "Power IC", "Battery Connector", "Mainboard",
    ],
    "Software/OS Issue": [
        "Reflash Firmware", "Update OS", "Mainboard Check",
    ],
}


# ── Symptom-specific parts map (for direct symptom guidance) ─────────────────
SYMPTOM_PARTS_MAP: dict[str, list[str]] = {
    "not_charging":           ["Charging Port", "USB Connector", "Charging IC", "Battery Connector"],
    "overheating":            ["Battery", "Thermal Sensor IC", "Heatsink", "Power IC"],
    "no_signal":              ["Antenna Module", "Baseband IC", "RF IC", "SIM Card Slot"],
    "battery_drains_fast":    ["Battery", "Power IC", "LCD Backlight"],
    "stuck_on_logo":          ["Mainboard (Reflash)", "CPU", "Flash Memory"],
    "screen_black":           ["Display Driver IC", "Backlight IC", "LCD Connector", "Power Supply"],
    "touch_not_working":      ["Touch Controller IC", "Digitizer", "Touch Panel Flex"],
    "speaker_no_sound":       ["Speaker Module", "Audio Codec IC", "Amplifier IC"],
    "mic_not_work":           ["Microphone Module", "Audio Codec IC", "Mic Flex Connector"],
    "screen_flickering":      ["Display Driver IC", "Backlight IC", "LCD Flex Cable"],
    "wifi_not_working":       ["WiFi Module", "Antenna", "RF IC"],
    "bluetooth_issue":        ["Bluetooth Module", "Baseband IC", "Antenna"],
    "phone_freezing":         ["RAM", "CPU", "Storage IC", "Power Management"],
    "water_damage":           ["Mainboard (Full Inspection)", "Battery", "Flex Cables", "All Connectors"],
}


# ── Helpers ────────────────────────────────────────────────────────────────────

def clean_text(text: str) -> str:
    """Lowercase and strip punctuation from user input."""
    text = text.lower()
    text = re.sub(r"[^\w\s]", "", text)
    return text.strip()


def text_to_features(user_text: str) -> tuple[pd.DataFrame, int]:
    """
    Convert plain-text problem description into a binary symptom feature vector.
    Short/ambiguous keywords use word-boundary matching to avoid false positives.

    Returns:
        features_df    : single-row DataFrame aligned to SYMPTOMS column order
        detected_count : number of distinct symptoms detected
    """
    user_text = clean_text(user_text)
    features: dict[str, int] = {symptom: 0 for symptom in SYMPTOMS}
    detected_count = 0

    for keyword, symptom in KEYWORD_MAP.items():
        if keyword in BOUNDARY_KEYWORDS:
            matched = bool(re.search(rf"\b{re.escape(keyword)}\b", user_text))
        else:
            matched = keyword in user_text

        if matched and features[symptom] == 0:
            features[symptom] = 1
            detected_count += 1

    return pd.DataFrame([features]), detected_count


def rule_based_override(
    features_df: pd.DataFrame,
    confidence: Optional[float] = None,
    top_classes: Optional[list[str]] = None,
) -> Optional[str]:
    """
    Apply heuristic rules on top of the ML prediction.
    Rules are ordered most-specific to least-specific.
    Returns a diagnosis string if fired, None to defer to ML.
    """
    f = features_df.iloc[0]
    
    # Count major failure symptoms
    major_failures = int(
        f["touch_not_working"] + f["mic_not_work"] +
        f["speaker_no_sound"]  + f["no_signal"]    + f["screen_black"]
    )
    
    # === MAINBOARD ISSUES (highest priority) ===
    if major_failures >= 3:
        return "Mainboard Issue (Rule-Based)"
    if f["touch_not_working"] == 1 and f["mic_not_work"] == 1:
        return "Mainboard Issue (Rule-Based)"
    if f["mic_not_work"] == 1 and f["speaker_no_sound"] == 1:
        return "Mainboard Issue (Rule-Based)"
    
    # === POWER/THERMAL ISSUES ===
    if f["overheating"] == 1 and f["battery_drains_fast"] == 1:
        return "Battery Issue (Rule-Based)"
    if f["overheating"] == 1 and f["not_charging"] == 1:
        return "Power IC Issue (Rule-Based)"
    if f["overheating"] == 1 and f["touch_not_working"] == 1:
        return "Power IC Issue (Rule-Based)"
    if f["overheating"] == 1 and f["screen_black"] == 1:
        return "Power IC Issue (Rule-Based)"
    
    # === DISPLAY ISSUES ===
    if f["screen_flickering"] == 1 and f["screen_black"] == 1:
        return "Display IC Issue (Rule-Based)"
    if f["screen_black"] == 1 and f["touch_not_working"] == 1:
        return "Display IC Issue (Rule-Based)"
    
    # === CHARGING ISSUES ===
    if f["not_charging"] == 1 and f["screen_black"] == 1:
        return "Charging IC Issue (Rule-Based)"
    if f["not_charging"] == 1 and f["battery_drains_fast"] == 1:
        return "Charging Port Issue (Rule-Based)"
    
    # === SIGNAL ISSUES ===
    if f["no_signal"] == 1 and f["mic_not_work"] == 1:
        return "Baseband Issue (Rule-Based)"
    if f["no_signal"] == 1 and f["wifi_not_working"] == 1:
        return "Antenna Issue (Rule-Based)"
    if f["no_signal"] == 1 and major_failures == 1:
        return "SIM IC Issue (Rule-Based)"
    
    # === SOFTWARE ISSUES ===
    if f["phone_freezing"] == 1 and f["stuck_on_logo"] == 1:
        return "Software/OS Issue (Rule-Based)"
    
    # === WATER DAMAGE (always highest priority) ===
    if f["water_damage"] == 1:
        return "Water Damage - Inspect All Components (Rule-Based)"
    
    # Low confidence: symptom is too generic to distinguish classes on its own.
    if confidence is not None and confidence < 50:
        competing = f" (tied between: {', '.join(top_classes)})" if top_classes else ""
        return f"Low Confidence - Need More Symptoms{competing}"

    return None


def get_replacement_parts(diagnosis: str) -> list[str]:
    """
    Look up replacement parts for a diagnosis label.
    Strips parenthetical suffixes like '(Rule-Based)' before lookup.
    """
    clean_key = re.sub(r"\s*\(.*?\)", "", diagnosis).strip()
    return REPLACEMENT_MAP.get(clean_key, ["Diagnosis not mapped to replacement parts"])


def divider() -> None:
    print("\n" + "-" * 48)


# ── Entry point ────────────────────────────────────────────────────────────────

def main() -> None:
    # 1. Load model and encoder
    try:
        model         = joblib.load(MODEL_PATH)
        label_encoder = joblib.load(ENCODER_PATH)
    except FileNotFoundError as e:
        raise FileNotFoundError(
            f"Model file not found: {e}\n"
            "Run generate_dataset.py then train_model.py first."
        ) from e

    if not hasattr(model, "predict_proba"):
        raise TypeError("Loaded model does not support predict_proba.")

    # 2. Get user input
    print("\nCellphone Diagnosis Tool")
    divider()
    user_input = input("Describe the phone problem: ").strip()

    if not user_input:
        print("\nNo input provided.")
        return

    features_df, detected_count = text_to_features(user_input)

    if detected_count == 0:
        print("\nNo recognizable symptoms detected.")
        print("Try phrases like: 'screen is black', 'not charging', 'boot loop'")
        return

    # 3. Show detected symptoms
    #    NOTE: iterate SYMPTOMS (the definitive feature list), never df.columns
    divider()
    print("Detected Symptoms:")
    for symptom in SYMPTOMS:
        if features_df.iloc[0][symptom] == 1:
            print(f"  - {symptom}")

    # 4. Single-symptom shortcut
    #    If the user only describes ONE problem, diagnose it directly.
    #    No need for ML — a customer saying "my phone is overheating"
    #    wants a single focused answer, not a probability distribution.
    if detected_count == 1:
        active_symptom = next(s for s in SYMPTOMS if features_df.iloc[0][s] == 1)
        direct_diagnosis = SINGLE_SYMPTOM_MAP.get(active_symptom, "Unknown Issue")
        parts = get_replacement_parts(direct_diagnosis)

        divider()
        print(f"Diagnosis         : {direct_diagnosis}")
        print(f"\nSuggested Replacement Parts:")
        for part in parts:
            print(f"  - {part}")
        divider()
        return

    # 5. Multi-symptom: run ML model
    prediction    = model.predict(features_df)
    probabilities = model.predict_proba(features_df)

    primary_diagnosis = label_encoder.inverse_transform(prediction)[0]
    confidence        = round(float(max(probabilities[0])) * 100, 2)
    top2_idx          = np.argsort(probabilities[0])[-2:][::-1]

    # Build top-3 class names for the low-confidence message
    top3_idx     = np.argsort(probabilities[0])[-3:][::-1]
    top3_classes = [label_encoder.inverse_transform([i])[0] for i in top3_idx]
    rule_result  = rule_based_override(features_df, confidence, top_classes=top3_classes)

    # 6. Primary result
    divider()
    print(f"Primary Diagnosis : {primary_diagnosis}")
    print(f"Confidence        : {confidence}%")

    parts = get_replacement_parts(primary_diagnosis)
    print("\nMain Diagnosis Parts:")
    for part in parts:
        print(f"  - {part}")

    # Show symptom-specific parts
    detected_symptom_names = [s for s in SYMPTOMS if features_df.iloc[0][s] == 1]
    print("\nSymptom-Specific Parts to Check:")
    for symptom in detected_symptom_names:
        if symptom in SYMPTOM_PARTS_MAP:
            print(f"\n  {symptom.replace('_', ' ').title()}:")
            for part in SYMPTOM_PARTS_MAP[symptom]:
                print(f"    - {part}")

    # 7. Top 2
    print("\nTop 2 Possible Issues:")
    for rank, idx in enumerate(top2_idx, start=1):
        label = label_encoder.inverse_transform([idx])[0]
        prob  = round(float(probabilities[0][idx]) * 100, 2)
        print(f"  {rank}. {label} - {prob}%")

    # 8. Rule-based note (only printed when a rule fires)
    if rule_result:
        divider()
        print(f"Rule-Based Suggestion: {rule_result}")
        if "Low Confidence" in rule_result:
            print("\n  Tip: The detected symptom(s) alone are too generic to")
            print("  distinguish between these classes. Try adding more detail,")
            print("  for example:")
            print("    'overheating and not charging'")
            print("    'overheating and screen black'")
            print("    'overheating and battery draining fast'")
        else:
            rb_parts = get_replacement_parts(rule_result)
            if rb_parts != ["Diagnosis not mapped to replacement parts"]:
                print("Extra Suggested Parts:")
                for part in rb_parts:
                    print(f"  - {part}")

    divider()


# main() is called ONLY here — never at module level — so importing this file
# in tests or other scripts won't trigger the input() prompt.
if __name__ == "__main__":
    main()