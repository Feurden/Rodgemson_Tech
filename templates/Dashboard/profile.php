<style>
.modal-overlay{
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.4);
    justify-content:center;
    align-items:center;
    animation: fadeIn 0.25s ease;
    z-index:1000;
}

.modal-box{
    background:white;
    padding:30px;
    border-radius:12px;
    width:400px;
    animation: scaleUp 0.25s ease;
}

@keyframes fadeIn{
    from{ opacity:0; }
    to{ opacity:1; }
}

@keyframes scaleUp{
    from{
        transform:scale(0.85);
        opacity:0;
    }
    to{
        transform:scale(1);
        opacity:1;
    }
}
</style>

<div style="
    min-height:calc(100vh - 70px);
    background:#f1f5f9;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    padding:40px;
    display:flex;
    justify-content:center;
    box-sizing:border-box;
">

    <div style="width:100%; max-width:600px;">

        <h2 style="font-size:24px;color:#1e293b;margin-bottom:4px;">
            Technician Account
        </h2>

        <p style="color:#64748b;margin-bottom:30px;">
            Manage your profile and system access
        </p>

        <div style="
            border:1px solid #e2e8f0;
            background:#ffffff;
            padding:24px;
            border-radius:12px;
        ">

            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:14px;font-weight:600;color:#64748b;">
                    Full Name
                </label>
                <p style="font-size:16px;color:#1e293b;margin:0;">John Doe</p>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:14px;font-weight:600;color:#64748b;">
                    Email
                </label>
                <p style="font-size:16px;color:#1e293b;margin:0;">john.doe@example.com</p>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:14px;font-weight:600;color:#64748b;">
                    Specialty
                </label>
                <p style="font-size:16px;color:#1e293b;margin:0;">
                    Laptop & Mobile Repairs
                </p>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:14px;font-weight:600;color:#64748b;">
                    Completed Jobs
                </label>
                <p style="font-size:16px;color:#1e293b;margin:0;">85</p>
            </div>

            <div style="margin-top:30px;">

                <button onclick="openEditModal()"
                    style="
                        display:block;
                        width:100%;
                        padding:10px;
                        background:#38bdf8;
                        color:#ffffff;
                        border:none;
                        border-radius:6px;
                        font-weight:600;
                        margin-bottom:20px;
                        cursor:pointer;
                    ">
                    Edit Information
                </button>

                <hr style="margin:20px 0;border:0;border-top:1px solid #e2e8f0;">

                <button onclick="openLogoutModal()"
                    style="
                        display:block;
                        width:100%;
                        padding:10px;
                        background:transparent;
                        color:#ef4444;
                        border:1px solid #ef4444;
                        border-radius:6px;
                        font-weight:600;
                        cursor:pointer;
                    ">
                    Logout & End Session
                </button>

            </div>
        </div>
    </div>
</div>

<!-- EDIT MODAL -->
<div id="editModal" class="modal-overlay">
    <div class="modal-box">

        <h3 style="margin-top:0;">Edit Profile</h3>

        <input placeholder="Full Name"
            style="width:100%;padding:10px;margin-bottom:10px;border:1px solid #ddd;border-radius:6px;">

        <input placeholder="Email"
            style="width:100%;padding:10px;margin-bottom:10px;border:1px solid #ddd;border-radius:6px;">

        <button style="
            width:100%;
            padding:10px;
            background:#38bdf8;
            border:none;
            color:white;
            border-radius:6px;
            cursor:pointer;
        ">
            Save Changes
        </button>

        <button onclick="closeEditModal()"
            style="
                width:100%;
                padding:10px;
                margin-top:10px;
                background:#f1f5f9;
                border:none;
                border-radius:6px;
                cursor:pointer;
            ">
            Cancel
        </button>

    </div>
</div>

<!-- LOGOUT MODAL -->
<div id="logoutModal" class="modal-overlay">
    <div class="modal-box" style="width:350px;text-align:center;">

        <h3>Confirm Logout</h3>

        <p style="color:#64748b;">
            Are you sure you want to logout?
        </p>

        <?= $this->Html->link(
            'Yes, Logout',
            ['controller' => 'Dashboard', 'action' => 'logout'],
            ['style'=>'display:block;padding:10px;background:#ef4444;color:white;border-radius:6px;text-decoration:none;margin-top:15px;']
        ) ?>

        <button onclick="closeLogoutModal()"
            style="
                width:100%;
                padding:10px;
                margin-top:10px;
                border:none;
                border-radius:6px;
                cursor:pointer;
            ">
            Cancel
        </button>

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