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
            <p style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:16px;">
                👨‍🔧 Technician Overview — Click to view stats
            </p>

            <div style="display:flex; flex-direction:column; gap:10px;">
                <?php foreach ($technicians ?? [] as $tech): ?>
                <div
                    onclick="toggleTechStats('tech-<?= $tech['id'] ?>')"
                    style="
                        padding:16px 18px; background:#f8fafc; border-radius:10px;
                        border:1px solid #e2e8f0; cursor:pointer;
                        transition:box-shadow .2s, border-color .2s;
                    "
                    onmouseover="this.style.borderColor='#38bdf8'; this.style.boxShadow='0 2px 12px rgba(56,189,248,0.12)';"
                    onmouseout="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';"
                >
                    <!-- Technician Row -->
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

                        <!-- Quick badges -->
                        <div style="display:flex; gap:8px; align-items:center;">
                            <span style="padding:3px 10px; border-radius:20px; background:#dcfce7; color:#166534; font-size:11px; font-weight:700;">
                                ✓ <?= $tech['completedJobs'] ?? 0 ?> done
                            </span>
                            <?php if (($tech['pendingJobs'] ?? 0) > 0): ?>
                            <span style="padding:3px 10px; border-radius:20px; background:#fef9c3; color:#713f12; font-size:11px; font-weight:700;">
                                ⏳ <?= $tech['pendingJobs'] ?> pending
                            </span>
                            <?php endif; ?>
                            <span style="color:#94a3b8; font-size:18px; transition:transform .2s;" id="arrow-tech-<?= $tech['id'] ?>">▾</span>
                        </div>
                    </div>

                    <!-- Expandable Stats -->
                    <div id="tech-<?= $tech['id'] ?>" style="display:none; margin-top:16px; padding-top:14px; border-top:1px solid #e2e8f0;">
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

                        <!-- Completion rate bar -->
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
                style="
                    width:100%; padding:12px; margin-bottom:10px;
                    background:linear-gradient(135deg,#f59e0b,#d97706);
                    color:white; border:none; border-radius:8px;
                    font-size:14px; font-weight:600; cursor:pointer;
                    box-shadow:0 4px 12px rgba(245,158,11,0.25);
                    transition:opacity .2s;
                "
                onmouseover="this.style.opacity='.9'"
                onmouseout="this.style.opacity='1'"
            >
                ✏️ Edit My Information
            </button>

            <hr class="sep">

            <button onclick="openLogoutModal()"
                style="
                    width:100%; padding:12px;
                    background:transparent; color:#ef4444;
                    border:1px solid #ef4444; border-radius:8px;
                    font-size:14px; font-weight:600; cursor:pointer;
                    transition:background .2s, color .2s;
                "
                onmouseover="this.style.background='#fee2e2'"
                onmouseout="this.style.background='transparent'"
            >
                🚪 Logout &amp; End Session
            </button>
        </div>

    </div>
</div>

<!-- EDIT MODAL -->
<div id="editModal" class="modal-overlay" onclick="if(event.target===this) closeEditModal()">
    <div class="modal-box" style="width:480px;">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="font-size:1.1rem; color:#1e293b; margin:0;">✏️ Edit Profile</h2>
            <button onclick="closeEditModal()"
                style="background:none; border:none; font-size:1.4rem; color:#94a3b8; cursor:pointer;">✕</button>
        </div>

        <?= $this->Flash->render() ?>

        <?= $this->Form->create(null, [
            'type' => 'post',
            'url'  => ['controller' => 'Dashboard', 'action' => 'updateProfile'],
        ]) ?>
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

<!-- LOGOUT MODAL -->
<div id="logoutModal" class="modal-overlay" onclick="if(event.target===this) closeLogoutModal()">
    <div class="modal-box modal-small" style="text-align:center;">

        <div style="
            width:56px; height:56px; border-radius:50%; margin:0 auto 16px;
            background:#fee2e2; display:flex; align-items:center; justify-content:center;
            font-size:1.5rem;
        ">🚪</div>

        <h3 style="color:#1e293b; margin:0 0 8px;">Confirm Logout</h3>
        <p style="color:#64748b; font-size:14px; margin:0 0 24px; line-height:1.6;">
            Are you sure you want to end your session?<br>Any unsaved changes will be lost.
        </p>

        <div style="display:flex; gap:10px;">
            <button onclick="closeLogoutModal()"
                style="flex:1; padding:10px; border:1px solid #e2e8f0; border-radius:8px; background:white; color:#64748b; font-weight:600; cursor:pointer;">
                Cancel
            </button>
            <?= $this->Html->link(
                '🚪 Yes, Logout',
                ['controller' => 'Dashboard', 'action' => 'logout'],
                [
                    'style' => 'flex:1; padding:10px; background:#ef4444; color:white; border:none; border-radius:8px; font-weight:600; cursor:pointer; text-decoration:none; display:inline-block; text-align:center;',
                    'escape' => false,
                ]
            ) ?>
        </div>

    </div>
</div>

<script>
function toggleTechStats(id) {
    const panel = document.getElementById(id);
    const techId = id.replace('tech-', '');
    const arrow  = document.getElementById('arrow-' + id);
    const isOpen = panel.style.display === 'block';
    panel.style.display = isOpen ? 'none' : 'block';
    if (arrow) arrow.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
}
function openEditModal()    { document.getElementById('editModal').style.display   = 'flex'; }
function closeEditModal()   { document.getElementById('editModal').style.display   = 'none'; }
function openLogoutModal()  { document.getElementById('logoutModal').style.display = 'flex'; }
function closeLogoutModal() { document.getElementById('logoutModal').style.display = 'none'; }
</script>