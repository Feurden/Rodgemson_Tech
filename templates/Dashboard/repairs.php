<div class="page-section">

    <!-- Header Row -->
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
        <div>
            <h1 style="font-size:26px; color:#1e293b; margin:0 0 4px;">🔧 Repair Dashboard</h1>
            <p style="color:#64748b; margin:0;">Overview of devices currently under repair</p>
        </div>
        <button class="btn-new-repair" onclick="document.getElementById('newRepairModal').style.display='flex'">
            + New Repair
        </button>
    </div>

    <!-- Summary Cards -->
    <div style="display:flex; gap:14px; margin-bottom:24px; flex-wrap:wrap;">

        <div class="repair-stat-card" style="border-left:4px solid #38bdf8;">
            <span class="repair-stat-label">Total Jobs</span>
            <span class="repair-stat-value">24</span>
        </div>

        <div class="repair-stat-card" style="border-left:4px solid #16a34a;">
            <span class="repair-stat-label">Completed</span>
            <span class="repair-stat-value" style="color:#16a34a;">18</span>
        </div>

        <div class="repair-stat-card" style="border-left:4px solid #2563eb;">
            <span class="repair-stat-label">In Progress</span>
            <span class="repair-stat-value" style="color:#2563eb;">4</span>
        </div>

        <div class="repair-stat-card" style="border-left:4px solid #f59e0b;">
            <span class="repair-stat-label">Pending</span>
            <span class="repair-stat-value" style="color:#f59e0b;">2</span>
        </div>

    </div>

    <!-- Filter & Search Row -->
    <div style="display:flex; gap:10px; margin-bottom:16px; flex-wrap:wrap; align-items:center;">

        <input type="text" placeholder="🔍  Search by job ID, device, or technician..."
            style="flex:1; min-width:220px; padding:9px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:14px; outline:none; color:#1e293b;"
            oninput="filterTable(this.value)"
        >

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
        <table class="dashboard-table" id="repairsTable">
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

            <tbody id="repairsBody">
                <tr data-status="completed">
                    <td><span class="job-id">R001</span></td>
                    <td>💻 Laptop</td>
                    <td>Screen replacement</td>
                    <td>John Doe</td>
                    <td style="color:#94a3b8; font-size:13px;">Feb 25, 2026</td>
                    <td><span class="status-badge badge-completed">Completed</span></td>
                    <td style="text-align:center;">
                        <button class="tbl-btn tbl-btn-view">View</button>
                        <button class="tbl-btn tbl-btn-edit">Edit</button>
                    </td>
                </tr>

                <tr data-status="progress">
                    <td><span class="job-id">R002</span></td>
                    <td>📱 Smartphone</td>
                    <td>Battery swap</td>
                    <td>Jane Smith</td>
                    <td style="color:#94a3b8; font-size:13px;">Feb 26, 2026</td>
                    <td><span class="status-badge badge-progress">In Progress</span></td>
                    <td style="text-align:center;">
                        <button class="tbl-btn tbl-btn-view">View</button>
                        <button class="tbl-btn tbl-btn-edit">Edit</button>
                    </td>
                </tr>

                <tr data-status="pending">
                    <td><span class="job-id">R003</span></td>
                    <td>🖥️ Desktop PC</td>
                    <td>Power supply issue</td>
                    <td>Mark Lee</td>
                    <td style="color:#94a3b8; font-size:13px;">Feb 27, 2026</td>
                    <td><span class="status-badge badge-pending">Pending</span></td>
                    <td style="text-align:center;">
                        <button class="tbl-btn tbl-btn-view">View</button>
                        <button class="tbl-btn tbl-btn-edit">Edit</button>
                    </td>
                </tr>

                <tr data-status="progress">
                    <td><span class="job-id">R004</span></td>
                    <td>🖨️ Printer</td>
                    <td>Paper jam / roller</td>
                    <td>Jane Smith</td>
                    <td style="color:#94a3b8; font-size:13px;">Feb 28, 2026</td>
                    <td><span class="status-badge badge-progress">In Progress</span></td>
                    <td style="text-align:center;">
                        <button class="tbl-btn tbl-btn-view">View</button>
                        <button class="tbl-btn tbl-btn-edit">Edit</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Empty state -->
    <div id="emptyState" style="display:none; text-align:center; padding:60px 20px; color:#94a3b8;">
        <div style="font-size:2.5rem; margin-bottom:10px;">🔍</div>
        <p style="font-size:15px;">No repairs match your search.</p>
    </div>

</div>

<!-- ── New Repair Modal ── -->
<div class="modal-overlay" id="newRepairModal" onclick="if(event.target===this) this.style.display='none'">
    <div class="modal-box" style="width:480px;">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="font-size:1.1rem; color:#1e293b;">+ New Repair Job</h2>
            <button onclick="document.getElementById('newRepairModal').style.display='none'"
                style="background:none; border:none; font-size:1.4rem; color:#94a3b8; cursor:pointer;">✕</button>
        </div>

        <form method="post">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">

                <div>
                    <label class="modal-label">Job ID</label>
                    <input type="text" placeholder="e.g. R005" class="modal-input">
                </div>

                <div>
                    <label class="modal-label">Device</label>
                    <input type="text" placeholder="e.g. Laptop" class="modal-input">
                </div>

                <div>
                    <label class="modal-label">Technician</label>
                    <input type="text" placeholder="Full name" class="modal-input">
                </div>

                <div>
                    <label class="modal-label">Date</label>
                    <input type="date" class="modal-input">
                </div>

            </div>

            <div style="margin-bottom:14px;">
                <label class="modal-label">Issue Description</label>
                <textarea placeholder="Describe the issue..." class="modal-input" rows="3" style="resize:vertical;"></textarea>
            </div>

            <div style="margin-bottom:20px;">
                <label class="modal-label">Status</label>
                <select class="modal-input">
                    <option>Pending</option>
                    <option>In Progress</option>
                    <option>Completed</option>
                </select>
            </div>

            <div style="display:flex; gap:10px;">
                <button type="button" onclick="document.getElementById('newRepairModal').style.display='none'"
                    style="flex:1; padding:10px; border:1px solid #e2e8f0; border-radius:8px; background:white; color:#64748b; font-weight:600; cursor:pointer;">
                    Cancel
                </button>
                <button type="submit"
                    style="flex:1; padding:10px; background:linear-gradient(135deg,#38bdf8,#0284c7); border:none; border-radius:8px; color:white; font-weight:600; cursor:pointer;">
                    Save Repair
                </button>
            </div>
        </form>

    </div>
</div>

<script>
function filterTable(val) {
    const rows = document.querySelectorAll('#repairsBody tr');
    const q = val.toLowerCase();
    let visible = 0;
    rows.forEach(r => {
        const match = r.innerText.toLowerCase().includes(q);
        r.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    document.getElementById('emptyState').style.display = visible === 0 ? 'block' : 'none';
}

function filterStatus(val) {
    const rows = document.querySelectorAll('#repairsBody tr');
    rows.forEach(r => {
        r.style.display = (val === 'all' || r.dataset.status === val) ? '' : 'none';
    });
}
</script>