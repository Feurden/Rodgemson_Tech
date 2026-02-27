<body style="
    margin:0;
    background:#f1f5f9;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
">

<!-- TOP NAVBAR -->
<div style="
    background:#1e293b;
    height:70px;
    padding:0 40px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 4px 10px rgba(0,0,0,0.1);
    box-sizing:border-box;
">

    <div style="
        color:white;
        font-size:1.3rem;
        font-weight:600;
    ">
        Technician Repair System
    </div>

    <div>

        <?= $this->Html->link('Repairs',
            ['controller' => 'Dashboard', 'action' => 'repairs'],
            ['style'=>'margin:0 15px;color:white;text-decoration:none;font-weight:500;']
        ) ?>

        <?= $this->Html->link('Stock',
            ['controller' => 'Dashboard', 'action' => 'stocks'],
            ['style'=>'margin:0 15px;color:white;text-decoration:none;font-weight:500;']
        ) ?>

        <?= $this->Html->link('Analytics',
            ['controller' => 'Dashboard', 'action' => 'analytics'],
            ['style'=>'margin:0 15px;color:white;text-decoration:none;font-weight:500;']
        ) ?>

        <?= $this->Html->link('Profile',
            ['controller' => 'Dashboard', 'action' => 'profile'],
            ['style'=>'margin:0 15px;color:white;text-decoration:none;font-weight:500;']
        ) ?>

        <?= $this->Html->link('Logout',
            ['controller' => 'Dashboard', 'action' => 'logout'],
            ['style'=>'margin-left:20px;color:#ef4444;text-decoration:none;font-weight:600;']
        ) ?>

    </div>
</div>

<!-- MAIN CONTENT -->
<div style="
    min-height:calc(100vh - 70px);
    padding:40px;
    display:flex;
    justify-content:center;
    box-sizing:border-box;
">

    <div style="
        width:100%;
        background:#ffffff;
        border-radius:12px;
        padding:clamp(25px, 4vw, 50px);
        box-shadow:0 10px 25px rgba(0,0,0,0.08);
        text-align:center;
    ">

        <?= $this->Flash->render() ?>
        <?= $this->fetch('content') ?>

    </div>

</div>

</body>