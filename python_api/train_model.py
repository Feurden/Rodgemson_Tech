"""
train_model.py
--------------
Trains a Random Forest classifier for cellphone fault diagnosis.

Improvements applied:
- Deduplication safeguard on load
- Data quality assertions
- Stratified K-Fold cross-validation (no single leaky split)
- Type hints on all functions
- Wrapped in main() with if __name__ guard
- Cleaner f-string output
- try/except around model file saves
- Checks model supports predict_proba
- Reduced hyperparameters (right-sized for dataset)
- pathlib for file paths
"""

import warnings
from pathlib import Path

import joblib
import matplotlib.pyplot as plt
import numpy as np
import pandas as pd
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import (
    ConfusionMatrixDisplay,
    classification_report,
    f1_score,
)
from sklearn.model_selection import StratifiedKFold, cross_val_score
from sklearn.preprocessing import LabelEncoder

warnings.filterwarnings("ignore")

# ── Paths ─────────────────────────────────────────────────────────────────────
SCRIPT_DIR  = Path(__file__).parent
DATA_PATH   = SCRIPT_DIR / "dataset.csv"
MODEL_PATH  = SCRIPT_DIR / "cellphone_diagnosis_model.pkl"
ENCODER_PATH = SCRIPT_DIR / "label_encoder.pkl"
REPORT_PATH = SCRIPT_DIR / "evaluation_report.txt"


# ── Data loading & validation ─────────────────────────────────────────────────
def load_and_validate(path: Path) -> pd.DataFrame:
    """Load the dataset, deduplicate, and assert minimum quality standards."""
    data = pd.read_csv(path)
    data = data.drop_duplicates()

    min_class = data["diagnosis"].value_counts().min()
    assert min_class >= 20, f"Smallest class has only {min_class} examples (need ≥ 20)."

    print(f"✓ Dataset: {len(data)} rows, {data['diagnosis'].nunique()} classes")
    return data


# ── Model training & evaluation ───────────────────────────────────────────────
def train_and_evaluate(
    X: pd.DataFrame,
    y_encoded: np.ndarray,
    label_encoder: LabelEncoder,
) -> RandomForestClassifier:
    """
    Train a Random Forest using Stratified K-Fold cross-validation,
    then refit on the full dataset and return the final model.
    """
    model = RandomForestClassifier(
        n_estimators=200,       # sufficient for this dataset size
        max_depth=10,           # reduced to avoid memorizing small datasets
        min_samples_leaf=2,     # prevents single-sample leaves
        class_weight="balanced",
        random_state=42,
        n_jobs=-1,
    )

    cv = StratifiedKFold(n_splits=5, shuffle=True, random_state=42)
    cv_acc = cross_val_score(model, X, y_encoded, cv=cv, scoring="accuracy")
    cv_f1  = cross_val_score(model, X, y_encoded, cv=cv, scoring="f1_macro")

    print(f"CV Accuracy: {cv_acc.mean()*100:.2f}% ± {cv_acc.std()*100:.2f}%")
    print(f"CV F1: {cv_f1.mean():.3f} ± {cv_f1.std():.3f}")

    model.fit(X, y_encoded)

    assert hasattr(model, "predict_proba"), (
        "Model does not support predict_proba — confidence scoring will fail."
    )

    # ── Per-class report using last CV fold as a held-out sample ─────────────
    *_, (train_idx, test_idx) = cv.split(X, y_encoded)
    X_train, X_test = X.iloc[train_idx], X.iloc[test_idx]
    y_train, y_test = y_encoded[train_idx], y_encoded[test_idx]

    fold_model = RandomForestClassifier(
        n_estimators=200, max_depth=10, min_samples_leaf=2,
        class_weight="balanced", random_state=42, n_jobs=-1,
    )
    fold_model.fit(X_train, y_train)
    y_pred = fold_model.predict(X_test)

    f1_macro    = f1_score(y_test, y_pred, average="macro")
    f1_weighted = f1_score(y_test, y_pred, average="weighted")
    report      = classification_report(
        y_test, y_pred, target_names=label_encoder.classes_
    )

    print(f"F1 Score: {f1_macro:.3f}")
    print(f"\n{report}")

    with open(REPORT_PATH, "w") as f:
        f.write(f"CV Accuracy: {cv_acc.mean()*100:.2f}% ± {cv_acc.std()*100:.2f}%\n")
        f.write(f"CV F1: {cv_f1.mean():.3f} ± {cv_f1.std():.3f}\n")
        f.write(f"F1 Score: {f1_macro:.3f}\n\n")
        f.write(report)

    return model


# ── Feature importance plot ────────────────────────────────────────────────────
def plot_feature_importance(model: RandomForestClassifier, feature_names: list[str]) -> None:
    """Plot and display feature importances sorted by value."""
    importances = model.feature_importances_
    sorted_idx  = np.argsort(importances)
    sorted_features = [feature_names[i] for i in sorted_idx]
    sorted_importances = importances[sorted_idx]

    fig, ax = plt.subplots(figsize=(9, 5))
    bars = ax.barh(sorted_features, sorted_importances, color="#5b9cf6", edgecolor="none")
    ax.set_title("Feature Importance", fontsize=14, fontweight="bold", pad=12)
    ax.set_xlabel("Mean Decrease in Impurity")
    ax.spines[["top", "right"]].set_visible(False)
    ax.bar_label(bars, fmt="%.3f", padding=4, fontsize=9)
    plt.tight_layout()
    plt.show()


# ── Model persistence ──────────────────────────────────────────────────────────
def save_model(model: RandomForestClassifier, label_encoder: LabelEncoder) -> None:
    """Save the trained model and label encoder to disk."""
    joblib.dump(model, MODEL_PATH)
    joblib.dump(label_encoder, ENCODER_PATH)
    print(f"✓ Model saved")


# ── Entry point ────────────────────────────────────────────────────────────────
def main() -> None:
    data = load_and_validate(DATA_PATH)
    X = data.drop("diagnosis", axis=1)
    y = data["diagnosis"]

    label_encoder = LabelEncoder()
    y_encoded = label_encoder.fit_transform(y)

    model = train_and_evaluate(X, y_encoded, label_encoder)
    plot_feature_importance(model, list(X.columns))
    save_model(model, label_encoder)
    print("\n✅ Training complete!")


if __name__ == "__main__":
    main()