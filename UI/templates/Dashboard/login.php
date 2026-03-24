<div class="dashboard-fullscreen">

    <div class="dashboard-card">

        <div class="icon-box">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </svg>
        </div>

        <h1 class="title">Rodgemson Repair Shop</h1>

        <p class="subtitle">Sign in to manage repairs, stock &amp; analytics</p>

        <!-- Flash messages (errors / success notices) -->
        <?= $this->Flash->render() ?>

        <?= $this->Form->create(null, [
            'type' => 'post',
            'url'  => ['controller' => 'Dashboard', 'action' => 'login'],
        ]) ?>

            <div class="form-group">
                <label>Username</label>
                <div class="icon-input">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <input type="text" name="username" placeholder="Enter your username" required class="input">
                </div>
            </div>

            <div class="form-group" style="margin-bottom:28px;">
                <label>Password</label>
                <div class="icon-input">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    <input type="password" name="password" placeholder="Enter your password" required class="input">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Log In</button>

        <?= $this->Form->end() ?>

        <p class="switch-link">
            Don't have an account?
            <a href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'signup']) ?>">Sign Up</a>
        </p>

    </div>

</div>
