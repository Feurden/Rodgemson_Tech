"""
python_api/main.py
------------------
FastAPI server that exposes the cellphone diagnosis model as a REST API.
CakePHP (or any HTTP client) sends a symptom description, gets back a
structured JSON diagnosis result.

Start the server:
    uvicorn main:app --host 0.0.0.0 --port 8000 --reload

Endpoints:
    POST /diagnose        - main diagnosis endpoint
    GET  /health          - health check (CakePHP can ping this)
    GET  /docs            - auto-generated Swagger UI (development only)
"""

from contextlib import asynccontextmanager
from pathlib import Path
from typing import Optional

import joblib
import numpy as np
import pandas as pd
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel

# ── Paths ──────────────────────────────────────────────────────────────────────
BASE_DIR     = Path(__file__).parent
MODEL_PATH   = BASE_DIR / "models" / "cellphone_diagnosis_model.pkl"
ENCODER_PATH = BASE_DIR / "models" / "label_encoder.pkl"

# ── Global model state (loaded once on startup) ────────────────────────────────
model         = None
label_encoder = None

# ── Feature list (must match training column order exactly) ───────────────────
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

# ── Replacement parts map (diagnosis-based) ────────────────────────────────────
REPLACEMENT_MAP: dict[str, list[str]] = {
    "Mainboard Issue":        ["Mainboard/Motherboard", "Power IC", "CPU", "RAM", "Baseband IC"],
    "Charging Port Issue":    ["Charging Port", "USB Connector", "Flex Cable"],
    "Charging IC Issue":      ["Charging IC Chip", "Power Management IC", "Battery Connector"],
    "Battery Issue":          ["Battery", "Battery Connector", "Thermal Sensor IC"],
    "SIM IC Issue":           ["SIM Card Slot", "SIM IC Chip", "Baseband IC"],
    "Touch Controller Issue": ["Touch Controller IC", "Digitizer", "Power Management IC", "Thermal Sensor IC"],
    "Display IC Issue":       ["Display Driver IC", "Backlight IC", "Connector Flex"],
    "Speaker Issue":          ["Speaker Module", "Audio IC", "Connector Flex"],
    "Microphone Issue":       ["Microphone Module", "Audio IC", "Connector Flex"],
    "Antenna Issue":          ["Antenna Module", "RF IC", "Baseband IC"],
    "Baseband Issue":         ["Baseband IC", "RF IC", "Antenna Module"],
    "Power IC Issue":         ["Power IC", "Battery Connector", "Mainboard"],
    "Software/OS Issue":      ["Reflash Firmware", "Update OS", "Mainboard Check"],
}

# ── Symptom-specific parts map (for direct symptom guidance) ──────────────────
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

# ── Single-symptom direct diagnosis ───────────────────────────────────────────
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

# ── Keyword map ────────────────────────────────────────────────────────────────
import re

KEYWORD_MAP: dict[str, str] = {
    "not charging": "not_charging", "cannot charge": "not_charging",
    "cant charge": "not_charging", "wont charge": "not_charging", "no charge": "not_charging",
    "overheating": "overheating", "overheat": "overheating",
    "getting hot": "overheating", "too hot": "overheating", "feels hot": "overheating",
    "no signal": "no_signal", "no network": "no_signal", "no service": "no_signal",
    "lost signal": "no_signal", "cannot connect network": "no_signal",
    "battery draining fast": "battery_drains_fast", "battery draining": "battery_drains_fast",
    "battery dies fast": "battery_drains_fast", "draining fast": "battery_drains_fast",
    "drain fast": "battery_drains_fast", "battery drain": "battery_drains_fast",
    "stuck on logo": "stuck_on_logo", "only show logo": "stuck_on_logo",
    "logo frozen": "stuck_on_logo", "logo stuck": "stuck_on_logo",
    "stuck logo": "stuck_on_logo", "wont boot": "stuck_on_logo",
    "wont turn on": "stuck_on_logo", "boot loop": "stuck_on_logo",
    "screen is black": "screen_black", "screen black": "screen_black",
    "black screen": "screen_black", "screen went black": "screen_black",
    "blank screen": "screen_black", "no display": "screen_black",
    "touch screen not working": "touch_not_working", "touch not working": "touch_not_working",
    "touchscreen not working": "touch_not_working", "ghost touching": "touch_not_working",
    "ghost touch": "touch_not_working", "hard touch": "touch_not_working",
    "cant touch": "touch_not_working", "screen not responding": "touch_not_working",
    "speaker not working": "speaker_no_sound", "speaker problem": "speaker_no_sound",
    "no sound": "speaker_no_sound", "no audio": "speaker_no_sound",
    "cant hear": "speaker_no_sound", "no volume": "speaker_no_sound",
    "microphone not working": "mic_not_work", "mic not working": "mic_not_work",
    "microphone problem": "mic_not_work", "mic problem": "mic_not_work",
    "no mic": "mic_not_work", "mic broken": "mic_not_work", "mic dead": "mic_not_work",
    "voice not heard": "mic_not_work", "they cant hear me": "mic_not_work",
    "caller cant hear me": "mic_not_work", "people cant hear me": "mic_not_work",
    "screen flickering": "screen_flickering", "flickering screen": "screen_flickering",
    "screen flickers": "screen_flickering", "display flickering": "screen_flickering",
    "screen flashing": "screen_flickering", "lines on screen": "screen_flickering",
    "screen glitching": "screen_flickering",
    "wifi not working": "wifi_not_working", "wifi problem": "wifi_not_working",
    "cannot connect wifi": "wifi_not_working", "no wifi": "wifi_not_working",
    "wifi disconnecting": "wifi_not_working", "wifi keeps dropping": "wifi_not_working",
    "bluetooth not working": "bluetooth_issue", "bluetooth problem": "bluetooth_issue",
    "cant connect bluetooth": "bluetooth_issue", "bluetooth wont pair": "bluetooth_issue",
    "bluetooth issue": "bluetooth_issue",
    "phone freezing": "phone_freezing", "phone frozen": "phone_freezing",
    "phone keeps freezing": "phone_freezing", "phone hanging": "phone_freezing",
    "phone lags": "phone_freezing", "keeps restarting": "phone_freezing",
    "random reboot": "phone_freezing", "phone reboots": "phone_freezing",
    "water damage": "water_damage", "got wet": "water_damage",
    "dropped in water": "water_damage", "liquid damage": "water_damage",
    "water inside": "water_damage", "fell in water": "water_damage",
}

BOUNDARY_KEYWORDS: set[str] = {"hot", "logo"}


# ── Core logic (mirrors diagnosis.py) ─────────────────────────────────────────

def clean_text(text: str) -> str:
    text = text.lower()
    text = re.sub(r"[^\w\s]", "", text)
    return text.strip()


def text_to_features(user_text: str) -> tuple[pd.DataFrame, int, list[str]]:
    """Returns (features_df, detected_count, detected_symptom_names)."""
    user_text = clean_text(user_text)
    features: dict[str, int] = {s: 0 for s in SYMPTOMS}
    detected_count = 0

    for keyword, symptom in KEYWORD_MAP.items():
        if keyword in BOUNDARY_KEYWORDS:
            matched = bool(re.search(rf"\b{re.escape(keyword)}\b", user_text))
        else:
            matched = keyword in user_text
        if matched and features[symptom] == 0:
            features[symptom] = 1
            detected_count += 1

    detected_names = [s for s in SYMPTOMS if features[s] == 1]
    return pd.DataFrame([features]), detected_count, detected_names


def get_replacement_parts(diagnosis: str) -> list[str]:
    """Get parts for the diagnosis (main issue)."""
    clean_key = re.sub(r"\s*\(.*?\)", "", diagnosis).strip()
    return REPLACEMENT_MAP.get(clean_key, [])


def get_symptom_specific_parts(symptoms: list[str]) -> dict[str, list[str]]:
    """Get parts for each detected symptom."""
    symptom_parts = {}
    for symptom in symptoms:
        if symptom in SYMPTOM_PARTS_MAP:
            symptom_parts[symptom] = SYMPTOM_PARTS_MAP[symptom]
    return symptom_parts


def rule_based_override(
    features_df: pd.DataFrame,
    confidence: Optional[float] = None,
    top_classes: Optional[list[str]] = None,
) -> Optional[str]:
    f = features_df.iloc[0]
    
    # Count major failure symptoms
    major_failures = int(
        f["touch_not_working"] + f["mic_not_work"] +
        f["speaker_no_sound"]  + f["no_signal"]    + f["screen_black"]
    )
    
    # === MAINBOARD ISSUES (highest priority) ===
    if major_failures >= 3:
        return "Mainboard Issue"
    if f["touch_not_working"] == 1 and f["mic_not_work"] == 1:
        return "Mainboard Issue"
    if f["mic_not_work"] == 1 and f["speaker_no_sound"] == 1:
        return "Mainboard Issue"
    
    # === POWER/THERMAL ISSUES (highest priority after mainboard) ===
    if f["overheating"] == 1 and f["battery_drains_fast"] == 1:
        return "Battery Issue"
    if f["overheating"] == 1 and f["not_charging"] == 1:
        return "Power IC Issue"
    if f["overheating"] == 1 and f["touch_not_working"] == 1:
        return "Power IC Issue"
    if f["overheating"] == 1 and f["screen_black"] == 1:
        return "Power IC Issue"
    
    # === DISPLAY ISSUES ===
    if f["screen_flickering"] == 1 and f["screen_black"] == 1:
        return "Display IC Issue"
    if f["screen_black"] == 1 and f["touch_not_working"] == 1:
        return "Display IC Issue"
    
    # === CHARGING ISSUES ===
    if f["not_charging"] == 1 and f["screen_black"] == 1:
        return "Charging IC Issue"
    if f["not_charging"] == 1 and f["battery_drains_fast"] == 1:
        return "Charging Port Issue"
    
    # === SIGNAL ISSUES ===
    if f["no_signal"] == 1 and f["mic_not_work"] == 1:
        return "Baseband Issue"
    if f["no_signal"] == 1 and f["wifi_not_working"] == 1:
        return "Antenna Issue"
    if f["no_signal"] == 1 and major_failures == 1:
        return "SIM IC Issue"
    
    # === SOFTWARE ISSUES ===
    if f["phone_freezing"] == 1 and f["stuck_on_logo"] == 1:
        return "Software/OS Issue"
    
    # === WATER DAMAGE (always highest priority) ===
    if f["water_damage"] == 1:
        return "Water Damage - Inspect All Components"
    
    return None


# ── Lifespan: load model once on startup ──────────────────────────────────────

@asynccontextmanager
async def lifespan(app: FastAPI):
    global model, label_encoder
    if not MODEL_PATH.exists():
        raise RuntimeError(f"Model not found at {MODEL_PATH}. Run train_model.py first.")
    if not ENCODER_PATH.exists():
        raise RuntimeError(f"Encoder not found at {ENCODER_PATH}. Run train_model.py first.")
    model         = joblib.load(MODEL_PATH)
    label_encoder = joblib.load(ENCODER_PATH)
    print(f"Model loaded: {MODEL_PATH}")
    yield
    # cleanup on shutdown (nothing needed for joblib models)


# ── FastAPI app ────────────────────────────────────────────────────────────────

app = FastAPI(
    title="Cellphone Diagnosis API",
    version="1.0.0",
    lifespan=lifespan,
)

# Allow CakePHP (same or different server) to call this API
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],   # tighten this to your CakePHP domain in production
    allow_methods=["POST", "GET"],
    allow_headers=["*"],
)


# ── Request / Response schemas ────────────────────────────────────────────────

class DiagnoseRequest(BaseModel):
    description: str                # plain-text symptom description from user


class DiagnoseResponse(BaseModel):
    success: bool
    mode: str                       # "single_symptom" | "ml_model"
    detected_symptoms: list[str]
    diagnosis: str
    confidence: Optional[float]     # None for single-symptom mode
    replacement_parts: list[str]
    symptom_parts: dict[str, list[str]]  # {symptom: [parts...]}
    symptom_diagnoses: dict[str, str]    # {symptom: individual_diagnosis}
    top2: list[dict]                # [{"diagnosis": ..., "confidence": ...}]
    rule_suggestion: Optional[str]  # None if no rule fired


# ── Endpoints ──────────────────────────────────────────────────────────────────

@app.get("/health")
def health():
    """CakePHP can ping this to check if the API is up before sending requests."""
    return {
        "status": "ok",
        "model_loaded": model is not None,
    }


@app.post("/diagnose", response_model=DiagnoseResponse)
def diagnose(request: DiagnoseRequest):
    """
    Main diagnosis endpoint.

    CakePHP sends:
        POST /diagnose
        Content-Type: application/json
        { "description": "overheat and no mic" }

    Returns a structured JSON diagnosis result.
    """
    if not request.description.strip():
        raise HTTPException(status_code=422, detail="Description cannot be empty.")

    features_df, detected_count, detected_symptoms = text_to_features(request.description)

    if detected_count == 0:
        raise HTTPException(
            status_code=422,
            detail="No recognizable symptoms detected. "
                   "Try phrases like 'screen is black', 'not charging', 'boot loop'."
        )

    # ── Single-symptom: direct lookup, no ML needed ────────────────────────────
    if detected_count == 1:
        active_symptom   = detected_symptoms[0]
        diagnosis        = SINGLE_SYMPTOM_MAP.get(active_symptom, "Unknown Issue")
        replacement_parts = get_replacement_parts(diagnosis)

        symptom_parts = get_symptom_specific_parts(detected_symptoms)
        symptom_diagnoses = {active_symptom: diagnosis}
        return DiagnoseResponse(
            success=True,
            mode="single_symptom",
            detected_symptoms=detected_symptoms,
            diagnosis=diagnosis,
            confidence=None,
            replacement_parts=replacement_parts,
            symptom_parts=symptom_parts,
            symptom_diagnoses=symptom_diagnoses,
            top2=[],
            rule_suggestion=diagnosis,  # Mark as rule-based (direct mapping, not ML)
        )

    # ── Multi-symptom: run ML model ────────────────────────────────────────────
    prediction    = model.predict(features_df)
    probabilities = model.predict_proba(features_df)[0]

    primary_diagnosis = label_encoder.inverse_transform(prediction)[0]
    
    # Calculate confidence, ensuring it's in the 0-100 range
    max_prob = float(max(probabilities))
    if max_prob > 1:
        confidence = round(max_prob, 2)  # already in percentage form
    else:
        confidence = round(max_prob * 100, 2)  # convert to percentage
    
    # Cap confidence at 100
    confidence = min(confidence, 100.0)

    top2_idx = np.argsort(probabilities)[-2:][::-1]
    top2 = [
        {
            "diagnosis":  label_encoder.inverse_transform([i])[0],
            "confidence": min(round(float(probabilities[i]) * 100 if float(probabilities[i]) <= 1 else float(probabilities[i]), 2), 100.0),
        }
        for i in top2_idx
    ]

    top3_classes = [label_encoder.inverse_transform([i])[0] for i in np.argsort(probabilities)[-3:][::-1]]
    rule_result  = rule_based_override(features_df, confidence, top_classes=top3_classes)

    # If a rule fired with high confidence, use it as the final diagnosis
    final_diagnosis = rule_result if rule_result else primary_diagnosis
    replacement_parts = get_replacement_parts(final_diagnosis)
    symptom_parts = get_symptom_specific_parts(detected_symptoms)
    
    # Build individual diagnoses for each detected symptom
    symptom_diagnoses = {}
    for symptom in detected_symptoms:
        symptom_diagnoses[symptom] = SINGLE_SYMPTOM_MAP.get(symptom, "Unknown Issue")

    return DiagnoseResponse(
        success=True,
        mode="ml_model",
        detected_symptoms=detected_symptoms,
        diagnosis=final_diagnosis,
        confidence=confidence,
        replacement_parts=replacement_parts,
        symptom_parts=symptom_parts,
        symptom_diagnoses=symptom_diagnoses,
        top2=top2,
        rule_suggestion=rule_result,
    )