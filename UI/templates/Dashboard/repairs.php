<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrfToken" content="<?= $this->request->getAttribute('csrfToken') ?>">
<title>CellFix – Repair Dashboard</title>
<style>
  @media (max-width: 768px) {
    .modal-box { width: 98% !important; max-width: 100% !important; max-height: 90vh !important; }
  }
  .modal-box { overflow-y: auto; max-height: 85vh; }
</style>
</head>
<body>
<div class="app-container">

  <main>
    <div class="page-section">

      <!-- Header -->
      <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
        <div>
          <h1 style="font-size:26px; color:#1e293b; margin:0 0 4px;">🔧 Repair Dashboard</h1>
          <p style="color:#64748b; margin:0;">Overview of cellphone repair jobs</p>
        </div>
        <button class="btn-new-repair" onclick="openNewModal()">+ New Repair</button>
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
          style="flex:1; min-width:220px; padding:9px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:14px; outline:none; color:#1e293b;"
          oninput="filterTable(this.value)">
        <select onchange="filterStatus(this.value)"
          style="padding:9px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:14px; color:#475569; outline:none; background:white;">
          <option value="all">All Statuses</option>
          <option value="completed">Completed</option>
          <option value="progress">In Progress</option>
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
  </main>
</div>

<!-- ── NEW REPAIR MODAL ── -->
<div class="modal-overlay" id="newRepairModal">
  <div class="modal-box" style="width:95%; max-width:1000px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
      <h2 style="font-size:1.1rem; color:#1e293b;">+ New Repair Job</h2>
      <button onclick="closeModal('newRepairModal')" style="background:none;border:none;font-size:1.4rem;color:#94a3b8;cursor:pointer;">✕</button>
    </div>

    <!-- CUSTOMER INFO SECTION -->
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

    <!-- DEVICE & ISSUE SECTION -->
    <p style="font-size:12px; font-weight:600; color:#64748b; margin:12px 0 8px;">📱 Device Information</p>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
      <div>
        <label class="modal-label">Device</label>
        <input type="text" id="new-device" placeholder="e.g. iPhone 13" class="modal-input">
      </div>
      <div>
        <label class="modal-label">Date Received</label>
        <input type="date" id="new-date" class="modal-input">
      </div>
    </div>

    <div style="margin-bottom:14px;">
      <label class="modal-label">Issue Description</label>
      <textarea id="new-issue" placeholder="Describe the customer's issue..." class="modal-input" rows="2" style="resize:vertical;"></textarea>
    </div>

    <button type="button"
        onclick="runDiagnosis()"
        style="width:100%;margin-bottom:14px;padding:10px 12px;background:#6366f1;color:white;border:none;border-radius:6px;cursor:pointer;font-weight:600;">
         🤖 Diagnose with AI
    </button>

    <div id="aiResultBox"
        style="display:none;margin:-16px -16px 14px -16px;padding:16px;background:#ffffff;border-radius:0;font-size:13px;border:none;border-bottom:1px solid #e2e8f0;box-shadow:none;">
    </div>

    <!-- Hidden fields to store AI diagnosis data -->
    <input type="hidden" id="aiDiagnosis" value="">
    <input type="hidden" id="aiSuggestedParts" value="">

    <div style="display:flex; gap:10px;">
      <button onclick="closeModal('newRepairModal')" style="flex:1;padding:10px;border:1px solid #e2e8f0;border-radius:8px;background:white;color:#64748b;font-weight:600;cursor:pointer;">Cancel</button>
      <button onclick="saveNewRepair()" style="flex:1;padding:10px;background:linear-gradient(135deg,#38bdf8,#0284c7);border:none;border-radius:8px;color:white;font-weight:600;cursor:pointer;">Save Repair</button>
    </div>
  </div>
</div>

<!-- ── VIEW MODAL ── -->
<div class="modal-overlay" id="viewModal" onclick="if(event.target===this) closeModal('viewModal')">
  <div class="modal-box" style="width:95%; max-width:1000px;">
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

<!-- ── EDIT MODAL ── -->
<div class="modal-overlay" id="editModal" onclick="if(event.target===this) closeModal('editModal')">
  <div class="modal-box" style="width:95%; max-width:1000px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
      <h2 style="font-size:1.1rem; color:#1e293b;">✏️ Edit Repair Job</h2>
      <button onclick="closeModal('editModal')" style="background:none;border:none;font-size:1.4rem;color:#94a3b8;cursor:pointer;">✕</button>
    </div>
    <input type="hidden" id="edit-idx">
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
      <div>
        <label class="modal-label">Job ID</label>
        <input type="text" id="edit-jobid" class="modal-input" disabled>
      </div>
      <div>
        <label class="modal-label">Device</label>
        <input type="text" id="edit-device" class="modal-input" disabled>
      </div>
      <div>
        <label class="modal-label">Technician</label>
        <input type="text" id="edit-tech" class="modal-input">
      </div>
      <div>
        <label class="modal-label">Status</label>
        <select id="edit-status" class="modal-input">
          <option value="pending">Pending</option>
          <option value="progress">In Progress</option>
          <option value="completed">Completed</option>
        </select>
      </div>
    </div>
    <div style="margin-bottom:14px;">
      <label class="modal-label">Date &amp; Time Finished</label>
      <input type="datetime-local" id="edit-finished" class="modal-input">
    </div>
    <div style="margin-bottom:20px;">
      <label class="modal-label">Notes</label>
      <textarea id="edit-notes" placeholder="Add notes..." class="modal-input" rows="3" style="resize:vertical;"></textarea>
    </div>
    <div style="display:flex; gap:10px;">
      <button onclick="closeModal('editModal')" style="flex:1;padding:10px;border:1px solid #e2e8f0;border-radius:8px;background:white;color:#64748b;font-weight:600;cursor:pointer;">Cancel</button>
      <button onclick="saveEdit()" style="flex:1;padding:10px;background:linear-gradient(135deg,#38bdf8,#0284c7);border:none;border-radius:8px;color:white;font-weight:600;cursor:pointer;">Save Changes</button>
    </div>
  </div>
</div>

<!-- ── FEEDBACK MODAL ── -->
<div class="modal-overlay" id="feedbackModal">
  <div class="modal-box" style="width:95%; max-width:1000px;">
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

<script>
let repairs = <?= json_encode($repairs ?? []) ?>;

const statusLabel = { completed:'Completed', progress:'In Progress', pending:'Pending', 'in progress':'In Progress', 'waiting parts':'Waiting Parts', released:'Released' };
const badgeClass  = { completed:'badge-completed', progress:'badge-progress', pending:'badge-pending', 'in progress':'badge-progress', 'waiting parts':'badge-warning', released:'badge-released' };

function badge(s) {
  return `<span class="status-badge ${badgeClass[s]}">${statusLabel[s]}</span>`;
}

function renderTable() {
  const body = document.getElementById('repairsBody');
  body.innerHTML = '';
  repairs.forEach((r, i) => {
    const tr = document.createElement('tr');
    tr.dataset.status = r.status;
    tr.innerHTML = `
      <td><span class="job-id">${r.id}</span></td>
      <td>📱 ${r.device}</td>
      <td>${r.issue}</td>
      <td>${r.customer}</td>
      <td>${r.technician}</td>
      <td style="color:#94a3b8; font-size:13px;">${r.date}</td>
      <td>${badge(r.status)}</td>
      <td style="text-align:center;">
        <button class="tbl-btn tbl-btn-view" onclick="openView(${i})">View</button>
        <button class="tbl-btn tbl-btn-edit" onclick="openEdit(${i})">Edit</button>
        <button class="tbl-btn" onclick="openFeedback(${i})" style="background:#22c55e; color:white; padding:5px 10px; font-size:12px; border:none; border-radius:4px; cursor:pointer; margin-top:2px;">📊 Feedback</button>
      </td>`;
    body.appendChild(tr);
  });
  updateStats();
  checkEmpty(repairs.length);
}

function updateStats() {
  document.getElementById('statTotal').textContent     = repairs.length;
  document.getElementById('statCompleted').textContent = repairs.filter(r => r.status === 'completed').length;
  document.getElementById('statProgress').textContent  = repairs.filter(r => r.status === 'progress').length;
  document.getElementById('statPending').textContent   = repairs.filter(r => r.status === 'pending').length;
}

function checkEmpty(n) {
  document.getElementById('emptyState').style.display = n === 0 ? 'block' : 'none';
}

let cf = { text: '', status: 'all' };
function applyFilters() {
  const rows = document.querySelectorAll('#repairsBody tr');
  let vis = 0;
  rows.forEach(r => {
    const ok = r.innerText.toLowerCase().includes(cf.text) && (cf.status === 'all' || r.dataset.status === cf.status);
    r.style.display = ok ? '' : 'none';
    if (ok) vis++;
  });
  checkEmpty(vis);
}
function filterTable(v)  { cf.text = v.toLowerCase(); applyFilters(); }
function filterStatus(v) { cf.status = v; applyFilters(); }

function openModal(id)  { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

/* NEW */
function openNewModal() {
  ['new-customer-name','new-contact-no','new-device','new-issue'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('aiDiagnosis').value = '';
  document.getElementById('aiSuggestedParts').value = '';
  document.getElementById('new-date').value = new Date().toISOString().split('T')[0];
  document.getElementById('aiResultBox').style.display = 'none';
  document.getElementById('newRepairModal').querySelector('button[onclick*="saveNewRepair"]').disabled = false;
  openModal('newRepairModal');
}

let isSavingRepair = false; // Prevent double submission

async function saveNewRepair() {
  if (isSavingRepair) return; // Prevent double click
  
  const customerName = document.getElementById('new-customer-name').value.trim();
  const contactNo = document.getElementById('new-contact-no').value.trim();
  const device = document.getElementById('new-device').value.trim();
  const issue = document.getElementById('new-issue').value.trim();
  const diagnostic = document.getElementById('aiDiagnosis').value.trim();
  const suggestedParts = document.getElementById('aiSuggestedParts').value.trim();
  
  if (!customerName || !device || !issue) { alert('Please fill in Customer Name, Device, and Issue.'); return; }
  
  // Parse device string into brand and model
  const parts = device.split(' ');
  const brand = parts[0] || 'Unknown';
  const model = parts.slice(1).join(' ') || 'Unknown';
  
  const csrfToken = document.querySelector('meta[name="csrfToken"]').getAttribute('content');
  if (!csrfToken) { alert('Security error: CSRF token not found'); return; }
  
  isSavingRepair = true;
  const saveBtn = document.getElementById('newRepairModal').querySelector('button[onclick*="saveNewRepair"]');
  saveBtn.disabled = true;
  saveBtn.style.opacity = '0.6';
  saveBtn.textContent = 'Saving...';
  
  const response = await fetch("<?= $this->Url->build('/devices/add') ?>", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-Token": csrfToken
    },
    body: JSON.stringify({
      customer_name: customerName,
      contact_no: contactNo,
      brand: brand,
      model: model,
      issue_description: issue,
      diagnostic: diagnostic,
      suggested_part_replacement: suggestedParts,
      status: 'Pending',
      priority_level: 'Medium'
    })
  });
  
  const data = await response.json();
  if (data.success) {
    alert('✓ Repair job created successfully!');
    location.reload();
  } else {
    alert('Error: ' + (data.error || 'Failed to create repair'));
    isSavingRepair = false;
    saveBtn.disabled = false;
    saveBtn.style.opacity = '1';
    saveBtn.textContent = 'Save Repair';
  }
}

/* VIEW */
function openView(i) {
  const r = repairs[i];
  const row = (k, v) => `<div class="view-row"><span class="view-key">${k}</span><span class="view-val">${v}</span></div>`;
  document.getElementById('viewContent').innerHTML = `
    ${row('Job ID', `<span class="job-id">${r.id}</span>`)}
    ${row('Device', '📱 ' + r.device)}
    ${row('Issue', r.issue)}
    ${row('Customer', r.customer)}
    ${row('Contact', r.contact_no || '—')}
    ${row('Technician', r.technician)}
    ${row('Date Received', r.date)}
    ${row('Status', badge(r.status))}
    ${row('Date Finished', r.finished || '—')}
    ${row('Diagnostic', r.diagnostic || '—')}
    ${row('Suggested Parts', r.suggested_parts || '—')}
    <div style="margin-top:16px;">
      <label class="modal-label">Notes</label>
      <div class="view-notes-box">${r.notes || 'No notes added yet.'}</div>
    </div>`;
  openModal('viewModal');
}

/* EDIT */
function openEdit(i) {
  const r = repairs[i];
  document.getElementById('edit-idx').value    = i;
  document.getElementById('edit-jobid').value  = r.id;
  document.getElementById('edit-device').value = r.device;
  document.getElementById('edit-tech').value   = r.tech;
  document.getElementById('edit-status').value = r.status;
  document.getElementById('edit-notes').value  = r.notes || '';
  if (r.finished) {
    try {
      const dt = new Date(r.finished);
      const p  = n => String(n).padStart(2, '0');
      document.getElementById('edit-finished').value =
        `${dt.getFullYear()}-${p(dt.getMonth()+1)}-${p(dt.getDate())}T${p(dt.getHours())}:${p(dt.getMinutes())}`;
    } catch { document.getElementById('edit-finished').value = ''; }
  } else {
    document.getElementById('edit-finished').value = '';
  }
  openModal('editModal');
}
async function saveEdit() {
  const idx = parseInt(document.getElementById('edit-idx').value);
  const repair = repairs[idx];
  const deviceId = repair.device_id; // Use device_id directly
  const technician = document.getElementById('edit-tech').value.trim();
  const status = document.getElementById('edit-status').value;
  const finished = document.getElementById('edit-finished').value;
  const notes = document.getElementById('edit-notes').value.trim();
  
  // Map status values to match database enums
  const statusMap = {
    'pending': 'Pending',
    'progress': 'In Progress',
    'completed': 'Completed'
  };
  
  const csrfToken = document.querySelector('meta[name="csrfToken"]').getAttribute('content');
  if (!csrfToken) { alert('Security error: CSRF token not found'); return; }
  
  const response = await fetch("<?= $this->Url->build('/devices/update') ?>", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-Token": csrfToken
    },
    body: JSON.stringify({
      id: deviceId,
      technician: technician || null,
      status: statusMap[status] || 'Pending',
      date_released: finished ? new Date(finished).toISOString().split('T')[0] : null,
      notes: notes || null
    })
  });
  
  const data = await response.json();
  if (data.success) {
    alert('✓ Repair updated successfully!');
    location.reload();
  } else {
    alert('Error: ' + (data.error || 'Failed to update repair'));
  }
}

renderTable();

async function runDiagnosis() {

    const description = document.getElementById('new-issue').value.trim();

    if (!description) {
        alert("Please describe the problem first.");
        return;
    }

    const csrfToken = document
        .querySelector('meta[name="csrfToken"]')
        .getAttribute('content');

    const response = await fetch("<?= $this->Url->build('/ai/diagnose') ?>", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-Token": csrfToken
        },
        body: JSON.stringify({
            description: description
        })
    });

    const data = await response.json();
    const box = document.getElementById("aiResultBox");

    if (!data.success) {
        box.style.display = "block";
        box.innerHTML = `<span style="color:#dc2626; font-weight:600;">✕ ${data.error}</span>`;
        return;
    }

    // Symptom label map for user-friendly display
    const symptomLabels = {
        'not_charging': 'Not charging',
        'overheating': 'Overheating',
        'no_signal': 'No signal',
        'battery_drains_fast': 'Battery drains fast',
        'stuck_on_logo': 'Stuck on logo',
        'screen_black': 'Black screen',
        'touch_not_working': 'Touch screen not responding',
        'speaker_no_sound': 'No speaker sound',
        'mic_not_work': 'Microphone not working',
        'screen_flickering': 'Screen flickering',
        'wifi_not_working': 'WiFi not working',
        'bluetooth_issue': 'Bluetooth issues',
        'phone_freezing': 'Phone freezing/restarting',
        'water_damage': 'Water damage'
    };

    const detectedSymptoms = data.detected_symptoms
        .map(s => symptomLabels[s] || s)
        .join(', ');

    const confidenceText = data.confidence !== null
        ? data.confidence.toFixed(1) + "%"
        : "Rule-Based";

    const confidenceColor = data.confidence >= 80 ? "#22c55e" 
                          : data.confidence >= 60 ? "#f59e0b"
                          : data.confidence >= 40 ? "#f97316"
                          : "#ef4444";

    const confidenceLabel = data.confidence >= 80 ? "High confidence" 
                          : data.confidence >= 60 ? "Good confidence"
                          : data.confidence >= 50 ? "Moderate confidence"
                          : "Low confidence";

    // Only show confidence if ML model was used (not rule-based)
    const isRuleBased = data.rule_suggestion !== null;
    const isUncertain = !isRuleBased && data.confidence < 50;
    
    const confidenceBar = isRuleBased ? '' : `
        <div style="background:rgba(255,255,255,0.6); padding:10px; border-radius:6px; margin-bottom:12px;">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                <div style="width:100%; background:#e5e7eb; border-radius:4px; height:6px; overflow:hidden;">
                    <div style="background:${confidenceColor}; height:100%; width:${data.confidence}%;"></div>
                </div>
                <span style="color:${confidenceColor}; font-weight:600; font-size:13px; white-space:nowrap;">${confidenceText}</span>
            </div>
            <p style="font-size:12px; color:#64748b; margin:0;">${confidenceLabel}</p>
        </div>
    `;
    
    const borderColor = isRuleBased ? "#6366f1" : isUncertain ? "#f59e0b" : confidenceColor;
    const uncertainBadge = isUncertain ? '<span style="font-size:11px; color:#d97706; background:#fef3c7; padding:4px 10px; border-radius:4px; font-weight:600;">⚠ Uncertain</span>' : '';
    
    const uncertainWarning = isUncertain ? '<div style="background:#fef3c7; border:1px solid #fcd34d; padding:10px 12px; border-radius:6px; margin:14px 0; font-size:12px; color:#92400e; line-height:1.5;"><strong>⚠ Low Confidence:</strong> Confidence is below 50%. Verify with technician expertise or request more details from customer.</div>' : '';

    // Build individual symptom diagnoses section (multi-symptom only)
    let individualDiagnosesHtml = '';
    if (data.detected_symptoms.length > 1 && data.symptom_diagnoses) {
        individualDiagnosesHtml = '<div style="margin-bottom:16px; padding:14px; background:#f0fdf4; border-radius:8px; border-left:4px solid #16a34a;"><p style="font-size:11px; font-weight:700; color:#166534; margin:0 0 12px; text-transform:uppercase; letter-spacing:0.5px;">Individual Symptom Diagnoses</p>';
        
        for (const symptom of data.detected_symptoms) {
            const symptomLabel = symptomLabels[symptom] || symptom.replace(/_/g, ' ').charAt(0).toUpperCase() + symptom.replace(/_/g, ' ').slice(1);
            const individualDiagnosis = data.symptom_diagnoses[symptom] || 'Unknown';
            const individualParts = data.symptom_parts[symptom] || [];
            
            individualDiagnosesHtml += `<div style="margin-bottom:10px;">`;
            individualDiagnosesHtml += `<div style="display:flex; gap:8px; align-items:baseline; margin-bottom:6px;">`;
            individualDiagnosesHtml += `<span style="color:#16a34a; font-size:16px;">→</span>`;
            individualDiagnosesHtml += `<div><div style="font-size:13px; font-weight:600; color:#1e293b;">${symptomLabel}</div>`;
            individualDiagnosesHtml += `<div style="font-size:12px; color:#16a34a; font-weight:600; margin-top:2px;">${individualDiagnosis}</div></div>`;
            individualDiagnosesHtml += `</div>`;
            
            if (individualParts.length > 0) {
                individualDiagnosesHtml += `<div style="font-size:12px; color:#475569; margin-left:24px; display:flex; flex-direction:column; gap:2px;">`;
                individualParts.forEach(part => {
                    individualDiagnosesHtml += `<div>◦ ${part}</div>`;
                });
                individualDiagnosesHtml += `</div>`;
            }
            individualDiagnosesHtml += `</div>`;
        }
        individualDiagnosesHtml += '</div>';
    }

    // Build symptom-specific parts sections
    let symptomPartsHtml = '';
    if (data.symptom_parts && Object.keys(data.symptom_parts).length > 0) {
        symptomPartsHtml = '<div style="margin-top:16px; padding-top:16px; border-top:1px solid #e2e8f0;"><p style="font-size:11px; font-weight:700; color:#64748b; margin:0 0 12px; text-transform:uppercase; letter-spacing:0.5px;">Parts by Symptom</p>';
        for (const [symptom, parts] of Object.entries(data.symptom_parts)) {
            const symptomLabel = symptomLabels[symptom] || symptom.replace(/_/g, ' ').charAt(0).toUpperCase() + symptom.replace(/_/g, ' ').slice(1);
            symptomPartsHtml += `<div style="margin-bottom:12px;"><span style="font-size:12px; color:#0f766e; font-weight:600; display:block; margin-bottom:6px;">→ ${symptomLabel}</span><div style="font-size:12px; color:#475569; display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:8px; margin-left:0;">`;
            parts.forEach(part => {
                symptomPartsHtml += `<div style="background:#f8fafc; padding:6px 10px; border-radius:4px; border-left:2px solid #0f766e;">◦ ${part}</div>`;
            });
            symptomPartsHtml += '</div></div>';
        }
        symptomPartsHtml += '</div>';
    }

    box.style.display = "block";
    box.innerHTML = `
        <div style="border-left:6px solid ${borderColor}; padding:16px;">
            
            <!-- Header -->
            <div style="margin-bottom:16px;">
                <div style="display:flex; align-items:baseline; gap:12px; margin-bottom:8px;">
                    <span style="font-size:28px;">✓</span>
                    <div>
                        <strong style="color:#1e293b; font-size:18px; display:block;">Combined Diagnosis</strong>
                        <div style="font-size:16px; color:${borderColor}; font-weight:700; margin-top:4px;">${data.diagnosis}</div>
                    </div>
                </div>
                <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:10px;">
                    ${isRuleBased ? '<span style="font-size:11px; color:#6366f1; background:#eef2ff; padding:4px 10px; border-radius:4px; font-weight:600;">Rule-Based</span>' : ''}
                    ${uncertainBadge}
                </div>
            </div>

            ${uncertainWarning}

            <!-- Confidence Bar -->
            ${confidenceBar}

            <!-- Symptoms -->
            <div style="margin-bottom:16px;">
                <p style="font-size:11px; font-weight:700; color:#64748b; margin:0 0 8px; text-transform:uppercase; letter-spacing:0.5px;">Detected Symptoms</p>
                <p style="font-size:14px; color:#1e293b; margin:0; line-height:1.6;">${detectedSymptoms}</p>
            </div>

            ${individualDiagnosesHtml}

            <!-- Main Parts -->
            <div style="margin-bottom:16px;">
                <p style="font-size:11px; font-weight:700; color:#64748b; margin:0 0 10px; text-transform:uppercase; letter-spacing:0.5px;">Suggested Replacement Parts</p>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:10px;">
                    ${data.replacement_parts.map(part => `<div style="background:#f8fafc; padding:10px 12px; border-radius:6px; color:#0f766e; border-left:3px solid #14b8a6; font-size:13px;">• ${part}</div>`).join('')}
                </div>
            </div>

            ${symptomPartsHtml}
        </div>
    `;

    // Auto-fill AI diagnostic fields and store for later
    document.getElementById('aiDiagnosis').value = data.diagnosis;
    document.getElementById('aiSuggestedParts').value = data.replacement_parts.join(', ');
    document.getElementById('new-issue').value = data.diagnosis;
}

/* ── FEEDBACK ── */
let currentFeedbackData = {};

function openFeedback(i) {
  const r = repairs[i];
  document.getElementById('feedback-jobid').value = r.id;
  document.getElementById('feedback-ai-diagnosis').value = r.issue;
  document.getElementById('feedback-ai-display').textContent = r.issue;
  document.getElementById('feedback-incorrect-section').style.display = 'none';
  document.getElementById('feedback-actual-diagnosis').value = '';
  document.getElementById('feedback-root-cause').value = '';
  document.getElementById('feedback-actual-parts').value = '';
  document.getElementById('feedback-notes').value = '';
  
  // Reset button states
  document.getElementById('btn-correct').style.background = '#22c55e';
  document.getElementById('btn-incorrect').style.background = '#ef4444';
  
  currentFeedbackData = { jobid: r.id, aiDiagnosis: r.issue, correct: undefined };
  openModal('feedbackModal');
}

function feedbackCorrect(isCorrect) {
  currentFeedbackData.correct = isCorrect;
  
  // Visual feedback on button selection
  document.getElementById('btn-correct').style.opacity = isCorrect ? '1' : '0.6';
  document.getElementById('btn-incorrect').style.opacity = isCorrect ? '0.6' : '1';
  
  document.getElementById('feedback-incorrect-section').style.display = 
    isCorrect ? 'none' : 'block';
}

async function saveFeedback() {
  const jobId = document.getElementById('feedback-jobid').value;
  const isCorrect = currentFeedbackData.correct;
  
  if (isCorrect === undefined) {
    alert('Please indicate if the diagnosis was correct.');
    return;
  }
  
  if (!isCorrect) {
    if (!document.getElementById('feedback-actual-diagnosis').value.trim()) {
      alert('Please enter the actual diagnosis.');
      return;
    }
  }
  
  const csrfToken = document
    .querySelector('meta[name="csrfToken"]')
    .getAttribute('content');

  const response = await fetch("<?= $this->Url->build('/ai/saveFeedback') ?>", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-Token": csrfToken
    },
    body: JSON.stringify({
      job_id: jobId,
      ai_diagnosis: currentFeedbackData.aiDiagnosis,
      diagnosis_correct: isCorrect,
      actual_diagnosis: isCorrect ? null : document.getElementById('feedback-actual-diagnosis').value.trim(),
      root_cause: isCorrect ? null : document.getElementById('feedback-root-cause').value.trim(),
      parts_replaced: isCorrect ? null : document.getElementById('feedback-actual-parts').value.trim(),
      notes: document.getElementById('feedback-notes').value.trim()
    })
  });

  const data = await response.json();
  if (data.success) {
    alert('✓ Feedback saved! This data will improve the AI model.');
    closeModal('feedbackModal');
  } else {
    alert('Error saving feedback: ' + (data.error || 'Unknown error'));
  }
}
</script>
</body>
</html>