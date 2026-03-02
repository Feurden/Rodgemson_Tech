<div class="page-section">
    <div class="analytics-wrapper">

        <h2 class="analytics-title">📊 Analytics</h2>
        <p class="analytics-sub">Visualize repair trends, technician performance, and stock usage.</p>

        <!-- Grid Layout -->
        <div class="analytics-grid">

            <!-- DIV 1: Repair Status Breakdown (left tall panel) -->
            <div class="analytics-panel div1">
                <h3 class="panel-title">🔧 Repair Status</h3>
                <p class="panel-sub">All time overview</p>

                <div class="donut-wrap">
                    <?php
                        $completed = $completedRepairs ?? 0;
                        $pending = $pendingRepairs ?? 0;
                        $total = $completed + $pending;
                        $circumference = 251;
                        $completedDash = $total > 0 ? round(($completed / $total) * $circumference) : 0;
                        $pendingDash = $circumference - $completedDash;
                    ?>
                    <svg viewBox="0 0 120 120" width="160" height="160">
                        <circle cx="60" cy="60" r="40" fill="none" stroke="#e2e8f0" stroke-width="18"/>
                        <circle cx="60" cy="60" r="40" fill="none" stroke="#38bdf8" stroke-width="18"
                            stroke-dasharray="<?= $completedDash ?> <?= $circumference ?>" stroke-dashoffset="<?= $pendingDash ?>" stroke-linecap="round"
                            transform="rotate(-90 60 60)"/>
                        <circle cx="60" cy="60" r="40" fill="none" stroke="#ef4444" stroke-width="18"
                            stroke-dasharray="<?= $pendingDash ?> <?= $circumference ?>" stroke-dashoffset="-<?= $completedDash ?>" stroke-linecap="round"
                            transform="rotate(-90 60 60)"/>
                    </svg>
                    <div class="donut-center-label">
                        <span class="donut-total"><?= $total ?></span>
                        <span class="donut-label-text">Total</span>
                    </div>
                </div>

                <div class="legend-list">
                    <div class="legend-item">
                        <span class="legend-dot" style="background:#38bdf8;"></span>
                        <span class="legend-name">Completed</span>
                        <span class="legend-value"><?= $completedRepairs ?? 0 ?></span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot" style="background:#ef4444;"></span>
                        <span class="legend-name">Pending / In Progress</span>
                        <span class="legend-value"><?= $pendingRepairs ?? 0 ?></span>
                    </div>
                </div>

                <div class="completion-bar-wrap">
                    <div style="display:flex; justify-content:space-between; font-size:12px; color:#64748b; margin-bottom:6px;">
                        <span>Completion Rate</span>
                        <span><?= $completionRate ?? 0 ?>%</span>
                    </div>
                    <div class="bar-track">
                        <div class="bar-fill" style="width:<?= $completionRate ?? 0 ?>%; background: linear-gradient(90deg, #38bdf8, #0284c7);"></div>
                    </div>
                </div>
            </div>

            <!-- DIV 2: Weekly & Monthly Reports (middle tall panel) -->
            <div class="analytics-panel div2">
                <h3 class="panel-title">📅 Repair Reports</h3>

                <div class="report-tabs">
                    <button class="report-tab active" onclick="switchTab(this, 'weekly')">Weekly</button>
                    <button class="report-tab" onclick="switchTab(this, 'monthly')">Monthly</button>
                </div>

                <!-- Weekly Report -->
                <div id="weekly" class="report-content">
                    <p class="panel-sub" style="margin-bottom:16px;">This week's repair activity</p>
                    <div class="bar-chart">
                        <?php
                            $days = $weeklyData ?? [];
                            $values = array_map(fn($d) => $d['count'], $days);
                            $max = max($values) ?: 1;
                            foreach ($days as $i => $day):
                                $pct = round(($day['count'] / $max) * 100);
                        ?>
                        <div class="bar-col">
                            <span class="bar-val"><?= $day['count'] ?></span>
                            <div class="bar-body">
                                <div class="bar-fill-v" style="height:<?= $pct ?>%;"></div>
                            </div>
                            <span class="bar-label"><?= $day['day'] ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="report-summary">
                        <div class="summary-item">
                            <span class="summary-label">Total This Week</span>
                            <span class="summary-value"><?= array_sum($values) ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Completed</span>
                            <span class="summary-value" style="color:#16a34a;"><?= $completedRepairs ?? 0 ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Pending</span>
                            <span class="summary-value" style="color:#ef4444;"><?= $pendingRepairs ?? 0 ?></span>
                        </div>
                    </div>
                </div>

                <!-- Monthly Report -->
                <div id="monthly" class="report-content" style="display:none;">
                    <p class="panel-sub" style="margin-bottom:16px;">This month's repair activity</p>
                    <div class="bar-chart">
                        <?php
                            $weeks = $monthlyData ?? [];
                            $mvalues = array_map(fn($w) => $w['count'], $weeks);
                            $mmax = max($mvalues) ?: 1;
                            foreach ($weeks as $i => $wk):
                                $mpct = round(($wk['count'] / $mmax) * 100);
                        ?>
                        <div class="bar-col">
                            <span class="bar-val"><?= $wk['count'] ?></span>
                            <div class="bar-body">
                                <div class="bar-fill-v" style="height:<?= $mpct ?>%; background: linear-gradient(180deg, #a78bfa, #7c3aed);"></div>
                            </div>
                            <span class="bar-label"><?= $wk['week'] ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="report-summary">
                        <div class="summary-item">
                            <span class="summary-label">Total This Month</span>
                            <span class="summary-value"><?= array_sum($mvalues) ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Completed</span>
                            <span class="summary-value" style="color:#16a34a;"><?= $completedRepairs ?? 0 ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Pending</span>
                            <span class="summary-value" style="color:#ef4444;"><?= $pendingRepairs ?? 0 ?></span>
                        </div>
                    </div>
                </div>
            </div>

          <!-- DIV 4: Stock Levels -->
        <div class="analytics-panel div4">
            <h3 class="panel-title">📦 Stock Levels</h3>
            <p class="panel-sub">Current inventory breakdown</p>

            <div style="display:flex; flex-direction:column; gap:14px; flex:1; overflow-y:auto;">

                <?php
                    $stocks = $stockLevels ?? [];

                    foreach ($stocks as $stock):
                        $pct = round(($stock['current'] / $stock['total']) * 100);
                        $warn = $pct <= 30 ? '#ef4444' : ($pct <= 60 ? '#f59e0b' : $stock['color']);
                ?>
                    <div>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                            <span style="font-size:13px; font-weight:600; color:#1e293b;"><?= $stock['name'] ?></span>
                            <span style="font-size:12px; color:#64748b;">
                                <?= $stock['current'] ?> / <?= $stock['total'] ?>
                                <strong style="color:<?= $warn ?>; margin-left:4px;"><?= $pct ?>%</strong>
                            </span>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill" style="width:<?= $pct ?>%; background:<?= $warn ?>;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                </div>
            </div>

        </div>
    </div>
</div>