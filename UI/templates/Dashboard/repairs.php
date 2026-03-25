<?php
/** @var \App\View\AppView $this */
/** @var array $repairs */
?>
<?= $this->Html->css('repairs') ?>

<div class="page-section">

  <!-- Header -->
  <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
    <div>
      <h1 style="font-size:26px; color:#1e293b; margin:0 0 4px;">🔧 Repair Dashboard</h1>
      <p style="color:#64748b; margin:0;">Overview of cellphone repair jobs</p>
    </div>
    <button class="btn-new-repair" onclick="window.openNewModal && window.openNewModal()">+ New Repair</button>
  </div>

  <!-- Stat Cards -->
  <div style="display:flex; gap:14px; margin-bottom:24px; flex-wrap:wrap;">
    <div class="repair-stat-card" style="border-left:4px solid #38bdf8;">
      <span class="repair-stat-label">Total Jobs</span>
      <span class="repair-stat-value" id="statTotal">0</span>
    </div>
    <div class="repair-stat-card" style="border-left:4px solid #16a34a;">
      <span class="repair-stat-label">Completed</span>
      <span class="repair-stat-value" style="color:#16a34a;" id="statCompleted">0</span>
    </div>
    <div class="repair-stat-card" style="border-left:4px solid #2563eb;">
      <span class="repair-stat-label">In Progress</span>
      <span class="repair-stat-value" style="color:#2563eb;" id="statProgress">0</span>
    </div>
    <div class="repair-stat-card" style="border-left:4px solid #f59e0b;">
      <span class="repair-stat-label">Pending</span>
      <span class="repair-stat-value" style="color:#f59e0b;" id="statPending">0</span>
    </div>
  </div>

  <!-- Filters -->
  <div style="display:flex; gap:10px; margin-bottom:16px; flex-wrap:wrap; align-items:center;">
    <input type="text" placeholder="🔍  Search by job ID, device, or technician..."
      class="modal-input"
      style="flex:1; min-width:220px;"
      oninput="filterTable(this.value)">
    <select onchange="filterStatus(this.value)" class="modal-input" style="width:auto;">
      <option value="all">All Statuses</option>
      <option value="completed">Completed</option>
      <option value="in progress">In Progress</option>
      <option value="pending">Pending</option>
      <option value="waiting parts">Waiting Parts</option>
    </select>
  </div>

  <!-- Table -->
  <div class="table-responsive">
    <table class="dashboard-table">
      <thead>
        <tr>
          <th>Job ID</th>
          <th>Device</th>
          <th>Issue</th>
          <th>Customer</th>
          <th>Technician</th>
          <th>Date</th>
          <th>Status</th>
          <th style="text-align:center;">Actions</th>
        </tr>
      </thead>
      <tbody id="repairsBody"></tbody>
    </table>
  </div>

  <!-- Empty state -->
  <div id="emptyState" style="display:none; text-align:center; padding:60px 20px; color:#94a3b8;">
    <div style="font-size:2.5rem; margin-bottom:10px;">🔍</div>
    <p style="font-size:15px;">No repairs match your search.</p>
  </div>

</div>

<!-- ── NEW REPAIR MODAL ─────────────────────────────────────────────────── -->
<div class="modal-overlay" id="newRepairModal">
  <div class="modal-box" style="width:95%; max-width:600px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
      <h2 style="font-size:1.1rem; color:#1e293b;">+ New Repair Job</h2>
      <button onclick="closeModal('newRepairModal')" style="background:none;border:none;font-size:1.4rem;color:#94a3b8;cursor:pointer;">✕</button>
    </div>

    <div style="background:#f0f9ff; padding:12px; border-radius:6px; border-left:4px solid #0284c7; margin-bottom:14px;">
      <p style="font-size:12px; font-weight:600; color:#0c4a6e; margin:0 0 12px;">👤 Customer Information</p>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
        <div>
          <label class="modal-label">Customer Name</label>
          <input type="text" id="new-customer-name" placeholder="e.g. John Doe" class="modal-input">
        </div>
        <div>
          <label class="modal-label">Contact Number</label>
          <input type="text" id="new-contact-no" placeholder="e.g. 09XX-XXX-XXXX" class="modal-input">
        </div>
      </div>
    </div>

    <p style="font-size:12px; font-weight:600; color:#64748b; margin:12px 0 8px;">📱 Device Information</p>
    <div style="margin-bottom:14px;">
      <label class="modal-label">Device</label>
      <input type="text" id="new-device" placeholder="e.g. iPhone 13" class="modal-input">
    </div>
    <div style="margin-bottom:14px;">
      <label class="modal-label">Issue Description</label>
      <textarea id="new-issue" placeholder="Describe the customer's issue..." class="modal-input" rows="2" style="resize:vertical;"></textarea>
    </div>
    <div style="margin-bottom:14px;">
      <label class="modal-label">Assigned Technician</label>
      <select id="new-technician" class="modal-input">
        <option value="">-- Select Technician --</option>
        <?php foreach ($technicianList ?? [] as $techName): ?>
        <option value="<?= htmlspecialchars($techName) ?>"><?= htmlspecialchars($techName) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <button type="button" onclick="runDiagnosis()"
      style="width:100%;margin-bottom:14px;padding:10px 12px;background:#6366f1;color:white;border:none;border-radius:6px;cursor:pointer;font-weight:600;">
      🤖 Diagnose with AI
    </button>

    <div id="aiResultBox" style="display:none;margin:-16px -16px 14px -16px;padding:16px;background:#ffffff;font-size:13px;border-bottom:1px solid #e2e8f0;"></div>

    <input type="hidden" id="aiDiagnosis" value="">
    <input type="hidden" id="aiSuggestedParts" value="">

    <div style="display:flex; gap:10px;">
      <button onclick="closeModal('newRepairModal')" style="flex:1;padding:10px;border:1px solid #e2e8f0;border-radius:8px;background:white;color:#64748b;font-weight:600;cursor:pointer;">Cancel</button>
      <button onclick="saveNewRepair()" style="flex:1;padding:10px;background:linear-gradient(135deg,#38bdf8,#0284c7);border:none;border-radius:8px;color:white;font-weight:600;cursor:pointer;">Save Repair</button>
    </div>
  </div>
</div>

<!-- ── VIEW MODAL ───────────────────────────────────────────────────────── -->
<div class="modal-overlay" id="viewModal">
  <div class="modal-box" style="width:95%; max-width:700px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
      <h2 style="font-size:1.1rem; color:#1e293b;">🔍 Repair Details</h2>
      <button onclick="closeModal('viewModal')" style="background:none;border:none;font-size:1.4rem;color:#94a3b8;cursor:pointer;">✕</button>
    </div>
    <div id="viewContent"></div>
    <div style="margin-top:20px;">
      <button onclick="closeModal('viewModal')" style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:8px;background:white;color:#64748b;font-weight:600;cursor:pointer;">Close</button>
    </div>
  </div>
</div>

<!-- ── EDIT MODAL ───────────────────────────────────────────────────────── -->
<div class="modal-overlay" id="editModal">
  <div class="modal-box" style="width:95%; max-width:600px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
      <h2 style="font-size:1.1rem; color:#1e293b;">✏️ Edit Repair Job</h2>
      <button onclick="closeModal('editModal')" style="background:none;border:none;font-size:1.4rem;color:#94a3b8;cursor:pointer;">✕</button>
    </div>

    <input type="hidden" id="edit-idx">

    <div style="background:#f0f9ff; border-radius:8px; padding:12px 14px; margin-bottom:16px; border-left:4px solid #0284c7; display:flex; justify-content:space-between; align-items:center;">
      <div>
        <p style="font-size:11px; color:#0284c7; font-weight:700; text-transform:uppercase; margin:0 0 2px;">Device</p>
        <p style="font-size:15px; font-weight:700; color:#1e293b; margin:0;" id="edit-device-display">—</p>
      </div>
      <div style="text-align:right;">
        <p style="font-size:11px; color:#0284c7; font-weight:700; text-transform:uppercase; margin:0 0 2px;">Job ID</p>
        <p style="font-size:15px; font-weight:700; color:#1e293b; margin:0;" id="edit-jobid-display">—</p>
      </div>
    </div>

    <input type="hidden" id="edit-jobid">
    <input type="hidden" id="edit-device">

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
      <div>
        <label class="modal-label">🔧 Technician</label>
        <select id="edit-tech" class="modal-input">
          <option value="">-- Select Technician --</option>
          <?php foreach ($technicianList ?? [] as $techName): ?>
          <option value="<?= htmlspecialchars($techName) ?>"><?= htmlspecialchars($techName) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="modal-label">📋 Status</label>
        <select id="edit-status" class="modal-input">
          <option value="Pending">Pending</option>
          <option value="In Progress">In Progress</option>
          <option value="Completed">Completed</option>
        </select>
      </div>
    </div>

    <div style="margin-bottom:6px;">
      <label class="modal-label">⚠️ Issue Description</label>
      <textarea id="edit-issue" placeholder="Update issue description..." class="modal-input" rows="2" style="resize:vertical;"></textarea>
    </div>
    <button type="button" onclick="runEditDiagnosis()"
      style="width:100%;margin-bottom:14px;padding:9px 12px;background:#6366f1;color:white;border:none;border-radius:6px;cursor:pointer;font-weight:600;font-size:13px;">
      🤖 Re-Diagnose with AI
    </button>
    <div id="editAiResultBox" style="display:none;margin-bottom:14px;padding:12px;background:#f8fafc;border-radius:8px;border-left:4px solid #6366f1;font-size:13px;"></div>
    <input type="hidden" id="edit-ai-diagnosis" value="">
    <input type="hidden" id="edit-ai-parts" value="">

    <div id="editSuggestedServicesWrap" style="display:none; margin-bottom:14px; padding:12px; background:#f8fafc; border-radius:8px; border-left:4px solid #8b5cf6;">
      <p style="font-size:11px; color:#6d28d9; font-weight:700; text-transform:uppercase; margin:0 0 10px;">💻 Suggested Services</p>
      <div id="editSuggestedServicesList" style="display:flex; flex-wrap:wrap; gap:6px;">
        <span style="color:#94a3b8; font-size:13px;">No services suggested</span>
      </div>
    </div>

    <div id="editSuggestedPartsWrap" style="display:none; margin-bottom:14px; padding:12px; background:#f8fafc; border-radius:8px; border-left:4px solid #10b981;">
      <p style="font-size:11px; color:#047857; font-weight:700; text-transform:uppercase; margin:0 0 10px;">🔧 Suggested Physical Parts</p>
      <div id="editSuggestedPartsList" style="display:flex; flex-wrap:wrap; gap:6px;">
        <span style="color:#94a3b8; font-size:13px;">No parts suggested</span>
      </div>
    </div>

    <div style="margin-bottom:14px;">
      <label class="modal-label">✅ Date & Time Finished</label>
      <input type="datetime-local" id="edit-finished" class="modal-input">
    </div>

    <div style="margin-bottom:20px;">
      <label class="modal-label">📝 Notes</label>
      <textarea id="edit-notes" placeholder="Add technician notes..." class="modal-input" rows="3" style="resize:vertical;"></textarea>
    </div>

    <div style="display:flex; gap:10px;">
      <button onclick="closeModal('editModal')" style="flex:1;padding:10px;border:1px solid #e2e8f0;border-radius:8px;background:white;color:#64748b;font-weight:600;cursor:pointer;">Cancel</button>
      <button onclick="saveEdit()" style="flex:1;padding:10px;background:linear-gradient(135deg,#38bdf8,#0284c7);border:none;border-radius:8px;color:white;font-weight:600;cursor:pointer;">Save Changes</button>
    </div>
  </div>
</div>

<!-- ── FEEDBACK MODAL ────────────────────────────────────────────────────── -->
<div class="modal-overlay" id="feedbackModal">
  <div class="modal-box" style="width:95%; max-width:600px; max-height:90vh; overflow-y:auto;">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
      <div>
        <h2 style="font-size:1.1rem; color:#1e293b; margin:0;">📊 Diagnosis Feedback</h2>
        <p style="font-size:11px; color:#94a3b8; margin:2px 0 0;">Help improve the AI by confirming or correcting the diagnosis</p>
      </div>
      <button onclick="closeModal('feedbackModal')" style="background:none;border:none;font-size:1.4rem;color:#94a3b8;cursor:pointer;">✕</button>
    </div>

    <input type="hidden" id="feedback-jobid">
    <input type="hidden" id="feedback-ai-diagnosis">
    <input type="hidden" id="feedback-ai-confidence">

    <div id="feedback-already-submitted" style="display:none; margin-bottom:14px; padding:10px 14px; background:#f0fdf4; border-radius:6px; border-left:4px solid #22c55e;">
      <p style="font-size:12px; color:#166534; margin:0;">✓ <strong>Feedback already submitted</strong> for this job. Submitting again will update the existing record.</p>
    </div>

    <div style="margin-bottom:16px; padding:12px 14px; background:#f0f9ff; border-radius:8px; border-left:4px solid #0284c7;">
      <p style="font-size:11px; font-weight:700; color:#0c4a6e; text-transform:uppercase; letter-spacing:0.5px; margin:0 0 6px;">🤖 AI Diagnosis</p>
      <p style="font-size:15px; font-weight:700; color:#1e293b; margin:0 0 4px;" id="feedback-ai-display">—</p>
      <div id="feedback-confidence-bar-wrap" style="display:none; margin-top:8px;">
        <div style="display:flex; align-items:center; gap:8px;">
          <div style="flex:1; background:#e5e7eb; border-radius:4px; height:5px; overflow:hidden;">
            <div id="feedback-confidence-bar" style="height:100%; border-radius:4px; transition:width 0.4s;"></div>
          </div>
          <span id="feedback-confidence-text" style="font-size:12px; font-weight:700; white-space:nowrap;"></span>
        </div>
        <p id="feedback-confidence-label" style="font-size:11px; color:#64748b; margin:4px 0 0;"></p>
      </div>
    </div>

    <div style="margin-bottom:16px;">
      <label class="modal-label">Was the AI diagnosis correct?</label>
      <div style="display:flex; gap:10px;">
        <button onclick="feedbackCorrect(true)" id="btn-correct"
          style="flex:1; padding:10px; background:#22c55e; color:white; border:2px solid transparent; border-radius:6px; cursor:pointer; font-weight:600; transition:all 0.15s;">
          ✓ Correct
        </button>
        <button onclick="feedbackCorrect(false)" id="btn-incorrect"
          style="flex:1; padding:10px; background:#ef4444; color:white; border:2px solid transparent; border-radius:6px; cursor:pointer; font-weight:600; transition:all 0.15s;">
          ✕ Incorrect
        </button>
      </div>
    </div>

    <div id="feedback-incorrect-section" style="display:none; margin-bottom:16px; padding:14px; background:#fef3c7; border-radius:8px; border-left:4px solid #f59e0b;">
      <p style="font-size:11px; font-weight:700; color:#92400e; text-transform:uppercase; margin:0 0 12px;">⚠ Correction Details</p>

      <label class="modal-label">Actual diagnosis <span style="color:#ef4444;">*</span></label>
      <select id="feedback-actual-diagnosis" class="modal-input" style="background:white;">
        <option value="">— Select the correct diagnosis —</option>
        <option value="Battery Issue">Battery Issue</option>
        <option value="Charging Port Issue">Charging Port Issue</option>
        <option value="Charging IC Issue">Charging IC Issue</option>
        <option value="Display IC Issue">Display IC Issue</option>
        <option value="Touch Controller Issue">Touch Controller Issue</option>
        <option value="Speaker Issue">Speaker Issue</option>
        <option value="Microphone Issue">Microphone Issue</option>
        <option value="Baseband Issue">Baseband Issue</option>
        <option value="Antenna Issue">Antenna Issue</option>
        <option value="Power IC Issue">Power IC Issue</option>
        <option value="Mainboard Issue">Mainboard Issue</option>
        <option value="SIM IC Issue">SIM IC Issue</option>
        <option value="Software/OS Issue">Software/OS Issue</option>
        <option value="Display Issue">Display Issue</option>
        <option value="Water Damage - Inspect All Components">Water Damage</option>
        <option value="Other">Other (specify in notes)</option>
      </select>

      <label class="modal-label" style="margin-top:12px;">Root cause / what you found</label>
      <textarea id="feedback-root-cause" class="modal-input"
        placeholder="Describe what you actually discovered during repair..."
        rows="2" style="resize:vertical;"></textarea>

      <label class="modal-label" style="margin-top:12px;">Parts actually replaced</label>
      <div id="feedback-parts-checklist"
        style="background:white; border:1px solid #e2e8f0; border-radius:6px; padding:10px; max-height:160px; overflow-y:auto; display:grid; grid-template-columns:1fr 1fr; gap:6px;">
        <p style="color:#94a3b8; font-size:12px; grid-column:span 2; margin:0;">Loading parts...</p>
      </div>
    </div>

    <div style="margin-bottom:16px;">
      <label class="modal-label">Technician notes <span style="font-size:11px; color:#94a3b8;">(optional)</span></label>
      <textarea id="feedback-notes" class="modal-input"
        placeholder="Any additional observations about this repair..."
        rows="2" style="resize:vertical;"></textarea>
    </div>

    <div id="feedback-msg" style="display:none; margin-bottom:12px; padding:10px 14px; border-radius:6px; font-size:13px; font-weight:600;"></div>

    <div style="display:flex; gap:10px;">
      <button onclick="closeModal('feedbackModal')"
        style="flex:1;padding:10px;border:1px solid #e2e8f0;border-radius:8px;background:white;color:#64748b;font-weight:600;cursor:pointer;">
        Cancel
      </button>
      <button onclick="saveFeedback()" id="feedback-save-btn"
        style="flex:1;padding:10px;background:linear-gradient(135deg,#38bdf8,#0284c7);border:none;border-radius:8px;color:white;font-weight:600;cursor:pointer;">
        Save Feedback
      </button>
    </div>

  </div>
</div>

<!-- ── PARTS & SERVICES SELECTION MODAL ──────────────────────────────────── -->
<div class="modal-overlay" id="partsModal">
  <div class="modal-box" style="width:95%; max-width:600px;">

    <!-- Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
      <h2 style="font-size:1.1rem; color:#1e293b;">🔧 Parts & Services</h2>
      <button onclick="cancelPartsModal()" style="background:none;border:none;font-size:1.4rem;color:#94a3b8;cursor:pointer;">✕</button>
    </div>
    <p style="font-size:12px; color:#64748b; margin:0 0 14px;">Select parts and services based on AI diagnosis. Stock will be updated automatically.</p>

    <!-- Diagnosis Banner -->
    <div style="background:#eef2ff; border-left:4px solid #6366f1; padding:10px 14px; border-radius:6px; margin-bottom:16px;">
      <p style="font-size:11px; color:#4338ca; font-weight:700; text-transform:uppercase; margin:0 0 2px;">🤖 AI Diagnosis</p>
      <p style="font-size:14px; font-weight:700; color:#1e293b; margin:0;" id="parts-modal-diagnosis">—</p>
    </div>

    <input type="hidden" id="parts-modal-device-id">

    <!-- Tab Switcher -->
    <div style="display:flex; gap:8px; margin-bottom:16px;">
      <button id="pm-tab-parts" onclick="switchPartsModalTab('parts')"
        style="padding:8px 18px; border-radius:6px; font-weight:700; font-size:13px; cursor:pointer; border:none; background:linear-gradient(135deg,#38bdf8,#0284c7); color:white;">
        🔩 Parts
      </button>
      <button id="pm-tab-services" onclick="switchPartsModalTab('services')"
        style="padding:8px 18px; border-radius:6px; font-weight:600; font-size:13px; cursor:pointer; border:1px solid #e2e8f0; background:white; color:#64748b;">
        💻 Services
      </button>
    </div>

    <!-- ── PARTS PANE ── -->
    <div id="pm-pane-parts">
      <div id="parts-modal-loading" style="text-align:center; padding:30px; color:#94a3b8;">
        <p>Loading parts...</p>
      </div>

      <div id="parts-modal-list" style="display:none; max-height:300px; overflow-y:auto; margin-bottom:12px;"></div>

      <div id="parts-modal-empty" style="display:none; text-align:center; padding:20px; color:#94a3b8; font-size:13px;">
        ⚠️ No matching parts found in inventory for this diagnosis.
      </div>

      <!-- Return Parts Section -->
      <div id="return-parts-section" style="display:none; margin-bottom:12px; padding:12px; background:#fef3c7; border-radius:8px; border-left:4px solid #f59e0b;">
        <p style="font-size:12px; font-weight:700; color:#92400e; margin:0 0 10px;">↩️ Parts Currently In Use — Return to Stock?</p>
        <p style="font-size:11px; color:#b45309; margin:0 0 10px;">Tick the parts you want to return. You'll be asked about the job status after confirming.</p>
        <div id="return-parts-list"></div>
      </div>
    </div>

    <!-- ── SERVICES PANE ── -->
    <div id="pm-pane-services" style="display:none;">
      <div id="services-modal-loading" style="text-align:center; padding:30px; color:#94a3b8;">
        <p>Loading services...</p>
      </div>

      <div id="services-modal-list" style="display:none; max-height:320px; overflow-y:auto; margin-bottom:12px;"></div>

      <div id="services-modal-empty" style="display:none; text-align:center; padding:20px; color:#94a3b8; font-size:13px; background:#f8f7ff; border-radius:8px; border:2px dashed #c7d2fe;">
        <div style="font-size:32px; margin-bottom:8px;">💻</div>
        <p style="margin:0; font-weight:600;">No services mapped for this diagnosis.</p>
        <p style="margin:6px 0 0; font-size:12px;">You can add services manually from the Services module.</p>
      </div>
    </div>

    <!-- Actions -->
    <div style="display:flex; gap:10px; margin-top:4px;">
      <button onclick="cancelPartsModal()"
        style="flex:1;padding:10px;border:1px solid #e2e8f0;border-radius:8px;background:white;color:#64748b;font-weight:600;cursor:pointer;">
        Cancel
      </button>
      <button onclick="confirmPartsSelection()" id="parts-confirm-btn"
        style="flex:1;padding:10px;background:linear-gradient(135deg,#38bdf8,#0284c7);border:none;border-radius:8px;color:white;font-weight:600;cursor:pointer;">
        ✓ Confirm & Save
      </button>
    </div>
  </div>
</div>

<!-- ── Config + JS ───────────────────────────────────────────────────────── -->
<script>
let repairs = <?= json_encode($repairs ?? []) ?>;

window.REPAIRS_CONFIG = {
  addUrl               : '<?= $this->Url->build('/devices/add') ?>',
  updateUrl            : '<?= $this->Url->build('/devices/update') ?>',
  diagnoseUrl          : '<?= $this->Url->build('/ai/diagnose') ?>',
  feedbackUrl          : '<?= $this->Url->build('/ai/saveFeedback') ?>',
  checkFeedbackUrl     : '<?= $this->Url->build('/ai/checkFeedback') ?>',
  partsGetUrl          : '<?= $this->Url->build('/parts-usage/get-by-names') ?>',
  partsGetByDiagUrl    : '<?= $this->Url->build('/parts-usage/get-by-diagnosis') ?>',
  partsDeductUrl       : '<?= $this->Url->build('/parts-usage/deduct') ?>',
  partsReturnUrl       : '<?= $this->Url->build('/parts-usage/return') ?>',
  partsGetUsedUrl      : '<?= $this->Url->build('/parts-usage/get-used') ?>',
  servicesGetByDiagUrl : '<?= $this->Url->build('/repair-services-usage/get-by-diagnosis') ?>',
  servicesGetUsedUrl   : '<?= $this->Url->build('/repair-services-usage/get-used') ?>',
  servicesAddUrl       : '<?= $this->Url->build('/repair-services-usage/add') ?>',
};
</script>
<?= $this->Html->script('repairs') ?>