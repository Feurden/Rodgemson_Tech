"""
main.py - UPGRADED WITH NLP v3 (Physical Parts + Services Separation)
---------------------------------------------------------------------
Fixed for Windows Unicode issues.

Changes from v2:
- Separated physical parts from services
- Added SERVICE_MAP for software, cleaning, and labor services
- Updated DiagnoseResponse to include symptom_services
- Fixed validation error for missing symptom_services field
"""

from contextlib import asynccontextmanager
from pathlib import Path
from typing import Optional, List, Tuple
from dataclasses import dataclass
import joblib
import numpy as np
import pandas as pd
import re

from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel

# NLP Libraries
try:
    from sentence_transformers import SentenceTransformer, util
except ImportError:
    print("[!] Install: pip install sentence-transformers")

BASE_DIR = Path(__file__).parent
MODEL_PATH = BASE_DIR / "models" / "cellphone_diagnosis_model.pkl"
ENCODER_PATH = BASE_DIR / "models" / "label_encoder.pkl"

model = None
label_encoder = None
nlp_model = None
symptom_embeddings = None

# ============================================================================
# SYMPTOM DEFINITIONS
# ============================================================================

SYMPTOMS = [
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
    "screen_physically_damaged",
    "battery_issue_natural",
]

SYMPTOM_DESCRIPTIONS = {
    "not_charging": [
        "not charging", "phone not charging", "won't charge", "not charging at all",
        "slow charging", "charging very slowly", "charges slower than normal",
        "usb port not accepting power", "charging port is loose or broken",
        "charger plug not detected by phone", "phone shows no charging indicator",
        "charging stops and starts by itself", "cable connected but battery percentage not rising",
        "phone only charges with certain angle", "charging port physically damaged",
        "battery charges at 5 watts instead of fast charge",
    ],
    "overheating": [
        "overheating", "phone overheating", "phone is hot", "phone getting too hot",
        "device overheating", "phone body gets extremely hot", "back of phone is burning hot to touch",
        "thermal throttling due to high temperature", "device temperature warning appears",
        "unusually hot near the battery area", "hot to the touch near charging port",
        "overheating during calls or gaming", "overheating while charging",
        "overheating even when not in use",
    ],
    "no_signal": [
        "no signal", "no mobile signal", "no network", "no reception", "SIM not detected",
        "no cellular bars showing", "SIM card not detected", "emergency calls only mode",
        "carrier name not showing in status bar", "cellular network unavailable",
        "calls dropping due to no reception", "mobile data completely unavailable",
    ],
    "battery_drains_fast": [
        "battery drains fast", "battery draining fast", "battery dies quickly",
        "battery drains quickly", "battery percentage drops fast",
        "battery percentage falling rapidly while idle", "full charge only lasts one to two hours",
        "battery depletes faster than normal usage", "phone loses 20 percent charge per hour",
        "battery draining in standby mode", "charge level drops even when screen is off",
    ],
    "stuck_on_logo": [
        "stuck on logo", "phone stuck on boot screen", "bootloop", "boot loop",
        "keeps restarting", "stuck on startup", "phone stuck on manufacturer boot logo",
        "bootloop cycling through startup repeatedly", "phone restarts and never reaches home screen",
        "frozen at splash screen on power on", "device loops endlessly during boot sequence",
        "cannot get past the startup animation",
    ],
    "screen_black": [
        "screen black", "black screen", "screen not turning on", "display not working",
        "screen is dark", "screen won't turn on", "display shows nothing but is powered on",
        "backlight not turning on", "screen completely unlit and unresponsive to power button",
        "no visual output on display", "screen stays dark after pressing power",
        "screen very dim and barely visible", "display too dark even at max brightness",
        "dim screen", "black screen of death", "screen completely dead and shows nothing",
    ],
    "touch_not_working": [
        "touch not working", "touchscreen not working", "screen not responding to touch",
        "touch unresponsive", "ghost touch", "finger taps not registering on glass",
        "touchscreen digitizer unresponsive", "swipe gestures not detected",
        "phantom touches appearing by themselves", "ghost touching by itself",
        "screen touches itself randomly", "touch input delayed or inaccurate",
        "screen does not respond to finger press",
    ],
    "speaker_no_sound": [
        "speaker not working", "no sound from speaker", "speaker no sound", "no audio",
        "can't hear anything", "earpiece not working", "ear speaker no sound",
        "loudspeaker grille producing no audio", "ringtone plays silently through bottom speaker",
        "speakerphone mode has zero output volume", "music and videos have no sound from speaker",
        "external speaker blown or dead", "notification sounds not coming from speaker",
        "phone speaker completely silent", "bottom speaker stopped working",
        "speaker stopped producing any sound", "cannot hear caller through earpiece",
        "call audio not audible through earpiece",
    ],
    "mic_not_work": [
        "microphone not working", "mic not working", "mic issue", "microphone issue",
        "people can't hear me", "caller can't hear me", "caller on other end cannot hear my voice",
        "microphone not picking up speech", "voice recordings are completely silent",
        "mic input completely dead during calls", "voice memos record nothing but silence",
        "other party says they hear nothing",
    ],
    "screen_flickering": [
        "screen flickering", "screen flashing", "display flickering", "lines on screen",
        "screen has lines", "screen glitching", "display flashing on and off rapidly",
        "LCD backlight strobing or pulsing", "horizontal lines appearing across display",
        "vertical lines on the screen", "colored lines running down the LCD",
        "green or pink lines across display", "screen brightness fluctuating by itself",
        "visual glitches and artifacts on screen", "display unstable and flickering during use",
        "LCD showing colored lines or streaks",
    ],
    "wifi_not_working": [
        "wifi not working", "no wifi", "can't connect to wifi", "wifi issue", "wifi problem",
        "wifi keeps disconnecting", "wifi toggle not finding any networks",
        "cannot join any wireless access point", "wifi connects then immediately drops",
        "wireless router visible but authentication fails", "wifi symbol with exclamation showing",
        "internet unavailable despite wifi being on",
    ],
    "bluetooth_issue": [
        "bluetooth not working", "bluetooth issue", "bluetooth problem", "can't pair bluetooth",
        "bluetooth not connecting", "bluetooth pairing with other devices fails",
        "paired headphones not connecting via bluetooth", "bluetooth toggle not discovering nearby devices",
        "bluetooth connection drops repeatedly", "cannot send files over bluetooth",
    ],
    "phone_freezing": [
        "phone freezing", "phone frozen", "phone keeps freezing", "phone lagging", "phone hanging",
        "phone keeps restarting", "phone restarts randomly", "phone completely unresponsive to any input",
        "app crashes and brings down entire system", "UI stutters and becomes permanently frozen",
        "touch and buttons stop responding mid-use", "forced reboot required due to system hang",
        "home screen freezes and will not animate", "phone is very slow and laggy",
        "phone hangs and becomes unresponsive", "device hangs randomly",
        "phone randomly restarts by itself unrelated to heat", "phone freezes and needs a manual restart",
        "screen becomes unresponsive and stuck",
    ],
    "water_damage": [
        "water damage", "phone got wet", "dropped in water", "liquid damage", "phone fell in water",
        "phone submerged in water or liquid", "liquid got inside the device",
        "corrosion visible on charging port or SIM tray", "moisture indicator inside phone triggered",
        "phone dropped in sink, toilet, or puddle", "internal components wet from rain or spill",
    ],
    "screen_physically_damaged": [
        "cracked screen", "broken screen", "shattered screen", "screen cracked", "screen broken",
        "black spots on screen", "glass cracked or shattered from drop",
        "dark spots spreading across LCD panel", "green or purple lines burned into display",
        "dead pixel cluster visible on screen", "half of screen permanently blacked out",
        "screen discoloration from pressure damage", "LCD bleed visible as bright patches on edges",
    ],
    "battery_issue_natural": [
        "battery swollen", "swollen battery", "battery bulging", "battery problem",
        "battery health low", "phone shuts off randomly", "battery swollen or bulging physically",
        "phone shuts down unexpectedly at 30 percent", "battery health degraded below 80 percent",
        "phone powers off randomly without warning", "battery no longer holds any meaningful charge",
        "device dies after just a few hours unplugged",
    ],
}

SINGLE_SYMPTOM_MAP = {
    "not_charging": "Charging Port Issue",
    "overheating": "Power IC Issue",
    "no_signal": "Baseband Issue",
    "battery_drains_fast": "Battery Issue",
    "stuck_on_logo": "Software/OS Issue",
    "screen_black": "Display IC Issue",
    "touch_not_working": "Touch Controller Issue",
    "speaker_no_sound": "Speaker Issue",
    "mic_not_work": "Microphone Issue",
    "screen_flickering": "Display IC Issue",
    "wifi_not_working": "Antenna Issue",
    "bluetooth_issue": "Baseband Issue",
    "phone_freezing": "Software/OS Issue",
    "water_damage": "Water Damage - Inspect All Components",
    "screen_physically_damaged": "Display Issue",
    "battery_issue_natural": "Battery Issue",
}

# PHYSICAL PARTS ONLY (for inventory deduction)
REPLACEMENT_MAP = {
    "Touch Controller Issue": [
        "Touch Controller IC", "Digitizer", "Touch Flex Cable", "LCD Screen Assembly", "Power Management IC"
    ],
    "Speaker Issue": [
        "Speaker Module", "Ear Speaker", "Audio IC", "Speaker Flex Cable", "Audio Codec IC"
    ],
    "Microphone Issue": [
        "Microphone Module", "Audio IC", "Charging Flex Cable", "Sub Board", "Microphone Mesh"
    ],
    "Battery Issue": [
        "Battery", "Battery Connector", "Power IC", "Charging IC", "Charging Flex Cable"
    ],
    "Display IC Issue": [
        "Display Driver IC", "Backlight IC", "LCD Screen", "Display Flex Cable", "GPU IC"
    ],
    "Charging Port Issue": [
        "Charging Port", "USB Connector", "Charging Flex Cable", "Charging IC", "Power IC"
    ],
    "Power IC Issue": [
        "Power Management IC", "Battery Connector", "Charging IC"
    ],
    "Baseband Issue": [
        "Baseband IC", "RF IC", "Antenna Module", "SIM IC"
    ],
    "Antenna Issue": [
        "Antenna Cable", "Antenna Module", "RF IC", "Signal Booster IC"
    ],
    "Software/OS Issue": [],  # No physical parts for software issues
    "Water Damage - Inspect All Components": [
        "Connector Replacement", "Battery Replacement"
    ],
    "Display Issue": [
        "LCD/OLED Screen Assembly", "Front Glass Digitizer", "Screen Frame", "Adhesive Seal Kit"
    ]
}

# SERVICES MAP (software, cleaning, labor - not deducted from inventory)
SERVICE_MAP = {
    "Software/OS Issue": [
        "Firmware Reinstall", "OS Update", "Factory Reset", "System Reflash"
    ],
    "Water Damage - Inspect All Components": [
        "Ultrasonic Cleaning", "Mainboard Cleaning", "Full Diagnostic Test"
    ],
    "Display Issue": [
        "Screen Replacement Service"
    ],
    "Power IC Issue": [
        "Mainboard Repair"
    ]
}

# ============================================================================
# NLP TEXT PROCESSING
# ============================================================================

DEFAULT_NLP_THRESHOLD = 0.62

SYMPTOM_THRESHOLDS = {
    "phone_freezing": 0.68,
    "battery_drains_fast": 0.65,
    "battery_issue_natural": 0.65,
}

def split_input_segments(text: str) -> List[str]:
    """
    Split user input into individual symptom segments.
    """
    parts = re.split(r",|;|\band\b|\bor\b", text, flags=re.IGNORECASE)
    segments = [p.strip() for p in parts if p.strip()]
    if len(segments) > 1:
        segments.append(text)
    return segments if segments else [text]

def detect_symptoms_nlp(
    user_text: str,
    threshold: float = DEFAULT_NLP_THRESHOLD
) -> Tuple[List[str], List[float]]:
    """
    Detect symptoms using NLP semantic similarity.
    """
    if nlp_model is None or symptom_embeddings is None:
        raise RuntimeError("Models not loaded — did startup complete?")

    segments = split_input_segments(user_text)
    segment_embeddings = nlp_model.encode(segments, convert_to_numpy=True)

    detected_symptoms = []
    best_scores: dict = {}

    for symptom_id in SYMPTOMS:
        effective_threshold = SYMPTOM_THRESHOLDS.get(symptom_id, threshold)
        cached_embeddings = symptom_embeddings[symptom_id]

        max_sim = 0.0
        for seg_emb in segment_embeddings:
            sims = [util.pytorch_cos_sim(seg_emb, emb)[0][0].item() for emb in cached_embeddings]
            max_sim = max(max_sim, max(sims))

        best_scores[symptom_id] = max_sim
        if max_sim >= effective_threshold:
            detected_symptoms.append(symptom_id)

    scores = [best_scores[s] for s in detected_symptoms]
    return detected_symptoms, scores

# ============================================================================
# FASTAPI SETUP
# ============================================================================

@asynccontextmanager
async def lifespan(app: FastAPI):
    """Load models on startup"""
    global model, label_encoder, nlp_model, symptom_embeddings
    
    print("[*] Loading ML model...")
    model = joblib.load(MODEL_PATH)
    label_encoder = joblib.load(ENCODER_PATH)
    print("[OK] ML model loaded")
    
    print("[*] Loading NLP model (first time takes 30-60 seconds)...")
    try:
        nlp_model = SentenceTransformer("sentence-transformers/all-MiniLM-L6-v2")
        print("[OK] NLP model loaded")
    except Exception as e:
        print(f"[ERROR] Failed to load NLP model: {e}")
        print("Install: pip install sentence-transformers torch")
        raise

    print("[*] Pre-computing symptom embeddings...")
    symptom_embeddings = {
        symptom_id: nlp_model.encode(
            SYMPTOM_DESCRIPTIONS[symptom_id],
            convert_to_numpy=True
        )
        for symptom_id in SYMPTOMS
    }
    print(f"[OK] {len(symptom_embeddings)} symptom embeddings cached")
    
    yield
    
    # Cleanup
    model = None
    label_encoder = None
    nlp_model = None
    symptom_embeddings = None


app = FastAPI(
    title="Phone Diagnosis API - NLP Enhanced",
    description="Uses NLP for better symptom detection",
    lifespan=lifespan
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"]
)


class DiagnoseRequest(BaseModel):
    description: str


class DiagnoseResponse(BaseModel):
    success: bool
    mode: str
    detected_symptoms: list[str]
    diagnosis: str
    confidence: Optional[float]
    replacement_parts: list[str]
    symptom_diagnoses: dict
    symptom_parts: dict
    symptom_services: dict = {}  # Services (software, cleaning, labor)


@app.post("/diagnose", response_model=DiagnoseResponse)
def diagnose(request: DiagnoseRequest):
    """
    MAIN DIAGNOSIS ENDPOINT
    
    Returns both physical parts (for inventory) and services (for billing)
    """
    
    text = request.description.strip()
    
    # Detect symptoms using NLP
    detected_symptoms, symptom_scores = detect_symptoms_nlp(text, threshold=DEFAULT_NLP_THRESHOLD)
    
    if not detected_symptoms:
        return DiagnoseResponse(
            success=True,
            mode="no_symptoms",
            detected_symptoms=[],
            diagnosis="Unknown Issue",
            confidence=None,
            replacement_parts=[],
            symptom_diagnoses={},
            symptom_parts={},
            symptom_services={}
        )
    
    # Map symptoms to diagnoses
    symptom_diagnoses = {}
    for symptom in detected_symptoms:
        symptom_diagnoses[symptom] = SINGLE_SYMPTOM_MAP.get(symptom, "Unknown Issue")
    
    unique_diagnoses = list(set(symptom_diagnoses.values()))
    
    if len(unique_diagnoses) == 1:
        final_diagnosis = unique_diagnoses[0]
        mode = "single_symptom"
    else:
        final_diagnosis = " + ".join(sorted(unique_diagnoses))
        mode = "combined_symptoms"
    
    # Collect physical parts and services
    symptom_parts = {}
    symptom_services = {}
    all_parts = []
    all_services = []
    
    for symptom in detected_symptoms:
        diagnosis = symptom_diagnoses.get(symptom)
        
        # Get physical parts (for inventory deduction)
        parts = REPLACEMENT_MAP.get(diagnosis, [])
        symptom_parts[symptom] = parts
        all_parts.extend(parts)
        
        # Get services (software, cleaning, labor - not deducted from inventory)
        services = SERVICE_MAP.get(diagnosis, [])
        symptom_services[symptom] = services
        all_services.extend(services)
    
    replacement_parts = list(dict.fromkeys(all_parts))
    suggested_services = list(dict.fromkeys(all_services))
    
    # ML confidence scoring
    confidence = None
    try:
        feature_vector = pd.DataFrame([
            {s: (1 if s in detected_symptoms else 0) for s in SYMPTOMS}
        ])
        proba = model.predict_proba(feature_vector)[0]
        predicted_idx = int(proba.argmax())
        predicted_label = label_encoder.inverse_transform([predicted_idx])[0]
        confidence = round(float(proba[predicted_idx]), 4)
        
        if confidence >= 0.60 and predicted_label != final_diagnosis:
            final_diagnosis = predicted_label
            mode = "ml_override"
    except Exception as e:
        print(f"[WARNING] ML confidence scoring failed: {e}")
        confidence = None
    
    return DiagnoseResponse(
        success=True,
        mode=mode,
        detected_symptoms=detected_symptoms,
        diagnosis=final_diagnosis,
        confidence=confidence,
        replacement_parts=replacement_parts,
        symptom_diagnoses=symptom_diagnoses,
        symptom_parts=symptom_parts,
        symptom_services=symptom_services
    )


@app.get("/health")
def health():
    """Health check endpoint"""
    return {
        "status": "healthy",
        "model_loaded": model is not None,
        "nlp_loaded": nlp_model is not None
    }


@app.get("/")
def root():
    """API info"""
    return {
        "name": "Phone Diagnosis API",
        "version": "3.0",
        "mode": "NLP-Enhanced with Parts/Services Separation",
        "endpoints": {
            "POST /diagnose": "Main diagnosis endpoint - returns parts and services",
            "GET /health": "Health check"
        },
        "description": "Separates physical parts (for inventory) from services (for billing)"
    }