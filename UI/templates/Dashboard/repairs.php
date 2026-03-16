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

    <!-- Customer Info -->
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

    <!-- Device Info -->
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
        <option value="Rod">Rod</option>
        <option value="Rodel">Rodel</option>
        <option value="Raymark">Raymark</option>
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

    <!-- Read-only banner -->
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

    <!-- Technician + Status -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
      <div>
        <label class="modal-label">🔧 Technician</label>
        <select id="edit-tech" class="modal-input">
          <option value="">-- Select Technician --</option>
          <option value="Rod">Rod</option>
          <option value="Rodel">Rodel</option>
          <option value="Raymark">Raymark</option>
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

    <!-- Issue + Re-diagnose -->
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

    <!-- Date Finished -->
    <div style="margin-bottom:14px;">
      <label class="modal-label">✅ Date & Time Finished</label>
      <input type="datetime-local" id="edit-finished" class="modal-input">
    </div>

    <!-- Notes -->
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
  <div class="modal-box" style="width:95%; max-width:600px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
      <h2 style="font-size:1.1rem; color:#1e293b;">📊 Diagnosis Feedback</h2>
      <button onclick="closeModal('feedbackModal')" style="background:none;border:none;font-size:1.4rem;color:#94a3b8;cursor:pointer;">✕</button>
    </div>

    <input type="hidden" id="feedback-jobid">
    <input type="hidden" id="feedback-ai-diagnosis">

    <div style="margin-bottom:14px; padding:10px; background:#f0f9ff; border-radius:6px; border-left:4px solid #0284c7;">
      <p style="font-size:12px; color:#0c4a6e; margin:0;"><strong>AI Suggested:</strong> <span id="feedback-ai-display"></span></p>
    </div>

    <div style="margin-bottom:14px;">
      <label class="modal-label">Was the AI diagnosis correct?</label>
      <div style="display:flex; gap:10px;">
        <button onclick="feedbackCorrect(true)" id="btn-correct" style="flex:1; padding:10px; background:#22c55e; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:600;">✓ Correct</button>
        <button onclick="feedbackCorrect(false)" id="btn-incorrect" style="flex:1; padding:10px; background:#ef4444; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:600;">✕ Incorrect</button>
      </div>
    </div>

    <div id="feedback-incorrect-section" style="display:none; margin-bottom:14px; padding:12px; background:#fef3c7; border-radius:6px; border-left:4px solid #f59e0b;">
      <label class="modal-label">What was the actual diagnosis?</label>
      <input type="text" id="feedback-actual-diagnosis" class="modal-input" placeholder="e.g. Charging IC Issue">
      <label class="modal-label" style="margin-top:12px;">Root cause / what you found:</label>
      <textarea id="feedback-root-cause" class="modal-input" placeholder="Describe what you actually discovered during repair..." rows="2" style="resize:vertical;"></textarea>
      <label class="modal-label" style="margin-top:12px;">Parts actually replaced:</label>
      <input type="text" id="feedback-actual-parts" class="modal-input" placeholder="e.g. Charging IC, Power Connector">
    </div>

    <div style="margin-bottom:14px;">
      <label class="modal-label">Technician notes (optional)</label>
      <textarea id="feedback-notes" class="modal-input" placeholder="Any additional observations..." rows="2" style="resize:vertical;"></textarea>
    </div>

    <div style="display:flex; gap:10px;">
      <button onclick="closeModal('feedbackModal')" style="flex:1;padding:10px;border:1px solid #e2e8f0;border-radius:8px;background:white;color:#64748b;font-weight:600;cursor:pointer;">Cancel</button>
      <button onclick="saveFeedback()" style="flex:1;padding:10px;background:linear-gradient(135deg,#38bdf8,#0284c7);border:none;border-radius:8px;color:white;font-weight:600;cursor:pointer;">Save Feedback</button>
    </div>
  </div>
</div>
<!-- ── PARTS SELECTION MODAL ────────────────────────────────────────────── -->
<div class="modal-overlay" id="partsModal">
  <div class="modal-box" style="width:95%; max-width:580px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
      <h2 style="font-size:1.1rem; color:#1e293b;">🔩 Select Parts to Use</h2>
      <button onclick="cancelPartsModal()" style="background:none;border:none;font-size:1.4rem;color:#94a3b8;cursor:pointer;">✕</button>
    </div>
    <p style="font-size:12px; color:#64748b; margin:0 0 16px;">Based on AI diagnosis. Select parts the technician will use — stock will be deducted automatically.</p>

    <div style="background:#eef2ff; border-left:4px solid #6366f1; padding:10px 14px; border-radius:6px; margin-bottom:16px;">
      <p style="font-size:11px; color:#4338ca; font-weight:700; text-transform:uppercase; margin:0 0 2px;">🤖 AI Diagnosis</p>
      <p style="font-size:14px; font-weight:700; color:#1e293b; margin:0;" id="parts-modal-diagnosis">—</p>
    </div>

    <input type="hidden" id="parts-modal-device-id">

    <div id="parts-modal-loading" style="text-align:center; padding:30px; color:#94a3b8;">
      <p>Loading parts...</p>
    </div>

    <div id="parts-modal-list" style="display:none; max-height:320px; overflow-y:auto; margin-bottom:16px;"></div>

    <div id="parts-modal-empty" style="display:none; text-align:center; padding:20px; color:#94a3b8; font-size:13px;">
      ⚠️ No matching parts found in inventory for this diagnosis.
    </div>

    <!-- Return Parts Section (shown when job already In Progress) -->
    <div id="return-parts-section" style="display:none; margin-bottom:16px; padding:12px; background:#fef3c7; border-radius:8px; border-left:4px solid #f59e0b;">
      <p style="font-size:12px; font-weight:700; color:#92400e; margin:0 0 10px;">↩️ Parts Currently In Use — Return to Stock?</p>
      <div id="return-parts-list"></div>
    </div>

    <div style="display:flex; gap:10px;">
      <button onclick="cancelPartsModal()" style="flex:1;padding:10px;border:1px solid #e2e8f0;border-radius:8px;background:white;color:#64748b;font-weight:600;cursor:pointer;">Cancel</button>
      <button onclick="confirmPartsSelection()" id="parts-confirm-btn" style="flex:1;padding:10px;background:linear-gradient(135deg,#38bdf8,#0284c7);border:none;border-radius:8px;color:white;font-weight:600;cursor:pointer;">✓ Confirm & Deduct Stock</button>
    </div>
  </div>
</div>
<!-- ── Config + JS ───────────────────────────────────────────────────────── -->
<script>
// PHP injects data and URLs here — this is the only PHP in the JS block
let repairs = <?= json_encode($repairs ?? []) ?>;

window.REPAIRS_CONFIG = {
  addUrl             : '<?= $this->Url->build('/devices/add') ?>',
  updateUrl          : '<?= $this->Url->build('/devices/update') ?>',
  diagnoseUrl        : '<?= $this->Url->build('/ai/diagnose') ?>',
  feedbackUrl        : '<?= $this->Url->build('/ai/saveFeedback') ?>',
  partsGetUrl        : '<?= $this->Url->build('/parts-usage/get-by-names') ?>',
  partsGetByDiagUrl  : '<?= $this->Url->build('/parts-usage/get-by-diagnosis') ?>',
  partsDeductUrl     : '<?= $this->Url->build('/parts-usage/deduct') ?>',
  partsReturnUrl     : '<?= $this->Url->build('/parts-usage/return') ?>',
  partsGetUsedUrl    : '<?= $this->Url->build('/parts-usage/get-used') ?>',
};
</script>
<?= $this->Html->script('repairs') ?>