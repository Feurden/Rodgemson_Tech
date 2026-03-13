from contextlib import asynccontextmanager
from pathlib import Path
from typing import Optional

import joblib
import numpy as np
import pandas as pd
import re

from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel


BASE_DIR = Path(__file__).parent

MODEL_PATH = BASE_DIR / "models" / "cellphone_diagnosis_model.pkl"
ENCODER_PATH = BASE_DIR / "models" / "label_encoder.pkl"


model = None
label_encoder = None


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
"water_damage"

]


KEYWORD_MAP = {

# =================
# CHARGING
# =================

"not charging":"not_charging",
"cant charge":"not_charging",
"won't charge":"not_charging",
"charging problem":"not_charging",
"charging issue":"not_charging",
"charging port loose":"not_charging",
"charger not detected":"not_charging",
"usb not working":"not_charging",
"charging intermittent":"not_charging",

# =================
# OVERHEATING
# =================

"overheating":"overheating",
"overheat":"overheating",
"gets hot":"overheating",
"too hot":"overheating",
"heating issue":"overheating",
"phone heating":"overheating",

# =================
# SIGNAL
# =================

"no signal":"no_signal",
"no network":"no_signal",
"no service":"no_signal",
"emergency calls only":"no_signal",
"no sim signal":"no_signal",
"weak signal":"no_signal",
"signal dropping":"no_signal",

# =================
# BATTERY
# =================

"battery draining":"battery_drains_fast",
"battery dies fast":"battery_drains_fast",
"battery drain":"battery_drains_fast",
"low battery fast":"battery_drains_fast",
"battery problem":"battery_drains_fast",
"battery drops fast":"battery_drains_fast",

# =================
# BOOT
# =================

"stuck on logo":"stuck_on_logo",
"boot loop":"stuck_on_logo",
"bootloop":"stuck_on_logo",
"not booting":"stuck_on_logo",
"boot problem":"stuck_on_logo",

# =================
# DISPLAY
# =================

"black screen":"screen_black",
"no display":"screen_black",
"display not working":"screen_black",
"screen dead":"screen_black",
"no lcd":"screen_black",

# =================
# TOUCH
# =================

"touch not working":"touch_not_working",
"touchscreen not working":"touch_not_working",
"touch not responding":"touch_not_working",
"screen not responding":"touch_not_working",
"ghost touch":"touch_not_working",
"touch delay":"touch_not_working",
"touch lag":"touch_not_working",
"touch problem":"touch_not_working",
"touch issue":"touch_not_working",
"unresponsive touch":"touch_not_working",
"touch malfunction":"touch_not_working",

# =================
# SPEAKER
# =================

"speaker not working":"speaker_no_sound",
"no sound":"speaker_no_sound",
"no speaker sound":"speaker_no_sound",
"speaker no sound":"speaker_no_sound",
"no audio":"speaker_no_sound",
"speaker dead":"speaker_no_sound",
"sound not working":"speaker_no_sound",
"no ringtone":"speaker_no_sound",
"media no sound":"speaker_no_sound",
"video no sound":"speaker_no_sound",

# =================
# MICROPHONE
# =================

"mic not working":"mic_not_work",
"no mic":"mic_not_work",
"microphone not working":"mic_not_work",
"no voice":"mic_not_work",
"caller can't hear":"mic_not_work",
"no recording sound":"mic_not_work",

# =================
# FLICKER
# =================

"screen flickering":"screen_flickering",
"screen blinking":"screen_flickering",
"display flicker":"screen_flickering",

# =================
# WIFI
# =================

"wifi not working":"wifi_not_working",
"no wifi":"wifi_not_working",
"wifi disconnecting":"wifi_not_working",
"wifi problem":"wifi_not_working",
"cant connect wifi":"wifi_not_working",

# =================
# BLUETOOTH
# =================

"bluetooth not working":"bluetooth_issue",
"bluetooth problem":"bluetooth_issue",
"cant connect bluetooth":"bluetooth_issue",
"bluetooth disconnect":"bluetooth_issue",

# =================
# FREEZE
# =================

"phone freezing":"phone_freezing",
"phone lag":"phone_freezing",
"phone slow":"phone_freezing",
"random restart":"phone_freezing",
"auto restart":"phone_freezing",
"system crash":"phone_freezing",

# =================
# WATER
# =================

"water damage":"water_damage",
"wet phone":"water_damage",
"dropped in water":"water_damage",
"liquid damage":"water_damage",
"phone got wet":"water_damage"

}
SINGLE_SYMPTOM_MAP = {

    # Charging
    "not_charging":"Charging Port Issue",
    # Heat
    "overheating":"Power IC Issue",
    # Signal
    "no_signal":"Baseband Issue",
    # Battery
    "battery_drains_fast":"Battery Issue",
    # Boot
    "stuck_on_logo":"Software/OS Issue",
    # Display
    "screen_black":"Display IC Issue",
    # Touch
    "touch_not_working":"Touch Controller Issue",
    # Speaker
    "speaker_no_sound":"Speaker Issue",
    # Mic
    "mic_not_work":"Microphone Issue",
    # Flicker
    "screen_flickering":"Display IC Issue",
    # WiFi
    "wifi_not_working":"Antenna Issue",
    # Bluetooth
    "bluetooth_issue":"Baseband Issue",
    # Freeze
    "phone_freezing":"Software/OS Issue",
    # Water
    "water_damage":"Water Damage - Inspect All Components"
}


REPLACEMENT_MAP = {

    # =================
    # TOUCH
    # =================

    "Touch Controller Issue":[

    "Touch Controller IC",
    "Digitizer",
    "Touch Flex Cable",
    "LCD Screen Assembly",
    "Power Management IC"

    ],


    # =================
    # SPEAKER
    # =================

    "Speaker Issue":[

    "Speaker Module",
    "Ear Speaker",
    "Audio IC",
    "Speaker Flex Cable",
    "Audio Codec IC"

    ],


    # =================
    # MICROPHONE
    # =================

    "Microphone Issue":[

    "Microphone Module",
    "Audio IC",
    "Charging Flex Cable",
    "Sub Board",
    "Microphone Mesh"

    ],


    # =================
    # BATTERY
    # =================

    "Battery Issue":[

    "Battery",
    "Battery Connector",
    "Power IC",
    "Charging IC",
    "Charging Flex Cable"

    ],


    # =================
    # DISPLAY
    # =================

    "Display IC Issue":[

    "Display Driver IC",
    "Backlight IC",
    "LCD Screen",
    "Display Flex Cable",
    "GPU IC"

    ],


    # =================
    # CHARGING
    # =================

    "Charging Port Issue":[

    "Charging Port",
    "USB Connector",
    "Charging Flex Cable",
    "Charging IC",
    "Power IC"

    ],


    # =================
    # POWER IC
    # =================

    "Power IC Issue":[

    "Power Management IC",
    "Battery Connector",
    "Charging IC",
    "Mainboard Repair"

    ],


    # =================
    # BASEBAND
    # =================

    "Baseband Issue":[

    "Baseband IC",
    "RF IC",
    "Antenna Module",
    "SIM IC"

    ],


    # =================
    # ANTENNA
    # =================

    "Antenna Issue":[

    "Antenna Cable",
    "Antenna Module",
    "RF IC",
    "Signal Booster IC"

    ],


    # =================
    # SOFTWARE
    # =================

    "Software/OS Issue":[

    "Firmware Reinstall",
    "OS Update",
    "Factory Reset",
    "System Reflash"

    ],


    # =================
    # WATER
    # =================

    "Water Damage - Inspect All Components":[

    "Ultrasonic Cleaning",
    "Mainboard Cleaning",
    "Connector Replacement",
    "Battery Replacement",
    "Full Diagnostic Test"
    ]
}
def clean_text(text):

    text=text.lower()

    text=re.sub(r"[^\w\s]","",text)

    return text


def text_to_features(user_text):

    user_text=clean_text(user_text)

    features={s:0 for s in SYMPTOMS}

    detected=0


    for keyword,symptom in KEYWORD_MAP.items():

        words=keyword.split()

        if all(word in user_text.split() for word in words):

            if features[symptom]==0:

                features[symptom]=1

                detected+=1


    detected_names=[s for s in SYMPTOMS if features[s]==1]

    return pd.DataFrame([features]),detected,detected_names


def get_replacement_parts(diagnosis):

    return REPLACEMENT_MAP.get(diagnosis,[])


@asynccontextmanager
async def lifespan(app:FastAPI):

    global model,label_encoder

    model=joblib.load(MODEL_PATH)

    label_encoder=joblib.load(ENCODER_PATH)

    yield


app=FastAPI(

title="Diagnosis API",

lifespan=lifespan

)


app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"]

)


class DiagnoseRequest(BaseModel):
    description:str


class DiagnoseResponse(BaseModel):

    success: bool

    mode: str

    detected_symptoms: list[str]

    diagnosis: str

    confidence: Optional[float]

    replacement_parts: list[str]

    symptom_diagnoses: dict

    symptom_parts: dict

@app.post("/diagnose", response_model=DiagnoseResponse)
def diagnose(request: DiagnoseRequest):

    text = request.description.lower().strip()

    detected_symptoms = []

    for keyword, symptom in KEYWORD_MAP.items():

        if keyword in text:

            if symptom not in detected_symptoms:

                detected_symptoms.append(symptom)


    if not detected_symptoms:

        return DiagnoseResponse(

            success=True,

            mode="no_symptoms",

            detected_symptoms=[],

            diagnosis="Unknown Issue",

            confidence=None,

            replacement_parts=[],

            symptom_diagnoses={},

            symptom_parts={}

        )


    symptom_diagnoses = {}

    for symptom in detected_symptoms:

        symptom_diagnoses[symptom] = SINGLE_SYMPTOM_MAP.get(

            symptom,

            "Unknown Issue"

        )


    unique_diagnoses = list(

        set(symptom_diagnoses.values())

    )


    if len(unique_diagnoses) == 1:

        final_diagnosis = unique_diagnoses[0]

        mode = "single_symptom"

    else:

        final_diagnosis = " + ".join(

            sorted(unique_diagnoses)

        )

        mode = "combined_symptoms"


    symptom_parts = {}

    all_parts = []

    for symptom in detected_symptoms:

        diagnosis = symptom_diagnoses.get(

            symptom

        )

        parts = REPLACEMENT_MAP.get(

            diagnosis,

            []

        )

        symptom_parts[symptom] = parts

        all_parts.extend(parts)


    replacement_parts = list(

        dict.fromkeys(all_parts)

    )


    # ML optional (safe)
    confidence = None


    return DiagnoseResponse(

        success=True,

        mode=mode,

        detected_symptoms=detected_symptoms,

        diagnosis=final_diagnosis,

        confidence=confidence,

        replacement_parts=replacement_parts,

        symptom_diagnoses=symptom_diagnoses,

        symptom_parts=symptom_parts

    )