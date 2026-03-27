<div class="page-section">
    <div class="analytics-wrapper">

        <h2 class="analytics-title">📊 Analytics</h2>
        <p class="analytics-sub">Visualize repair trends, technician performance, and stock usage.</p>

        <!-- Grid Layout -->
        <div class="analytics-grid">

            <!-- DIV 1: Repair Status Breakdown -->
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

                        $compOffset = 0;
                        $progOffset = -$compDash;
                        $pendOffset = -($compDash + $progDash);
                    ?>
                    <svg viewBox="0 0 120 120" width="160" height="160">
                        <circle cx="60" cy="60" r="40" fill="none" stroke="#e2e8f0" stroke-width="18"/>
                        <circle cx="60" cy="60" r="40" fill="none" stroke="#16a34a" stroke-width="18"
                            stroke-dasharray="<?= $compDash ?> <?= $circumference ?>"
                            stroke-dashoffset="<?= $compOffset ?>"
                            stroke-linecap="round"
                            transform="rotate(-90 60 60)"/>
                        <circle cx="60" cy="60" r="40" fill="none" stroke="#f59e0b" stroke-width="18"
                            stroke-dasharray="<?= $progDash ?> <?= $circumference ?>"
                            stroke-dashoffset="<?= $progOffset ?>"
                            stroke-linecap="round"
                            transform="rotate(-90 60 60)"/>
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

            <!-- DIV 2: Weekly & Monthly Reports -->
            <div class="analytics-panel div2">
                <h3 class="panel-title">📅 Repair Reports</h3>

                <div style="display:flex; gap:6px; margin-bottom:10px;">
                    <select id="report-month" class="specific-select" style="flex:2;" onchange="loadReport(); loadIncome();">
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
                    <select id="report-year" class="specific-select" style="flex:1;" onchange="loadReport(); loadIncome();">
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
                                $compH = $mmax > 0 ? round(($wk['completed']   / $mmax) * 100) : 0;
                                $progH = $mmax > 0 ? round(($wk['in_progress'] / $mmax) * 100) : 0;
                                $pendH = $mmax > 0 ? round(($wk['pending']     / $mmax) * 100) : 0;
                        ?>
                        <div class="bar-col">
                            <span class="bar-val"><?= $wk['count'] ?></span>
                            <div class="bar-body">
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

            <!-- DIV 3: Income Overview -->
            <div class="analytics-panel div3">
                <h3 class="panel-title">💰 Income Overview</h3>
                <p class="panel-sub">Total revenue from completed repairs</p>

                <!-- Period tabs -->
                <div class="report-tabs" style="margin-bottom:10px;">
                    <button class="report-tab active" id="income-tab-day"   onclick="switchIncomeTab('day', this)">Daily</button>
                    <button class="report-tab"        id="income-tab-week"  onclick="switchIncomeTab('week', this)">Weekly</button>
                    <button class="report-tab"        id="income-tab-month" onclick="switchIncomeTab('month', this)">Monthly</button>
                </div>

                <!-- Big total -->
                <div style="text-align:center; padding:10px 0 8px;">
                    <span style="font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px;">Total Income</span>
                    <div style="font-size:1.75rem; font-weight:800; color:#1e293b; margin-top:3px; line-height:1;">
                        ₱<span id="income-total">—</span>
                    </div>
                    <span id="income-period-label" style="font-size:11px; color:#64748b; margin-top:3px; display:block;"></span>
                </div>

                <!-- Repair count card -->
                <div style="background:#f0f9ff; border-radius:8px; padding:8px 12px; margin-bottom:12px; text-align:center;">
                    <span style="font-size:9px; font-weight:700; color:#0284c7; text-transform:uppercase; display:block; margin-bottom:3px;">Repairs Completed</span>
                    <span style="font-size:18px; font-weight:700; color:#0c4a6e;" id="income-repairs">—</span>
                </div>

                <!-- Chart -->
                <div style="flex:1; min-height:0; display:flex; flex-direction:column;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <span style="font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px;" id="income-chart-label">Daily Breakdown</span>
                    </div>
                    <div class="bar-chart" id="income-chart" style="height:110px; align-items:flex-end; gap:2px;"></div>
                </div>
            </div>

            <!-- DIV 4: Stock Levels -->
            <div class="analytics-panel div4">
                <h3 class="panel-title">📦 Stock Levels</h3>
                <p class="panel-sub">Parts inventory — minimum stock warnings</p>

                <div style="display:flex; flex-direction:column; gap:14px; flex:1; overflow-y:auto;">
                    <?php
                        $stocks = $stockLevels ?? [];
                        foreach ($stocks as $stock):
                            $current = $stock['current'];
                            $min     = $stock['min'] ?? 1;
                            $scale   = $stock['total'];  // = min * 3
                            $pct     = min(100, round(($current / $scale) * 100));
                            if ($current <= $min) {
                                $warn = '#ef4444'; $statusText = '⚠ Low';
                            } elseif ($current <= $min * 2) {
                                $warn = '#f59e0b'; $statusText = '~ OK';
                            } else {
                                $warn = '#16a34a'; $statusText = '✓ Good';
                            }
                    ?>
                        <div>
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                                <span style="font-size:12px; font-weight:600; color:#1e293b; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:55%;"><?= htmlspecialchars($stock['name']) ?></span>
                                <span style="font-size:11px; color:#64748b; white-space:nowrap;">
                                    <strong style="color:<?= $warn ?>;"><?= $current ?></strong><span style="color:#94a3b8;"> / min <?= $min ?></span>
                                    <strong style="color:<?= $warn ?>; margin-left:4px;"><?= $statusText ?></strong>
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

<style>
/* Grid Layout */
.analytics-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    grid-template-rows: repeat(5, 90px);
    gap: 16px;
}

.div1 { grid-column: span 2 / span 2; grid-column-start: 1; grid-row: span 5 / span 5; }
.div2 { grid-column: span 2 / span 2; grid-column-start: 3; grid-row: span 5 / span 5; }
.div3 { grid-column: span 1 / span 1; grid-column-start: 5; grid-row: span 5 / span 5; grid-row-start: 1; }
.div4 { grid-column: span 1 / span 1; grid-column-start: 6; grid-row: span 5 / span 5; grid-row-start: 1; }

/* Ensure bar chart has proper height */
.bar-chart {
    display: flex;
    align-items: flex-end;
    gap: 4px;
    height: 120px;
    min-height: 100px;
    width: 100%;
}

.bar-col {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    text-align: center;
    min-width: 0; /* Prevent overflow */
}

.bar-body {
    width: 100%;
    height: 80px;
    background: #f1f5f9;
    border-radius: 6px;
    position: relative;
    overflow: hidden;
    margin-bottom: 4px;
}

.bar-fill-v {
    width: 100%;
    position: absolute;
    bottom: 0;
    left: 0;
    transition: height 0.3s ease;
}

.bar-val {
    font-size: 9px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 2px;
    white-space: nowrap;
}

.bar-label {
    font-size: 9px;
    color: #64748b;
    margin-top: 4px;
    white-space: nowrap;
}
.bar-track {
    background: #e2e8f0;
    border-radius: 4px;
    height: 6px;
    overflow: hidden;
}
.bar-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.3s ease;
}

/* Report tabs */
.report-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 12px;
}
.report-tab {
    padding: 6px 12px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s;
}
.report-tab.active {
    background: linear-gradient(135deg, #38bdf8, #0284c7);
    color: white;
    border: none;
}
</style>
<script>
/* ══════════════════════════════════════════════════
   Report Tab Logic
   ══════════════════════════════════════════════════ */
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
    const month = document.getElementById('report-month').value;
    const year = document.getElementById('report-year').value;
    const label = _MONTHS[month] + ' ' + year;
    const isWeekly = document.getElementById('weekly').style.display !== 'none';

    document.getElementById('weekly-sub').textContent = label + ' — daily activity';
    document.getElementById('monthly-sub').textContent = label + ' — weekly breakdown';

    if (isWeekly) {
        fetch(`/dashboard/getWeeklyByMonth?month=${month}&year=${year}`)
            .then(r => r.json())
            .then(data => {
                renderWeeklyChart('weekly-chart', data.days);
                document.getElementById('w-total').textContent = data.total ?? '—';
                document.getElementById('w-completed').textContent = data.completed ?? '—';
                document.getElementById('w-pending').textContent = data.pending ?? '—';
            }).catch(console.error);
    } else {
        fetch(`/dashboard/getMonthlyByYear?month=${month}&year=${year}`)
            .then(r => r.json())
            .then(data => {
                renderMonthlyChart('monthly-chart', data.weeks);
                document.getElementById('m-total').textContent = data.total ?? '—';
                document.getElementById('m-completed').textContent = data.completed ?? '—';
                document.getElementById('m-inprogress').textContent = data.in_progress ?? '—';
                document.getElementById('m-pending').textContent = data.pending ?? '—';
            }).catch(console.error);
    }
}

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

function renderMonthlyChart(containerId, items) {
    const container = document.getElementById(containerId);
    if (!container || !items?.length) return;
    const max = Math.max(...items.map(i => i.count), 1);
    container.innerHTML = items.map(item => {
        const compH = Math.round((item.completed / max) * 100);
        const progH = Math.round((item.in_progress / max) * 100);
        const pendH = Math.round((item.pending / max) * 100);
        const compSeg = item.completed > 0 ? `<div style="height:${compH}%;background:#16a34a;border-radius:3px 3px 0 0;min-height:3px;"></div>` : '';
        const progSeg = item.in_progress > 0 ? `<div style="height:${progH}%;background:#f59e0b;min-height:3px;"></div>` : '';
        const pendSeg = item.pending > 0 ? `<div style="height:${pendH}%;background:#ef4444;border-radius:0 0 3px 3px;min-height:3px;"></div>` : '';
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

/* ══════════════════════════════════════════════════
   Income Panel Logic
   ══════════════════════════════════════════════════ */
let _incomeTab = 'day';

function switchIncomeTab(tab, btn) {
    _incomeTab = tab;
    
    // Update tab styling
    const tabs = document.querySelectorAll('#income-tab-day, #income-tab-week, #income-tab-month');
    tabs.forEach(b => {
        b.classList.remove('active');
        b.style.background = 'white';
        b.style.color = '#64748b';
        b.style.border = '1px solid #e2e8f0';
    });
    
    btn.classList.add('active');
    btn.style.background = 'linear-gradient(135deg, #38bdf8, #0284c7)';
    btn.style.color = 'white';
    btn.style.border = 'none';
    
    loadIncome();
}
function loadIncome() {
    const monthSelect = document.getElementById('report-month');
    const yearSelect = document.getElementById('report-year');
    
    const month = monthSelect ? monthSelect.value : new Date().getMonth() + 1;
    const year = yearSelect ? yearSelect.value : new Date().getFullYear();

    let url;
    if (_incomeTab === 'day') {
        url = '/dashboard/getIncomeDay';
    } else if (_incomeTab === 'week') {
        url = '/dashboard/getIncomeWeek';
    } else {
        url = `/dashboard/getIncomeMonth?month=${month}&year=${year}`;
    }

    // Show loading state
    const totalEl = document.getElementById('income-total');
    const repairsEl = document.getElementById('income-repairs');
    const periodEl = document.getElementById('income-period-label');
    const chartLabelEl = document.getElementById('income-chart-label');
    
    if (totalEl) totalEl.textContent = '…';
    if (repairsEl) repairsEl.textContent = '…';
    if (periodEl) periodEl.textContent = 'Loading...';
    if (chartLabelEl) chartLabelEl.textContent = 'Loading...';

    fetch(url)
        .then(r => {
            if (!r.ok) {
                throw new Error(`HTTP ${r.status}`);
            }
            return r.json();
        })
        .then(data => {
            console.log('Income data received:', data); // Debug log
            
            if (!data.success) {
                incomeError();
                return;
            }
            if (totalEl) totalEl.textContent = data.total.toLocaleString();
            if (repairsEl) repairsEl.textContent = data.repairs || 0;
            if (periodEl) periodEl.textContent = data.period ?? '';
            if (chartLabelEl) chartLabelEl.textContent = data.chart_label ?? '';
            
            renderIncomeChart(data.bars ?? []);
        })
        .catch(error => {
            console.error('Income fetch error:', error);
            incomeError();
        });
}

function incomeError() {
    const totalEl = document.getElementById('income-total');
    const repairsEl = document.getElementById('income-repairs');
    const periodEl = document.getElementById('income-period-label');
    
    if (totalEl) totalEl.textContent = '—';
    if (repairsEl) repairsEl.textContent = '—';
    if (periodEl) periodEl.textContent = 'Could not load';
    
    const chartContainer = document.getElementById('income-chart');
    if (chartContainer) chartContainer.innerHTML = '<div style="text-align:center;color:#94a3b8;padding:20px;">No income data available</div>';
}
function renderIncomeChart(bars) {
    const container = document.getElementById('income-chart');
    if (!container) return;
    
    if (!bars || bars.length === 0) {
        container.innerHTML = '<div style="text-align:center;color:#94a3b8;padding:20px;">No income data available</div>';
        return;
    }
    
    // Filter out zero-value bars for better display, but keep at least one
    let nonZeroBars = bars.filter(bar => bar.total > 0);
    if (nonZeroBars.length === 0 && bars.length > 0) {
        nonZeroBars = bars; // Show all bars even if zero
    }
    
    const maxVal = Math.max(...nonZeroBars.map(b => b.total), 1);
    
    container.innerHTML = nonZeroBars.map(bar => {
        const heightPct = bar.total > 0 ? Math.round((bar.total / maxVal) * 100) : 0;
        const valLabel = bar.total >= 1000
            ? '₱' + (bar.total / 1000).toFixed(1) + 'k'
            : (bar.total > 0 ? '₱' + Math.round(bar.total) : '₱0');
        
        // Create a tooltip with more info
        const tooltip = `${bar.label}\nIncome: ${valLabel}\nRepairs: ${bar.repairs || 0}`;
        
        return `<div class="bar-col" style="cursor:pointer;" title="${tooltip}">
            <span class="bar-val" style="font-size:9px;">${valLabel}</span>
            <div class="bar-body">
                <div style="width:100%; height:100%; display:flex; flex-direction:column; justify-content:flex-end;">
                    ${bar.total > 0 ? `<div style="height:${heightPct}%; background: linear-gradient(180deg, #38bdf8, #0284c7); border-radius:3px;"></div>` : '<div style="height:2px; background:#e2e8f0; border-radius:3px;"></div>'}
                </div>
            </div>
            <span class="bar-label" style="font-size:9px;">${bar.label}</span>
            ${bar.repairs > 0 ? `<span style="font-size:8px; color:#94a3b8; margin-top:2px;">${bar.repairs}</span>` : ''}
        </div>`;
    }).join('');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadReport();
    loadIncome();
});
</script>