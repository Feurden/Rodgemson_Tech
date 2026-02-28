<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrfToken" content="<?= $this->request->getAttribute('csrfToken') ?>">
<title>CellFix – Repair Dashboard</title>
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
<div class="modal-overlay" id="newRepairModal" onclick="if(event.target===this) closeModal('newRepairModal')">
  <div class="modal-box">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
      <h2 style="font-size:1.1rem; color:#1e293b;">+ New Repair Job</h2>
      <button onclick="closeModal('newRepairModal')" style="background:none;border:none;font-size:1.4rem;color:#94a3b8;cursor:pointer;">✕</button>
    </div>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
      <div>
        <label class="modal-label">Job ID</label>
        <input type="text" id="new-jobid" placeholder="e.g. R005" class="modal-input">
      </div>
      <div>
        <label class="modal-label">Device</label>
        <input type="text" id="new-device" placeholder="e.g. iPhone 13" class="modal-input">
      </div>
      <div>
        <label class="modal-label">Technician</label>
        <input type="text" id="new-tech" placeholder="Full name" class="modal-input">
      </div>
      <div>
        <label class="modal-label">Date Received</label>
        <input type="date" id="new-date" class="modal-input">
      </div>
    </div>
    <div style="margin-bottom:14px;">
      <label class="modal-label">Issue Description</label>
      <textarea id="new-issue"
          placeholder="Describe the issue..."
          class="modal-input"
          rows="3">
        </textarea>
      <button type="button"
          onclick="runDiagnosis()"
          style="margin-top:8px;padding:8px 12px;background:#6366f1;color:white;border:none;border-radius:6px;cursor:pointer;">
          🔍 Diagnose with AI
      </button>

      <div id="aiResultBox"
          style="display:none;margin-top:12px;padding:12px;background:#f1f5f9;border-radius:8px;font-size:14px;">
      </div>
    </div>
    <div style="margin-bottom:20px;">
      <label class="modal-label">Notes</label>
      <textarea id="new-notes" placeholder="Any initial notes..." class="modal-input" rows="2" style="resize:vertical;"></textarea>
    </div>
    <div style="display:flex; gap:10px;">
      <button onclick="closeModal('newRepairModal')" style="flex:1;padding:10px;border:1px solid #e2e8f0;border-radius:8px;background:white;color:#64748b;font-weight:600;cursor:pointer;">Cancel</button>
      <button onclick="saveNewRepair()" style="flex:1;padding:10px;background:linear-gradient(135deg,#38bdf8,#0284c7);border:none;border-radius:8px;color:white;font-weight:600;cursor:pointer;">Save Repair</button>
    </div>
  </div>
</div>

<!-- ── VIEW MODAL ── -->
<div class="modal-overlay" id="viewModal" onclick="if(event.target===this) closeModal('viewModal')">
  <div class="modal-box">
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
  <div class="modal-box">
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

<script>
let repairs = [
  { id:'R001', device:'iPhone 13',     issue:'Screen replacement',  tech:'John Doe',  date:'Feb 25, 2026', status:'completed', finished:'Feb 25, 2026 4:30 PM', notes:'Replaced with OEM screen. Tested OK.' },
  { id:'R002', device:'Samsung S22',   issue:'Battery swap',        tech:'Jane Smith', date:'Feb 26, 2026', status:'progress',  finished:'', notes:'' },
  { id:'R003', device:'Xiaomi 12',     issue:'Charging port issue', tech:'Mark Lee',   date:'Feb 27, 2026', status:'pending',   finished:'', notes:'' },
  { id:'R004', device:'iPhone 14 Pro', issue:'Camera lens cracked', tech:'Jane Smith', date:'Feb 28, 2026', status:'progress',  finished:'', notes:'' },
];

const statusLabel = { completed:'Completed', progress:'In Progress', pending:'Pending' };
const badgeClass  = { completed:'badge-completed', progress:'badge-progress', pending:'badge-pending' };

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
      <td>${r.tech}</td>
      <td style="color:#94a3b8; font-size:13px;">${r.date}</td>
      <td>${badge(r.status)}</td>
      <td style="text-align:center;">
        <button class="tbl-btn tbl-btn-view" onclick="openView(${i})">View</button>
        <button class="tbl-btn tbl-btn-edit" onclick="openEdit(${i})">Edit</button>
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
  ['new-jobid','new-device','new-tech','new-issue','new-notes'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('new-date').value = new Date().toISOString().split('T')[0];
  openModal('newRepairModal');
}
function saveNewRepair() {
  const jobId  = document.getElementById('new-jobid').value.trim();
  const device = document.getElementById('new-device').value.trim();
  const tech   = document.getElementById('new-tech').value.trim();
  const date   = document.getElementById('new-date').value;
  const issue  = document.getElementById('new-issue').value.trim();
  const notes  = document.getElementById('new-notes').value.trim();
  if (!jobId || !device || !tech || !issue) { alert('Please fill in Job ID, Device, Technician, and Issue.'); return; }
  const d = new Date(date);
  repairs.push({ id: jobId, device, issue, tech, date: d.toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' }), status: 'progress', finished: '', notes });
  renderTable();
  closeModal('newRepairModal');
}

/* VIEW */
function openView(i) {
  const r = repairs[i];
  const row = (k, v) => `<div class="view-row"><span class="view-key">${k}</span><span class="view-val">${v}</span></div>`;
  document.getElementById('viewContent').innerHTML = `
    ${row('Job ID', `<span class="job-id">${r.id}</span>`)}
    ${row('Device', '📱 ' + r.device)}
    ${row('Issue', r.issue)}
    ${row('Technician', r.tech)}
    ${row('Date Received', r.date)}
    ${row('Status', badge(r.status))}
    ${row('Date Finished', r.finished || '—')}
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
function saveEdit() {
  const i = parseInt(document.getElementById('edit-idx').value);
  repairs[i].tech   = document.getElementById('edit-tech').value.trim();
  repairs[i].status = document.getElementById('edit-status').value;
  repairs[i].notes  = document.getElementById('edit-notes').value.trim();
  const fin = document.getElementById('edit-finished').value;
  if (fin) {
    const d = new Date(fin);
    repairs[i].finished = d.toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' })
      + ' ' + d.toLocaleTimeString('en-US', { hour:'numeric', minute:'2-digit' });
  } else {
    repairs[i].finished = '';
  }
  renderTable();
  applyFilters();
  closeModal('editModal');
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
        box.innerHTML = `<span style="color:red;">${data.error}</span>`;
        return;
    }

    const confidenceText = data.confidence !== null
        ? (data.confidence * 100).toFixed(1) + "%"
        : "Rule-Based (No ML confidence)";

    box.style.display = "block";
    box.innerHTML = `
        <strong>AI Diagnosis:</strong> ${data.diagnosis}<br>
        <strong>Mode:</strong> ${data.mode}<br>
        <strong>Confidence:</strong> ${confidenceText}<br>
        <strong>Detected Symptoms:</strong> ${data.detected_symptoms.join(", ")}<br>
        <strong>Suggested Parts:</strong> ${data.replacement_parts.join(", ")}
    `;

    // Auto-fill issue field
    document.getElementById('new-issue').value = data.diagnosis;
}
</script>
</body>
</html>