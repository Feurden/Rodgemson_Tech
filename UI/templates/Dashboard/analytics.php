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
                        $completed   = $completedRepairs   ?? 0;
                        $inProgress  = $inProgressRepairs  ?? 0;
                        $pending     = $pendingRepairs      ?? 0;
                        $total       = $completed + $inProgress + $pending;
                        $circumference = 251;

                        $compDash   = $total > 0 ? round(($completed  / $total) * $circumference) : 0;
                        $progDash   = $total > 0 ? round(($inProgress / $total) * $circumference) : 0;
                        $pendDash   = $circumference - $compDash - $progDash;

                        // stroke-dashoffset positions each segment after the previous
                        $compOffset = 0;
                        $progOffset = -$compDash;
                        $pendOffset = -($compDash + $progDash);
                    ?>
                    <svg viewBox="0 0 120 120" width="160" height="160">
                        <!-- Track -->
                        <circle cx="60" cy="60" r="40" fill="none" stroke="#e2e8f0" stroke-width="18"/>
                        <!-- Completed: green -->
                        <circle cx="60" cy="60" r="40" fill="none" stroke="#16a34a" stroke-width="18"
                            stroke-dasharray="<?= $compDash ?> <?= $circumference ?>"
                            stroke-dashoffset="<?= $compOffset ?>"
                            stroke-linecap="round"
                            transform="rotate(-90 60 60)"/>
                        <!-- In Progress: yellow -->
                        <circle cx="60" cy="60" r="40" fill="none" stroke="#f59e0b" stroke-width="18"
                            stroke-dasharray="<?= $progDash ?> <?= $circumference ?>"
                            stroke-dashoffset="<?= $progOffset ?>"
                            stroke-linecap="round"
                            transform="rotate(-90 60 60)"/>
                        <!-- Pending: red -->
                        <circle cx="60" cy="60" r="40" fill="none" stroke="#ef4444" stroke-width="18"
                            stroke-dasharray="<?= $pendDash ?> <?= $circumference ?>"
                            stroke-dashoffset="<?= $pendOffset ?>"
                            stroke-linecap="round"
                            transform="rotate(-90 60 60)"/>
                    </svg>
                    <div class="donut-center-label">
                        <span class="donut-total"><?= $total ?></span>
                        <span class="donut-label-text">Total</span>
                    </div>
                </div>

                <div class="legend-list">
                    <div class="legend-item">
                        <span class="legend-dot" style="background:#16a34a;"></span>
                        <span class="legend-name">Completed</span>
                        <span class="legend-value"><?= $completed ?></span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot" style="background:#f59e0b;"></span>
                        <span class="legend-name">In Progress</span>
                        <span class="legend-value"><?= $inProgress ?></span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot" style="background:#ef4444;"></span>
                        <span class="legend-name">Pending</span>
                        <span class="legend-value"><?= $pending ?></span>
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

                <!-- Month + Year picker -->
                <div style="display:flex; gap:6px; margin-bottom:10px;">
                    <select id="report-month" class="specific-select" style="flex:2;" onchange="loadReport()">
                        <?php
                            $monthNames = [1=>'January',2=>'February',3=>'March',4=>'April',
                                          5=>'May',6=>'June',7=>'July',8=>'August',
                                          9=>'September',10=>'October',11=>'November',12=>'December'];
                            $curMonth = (int) date('n');
                            $curYear  = (int) date('Y');
                            foreach ($monthNames as $num => $name):
                        ?>
                        <option value="<?= $num ?>" <?= $num === $curMonth ? 'selected' : '' ?>><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="report-year" class="specific-select" style="flex:1;" onchange="loadReport()">
                        <?php for ($y = $curYear; $y >= $curYear - 4; $y--): ?>
                        <option value="<?= $y ?>" <?= $y === $curYear ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="report-tabs">
                    <button class="report-tab active" onclick="switchTab(this, 'weekly')">Weekly</button>
                    <button class="report-tab" onclick="switchTab(this, 'monthly')">Monthly</button>
                </div>

                <!-- Weekly Report -->
                <div id="weekly" class="report-content">
                    <p class="panel-sub" id="weekly-sub" style="margin-bottom:16px;">This week's repair activity</p>
                    <div class="bar-chart" id="weekly-chart">
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
                            <span class="summary-value" id="w-total"><?= array_sum($values) ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Completed</span>
                            <span class="summary-value" style="color:#16a34a;" id="w-completed"><?= $completedRepairs ?? 0 ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Pending</span>
                            <span class="summary-value" style="color:#ef4444;" id="w-pending"><?= $pendingRepairs ?? 0 ?></span>
                        </div>
                    </div>
                </div>

                <!-- Monthly Report -->
                <div id="monthly" class="report-content" style="display:none;">
                    <p class="panel-sub" id="monthly-sub" style="margin-bottom:8px;">This month's repair activity</p>

                    <!-- Color legend -->
                    <div style="display:flex; gap:12px; margin-bottom:10px; flex-wrap:wrap;">
                        <span style="display:flex; align-items:center; gap:4px; font-size:11px; color:#475569;">
                            <span style="width:10px;height:10px;border-radius:2px;background:#16a34a;display:inline-block;"></span> Completed
                        </span>
                        <span style="display:flex; align-items:center; gap:4px; font-size:11px; color:#475569;">
                            <span style="width:10px;height:10px;border-radius:2px;background:#f59e0b;display:inline-block;"></span> In Progress
                        </span>
                        <span style="display:flex; align-items:center; gap:4px; font-size:11px; color:#475569;">
                            <span style="width:10px;height:10px;border-radius:2px;background:#ef4444;display:inline-block;"></span> Pending
                        </span>
                    </div>

                    <div class="bar-chart" id="monthly-chart">
                        <?php
                            $weeks   = $monthlyData ?? [];
                            $mmax    = max(array_map(fn($w) => $w['count'], $weeks) ?: [1]);
                            foreach ($weeks as $wk):
                                $totalH     = $mmax > 0 ? round(($wk['count']       / $mmax) * 100) : 0;
                                $compH      = $mmax > 0 ? round(($wk['completed']   / $mmax) * 100) : 0;
                                $progH      = $mmax > 0 ? round(($wk['in_progress'] / $mmax) * 100) : 0;
                                $pendH      = $mmax > 0 ? round(($wk['pending']     / $mmax) * 100) : 0;
                        ?>
                        <div class="bar-col">
                            <span class="bar-val"><?= $wk['count'] ?></span>
                            <div class="bar-body">
                                <!-- Stacked bar: pending (bottom) → in progress → completed (top) -->
                                <div style="width:100%; height:100%; display:flex; flex-direction:column; justify-content:flex-end; gap:1px;">
                                    <?php if ($wk['completed'] > 0): ?>
                                    <div style="height:<?= $compH ?>%; background:#16a34a; border-radius:3px 3px 0 0; min-height:3px;"></div>
                                    <?php endif; ?>
                                    <?php if ($wk['in_progress'] > 0): ?>
                                    <div style="height:<?= $progH ?>%; background:#f59e0b; min-height:3px;"></div>
                                    <?php endif; ?>
                                    <?php if ($wk['pending'] > 0): ?>
                                    <div style="height:<?= $pendH ?>%; background:#ef4444; border-radius:0 0 3px 3px; min-height:3px;"></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="bar-label"><?= $wk['week'] ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="report-summary">
                        <div class="summary-item">
                            <span class="summary-label">Total</span>
                            <span class="summary-value" id="m-total"><?= array_sum(array_map(fn($w) => $w['count'], $weeks)) ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Completed</span>
                            <span class="summary-value" style="color:#16a34a;" id="m-completed"><?= array_sum(array_map(fn($w) => $w['completed'], $weeks)) ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">In Progress</span>
                            <span class="summary-value" style="color:#f59e0b;" id="m-inprogress"><?= array_sum(array_map(fn($w) => $w['in_progress'], $weeks)) ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Pending</span>
                            <span class="summary-value" style="color:#ef4444;" id="m-pending"><?= array_sum(array_map(fn($w) => $w['pending'], $weeks)) ?></span>
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

<script>
const _MONTHS = ['','January','February','March','April','May','June',
                 'July','August','September','October','November','December'];

function switchTab(btn, tab) {
    document.querySelectorAll('.report-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.report-content').forEach(el => el.style.display = 'none');
    document.getElementById(tab).style.display = 'block';
    loadReport();
}

function loadReport() {
    const month    = document.getElementById('report-month').value;
    const year     = document.getElementById('report-year').value;
    const label    = _MONTHS[month] + ' ' + year;
    const isWeekly = document.getElementById('weekly').style.display !== 'none';

    document.getElementById('weekly-sub').textContent  = label + ' — daily activity';
    document.getElementById('monthly-sub').textContent = label + ' — weekly breakdown';

    if (isWeekly) {
        fetch(`/dashboard/getWeeklyByMonth?month=${month}&year=${year}`)
            .then(r => r.json())
            .then(data => {
                renderWeeklyChart('weekly-chart', data.days);
                document.getElementById('w-total').textContent     = data.total     ?? '—';
                document.getElementById('w-completed').textContent = data.completed ?? '—';
                document.getElementById('w-pending').textContent   = data.pending   ?? '—';
            }).catch(console.error);
    } else {
        fetch(`/dashboard/getMonthlyByYear?month=${month}&year=${year}`)
            .then(r => r.json())
            .then(data => {
                renderMonthlyChart('monthly-chart', data.weeks);
                document.getElementById('m-total').textContent      = data.total       ?? '—';
                document.getElementById('m-completed').textContent  = data.completed   ?? '—';
                document.getElementById('m-inprogress').textContent = data.in_progress ?? '—';
                document.getElementById('m-pending').textContent    = data.pending     ?? '—';
            }).catch(console.error);
    }
}

/* Weekly: plain blue bars */
function renderWeeklyChart(containerId, items) {
    const container = document.getElementById(containerId);
    if (!container || !items?.length) return;
    const max = Math.max(...items.map(i => i.count), 1);
    container.innerHTML = items.map(item => {
        const pct = Math.round((item.count / max) * 100);
        return `<div class="bar-col">
            <span class="bar-val">${item.count}</span>
            <div class="bar-body"><div class="bar-fill-v" style="height:${pct}%;background:linear-gradient(180deg,#38bdf8,#0284c7);"></div></div>
            <span class="bar-label">${item.day ?? ''}</span>
        </div>`;
    }).join('');
}

/* Monthly: stacked bars — completed (green) / in progress (yellow) / pending (red) */
function renderMonthlyChart(containerId, items) {
    const container = document.getElementById(containerId);
    if (!container || !items?.length) return;
    const max = Math.max(...items.map(i => i.count), 1);

    container.innerHTML = items.map(item => {
        const compH = Math.round((item.completed   / max) * 100);
        const progH = Math.round((item.in_progress / max) * 100);
        const pendH = Math.round((item.pending     / max) * 100);

        const compSeg = item.completed   > 0 ? `<div style="height:${compH}%;background:#16a34a;border-radius:3px 3px 0 0;min-height:3px;"></div>` : '';
        const progSeg = item.in_progress > 0 ? `<div style="height:${progH}%;background:#f59e0b;min-height:3px;"></div>` : '';
        const pendSeg = item.pending     > 0 ? `<div style="height:${pendH}%;background:#ef4444;border-radius:0 0 3px 3px;min-height:3px;"></div>` : '';

        return `<div class="bar-col">
            <span class="bar-val">${item.count}</span>
            <div class="bar-body">
                <div style="width:100%;height:100%;display:flex;flex-direction:column;justify-content:flex-end;gap:1px;">
                    ${compSeg}${progSeg}${pendSeg}
                </div>
            </div>
            <span class="bar-label">${item.week ?? ''}</span>
        </div>`;
    }).join('');
}
</script>