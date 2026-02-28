<div class="page-section">

    <!-- Header -->
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
        <div>
            <h1 style="font-size:26px; color:#1e293b; margin:0 0 4px;">📦 Stock Inventory</h1>
            <p style="color:#64748b; margin:0;">Monitor spare parts and supplies available for repairs.</p>
        </div>
        <button class="btn-new-repair" onclick="document.getElementById('addStockModal').style.display='flex'">
            + Add Stock
        </button>
    </div>

    <!-- Summary Cards -->
    <div style="display:flex; gap:14px; margin-bottom:24px; flex-wrap:wrap;">

        <div class="repair-stat-card" style="border-left:4px solid #38bdf8;">
            <span class="repair-stat-label">Total Items</span>
            <span class="repair-stat-value">6</span>
        </div>

        <div class="repair-stat-card" style="border-left:4px solid #16a34a;">
            <span class="repair-stat-label">Well Stocked</span>
            <span class="repair-stat-value" style="color:#16a34a;">3</span>
        </div>

        <div class="repair-stat-card" style="border-left:4px solid #f59e0b;">
            <span class="repair-stat-label">Low Stock</span>
            <span class="repair-stat-value" style="color:#f59e0b;">2</span>
        </div>

        <div class="repair-stat-card" style="border-left:4px solid #ef4444;">
            <span class="repair-stat-label">Critical</span>
            <span class="repair-stat-value" style="color:#ef4444;">1</span>
        </div>

    </div>

    <!-- Search & Filter -->
    <div style="display:flex; gap:10px; margin-bottom:16px; flex-wrap:wrap; align-items:center;">
        <input type="text" placeholder="🔍  Search parts..."
            style="flex:1; min-width:200px; padding:9px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:14px; outline:none; color:#1e293b;"
            oninput="filterStock(this.value)">

        <select onchange="filterStockStatus(this.value)"
            style="padding:9px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:14px; color:#475569; outline:none; background:white;">
            <option value="all">All Levels</option>
            <option value="good">Well Stocked</option>
            <option value="low">Low Stock</option>
            <option value="critical">Critical</option>
        </select>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="dashboard-table" id="stockTable">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Specification</th>
                    <th>Category</th>
                    <th style="min-width:180px;">Stock Level</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th style="text-align:center;">Actions</th>
                </tr>
            </thead>

            <tbody id="stockBody">

                <?php
                $stocks = [
                    ['name'=>'Hard Drive',        'spec'=>'1TB HDD, SATA III',       'cat'=>'Storage',   'qty'=>12, 'max'=>50,  'icon'=>'💾'],
                    ['name'=>'RAM Module',         'spec'=>'8GB DDR4 3200MHz',        'cat'=>'Memory',    'qty'=>25, 'max'=>60,  'icon'=>'🧠'],
                    ['name'=>'Screen Replacement', 'spec'=>'15" FHD IPS Display',     'cat'=>'Display',   'qty'=>8,  'max'=>30,  'icon'=>'🖥️'],
                    ['name'=>'Laptop Battery',     'spec'=>'65Wh Li-ion',             'cat'=>'Power',     'qty'=>3,  'max'=>20,  'icon'=>'🔋'],
                    ['name'=>'Charging Port',      'spec'=>'USB-C / DC Jack',         'cat'=>'Connector', 'qty'=>15, 'max'=>40,  'icon'=>'🔌'],
                    ['name'=>'Thermal Paste',      'spec'=>'MX-4 / Arctic Silver 5',  'cat'=>'Cooling',   'qty'=>2,  'max'=>25,  'icon'=>'🌡️'],
                ];

                foreach ($stocks as $item):
                    $pct = round(($item['qty'] / $item['max']) * 100);

                    if ($pct <= 20) {
                        $level      = 'critical';
                        $barColor   = '#ef4444';
                        $badgeClass = 'badge-critical';
                        $badgeText  = 'Critical';
                    } elseif ($pct <= 50) {
                        $level      = 'low';
                        $barColor   = '#f59e0b';
                        $badgeClass = 'badge-low';
                        $badgeText  = 'Low Stock';
                    } else {
                        $level      = 'good';
                        $barColor   = '#16a34a';
                        $badgeClass = 'badge-stocked';
                        $badgeText  = 'Well Stocked';
                    }
                ?>
                <tr data-level="<?= $level ?>">
                    <td>
                        <span style="margin-right:6px;"><?= $item['icon'] ?></span>
                        <strong style="color:#1e293b;"><?= $item['name'] ?></strong>
                    </td>
                    <td style="color:#64748b; font-size:13px;"><?= $item['spec'] ?></td>
                    <td>
                        <span class="job-id"><?= $item['cat'] ?></span>
                    </td>
                    <td style="min-width:180px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div class="bar-track" style="flex:1; height:10px;">
                                <div class="bar-fill" style="width:<?= $pct ?>%; background:<?= $barColor ?>;"></div>
                            </div>
                            <span style="font-size:12px; font-weight:700; color:<?= $barColor ?>; min-width:32px;"><?= $pct ?>%</span>
                        </div>
                    </td>
                    <td>
                        <span style="font-size:15px; font-weight:700; color:#1e293b;"><?= $item['qty'] ?></span>
                        <span style="font-size:12px; color:#94a3b8;"> / <?= $item['max'] ?> units</span>
                    </td>
                    <td><span class="status-badge <?= $badgeClass ?>"><?= $badgeText ?></span></td>
                    <td style="text-align:center;">
                        <button class="tbl-btn tbl-btn-view">View</button>
                        <button class="tbl-btn tbl-btn-edit">Restock</button>
                    </td>
                </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>

    <!-- Empty state -->
    <div id="stockEmpty" style="display:none; text-align:center; padding:60px 20px; color:#94a3b8;">
        <div style="font-size:2.5rem; margin-bottom:10px;">📭</div>
        <p style="font-size:15px;">No items match your search.</p>
    </div>

</div>

<!-- Add Stock Modal -->
<div class="modal-overlay" id="addStockModal" onclick="if(event.target===this) this.style.display='none'">
    <div class="modal-box" style="width:480px;">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="font-size:1.1rem; color:#1e293b;">+ Add Stock Item</h2>
            <button onclick="document.getElementById('addStockModal').style.display='none'"
                style="background:none; border:none; font-size:1.4rem; color:#94a3b8; cursor:pointer;">✕</button>
        </div>

        <form method="post">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">

                <div>
                    <label class="modal-label">Item Name</label>
                    <input type="text" placeholder="e.g. Hard Drive" class="modal-input">
                </div>

                <div>
                    <label class="modal-label">Category</label>
                    <input type="text" placeholder="e.g. Storage" class="modal-input">
                </div>

                <div>
                    <label class="modal-label">Quantity</label>
                    <input type="number" placeholder="0" min="0" class="modal-input">
                </div>

                <div>
                    <label class="modal-label">Max Capacity</label>
                    <input type="number" placeholder="0" min="0" class="modal-input">
                </div>

            </div>

            <div style="margin-bottom:20px;">
                <label class="modal-label">Specification</label>
                <input type="text" placeholder="e.g. 1TB HDD, SATA III" class="modal-input">
            </div>

            <div style="display:flex; gap:10px;">
                <button type="button" onclick="document.getElementById('addStockModal').style.display='none'"
                    style="flex:1; padding:10px; border:1px solid #e2e8f0; border-radius:8px; background:white; color:#64748b; font-weight:600; cursor:pointer;">
                    Cancel
                </button>
                <button type="submit"
                    style="flex:1; padding:10px; background:linear-gradient(135deg,#38bdf8,#0284c7); border:none; border-radius:8px; color:white; font-weight:600; cursor:pointer;">
                    Save Item
                </button>
            </div>
        </form>

    </div>
</div>

<script>
function filterStock(val) {
    const rows = document.querySelectorAll('#stockBody tr');
    const q = val.toLowerCase();
    let visible = 0;
    rows.forEach(r => {
        const match = r.innerText.toLowerCase().includes(q);
        r.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    document.getElementById('stockEmpty').style.display = visible === 0 ? 'block' : 'none';
}

function filterStockStatus(val) {
    const rows = document.querySelectorAll('#stockBody tr');
    rows.forEach(r => {
        r.style.display = (val === 'all' || r.dataset.level === val) ? '' : 'none';
    });
}
</script>