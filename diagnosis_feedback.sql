-- Table to store AI diagnosis feedback
CREATE TABLE repair_diagnoses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    job_id VARCHAR(50) UNIQUE NOT NULL,
    device VARCHAR(100),
    customer_description LONGTEXT,
    ai_diagnosis VARCHAR(255),
    ai_confidence FLOAT,
    ai_rule_based BOOLEAN,
    actual_diagnosis VARCHAR(255),
    actual_root_cause LONGTEXT,
    parts_replaced LONGTEXT,
    diagnosis_correct BOOLEAN,
    technician_notes LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    INDEX idx_job_id (job_id),
    INDEX idx_diagnosis_correct (diagnosis_correct),
    INDEX idx_created_at (created_at)
);

-- Query to check accuracy after collecting data:
-- SELECT 
--     COUNT(*) as total_diagnoses,
--     SUM(CASE WHEN diagnosis_correct = 1 THEN 1 ELSE 0 END) as correct_count,
--     ROUND(SUM(CASE WHEN diagnosis_correct = 1 THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as accuracy_percent
-- FROM repair_diagnoses
-- WHERE diagnosis_correct IS NOT NULL;
