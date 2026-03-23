<div class="profile-page">
    <div class="profile-container" style="max-width:800px;">

        <!-- Header -->
        <div style="margin-bottom:24px;">
            <h1 style="font-size:26px; color:#1e293b; margin:0 0 4px;">🛡️ Super Admin Panel</h1>
            <p style="color:#64748b; margin:0;">Manage accounts and monitor technician performance</p>
        </div>

        <!-- Admin Identity Card -->
        <div class="profile-card" style="display:flex; align-items:center; gap:20px; margin-bottom:20px; padding:24px; border-left: 4px solid #0284c7;">
            <div style="
                width:72px; height:72px; border-radius:50%; flex-shrink:0;
                background:linear-gradient(135deg,#f59e0b,#d97706);
                display:flex; align-items:center; justify-content:center;
                font-size:1.8rem; font-weight:700; color:white;
                box-shadow:0 4px 14px rgba(245,158,11,0.35);
            "><?= htmlspecialchars($profile['avatar'] ?? strtoupper(substr($profile['username'] ?? 'A', 0, 2))) ?></div>
            <div style="flex:1;">
                <p style="font-size:1.2rem; font-weight:700; color:#1e293b; margin:0 0 2px;">
                    <?= htmlspecialchars($profile['full_name'] ?? $profile['username'] ?? 'Admin') ?>
                </p>
                <p style="font-size:13px; color:#64748b; margin:0 0 10px;">
                    <?= htmlspecialchars($profile['email'] ?? '') ?>
                </p>
                <span style="
                    display:inline-block; padding:3px 10px; border-radius:20px;
                    background:#fef3c7; color:#92400e;
                    font-size:11px; font-weight:700; letter-spacing:0.4px;
                ">⭐ SUPER ADMIN</span>
            </div>
            <div style="text-align:center; padding:14px 20px; background:#fef3c7; border-radius:10px;">
                <p style="font-size:2rem; font-weight:700; color:#d97706; margin:0; line-height:1;">
                    <?= count($technicians ?? []) ?>
                </p>
                <p style="font-size:11px; color:#92400e; margin:4px 0 0; text-transform:uppercase; letter-spacing:0.5px;">Technicians</p>
            </div>
        </div>

        <!-- Technician List -->
        <div class="profile-card" style="margin-bottom:20px;">
            <!-- Header row with Add button -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <p style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px; margin:0;">
                    👨‍🔧 Technician Overview — Click to view stats
                </p>
                <button onclick="openAddTechModal()"
                    style="padding:7px 16px; background:linear-gradient(135deg,#38bdf8,#0284c7); color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; box-shadow:0 3px 10px rgba(56,189,248,0.3);">
                    + Add Technician
                </button>
            </div>

            <div style="display:flex; flex-direction:column; gap:10px;">
                <?php foreach ($technicians ?? [] as $tech): ?>
                <div style="background:#f8fafc; border-radius:10px; border:1px solid #e2e8f0;">

                    <!-- Clickable header row -->
                    <div
                        onclick="toggleTechStats('tech-<?= $tech['id'] ?>')"
                        style="padding:16px 18px; cursor:pointer; transition:border-color .2s;"
                        onmouseover="this.parentElement.style.borderColor='#38bdf8'; this.parentElement.style.boxShadow='0 2px 12px rgba(56,189,248,0.12)';"
                        onmouseout="this.parentElement.style.borderColor='#e2e8f0'; this.parentElement.style.boxShadow='none';"
                    >
                        <div style="display:flex; align-items:center; gap:14px;">
                            <div style="
                                width:42px; height:42px; border-radius:50%; flex-shrink:0;
                                background:linear-gradient(135deg,#38bdf8,#0284c7);
                                display:flex; align-items:center; justify-content:center;
                                font-size:1rem; font-weight:700; color:white;
                            "><?= htmlspecialchars($tech['avatar'] ?? strtoupper(substr($tech['full_name'] ?? 'T', 0, 2))) ?></div>

                            <div style="flex:1;">
                                <p style="font-size:15px; font-weight:700; color:#1e293b; margin:0 0 2px;">
                                    <?= htmlspecialchars($tech['full_name'] ?? $tech['username']) ?>
                                </p>
                                <p style="font-size:12px; color:#64748b; margin:0;">
                                    <?= htmlspecialchars($tech['specialty'] ?? 'General Repairs') ?>
                                </p>
                            </div>

                            <div style="display:flex; gap:8px; align-items:center;">
                                <span style="padding:3px 10px; border-radius:20px; background:#dcfce7; color:#166534; font-size:11px; font-weight:700;">
                                    ✓ <?= $tech['completedJobs'] ?? 0 ?> done
                                </span>
                                <?php if (($tech['pendingJobs'] ?? 0) > 0): ?>
                                <span style="padding:3px 10px; border-radius:20px; background:#fef9c3; color:#713f12; font-size:11px; font-weight:700;">
                                    ⏳ <?= $tech['pendingJobs'] ?> pending
                                </span>
                                <?php endif; ?>

                                <!-- Edit / Delete buttons (stop propagation so they don't toggle stats) -->
                                <button
                                    onclick="event.stopPropagation(); openEditTechModal(<?= htmlspecialchars(json_encode($tech)) ?>)"
                                    style="padding:4px 10px; background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; border-radius:6px; font-size:11px; font-weight:600; cursor:pointer;">
                                    ✏️ Edit
                                </button>
                                <button
                                    onclick="event.stopPropagation(); openDeleteTechModal(<?= $tech['id'] ?>, '<?= htmlspecialchars($tech['full_name'] ?? $tech['username'], ENT_QUOTES) ?>')"
                                    style="padding:4px 10px; background:#fee2e2; color:#dc2626; border:1px solid #fca5a5; border-radius:6px; font-size:11px; font-weight:600; cursor:pointer;">
                                    🗑 Delete
                                </button>

                                <span style="color:#94a3b8; font-size:18px; transition:transform .2s;" id="arrow-tech-<?= $tech['id'] ?>">▾</span>
                            </div>
                        </div>
                    </div>

                    <!-- Expandable Stats -->
                    <div id="tech-<?= $tech['id'] ?>" style="display:none; margin:0 18px 16px; padding-top:14px; border-top:1px solid #e2e8f0;">
                        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:10px;">
                            <div style="padding:12px; background:white; border-radius:8px; border:1px solid #e2e8f0; text-align:center;">
                                <p style="font-size:1.4rem; font-weight:700; color:#0284c7; margin:0;"><?= $tech['totalJobs'] ?? 0 ?></p>
                                <p style="font-size:10px; color:#94a3b8; margin:4px 0 0; text-transform:uppercase; letter-spacing:0.4px;">Total</p>
                            </div>
                            <div style="padding:12px; background:white; border-radius:8px; border:1px solid #e2e8f0; text-align:center;">
                                <p style="font-size:1.4rem; font-weight:700; color:#16a34a; margin:0;"><?= $tech['completedJobs'] ?? 0 ?></p>
                                <p style="font-size:10px; color:#94a3b8; margin:4px 0 0; text-transform:uppercase; letter-spacing:0.4px;">Completed</p>
                            </div>
                            <div style="padding:12px; background:white; border-radius:8px; border:1px solid #e2e8f0; text-align:center;">
                                <p style="font-size:1.4rem; font-weight:700; color:#f59e0b; margin:0;"><?= $tech['inProgressJobs'] ?? 0 ?></p>
                                <p style="font-size:10px; color:#94a3b8; margin:4px 0 0; text-transform:uppercase; letter-spacing:0.4px;">In Progress</p>
                            </div>
                            <div style="padding:12px; background:white; border-radius:8px; border:1px solid #e2e8f0; text-align:center;">
                                <p style="font-size:1.4rem; font-weight:700; color:#ef4444; margin:0;"><?= $tech['pendingJobs'] ?? 0 ?></p>
                                <p style="font-size:10px; color:#94a3b8; margin:4px 0 0; text-transform:uppercase; letter-spacing:0.4px;">Pending</p>
                            </div>
                        </div>
                        <?php
                            $total = $tech['totalJobs'] ?? 0;
                            $rate  = $total > 0 ? round(($tech['completedJobs'] / $total) * 100) : 0;
                        ?>
                        <div style="margin-top:12px;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                                <span style="font-size:11px; color:#64748b; font-weight:600;">Completion Rate</span>
                                <span style="font-size:11px; color:#0284c7; font-weight:700;"><?= $rate ?>%</span>
                            </div>
                            <div style="height:6px; background:#e2e8f0; border-radius:99px; overflow:hidden;">
                                <div style="height:100%; width:<?= $rate ?>%; background:linear-gradient(90deg,#38bdf8,#0284c7); border-radius:99px; transition:width .6s ease;"></div>
                            </div>
                        </div>
                    </div>

                </div>
                <?php endforeach; ?>

                <?php if (empty($technicians)): ?>
                <p style="color:#94a3b8; text-align:center; padding:20px 0;">No technicians found.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Admin Actions -->
        <div class="profile-card">
            <p style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:16px;">
                Account Actions
            </p>

            <button onclick="openEditModal()"
                style="width:100%; padding:12px; margin-bottom:10px; background:linear-gradient(135deg,#f59e0b,#d97706); color:white; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; box-shadow:0 4px 12px rgba(245,158,11,0.25); transition:opacity .2s;"
                onmouseover="this.style.opacity='.9'" onmouseout="this.style.opacity='1'">
                ✏️ Edit My Information
            </button>

            <hr class="sep">

            <button onclick="openLogoutModal()"
                style="width:100%; padding:12px; background:transparent; color:#ef4444; border:1px solid #ef4444; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; transition:background .2s, color .2s;"
                onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='transparent'">
                🚪 Logout &amp; End Session
            </button>
        </div>

    </div>
</div>

<!-- ═══════════════════════════════════════════════
     ADD TECHNICIAN MODAL
════════════════════════════════════════════════ -->
<div id="addTechModal" class="modal-overlay" onclick="if(event.target===this) closeAddTechModal()">
    <div class="modal-box" style="width:480px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="font-size:1.1rem; color:#1e293b; margin:0;">👨‍🔧 Add Technician</h2>
            <button onclick="closeAddTechModal()" style="background:none; border:none; font-size:1.4rem; color:#94a3b8; cursor:pointer;">✕</button>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
            <div>
                <label class="modal-label">Username *</label>
                <input type="text" id="add-tech-username" class="modal-input" placeholder="e.g. jsmith">
            </div>
            <div>
                <label class="modal-label">Full Name</label>
                <input type="text" id="add-tech-fullname" class="modal-input" placeholder="e.g. John Smith">
            </div>
            <div>
                <label class="modal-label">Email</label>
                <input type="email" id="add-tech-email" class="modal-input" placeholder="john@example.com">
            </div>
            <div>
                <label class="modal-label">Specialty</label>
                <input type="text" id="add-tech-specialty" class="modal-input" placeholder="e.g. Screen Repair">
            </div>
        </div>

        <div style="margin-bottom:20px;">
            <label class="modal-label">Password * (min 8 characters)</label>
            <input type="password" id="add-tech-password" class="modal-input" placeholder="••••••••">
        </div>

        <div id="add-tech-error" style="display:none; color:#dc2626; font-size:13px; margin-bottom:12px;"></div>

        <div style="display:flex; gap:10px;">
            <button type="button" onclick="closeAddTechModal()"
                style="flex:1; padding:10px; border:1px solid #e2e8f0; border-radius:8px; background:white; color:#64748b; font-weight:600; cursor:pointer;">
                Cancel
            </button>
            <button type="button" onclick="saveAddTech()"
                style="flex:1; padding:10px; background:linear-gradient(135deg,#38bdf8,#0284c7); border:none; border-radius:8px; color:white; font-weight:600; cursor:pointer;">
                Add Technician
            </button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════
     EDIT TECHNICIAN MODAL
════════════════════════════════════════════════ -->
<div id="editTechModal" class="modal-overlay" onclick="if(event.target===this) closeEditTechModal()">
    <div class="modal-box" style="width:480px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="font-size:1.1rem; color:#1e293b; margin:0;">✏️ Edit Technician</h2>
            <button onclick="closeEditTechModal()" style="background:none; border:none; font-size:1.4rem; color:#94a3b8; cursor:pointer;">✕</button>
        </div>

        <input type="hidden" id="edit-tech-id">

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
            <div>
                <label class="modal-label">Full Name</label>
                <input type="text" id="edit-tech-fullname" class="modal-input">
            </div>
            <div>
                <label class="modal-label">Email</label>
                <input type="email" id="edit-tech-email" class="modal-input">
            </div>
            <div>
                <label class="modal-label">Specialty</label>
                <input type="text" id="edit-tech-specialty" class="modal-input">
            </div>
            <div>
                <label class="modal-label">New Password</label>
                <input type="password" id="edit-tech-password" class="modal-input" placeholder="Leave blank to keep current">
            </div>
        </div>

        <div id="edit-tech-error" style="display:none; color:#dc2626; font-size:13px; margin-bottom:12px;"></div>

        <div style="display:flex; gap:10px;">
            <button type="button" onclick="closeEditTechModal()"
                style="flex:1; padding:10px; border:1px solid #e2e8f0; border-radius:8px; background:white; color:#64748b; font-weight:600; cursor:pointer;">
                Cancel
            </button>
            <button type="button" onclick="saveEditTech()"
                style="flex:1; padding:10px; background:linear-gradient(135deg,#f59e0b,#d97706); border:none; border-radius:8px; color:white; font-weight:600; cursor:pointer;">
                Save Changes
            </button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════
     DELETE TECHNICIAN MODAL
════════════════════════════════════════════════ -->
<div id="deleteTechModal" class="modal-overlay" onclick="if(event.target===this) closeDeleteTechModal()">
    <div class="modal-box modal-small" style="text-align:center;">
        <div style="width:56px; height:56px; border-radius:50%; margin:0 auto 16px; background:#fee2e2; display:flex; align-items:center; justify-content:center; font-size:1.5rem;">🗑</div>
        <h3 style="color:#1e293b; margin:0 0 8px;">Delete Technician</h3>
        <p style="color:#64748b; font-size:14px; margin:0 0 6px; line-height:1.6;">
            Are you sure you want to delete
        </p>
        <p style="color:#1e293b; font-size:15px; font-weight:700; margin:0 0 20px;" id="delete-tech-name"></p>
        <p style="color:#94a3b8; font-size:12px; margin:0 0 20px;">This action cannot be undone.</p>
        <input type="hidden" id="delete-tech-id">

        <div id="delete-tech-error" style="display:none; color:#dc2626; font-size:13px; margin-bottom:12px;"></div>

        <div style="display:flex; gap:10px;">
            <button onclick="closeDeleteTechModal()"
                style="flex:1; padding:10px; border:1px solid #e2e8f0; border-radius:8px; background:white; color:#64748b; font-weight:600; cursor:pointer;">
                Cancel
            </button>
            <button onclick="confirmDeleteTech()"
                style="flex:1; padding:10px; background:#ef4444; color:white; border:none; border-radius:8px; font-weight:600; cursor:pointer;">
                Yes, Delete
            </button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════
     EDIT MY INFO MODAL (unchanged)
════════════════════════════════════════════════ -->
<div id="editModal" class="modal-overlay" onclick="if(event.target===this) closeEditModal()">
    <div class="modal-box" style="width:480px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="font-size:1.1rem; color:#1e293b; margin:0;">✏️ Edit Profile</h2>
            <button onclick="closeEditModal()" style="background:none; border:none; font-size:1.4rem; color:#94a3b8; cursor:pointer;">✕</button>
        </div>

        <?= $this->Flash->render() ?>

        <?= $this->Form->create(null, ['type' => 'post', 'url' => ['controller' => 'Dashboard', 'action' => 'updateProfile']]) ?>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                <div>
                    <label class="modal-label">Full Name</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($profile['full_name'] ?? '') ?>" class="modal-input">
                </div>
                <div>
                    <label class="modal-label">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($profile['email'] ?? '') ?>" class="modal-input">
                </div>
                <div>
                    <label class="modal-label">Specialty</label>
                    <input type="text" name="specialty" value="<?= htmlspecialchars($profile['specialty'] ?? '') ?>" class="modal-input">
                </div>
                <div>
                    <label class="modal-label">New Password</label>
                    <input type="password" name="new_password" placeholder="Leave blank to keep current" class="modal-input">
                </div>
            </div>
            <div style="display:flex; gap:10px; margin-top:6px;">
                <button type="button" onclick="closeEditModal()"
                    style="flex:1; padding:10px; border:1px solid #e2e8f0; border-radius:8px; background:white; color:#64748b; font-weight:600; cursor:pointer;">
                    Cancel
                </button>
                <button type="submit"
                    style="flex:1; padding:10px; background:linear-gradient(135deg,#f59e0b,#d97706); border:none; border-radius:8px; color:white; font-weight:600; cursor:pointer;">
                    Save Changes
                </button>
            </div>
        <?= $this->Form->end() ?>
    </div>
</div>

<!-- LOGOUT MODAL (unchanged) -->
<div id="logoutModal" class="modal-overlay" onclick="if(event.target===this) closeLogoutModal()">
    <div class="modal-box modal-small" style="text-align:center;">
        <div style="width:56px; height:56px; border-radius:50%; margin:0 auto 16px; background:#fee2e2; display:flex; align-items:center; justify-content:center; font-size:1.5rem;">🚪</div>
        <h3 style="color:#1e293b; margin:0 0 8px;">Confirm Logout</h3>
        <p style="color:#64748b; font-size:14px; margin:0 0 24px; line-height:1.6;">
            Are you sure you want to end your session?<br>Any unsaved changes will be lost.
        </p>
        <div style="display:flex; gap:10px;">
            <button onclick="closeLogoutModal()"
                style="flex:1; padding:10px; border:1px solid #e2e8f0; border-radius:8px; background:white; color:#64748b; font-weight:600; cursor:pointer;">
                Cancel
            </button>
            <?= $this->Html->link('🚪 Yes, Logout', ['controller' => 'Dashboard', 'action' => 'logout'], [
                'style' => 'flex:1; padding:10px; background:#ef4444; color:white; border:none; border-radius:8px; font-weight:600; cursor:pointer; text-decoration:none; display:inline-block; text-align:center;',
                'escape' => false,
            ]) ?>
        </div>
    </div>
</div>

<script>
/* ── Toggle stats panel ── */
function toggleTechStats(id) {
    const panel = document.getElementById(id);
    const arrow = document.getElementById('arrow-' + id);
    const isOpen = panel.style.display === 'block';
    panel.style.display = isOpen ? 'none' : 'block';
    if (arrow) arrow.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
}

/* ── Admin edit/logout modals ── */
function openEditModal()    { document.getElementById('editModal').style.display   = 'flex'; }
function closeEditModal()   { document.getElementById('editModal').style.display   = 'none'; }
function openLogoutModal()  { document.getElementById('logoutModal').style.display = 'flex'; }
function closeLogoutModal() { document.getElementById('logoutModal').style.display = 'none'; }

/* ── CSRF token helper ── */
function getCsrf() {
    return document.querySelector('meta[name="csrfToken"]')?.getAttribute('content') ?? '';
}

/* ── ADD TECHNICIAN ── */
function openAddTechModal() {
    ['add-tech-username','add-tech-fullname','add-tech-email','add-tech-specialty','add-tech-password']
        .forEach(id => document.getElementById(id).value = '');
    document.getElementById('add-tech-error').style.display = 'none';
    document.getElementById('addTechModal').style.display = 'flex';
}
function closeAddTechModal() { document.getElementById('addTechModal').style.display = 'none'; }

async function saveAddTech() {
    const errEl = document.getElementById('add-tech-error');
    errEl.style.display = 'none';

    const body = {
        username:  document.getElementById('add-tech-username').value.trim(),
        full_name: document.getElementById('add-tech-fullname').value.trim(),
        email:     document.getElementById('add-tech-email').value.trim(),
        specialty: document.getElementById('add-tech-specialty').value.trim(),
        password:  document.getElementById('add-tech-password').value,
    };

    try {
        const res  = await fetch('/dashboard/addTechnician', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrf() },
            body: JSON.stringify(body),
        });
        const data = await res.json();

        if (data.success) {
            closeAddTechModal();
            location.reload();
        } else {
            errEl.textContent    = data.error ?? 'Failed to add technician.';
            errEl.style.display  = 'block';
        }
    } catch (e) {
        errEl.textContent   = 'Network error. Please try again.';
        errEl.style.display = 'block';
    }
}

/* ── EDIT TECHNICIAN ── */
function openEditTechModal(tech) {
    document.getElementById('edit-tech-id').value        = tech.id;
    document.getElementById('edit-tech-fullname').value  = tech.full_name  ?? '';
    document.getElementById('edit-tech-email').value     = tech.email      ?? '';
    document.getElementById('edit-tech-specialty').value = tech.specialty  ?? '';
    document.getElementById('edit-tech-password').value  = '';
    document.getElementById('edit-tech-error').style.display = 'none';
    document.getElementById('editTechModal').style.display   = 'flex';
}
function closeEditTechModal() { document.getElementById('editTechModal').style.display = 'none'; }

async function saveEditTech() {
    const errEl = document.getElementById('edit-tech-error');
    errEl.style.display = 'none';

    const body = {
        id:        document.getElementById('edit-tech-id').value,
        full_name: document.getElementById('edit-tech-fullname').value.trim(),
        email:     document.getElementById('edit-tech-email').value.trim(),
        specialty: document.getElementById('edit-tech-specialty').value.trim(),
        password:  document.getElementById('edit-tech-password').value,
    };

    try {
        const res  = await fetch('/dashboard/editTechnician', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrf() },
            body: JSON.stringify(body),
        });
        const data = await res.json();

        if (data.success) {
            closeEditTechModal();
            location.reload();
        } else {
            errEl.textContent   = data.error ?? 'Failed to update technician.';
            errEl.style.display = 'block';
        }
    } catch (e) {
        errEl.textContent   = 'Network error. Please try again.';
        errEl.style.display = 'block';
    }
}

/* ── DELETE TECHNICIAN ── */
function openDeleteTechModal(id, name) {
    document.getElementById('delete-tech-id').value       = id;
    document.getElementById('delete-tech-name').textContent = name;
    document.getElementById('delete-tech-error').style.display = 'none';
    document.getElementById('deleteTechModal').style.display   = 'flex';
}
function closeDeleteTechModal() { document.getElementById('deleteTechModal').style.display = 'none'; }

async function confirmDeleteTech() {
    const errEl = document.getElementById('delete-tech-error');
    errEl.style.display = 'none';

    const body = { id: document.getElementById('delete-tech-id').value };

    try {
        const res  = await fetch('/dashboard/deleteTechnician', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrf() },
            body: JSON.stringify(body),
        });
        const data = await res.json();

        if (data.success) {
            closeDeleteTechModal();
            location.reload();
        } else {
            errEl.textContent   = data.error ?? 'Failed to delete technician.';
            errEl.style.display = 'block';
        }
    } catch (e) {
        errEl.textContent   = 'Network error. Please try again.';
        errEl.style.display = 'block';
    }
}
</script>