<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrfToken" content="<?= $this->request->getAttribute('csrfToken') ?>">
    <title><?= $this->fetch('title') ?: 'Rodgemson Repair Shop' ?></title>
    
    <?= $this->Html->css(['dashboard']) ?>
    <?= $this->fetch('css') ?>
</head>
<body>
    <div class="app-container">
        <?php if ($this->getRequest()->getSession()->read('Auth.User')): ?>
        <nav class="navbar">
            <div class="navbar-brand">Rodgemson Repair Shop</div>
            <ul class="navbar-menu">
                <li><?= $this->Html->link('Analytics', ['controller' => 'Dashboard', 'action' => 'analytics']) ?></li>
                <li><?= $this->Html->link('Repairs', ['controller' => 'Dashboard', 'action' => 'repairs']) ?></li>
                <li><?= $this->Html->link('Stock', ['controller' => 'Dashboard', 'action' => 'stocks']) ?></li>
                <li><?= $this->Html->link('Profile', ['controller' => 'Dashboard', 'action' => 'profile']) ?></li>
                <li><?= $this->Html->link('Logout', ['controller' => 'Dashboard', 'action' => 'logout']) ?></li>
            </ul>
        </nav>
        <?php endif; ?>

        <?= $this->Flash->render() ?>
        
        <main>
            <?= $this->fetch('content') ?>
        </main>
    </div>

    <?= $this->Html->script([], ['defer' => true]) ?>
    <?= $this->fetch('script') ?>
</body>
<script>
function switchTab(btn, id) {
    document.querySelectorAll('.report-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.report-content').forEach(c => c.style.display = 'none');
    btn.classList.add('active');
    document.getElementById(id).style.display = 'flex';
    document.getElementById(id).style.flexDirection = 'column';
}
</script>
</html>
