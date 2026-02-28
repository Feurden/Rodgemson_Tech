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
                    <svg viewBox="0 0 120 120" width="160" height="160">
                        <!-- Completed: 120/160 = 75% → stroke-dasharray: 75% of circumference (251) = 188 -->
                        <circle cx="60" cy="60" r="40" fill="none" stroke="#e2e8f0" stroke-width="18"/>
                        <circle cx="60" cy="60" r="40" fill="none" stroke="#38bdf8" stroke-width="18"
                            stroke-dasharray="188 251" stroke-dashoffset="63" stroke-linecap="round"
                            transform="rotate(-90 60 60)"/>
                        <circle cx="60" cy="60" r="40" fill="none" stroke="#ef4444" stroke-width="18"
                            stroke-dasharray="63 251" stroke-dashoffset="-188" stroke-linecap="round"
                            transform="rotate(-90 60 60)"/>
                    </svg>
                    <div class="donut-center-label">
                        <span class="donut-total">160</span>
                        <span class="donut-label-text">Total</span>
                    </div>
                </div>

                <div class="legend-list">
                    <div class="legend-item">
                        <span class="legend-dot" style="background:#38bdf8;"></span>
                        <span class="legend-name">Completed</span>
                        <span class="legend-value">120</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot" style="background:#ef4444;"></span>
                        <span class="legend-name">Pending / In Progress</span>
                        <span class="legend-value">40</span>
                    </div>
                </div>

                <div class="completion-bar-wrap">
                    <div style="display:flex; justify-content:space-between; font-size:12px; color:#64748b; margin-bottom:6px;">
                        <span>Completion Rate</span>
                        <span>75%</span>
                    </div>
                    <div class="bar-track">
                        <div class="bar-fill" style="width:75%; background: linear-gradient(90deg, #38bdf8, #0284c7);"></div>
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
                            $days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
                            $values = [8, 12, 6, 15, 10, 4, 7];
                            $max = max($values);
                            foreach ($days as $i => $day):
                                $pct = round(($values[$i] / $max) * 100);
                        ?>
                        <div class="bar-col">
                            <span class="bar-val"><?= $values[$i] ?></span>
                            <div class="bar-body">
                                <div class="bar-fill-v" style="height:<?= $pct ?>%;"></div>
                            </div>
                            <span class="bar-label"><?= $day ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="report-summary">
                        <div class="summary-item">
                            <span class="summary-label">Total This Week</span>
                            <span class="summary-value">62</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Completed</span>
                            <span class="summary-value" style="color:#16a34a;">48</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Pending</span>
                            <span class="summary-value" style="color:#ef4444;">14</span>
                        </div>
                    </div>
                </div>

                <!-- Monthly Report -->
                <div id="monthly" class="report-content" style="display:none;">
                    <p class="panel-sub" style="margin-bottom:16px;">This month's repair activity</p>
                    <div class="bar-chart">
                        <?php
                            $weeks = ['Wk 1','Wk 2','Wk 3','Wk 4'];
                            $mvalues = [45, 62, 38, 55];
                            $mmax = max($mvalues);
                            foreach ($weeks as $i => $wk):
                                $mpct = round(($mvalues[$i] / $mmax) * 100);
                        ?>
                        <div class="bar-col">
                            <span class="bar-val"><?= $mvalues[$i] ?></span>
                            <div class="bar-body">
                                <div class="bar-fill-v" style="height:<?= $mpct ?>%; background: linear-gradient(180deg, #a78bfa, #7c3aed);"></div>
                            </div>
                            <span class="bar-label"><?= $wk ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="report-summary">
                        <div class="summary-item">
                            <span class="summary-label">Total This Month</span>
                            <span class="summary-value">200</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Completed</span>
                            <span class="summary-value" style="color:#16a34a;">162</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Pending</span>
                            <span class="summary-value" style="color:#ef4444;">38</span>
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
                    $stocks = [
                        ['name' => 'LCD Screens',       'current' => 45, 'total' => 60,  'color' => '#38bdf8'],
                        ['name' => 'Batteries',         'current' => 30, 'total' => 100, 'color' => '#34d399'],
                        ['name' => 'Charging Ports',    'current' => 12, 'total' => 50,  'color' => '#f59e0b'],
                        ['name' => 'Back Covers',       'current' => 5,  'total' => 40,  'color' => '#ef4444'],
                        ['name' => 'Motherboards',      'current' => 22, 'total' => 30,  'color' => '#a78bfa'],
                        ['name' => 'Camera Modules',    'current' => 18, 'total' => 25,  'color' => '#fb923c'],
                    ];

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