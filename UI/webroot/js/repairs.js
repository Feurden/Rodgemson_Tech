/* ── repairs.js ──────────────────────────────────────────────────────────────
   JavaScript for the Repair Dashboard page (repairs.php)
   URLs are injected by PHP via window.REPAIRS_CONFIG before this script loads.
   ──────────────────────────────────────────────────────────────────────────── */

/* ── Status helpers ──────────────────────────────────────────────────────── */

const statusLabel = {
  'completed'    : 'Completed',
  'in progress'  : 'In Progress',
  'pending'      : 'Pending',
  'waiting parts': 'Waiting Parts',
  'released'     : 'Released',
};

const badgeClass = {
  'completed'    : 'badge-completed',
  'in progress'  : 'badge-progress',
  'pending'      : 'badge-pending',
  'waiting parts': 'badge-warning',
  'released'     : 'badge-released',
};

function badge(s) {
  const key = s ? s.toLowerCase() : 'pending';
  return `<span class="status-badge ${badgeClass[key] || 'badge-pending'}">${statusLabel[key] || s}</span>`;
}

/* ── Symptom label map ───────────────────────────────────────────────────── */

const symptomLabels = {
  'not_charging'      : 'Not charging',
  'overheating'       : 'Overheating',
  'no_signal'         : 'No signal',
  'battery_drains_fast': 'Battery drains fast',
  'stuck_on_logo'     : 'Stuck on logo',
  'screen_black'      : 'Black screen',
  'touch_not_working' : 'Touch screen not responding',
  'speaker_no_sound'  : 'No speaker sound',
  'mic_not_work'      : 'Microphone not working',
  'screen_flickering' : 'Screen flickering',
  'wifi_not_working'  : 'WiFi not working',
  'bluetooth_issue'   : 'Bluetooth issues',
  'phone_freezing'    : 'Phone freezing/restarting',
  'water_damage'      : 'Water damage',
};

/* ── Table rendering ─────────────────────────────────────────────────────── */

function renderTable() {
  const body = document.getElementById('repairsBody');
  body.innerHTML = '';
  repairs.forEach((r, i) => {
    const statusKey = r.status ? r.status.toLowerCase() : 'pending';
    const tr = document.createElement('tr');
    tr.dataset.status = statusKey;
    tr.innerHTML = `
      <td><span class="job-id">${r.id}</span></td>
      <td>📱 ${r.device}</td>
      <td>${r.issue}</td>
      <td>${r.customer}</td>
      <td>${r.technician}</td>
      <td style="color:#94a3b8; font-size:13px;">${r.date}</td>
      <td>${badge(statusKey)}</td>
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
  document.getElementById('statCompleted').textContent = repairs.filter(r => r.status && r.status.toLowerCase() === 'completed').length;
  document.getElementById('statProgress').textContent  = repairs.filter(r => r.status && r.status.toLowerCase() === 'in progress').length;
  document.getElementById('statPending').textContent   = repairs.filter(r => r.status && r.status.toLowerCase() === 'pending').length;
}

function checkEmpty(n) {
  document.getElementById('emptyState').style.display = n === 0 ? 'block' : 'none';
}

/* ── Filters ─────────────────────────────────────────────────────────────── */

let cf = { text: '', status: 'all' };

function applyFilters() {
  const rows = document.querySelectorAll('#repairsBody tr');
  let vis = 0;
  rows.forEach(r => {
    const ok = r.innerText.toLowerCase().includes(cf.text) &&
               (cf.status === 'all' || r.dataset.status === cf.status);
    r.style.display = ok ? '' : 'none';
    if (ok) vis++;
  });
  checkEmpty(vis);
}

function filterTable(v)  { cf.text = v.toLowerCase(); applyFilters(); }
function filterStatus(v) { cf.status = v; applyFilters(); }

/* ── Modal helpers ───────────────────────────────────────────────────────── */

function openModal(id)  { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

function getCsrf() {
  return document.querySelector('meta[name="csrfToken"]').getAttribute('content');
}

/* ── New Repair Modal ────────────────────────────────────────────────────── */

function openNewModal() {
  ['new-customer-name', 'new-contact-no', 'new-device', 'new-issue', 'new-technician']
    .forEach(id => document.getElementById(id).value = '');
  document.getElementById('aiDiagnosis').value      = '';
  document.getElementById('aiSuggestedParts').value = '';
  document.getElementById('aiResultBox').style.display = 'none';
  document.getElementById('newRepairModal')
    .querySelector('button[onclick*="saveNewRepair"]').disabled = false;
  openModal('newRepairModal');
}

let isSavingRepair = false;

async function saveNewRepair() {
  if (isSavingRepair) return;

  const customerName   = document.getElementById('new-customer-name').value.trim();
  const contactNo      = document.getElementById('new-contact-no').value.trim();
  const device         = document.getElementById('new-device').value.trim();
  const issue          = document.getElementById('new-issue').value.trim();
  const technician     = document.getElementById('new-technician').value.trim();
  const diagnostic     = document.getElementById('aiDiagnosis').value.trim();
  const suggestedParts = document.getElementById('aiSuggestedParts').value.trim();

  if (!customerName || !device || !issue || !technician) {
    alert('Please fill in Customer Name, Device, Issue, and select a Technician.');
    return;
  }

  const parts = device.split(' ');
  const brand = parts[0] || 'Unknown';
  const model = parts.slice(1).join(' ') || 'Unknown';

  const csrfToken = getCsrf();
  if (!csrfToken) { alert('Security error: CSRF token not found'); return; }

  isSavingRepair = true;
  const saveBtn = document.getElementById('newRepairModal')
    .querySelector('button[onclick*="saveNewRepair"]');
  saveBtn.disabled     = true;
  saveBtn.style.opacity = '0.6';
  saveBtn.textContent  = 'Saving...';

  const response = await fetch(REPAIRS_CONFIG.addUrl, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
    body: JSON.stringify({
      customer_name             : customerName,
      contact_no                : contactNo,
      brand, model,
      issue_description         : issue,
      technician                : technician,
      diagnostic,
      suggested_part_replacement: suggestedParts,
      status                    : 'Pending',
      priority_level            : 'Medium',
    }),
  });

  const data = await response.json();
  if (data.success) {
    alert('✓ Repair job created successfully!');
    location.reload();
  } else {
    alert('Error: ' + (data.error || 'Failed to create repair'));
    isSavingRepair        = false;
    saveBtn.disabled      = false;
    saveBtn.style.opacity = '1';
    saveBtn.textContent   = 'Save Repair';
  }
}

/* ── View Modal ──────────────────────────────────────────────────────────── */

async function openView(i) {
  const r = repairs[i];

  const partTags = r.suggested_parts
    ? r.suggested_parts.split(',').map(p => p.trim()).filter(Boolean)
        .map(p => `<span style="display:inline-block; background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; padding:4px 10px; border-radius:20px; font-size:12px; font-weight:500;">🔩 ${p}</span>`)
        .join('')
    : '<span style="color:#94a3b8; font-size:13px;">No parts recorded</span>';

  // Fetch used parts for this device
  let usedPartsHtml = '<span style="color:#94a3b8; font-size:13px;">Loading parts...</span>';
  const csrfToken = getCsrf();
  
  try {
    const usedRes = await fetch(REPAIRS_CONFIG.partsGetUsedUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
      body: JSON.stringify({ device_id: r.device_id }),
    });
    const usedData = await usedRes.json();
    
    if (usedData.used_parts && usedData.used_parts.length > 0) {
      usedPartsHtml = usedData.used_parts.map(u => `
        <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 12px; background:#ffffff; border-radius:6px; border:1px solid #e2e8f0; margin-bottom:6px;">
          <div>
            <span style="font-size:13px; font-weight:600; color:#1e293b;">${u.part_name}</span>
            <span style="font-size:11px; color:#64748b; margin-left:8px;">${u.category || ''}</span>
          </div>
          <span style="background:#dbeafe; color:#1d4ed8; padding:2px 8px; border-radius:12px; font-size:11px; font-weight:600;">x${u.quantity}</span>
        </div>
      `).join('');
    } else {
      usedPartsHtml = '<span style="color:#94a3b8; font-size:13px;">No parts used yet</span>';
    }
  } catch (e) {
    console.error('Failed to fetch used parts:', e);
    usedPartsHtml = '<span style="color:#94a3b8; font-size:13px;">Unable to load parts</span>';
  }

  document.getElementById('viewContent').innerHTML = `

    <!-- Header: Device + Status -->
    <div style="display:flex; align-items:center; justify-content:space-between; padding:16px; background:linear-gradient(135deg,#f0f9ff,#e0f2fe); border-radius:10px; margin-bottom:16px;">
      <div>
        <p style="font-size:11px; color:#0284c7; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; margin:0 0 4px;">Device</p>
        <p style="font-size:18px; font-weight:700; color:#1e293b; margin:0;">📱 ${r.device}</p>
        <p style="font-size:12px; color:#64748b; margin:4px 0 0;">Job ID: <strong>${r.id}</strong></p>
      </div>
      <div style="text-align:right;">
        ${badge(r.status)}
        <p style="font-size:12px; color:#64748b; margin:8px 0 0;">${r.date}</p>
      </div>
    </div>

    <!-- Customer + Technician -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
      <div style="padding:12px; background:#f8fafc; border-radius:8px; border:1px solid #e2e8f0;">
        <p style="font-size:11px; color:#94a3b8; font-weight:600; text-transform:uppercase; margin:0 0 4px;">👤 Customer</p>
        <p style="font-size:14px; font-weight:600; color:#1e293b; margin:0;">${r.customer}</p>
        <p style="font-size:12px; color:#64748b; margin:2px 0 0;">${r.contact_no || 'No contact'}</p>
      </div>
      <div style="padding:12px; background:#f8fafc; border-radius:8px; border:1px solid #e2e8f0;">
        <p style="font-size:11px; color:#94a3b8; font-weight:600; text-transform:uppercase; margin:0 0 4px;">🔧 Technician</p>
        <p style="font-size:14px; font-weight:600; color:#1e293b; margin:0;">${r.technician}</p>
        <p style="font-size:12px; color:#64748b; margin:2px 0 0;">Finished: ${r.finished || 'Not yet'}</p>
      </div>
    </div>

    <!-- Issue -->
    <div style="padding:12px; background:#fff7ed; border-radius:8px; border-left:4px solid #f97316; margin-bottom:12px;">
      <p style="font-size:11px; color:#c2410c; font-weight:700; text-transform:uppercase; margin:0 0 4px;">⚠️ Issue Reported</p>
      <p style="font-size:14px; color:#1e293b; margin:0; line-height:1.6;">${r.issue}</p>
    </div>

    <!-- AI Diagnosis -->
    <div style="padding:12px; background:#eef2ff; border-radius:8px; border-left:4px solid #6366f1; margin-bottom:12px;">
      <p style="font-size:11px; color:#4338ca; font-weight:700; text-transform:uppercase; margin:0 0 4px;">🤖 AI Diagnosis</p>
      <p style="font-size:15px; font-weight:700; color:#1e293b; margin:0;">${r.diagnostic || '—'}</p>
    </div>

    <!-- Parts Used (Actually Deducted from Stock) -->
    <div style="padding:12px; background:#f0fdf4; border-radius:8px; border-left:4px solid #16a34a; margin-bottom:12px;">
      <p style="font-size:11px; color:#15803d; font-weight:700; text-transform:uppercase; margin:0 0 10px;">✅ Parts Used (In Stock)</p>
      <div style="max-height:150px; overflow-y:auto;">${usedPartsHtml}</div>
    </div>

    <!-- Possible Parts -->
    <div style="padding:12px; background:#f8fafc; border-radius:8px; border-left:4px solid #64748b; margin-bottom:12px;">
      <p style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase; margin:0 0 10px;">🔩 Suggested Parts (AI Recommendation)</p>
      <div style="display:flex; flex-wrap:wrap; gap:6px;">${partTags}</div>
    </div>

    <!-- Notes -->
    <div style="padding:12px; background:#f8fafc; border-radius:8px; border:1px solid #e2e8f0;">
      <p style="font-size:11px; color:#94a3b8; font-weight:700; text-transform:uppercase; margin:0 0 6px;">📝 Notes</p>
      <p style="font-size:13px; color:#475569; margin:0; line-height:1.6;">${r.notes || 'No notes added yet.'}</p>
    </div>`;

  openModal('viewModal');
}

/* ── Edit Modal ──────────────────────────────────────────────────────────── */

function openEdit(i) {
  const r = repairs[i];
  document.getElementById('edit-idx').value                   = i;
  document.getElementById('edit-jobid').value                 = r.id;
  document.getElementById('edit-device').value                = r.device;
  document.getElementById('edit-jobid-display').textContent   = r.id;
  document.getElementById('edit-device-display').textContent  = r.device;
  document.getElementById('edit-tech').value                  = r.technician || '';
  document.getElementById('edit-issue').value                 = r.issue || '';
  document.getElementById('edit-notes').value                 = r.notes || '';
  document.getElementById('edit-ai-diagnosis').value          = r.diagnostic || '';
  document.getElementById('edit-ai-parts').value              = r.suggested_parts || '';
  document.getElementById('editAiResultBox').style.display    = 'none';

  const statusMap = {
    'pending'      : 'Pending',
    'in progress'  : 'In Progress',
    'completed'    : 'Completed',
    'waiting parts': 'Waiting Parts',
  };
  const normalizedStatus = statusMap[r.status ? r.status.toLowerCase() : 'pending'] || 'Pending';
  document.getElementById('edit-status').value = normalizedStatus;

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
  const idx        = parseInt(document.getElementById('edit-idx').value);
  const repair     = repairs[idx];
  const deviceId   = repair.device_id;
  const technician = document.getElementById('edit-tech').value.trim();
  const status     = document.getElementById('edit-status').value;
  const finished   = document.getElementById('edit-finished').value;
  const notes      = document.getElementById('edit-notes').value.trim();
  const issue      = document.getElementById('edit-issue').value.trim();
  const diagnostic = document.getElementById('edit-ai-diagnosis').value.trim();
  const suggestedParts = document.getElementById('edit-ai-parts').value.trim();

  const csrfToken = getCsrf();
  if (!csrfToken) { alert('Security error: CSRF token not found'); return; }

  const response = await fetch(REPAIRS_CONFIG.updateUrl, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
    body: JSON.stringify({
      id               : deviceId,
      technician       : technician || null,
      status,
      date_released    : finished ? new Date(finished).toISOString().split('T')[0] : null,
      issue_description: issue || null,
      notes            : notes || null,
      diagnostic       : diagnostic || null,
      suggested_parts  : suggestedParts || null,
    }),
  });

  const data = await response.json();
  if (data.success) {
    alert('✓ Repair updated successfully!');
    location.reload();
  } else {
    alert('Error: ' + (data.error || 'Failed to update repair'));
  }
}

/* ── AI Diagnosis (New Repair) ───────────────────────────────────────────── */

async function runDiagnosis() {
  const description = document.getElementById('new-issue').value.trim();
  if (!description) { alert('Please describe the problem first.'); return; }

  const csrfToken = getCsrf();
  const response  = await fetch(REPAIRS_CONFIG.diagnoseUrl, {
    method : 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
    body   : JSON.stringify({ description }),
  });

  const data = await response.json();
  const box  = document.getElementById('aiResultBox');

  if (!data.success) {
    box.style.display = 'block';
    box.innerHTML = `<span style="color:#dc2626; font-weight:600;">✕ ${data.error}</span>`;
    return;
  }

  const detectedSymptoms = data.detected_symptoms.map(s => symptomLabels[s] || s).join(', ');
  const confidenceText   = data.confidence !== null ? data.confidence.toFixed(1) + '%' : 'Rule-Based';
  const confidenceColor  = data.confidence >= 80 ? '#22c55e'
                         : data.confidence >= 60 ? '#f59e0b'
                         : data.confidence >= 40 ? '#f97316' : '#ef4444';
  const confidenceLabel  = data.confidence >= 80 ? 'High confidence'
                         : data.confidence >= 60 ? 'Good confidence'
                         : data.confidence >= 50 ? 'Moderate confidence' : 'Low confidence';
  const isRuleBased      = data.rule_suggestion !== null;
  const isUncertain      = !isRuleBased && data.confidence < 50;
  const borderColor      = isRuleBased ? '#6366f1' : isUncertain ? '#f59e0b' : confidenceColor;

  const confidenceBar = isRuleBased ? '' : `
    <div style="background:rgba(255,255,255,0.6); padding:10px; border-radius:6px; margin-bottom:12px;">
      <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
        <div style="width:100%; background:#e5e7eb; border-radius:4px; height:6px; overflow:hidden;">
          <div style="background:${confidenceColor}; height:100%; width:${data.confidence}%;"></div>
        </div>
        <span style="color:${confidenceColor}; font-weight:600; font-size:13px; white-space:nowrap;">${confidenceText}</span>
      </div>
      <p style="font-size:12px; color:#64748b; margin:0;">${confidenceLabel}</p>
    </div>`;

  const uncertainBadge   = isUncertain ? '<span style="font-size:11px; color:#d97706; background:#fef3c7; padding:4px 10px; border-radius:4px; font-weight:600;">⚠ Uncertain</span>' : '';
  const uncertainWarning = isUncertain ? '<div style="background:#fef3c7; border:1px solid #fcd34d; padding:10px 12px; border-radius:6px; margin:14px 0; font-size:12px; color:#92400e; line-height:1.5;"><strong>⚠ Low Confidence:</strong> Confidence is below 50%. Verify with technician expertise or request more details from customer.</div>' : '';

  // Individual symptom diagnoses (multi-symptom only)
  let individualDiagnosesHtml = '';
  if (data.detected_symptoms.length > 1 && data.symptom_diagnoses) {
    individualDiagnosesHtml = '<div style="margin-bottom:16px; padding:14px; background:#f0fdf4; border-radius:8px; border-left:4px solid #16a34a;"><p style="font-size:11px; font-weight:700; color:#166534; margin:0 0 12px; text-transform:uppercase; letter-spacing:0.5px;">Individual Symptom Diagnoses</p>';
    for (const symptom of data.detected_symptoms) {
      const label      = symptomLabels[symptom] || symptom.replace(/_/g, ' ');
      const diagnosis  = data.symptom_diagnoses[symptom] || 'Unknown';
      const partsList  = data.symptom_parts[symptom] || [];
      individualDiagnosesHtml += `<div style="margin-bottom:10px;">
        <div style="display:flex; gap:8px; align-items:baseline; margin-bottom:6px;">
          <span style="color:#16a34a; font-size:16px;">→</span>
          <div>
            <div style="font-size:13px; font-weight:600; color:#1e293b;">${label}</div>
            <div style="font-size:12px; color:#16a34a; font-weight:600; margin-top:2px;">${diagnosis}</div>
          </div>
        </div>`;
      if (partsList.length > 0) {
        individualDiagnosesHtml += `<div style="font-size:12px; color:#475569; margin-left:24px; display:flex; flex-direction:column; gap:2px;">`;
        partsList.forEach(part => { individualDiagnosesHtml += `<div>◦ ${part}</div>`; });
        individualDiagnosesHtml += `</div>`;
      }
      individualDiagnosesHtml += `</div>`;
    }
    individualDiagnosesHtml += '</div>';
  }

  // Parts by symptom
  let symptomPartsHtml = '';
  if (data.symptom_parts && Object.keys(data.symptom_parts).length > 0) {
    symptomPartsHtml = '<div style="margin-top:16px; padding-top:16px; border-top:1px solid #e2e8f0;"><p style="font-size:11px; font-weight:700; color:#64748b; margin:0 0 12px; text-transform:uppercase; letter-spacing:0.5px;">Possible Replacement Parts</p>';
    for (const [symptom, parts] of Object.entries(data.symptom_parts)) {
      const label = symptomLabels[symptom] || symptom.replace(/_/g, ' ');
      symptomPartsHtml += `<div style="margin-bottom:12px;">
        <span style="font-size:12px; color:#0f766e; font-weight:600; display:block; margin-bottom:6px;">→ ${label}</span>
        <div style="font-size:12px; color:#475569; display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:8px;">`;
      parts.forEach(part => {
        symptomPartsHtml += `<div style="background:#f8fafc; padding:6px 10px; border-radius:4px; border-left:2px solid #0f766e;">◦ ${part}</div>`;
      });
      symptomPartsHtml += '</div></div>';
    }
    symptomPartsHtml += '</div>';
  }

  box.style.display = 'block';
  box.innerHTML = `
    <div style="border-left:6px solid ${borderColor}; padding:16px;">
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
      ${confidenceBar}
      <div style="margin-bottom:16px;">
        <p style="font-size:11px; font-weight:700; color:#64748b; margin:0 0 8px; text-transform:uppercase; letter-spacing:0.5px;">Detected Symptoms</p>
        <p style="font-size:14px; color:#1e293b; margin:0; line-height:1.6;">${detectedSymptoms}</p>
      </div>
      ${individualDiagnosesHtml}
      ${symptomPartsHtml}
    </div>`;

  document.getElementById('aiDiagnosis').value      = data.diagnosis;
  document.getElementById('aiSuggestedParts').value = data.symptom_parts
    ? Object.values(data.symptom_parts).flat().join(', ') : '';
}

/* ── AI Re-Diagnosis (Edit Modal) ────────────────────────────────────────── */

async function runEditDiagnosis() {
  const description = document.getElementById('edit-issue').value.trim();
  if (!description) { alert('Please describe the issue first.'); return; }

  const csrfToken = getCsrf();
  const response  = await fetch(REPAIRS_CONFIG.diagnoseUrl, {
    method : 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
    body   : JSON.stringify({ description }),
  });

  const data = await response.json();
  const box  = document.getElementById('editAiResultBox');

  if (!data.success) {
    box.style.display = 'block';
    box.innerHTML = `<span style="color:#dc2626; font-weight:600;">✕ ${data.error}</span>`;
    return;
  }

  const detectedSymptoms = data.detected_symptoms.map(s => symptomLabels[s] || s).join(', ');
  const parts    = data.symptom_parts ? Object.values(data.symptom_parts).flat() : [];
  const partTags = parts.map(p =>
    `<span style="display:inline-block;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;padding:3px 8px;border-radius:12px;font-size:11px;margin:2px;">🔩 ${p}</span>`
  ).join('');

  box.style.display = 'block';
  box.innerHTML = `
    <div style="margin-bottom:8px;">
      <span style="font-size:11px; color:#6366f1; font-weight:700; text-transform:uppercase;">🤖 AI Diagnosis</span>
      <p style="font-size:15px; font-weight:700; color:#1e293b; margin:4px 0 0;">${data.diagnosis}</p>
    </div>
    <p style="font-size:11px; color:#64748b; margin:0 0 6px;"><strong>Symptoms:</strong> ${detectedSymptoms}</p>
    <div style="display:flex; flex-wrap:wrap; gap:4px;">${partTags}</div>`;

  document.getElementById('edit-ai-diagnosis').value = data.diagnosis;
  document.getElementById('edit-ai-parts').value     = parts.join(', ');
}

/* ── Feedback Modal ──────────────────────────────────────────────────────── */

let currentFeedbackData = {};

function openFeedback(i) {
  const r = repairs[i];
  document.getElementById('feedback-jobid').value              = r.id;
  document.getElementById('feedback-ai-diagnosis').value       = r.issue;
  document.getElementById('feedback-ai-display').textContent   = r.issue;
  document.getElementById('feedback-incorrect-section').style.display = 'none';
  document.getElementById('feedback-actual-diagnosis').value   = '';
  document.getElementById('feedback-root-cause').value         = '';
  document.getElementById('feedback-actual-parts').value       = '';
  document.getElementById('feedback-notes').value              = '';
  document.getElementById('btn-correct').style.opacity         = '1';
  document.getElementById('btn-incorrect').style.opacity       = '1';
  currentFeedbackData = { jobid: r.id, aiDiagnosis: r.issue, correct: undefined };
  openModal('feedbackModal');
}

function feedbackCorrect(isCorrect) {
  currentFeedbackData.correct = isCorrect;
  document.getElementById('btn-correct').style.opacity   = isCorrect ? '1' : '0.6';
  document.getElementById('btn-incorrect').style.opacity = isCorrect ? '0.6' : '1';
  document.getElementById('feedback-incorrect-section').style.display = isCorrect ? 'none' : 'block';
}

async function saveFeedback() {
  const jobId     = document.getElementById('feedback-jobid').value;
  const isCorrect = currentFeedbackData.correct;

  if (isCorrect === undefined) { alert('Please indicate if the diagnosis was correct.'); return; }
  if (!isCorrect && !document.getElementById('feedback-actual-diagnosis').value.trim()) {
    alert('Please enter the actual diagnosis.');
    return;
  }

  const csrfToken = getCsrf();
  const response  = await fetch(REPAIRS_CONFIG.feedbackUrl, {
    method : 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
    body   : JSON.stringify({
      job_id           : jobId,
      ai_diagnosis     : currentFeedbackData.aiDiagnosis,
      diagnosis_correct: isCorrect,
      actual_diagnosis : isCorrect ? null : document.getElementById('feedback-actual-diagnosis').value.trim(),
      root_cause       : isCorrect ? null : document.getElementById('feedback-root-cause').value.trim(),
      parts_replaced   : isCorrect ? null : document.getElementById('feedback-actual-parts').value.trim(),
      notes            : document.getElementById('feedback-notes').value.trim(),
    }),
  });

  const data = await response.json();
  if (data.success) {
    alert('✓ Feedback saved! This data will improve the AI model.');
    closeModal('feedbackModal');
  } else {
    alert('Error saving feedback: ' + (data.error || 'Unknown error'));
  }
}

/* ── Parts Selection Modal ───────────────────────────────────────────────── */

let pendingStatusChange = null; // holds { idx, newStatus } while parts modal is open

// Hook into the status dropdown in edit modal
document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('edit-status').addEventListener('change', function () {
    if (this.value === 'In Progress') {
      const idx = parseInt(document.getElementById('edit-idx').value);
      pendingStatusChange = { idx, newStatus: 'In Progress' };
      openPartsModal(idx);
    }
  });
});

async function openPartsModal(idx) {
  const repair    = repairs[idx];
  const deviceId  = repair.device_id;
  const diagnosis = repair.diagnostic || '';

  document.getElementById('parts-modal-device-id').value          = deviceId;
  document.getElementById('parts-modal-diagnosis').textContent     = diagnosis || 'No AI diagnosis on record';
  document.getElementById('parts-modal-loading').style.display     = 'block';
  document.getElementById('parts-modal-list').style.display        = 'none';
  document.getElementById('parts-modal-empty').style.display       = 'none';
  document.getElementById('return-parts-section').style.display    = 'none';

  openModal('partsModal');

  const csrfToken = getCsrf();

  // ── Fetch parts by diagnosis label ──────────────────────────────────────
  let availableParts = [];
  if (diagnosis) {
    // Split combined diagnosis (e.g., "Battery Issue + Touch Controller Issue") into individual diagnoses
    const diagnoses = diagnosis.split('+').map(d => d.trim()).filter(Boolean);
    
    // Fetch parts for each individual diagnosis and combine
    const allPartsMap = new Map();
    
    for (const individualDiagnosis of diagnoses) {
      try {
        const res  = await fetch(REPAIRS_CONFIG.partsGetByDiagUrl, {
          method : 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
          body   : JSON.stringify({ diagnosis: individualDiagnosis }),
        });
        const data = await res.json();
        
        if (data.parts && data.parts.length > 0) {
          for (const part of data.parts) {
            // Use part ID as key to avoid duplicates
            if (!allPartsMap.has(part.id)) {
              allPartsMap.set(part.id, part);
            } else {
              // If already exists, add to stock quantity
              const existing = allPartsMap.get(part.id);
              existing.stock_quantity += part.stock_quantity;
            }
          }
        }
      } catch (e) {
        console.error('Failed to fetch parts for diagnosis:', individualDiagnosis, e);
      }
    }
    
    availableParts = Array.from(allPartsMap.values());
  }

  document.getElementById('parts-modal-loading').style.display = 'none';

  if (availableParts.length === 0) {
    document.getElementById('parts-modal-empty').style.display = 'block';
  } else {
    renderPartsList(availableParts);
    document.getElementById('parts-modal-list').style.display = 'block';
  }

  // ── Check already-deducted parts (return section) ───────────────────────
  try {
    const usedRes  = await fetch(REPAIRS_CONFIG.partsGetUsedUrl, {
      method : 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
      body   : JSON.stringify({ device_id: deviceId }),
    });
    const usedData = await usedRes.json();
    if (usedData.used_parts && usedData.used_parts.length > 0) {
      renderReturnSection(usedData.used_parts);
    }
  } catch (e) { /* silent */ }
}

function renderPartsList(parts) {
  const list = document.getElementById('parts-modal-list');
  list.innerHTML = parts.map(p => {
    const outOfStock = p.stock_quantity <= 0;
    const lowStock   = p.stock_quantity > 0 && p.stock_quantity <= 3;
    const stockColor = outOfStock ? '#ef4444' : lowStock ? '#f59e0b' : '#16a34a';
    const stockLabel = outOfStock ? 'Out of Stock' : `${p.stock_quantity} in stock`;

    return `
    <div style="display:flex; align-items:center; gap:12px; padding:12px; border:1px solid #e2e8f0; border-radius:8px; margin-bottom:8px; background:${outOfStock ? '#fff5f5' : '#fff'};">
      <input type="checkbox"
        id="part-check-${p.id}"
        data-part-id="${p.id}"
        data-part-name="${p.part_name}"
        ${outOfStock ? 'disabled' : ''}
        style="width:16px; height:16px; cursor:${outOfStock ? 'not-allowed' : 'pointer'}; accent-color:#0284c7;">
      <div style="flex:1;">
        <p style="margin:0; font-size:14px; font-weight:600; color:${outOfStock ? '#94a3b8' : '#1e293b'};">${p.part_name}</p>
        <p style="margin:2px 0 0; font-size:11px; color:#94a3b8;">${p.category}</p>
      </div>
      <div style="text-align:right;">
        <span style="font-size:12px; font-weight:700; color:${stockColor};">${stockLabel}</span>
        <div style="display:flex; align-items:center; gap:6px; margin-top:4px; justify-content:flex-end;">
          <label style="font-size:11px; color:#64748b;">Qty:</label>
          <input type="number"
            id="part-qty-${p.id}"
            min="1" max="${p.stock_quantity}"
            value="1"
            ${outOfStock ? 'disabled' : ''}
            style="width:52px; padding:3px 6px; border:1px solid #e2e8f0; border-radius:4px; font-size:12px; text-align:center;">
        </div>
      </div>
    </div>`;
  }).join('');
}

function renderReturnSection(usedParts) {
  const section = document.getElementById('return-parts-section');
  const list    = document.getElementById('return-parts-list');

  list.innerHTML = usedParts.map(u => `
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
      <input type="checkbox" id="return-check-${u.usage_id}" data-usage-id="${u.usage_id}"
        style="width:15px; height:15px; accent-color:#f59e0b; cursor:pointer;">
      <label for="return-check-${u.usage_id}" style="font-size:13px; color:#1e293b; cursor:pointer; flex:1;">
        ${u.part_name} <span style="color:#94a3b8; font-size:12px;">(x${u.quantity})</span>
      </label>
    </div>
  `).join('');

  section.style.display = 'block';
}

async function confirmPartsSelection() {
  const deviceId  = document.getElementById('parts-modal-device-id').value;
  const csrfToken = getCsrf();
  const btn       = document.getElementById('parts-confirm-btn');

  // Gather selected parts
  const checkedParts = [];
  document.querySelectorAll('#parts-modal-list input[type="checkbox"]:checked').forEach(cb => {
    const partId  = cb.dataset.partId;
    const qty     = parseInt(document.getElementById(`part-qty-${partId}`).value || 1);
    checkedParts.push({ part_id: partId, quantity: qty });
  });

  // Gather parts to return
  const returnIds = [];
  document.querySelectorAll('#return-parts-list input[type="checkbox"]:checked').forEach(cb => {
    returnIds.push(cb.dataset.usageId);
  });

  btn.disabled = true;
  btn.textContent = 'Processing...';

  try {
    // Deduct selected parts
    if (checkedParts.length > 0) {
      const deductRes  = await fetch(REPAIRS_CONFIG.partsDeductUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
        body: JSON.stringify({ device_id: deviceId, parts_used: checkedParts }),
      });
      const deductData = await deductRes.json();
      if (!deductData.success) {
        alert('⚠️ ' + deductData.error);
        btn.disabled = false;
        btn.textContent = '✓ Confirm & Deduct Stock';
        return;
      }
    }

    // Return unchecked parts
    if (returnIds.length > 0) {
      await fetch(REPAIRS_CONFIG.partsReturnUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
        body: JSON.stringify({ device_id: deviceId, part_ids: returnIds }),
      });
    }

    closeModal('partsModal');
    alert('✓ Parts updated and stock adjusted!');
    location.reload();

  } catch (e) {
    alert('Error: ' + e.message);
    btn.disabled = false;
    btn.textContent = '✓ Confirm & Deduct Stock';
  }
}
/* ── Init ────────────────────────────────────────────────────────────────── */
renderTable();