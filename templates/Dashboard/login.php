<div style="
    min-height:calc(100vh - 70px);
    display:flex;
    justify-content:center;
    align-items:center;
    background:#f1f5f9;
    padding:40px;
    box-sizing:border-box;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
">

    <div style="
        width:100%;
        max-width:420px;
        background:#ffffff;
        border-radius:12px;
        padding:40px;
        box-shadow:0 10px 25px rgba(0,0,0,0.08);
        text-align:center;
    ">

        <h1 style="
            color:#1e293b;
            font-size:2rem;
            margin-bottom:15px;
            font-weight:600;
        ">
            Technician Login
        </h1>

        <p style="
            font-size:1rem;
            color:#475569;
            margin-bottom:30px;
            line-height:1.6;
        ">
            Sign in to manage repairs, stock, and analytics.
        </p>

        <!-- Login Form -->
        <form method="post" action="<?= $this->Url->build(['controller'=>'Dashboard','action'=>'login']) ?>">

            <!-- CSRF Token -->
            <?= $this->Form->hidden('_csrfToken', ['value' => $this->request->getAttribute('csrfToken')]) ?>

            <input type="text"
                name="username"
                placeholder="Username"
                required
                style="
                    width:100%;
                    padding:12px;
                    margin-bottom:15px;
                    border:1px solid #e2e8f0;
                    border-radius:8px;
                    font-size:15px;
                ">

            <input type="password"
                name="password"
                placeholder="Password"
                required
                style="
                    width:100%;
                    padding:12px;
                    margin-bottom:25px;
                    border:1px solid #e2e8f0;
                    border-radius:8px;
                    font-size:15px;
                ">

            <button type="submit"
                style="
                    width:100%;
                    padding:12px;
                    background:#38bdf8;
                    color:white;
                    border:none;
                    border-radius:8px;
                    font-weight:600;
                    cursor:pointer;
                ">
                Login
            </button>

        </form>

    </div>

</div>