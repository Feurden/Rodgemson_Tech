<div class="profile-page">
    <div class="profile-container" style="max-width:700px;">

        <!-- Header -->
        <div style="margin-bottom:24px;">
            <h1 style="font-size:26px; color:#1e293b; margin:0 0 4px;">👤 Technician Account</h1>
            <p style="color:#64748b; margin:0;">Manage your profile and system access</p>
        </div>

        <!-- Avatar + Name Card -->
        <div class="profile-card" style="display:flex; align-items:center; gap:20px; margin-bottom:20px; padding:24px;">
            <div style="
                width:72px; height:72px; border-radius:50%; flex-shrink:0;
                background:linear-gradient(135deg,#38bdf8,#0284c7);
                display:flex; align-items:center; justify-content:center;
                font-size:1.8rem; font-weight:700; color:white;
                box-shadow:0 4px 14px rgba(56,189,248,0.35);
            "><?= strtoupper(substr($profile['username'] ?? 'User', 0, 2)) ?></div>
            <div style="flex:1;">
                <p style="font-size:1.2rem; font-weight:700; color:#1e293b; margin:0 0 2px;"><?= htmlspecialchars($profile['username'] ?? 'Technician') ?></p>
                <p style="font-size:13px; color:#64748b; margin:0 0 10px;"><?= htmlspecialchars($profile['role'] ?? 'Tech') ?></p>
                <span class="status-badge badge-completed" style="font-size:11px;">🔧 Cellphone Repairs</span>
            </div>
            <div style="text-align:center; padding:14px 20px; background:#f1f5f9; border-radius:10px;">
                <p style="font-size:2rem; font-weight:700; color:#0284c7; margin:0; line-height:1;"><?= $profile['completedJobs'] ?? 0 ?></p>
                <p style="font-size:11px; color:#94a3b8; margin:4px 0 0; text-transform:uppercase; letter-spacing:0.5px;">Jobs Done</p>
            </div>
        </div>

        <!-- Info Card -->
        <div class="profile-card" style="margin-bottom:20px;">

            <p style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:16px;">
                Account Statistics
            </p>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

                <div style="padding:14px; background:#f8fafc; border-radius:10px; border:1px solid #e2e8f0;">
                    <p style="font-size:11px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:0.4px; margin:0 0 4px;">Username</p>
                    <p style="font-size:15px; font-weight:600; color:#1e293b; margin:0;"><?= htmlspecialchars($profile['username'] ?? 'User') ?></p>
                </div>

                <div style="padding:14px; background:#f8fafc; border-radius:10px; border:1px solid #e2e8f0;">
                    <p style="font-size:11px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:0.4px; margin:0 0 4px;">Role</p>
                    <p style="font-size:15px; font-weight:600; color:#1e293b; margin:0;"><?= htmlspecialchars($profile['role'] ?? 'Technician') ?></p>
                </div>

                <div style="padding:14px; background:#f8fafc; border-radius:10px; border:1px solid #e2e8f0;">
                    <p style="font-size:11px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:0.4px; margin:0 0 4px;">Completed Jobs</p>
                    <p style="font-size:15px; font-weight:600; color:#1e293b; margin:0;"><?= $profile['completedJobs'] ?? 0 ?> jobs</p>
                </div>

                <div style="padding:14px; background:#f8fafc; border-radius:10px; border:1px solid #e2e8f0;">
                    <p style="font-size:11px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:0.4px; margin:0 0 4px;">In Progress</p>
                    <p style="font-size:15px; font-weight:600; color:#1e293b; margin:0;"><?= $profile['inProgressJobs'] ?? 0 ?> jobs</p>
                </div>

            </div>
        </div>

        <!-- Actions Card -->
        <div class="profile-card">
            <p style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:16px;">
                Account Actions
            </p>

            <button onclick="openEditModal()"
                style="
                    width:100%; padding:12px; margin-bottom:10px;
                    background:linear-gradient(135deg,#38bdf8,#0284c7);
                    color:white; border:none; border-radius:8px;
                    font-size:14px; font-weight:600; cursor:pointer;
                    box-shadow:0 4px 12px rgba(56,189,248,0.25);
                    transition:opacity .2s;
                "
                onmouseover="this.style.opacity='.9'"
                onmouseout="this.style.opacity='1'"
            >
                ✏️ Edit Information
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
                🚪 Logout & End Session
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

        <form method="post">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                <div>
                    <label class="modal-label">Full Name</label>
                    <input type="text" value="John Doe" class="modal-input">
                </div>
                <div>
                    <label class="modal-label">Email</label>
                    <input type="email" value="john.doe@example.com" class="modal-input">
                </div>
                <div>
                    <label class="modal-label">Specialty</label>
                    <input type="text" value="Laptop & Mobile Repairs" class="modal-input">
                </div>
                <div>
                    <label class="modal-label">New Password</label>
                    <input type="password" placeholder="Leave blank to keep current" class="modal-input">
                </div>
            </div>

            <div style="display:flex; gap:10px; margin-top:6px;">
                <button type="button" onclick="closeEditModal()"
                    style="flex:1; padding:10px; border:1px solid #e2e8f0; border-radius:8px; background:white; color:#64748b; font-weight:600; cursor:pointer;">
                    Cancel
                </button>
                <button type="submit"
                    style="flex:1; padding:10px; background:linear-gradient(135deg,#38bdf8,#0284c7); border:none; border-radius:8px; color:white; font-weight:600; cursor:pointer;">
                    Save Changes
                </button>
            </div>
        </form>

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
                    'escape' => false
                ]
            ) ?>
        </div>

    </div>
</div>

<script>
function openEditModal(){
    document.getElementById("editModal").style.display="flex";
}
function closeEditModal(){
    document.getElementById("editModal").style.display="none";
}
function openLogoutModal(){
    document.getElementById("logoutModal").style.display="flex";
}
function closeLogoutModal(){
    document.getElementById("logoutModal").style.display="none";
}
</script>