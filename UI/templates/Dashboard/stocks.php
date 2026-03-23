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
            <span class="repair-stat-value"><?= count($stocks ?? []) ?></span>
        </div>

        <div class="repair-stat-card" style="border-left:4px solid #16a34a;">
            <span class="repair-stat-label">Well Stocked</span>
            <span class="repair-stat-value" style="color:#16a34a;"><?= count(array_filter($stocks ?? [], fn($s) => $s['status'] === 'normal')) ?></span>
        </div>

        <div class="repair-stat-card" style="border-left:4px solid #f59e0b;">
            <span class="repair-stat-label">Low Stock</span>
            <span class="repair-stat-value" style="color:#f59e0b;"><?= count(array_filter($stocks ?? [], fn($s) => $s['status'] === 'warning')) ?></span>
        </div>

        <div class="repair-stat-card" style="border-left:4px solid #ef4444;">
            <span class="repair-stat-label">Out of Stock</span>
            <span class="repair-stat-value" style="color:#ef4444;">0</span>
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
                $stocks = $stocks ?? [];
                $totalItems = count($stocks);
                $wellStocked = count(array_filter($stocks, fn($s) => $s['status'] === 'normal'));
                $lowStock = count(array_filter($stocks, fn($s) => $s['status'] === 'warning'));
                
                foreach ($stocks as $item):
                    $pct = $item['quantity'] > 0 ? round(($item['quantity'] / ($item['quantity'] + $item['minimum'])) * 100) : 0;

                    if ($item['status'] === 'warning') {
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
                        <strong style="color:#1e293b;"><?= htmlspecialchars($item['part'] ?? '') ?></strong>
                    </td>
                    <td style="color:#64748b; font-size:13px;">Part ID: <?= $item['id'] ?></td>
                    <td>
                        <span class="job-id"><?= htmlspecialchars($item['category'] ?? 'Uncategorized') ?></span>
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
                        <span style="font-size:15px; font-weight:700; color:#1e293b;"><?= $item['quantity'] ?></span>
                        <span style="font-size:12px; color:#94a3b8;"> units</span>
                    </td>
                    <td><span class="status-badge <?= $badgeClass ?>"><?= $badgeText ?></span></td>
                    <td style="text-align:center;">
                        <button class="tbl-btn tbl-btn-view" onclick="openStockView(<?= htmlspecialchars(json_encode($item)) ?>)">View</button>
                        <button class="tbl-btn tbl-btn-edit" onclick="openRestockModal(<?= htmlspecialchars(json_encode($item)) ?>)">Restock</button>
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

    <!-- Pagination -->
    <div id="paginationBar" style="display:flex; justify-content:space-between; align-items:center; margin-top:16px; flex-wrap:wrap; gap:10px;">
        <span id="paginationInfo" style="font-size:13px; color:#64748b;"></span>
        <div style="display:flex; gap:6px;" id="paginationBtns"></div>
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

        <form onsubmit="saveAddStock(event);">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">

                <div>
                    <label class="modal-label">Item Name</label>
                    <input type="text" id="add-part-name" placeholder="e.g. Hard Drive" class="modal-input" required>
                </div>

                <div>
                    <label class="modal-label">Category</label>
                    <input type="text" id="add-part-category" placeholder="e.g. Storage" class="modal-input" required>
                </div>

                <div>
                    <label class="modal-label">Quantity</label>
                    <input type="number" id="add-part-qty" placeholder="0" min="0" class="modal-input" required>
                </div>

                <div>
                    <label class="modal-label">Minimum Stock</label>
                    <input type="number" id="add-part-min" placeholder="0" min="0" class="modal-input" required>
                </div>

            </div>

            <div style="margin-bottom:20px;">
                <label class="modal-label">Unit Price ($)</label>
                <input type="number" id="add-part-price" placeholder="0.00" step="0.01" min="0" class="modal-input" required>
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

<!-- Stock View Modal -->
<div class="modal-overlay" id="stockViewModal" onclick="if(event.target===this) this.style.display='none'">
    <div class="modal-box" style="width:480px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="font-size:1.1rem; color:#1e293b;">📦 Stock Details</h2>
            <button onclick="this.closest('.modal-overlay').style.display='none'" style="background:none; border:none; font-size:1.4rem; color:#94a3b8; cursor:pointer;">✕</button>
        </div>
        <div id="stockViewContent"></div>
        <div style="margin-top:20px;">
            <button onclick="this.closest('.modal-overlay').style.display='none'" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px; background:white; color:#64748b; font-weight:600; cursor:pointer;">Close</button>
        </div>
    </div>
</div>

<!-- Restock Modal -->
<div class="modal-overlay" id="restockModal" onclick="if(event.target===this) this.style.display='none'">
    <div class="modal-box" style="width:480px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 style="font-size:1.1rem; color:#1e293b;">🔄 Restock Item</h2>
            <button onclick="this.closest('.modal-overlay').style.display='none'" style="background:none; border:none; font-size:1.4rem; color:#94a3b8; cursor:pointer;">✕</button>
        </div>
        <input type="hidden" id="restock-id">
        <div>
            <label class="modal-label">Current Quantity</label>
            <input type="number" id="restock-current" class="modal-input" disabled>
        </div>
        <div style="margin-bottom:14px;">
            <label class="modal-label">Units to Add</label>
            <input type="number" id="restock-qty" class="modal-input" placeholder="0" min="0" value="0">
        </div>
        <div style="margin-bottom:20px; padding:10px; background:#f0fdf4; border-radius:6px;">
            <p style="margin:0; font-size:13px; color:#15803d;"><strong>New Total:</strong> <span id="restock-total">0</span> units</p>
        </div>
        <div style="display:flex; gap:10px;">
            <button type="button" onclick="this.closest('.modal-overlay').style.display='none'" style="flex:1; padding:10px; border:1px solid #e2e8f0; border-radius:8px; background:white; color:#64748b; font-weight:600; cursor:pointer;">Cancel</button>
            <button type="button" onclick="saveRestock()" style="flex:1; padding:10px; background:linear-gradient(135deg,#38bdf8,#0284c7); border:none; border-radius:8px; color:white; font-weight:600; cursor:pointer;">Save Stock</button>
        </div>
    </div>
</div>

<style>
.pg-btn {
    padding: 6px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 7px;
    background: white;
    color: #475569;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
}
.pg-btn:hover { background: #f1f5f9; }
.pg-btn.active {
    background: linear-gradient(135deg, #38bdf8, #0284c7);
    color: white;
    border-color: transparent;
}
.pg-btn:disabled { opacity: 0.4; cursor: default; }
</style>

<script>
/* ======= PAGINATION ======= */
const ROWS_PER_PAGE = 8;
let currentPage = 1;
let allRows = [];
let filteredRows = [];

function initPagination() {
    allRows = Array.from(document.querySelectorAll('#stockBody tr'));
    filteredRows = [...allRows];
    renderPage(1);
}

function renderPage(page) {
    currentPage = page;
    const total = filteredRows.length;
    const totalPages = Math.max(1, Math.ceil(total / ROWS_PER_PAGE));
    const start = (page - 1) * ROWS_PER_PAGE;
    const end   = start + ROWS_PER_PAGE;

    // Show/hide rows
    allRows.forEach(r => r.style.display = 'none');
    filteredRows.forEach((r, i) => {
        r.style.display = (i >= start && i < end) ? '' : 'none';
    });

    // Info text
    const from = total === 0 ? 0 : start + 1;
    const to   = Math.min(end, total);
    document.getElementById('paginationInfo').textContent =
        total === 0 ? 'No items found' : `Showing ${from}–${to} of ${total} items`;

    // Empty state
    document.getElementById('stockEmpty').style.display = total === 0 ? 'block' : 'none';

    // Build page buttons
    const btns = document.getElementById('paginationBtns');
    btns.innerHTML = '';

    // Prev
    const prev = document.createElement('button');
    prev.className = 'pg-btn';
    prev.textContent = '‹';
    prev.disabled = page <= 1;
    prev.onclick = () => renderPage(page - 1);
    btns.appendChild(prev);

    // Page numbers (show max 5 around current)
    const range = pagRange(page, totalPages);
    range.forEach(p => {
        if (p === '…') {
            const dots = document.createElement('span');
            dots.textContent = '…';
            dots.style.cssText = 'padding:6px 4px; color:#94a3b8; font-size:13px;';
            btns.appendChild(dots);
        } else {
            const btn = document.createElement('button');
            btn.className = 'pg-btn' + (p === page ? ' active' : '');
            btn.textContent = p;
            btn.onclick = () => renderPage(p);
            btns.appendChild(btn);
        }
    });

    // Next
    const next = document.createElement('button');
    next.className = 'pg-btn';
    next.textContent = '›';
    next.disabled = page >= totalPages;
    next.onclick = () => renderPage(page + 1);
    btns.appendChild(next);
}

function pagRange(current, total) {
    if (total <= 7) return Array.from({length: total}, (_, i) => i + 1);
    if (current <= 4) return [1,2,3,4,5,'…',total];
    if (current >= total - 3) return [1,'…',total-4,total-3,total-2,total-1,total];
    return [1,'…',current-1,current,current+1,'…',total];
}

/* ======= SEARCH (override original to also re-paginate) ======= */
function filterStock(val) {
    const q = val.toLowerCase();
    filteredRows = allRows.filter(r => r.innerText.toLowerCase().includes(q));
    renderPage(1);
}

function filterStockStatus(val) {
    filteredRows = allRows.filter(r => val === 'all' || r.dataset.level === val);
    renderPage(1);
}

/* ======= MODALS ======= */
let currentStockItem = {};

function openStockView(item) {
    const row = (k, v) => `<div style="margin-bottom:12px; padding:10px; background:#f8fafc; border-radius:6px;"><span style="font-size:12px; color:#64748b; display:block; margin-bottom:4px;">${k}</span><span style="font-size:14px; color:#1e293b; font-weight:600;">${v}</span></div>`;
    document.getElementById('stockViewContent').innerHTML = `
        ${row('Part Name', item.part)}
        ${row('Part ID', item.id)}
        ${row('Category', item.category)}
        ${row('Current Stock', item.quantity + ' units')}
        ${row('Minimum Stock', item.minimum + ' units')}
        ${row('Unit Price', '$' + (item.price ?? '0.00'))}
        ${row('Status', item.status === 'warning' ? '⚠️ Low Stock' : '✓ Well Stocked')}
        ${row('Last Updated', new Date().toLocaleDateString())}`;
    document.getElementById('stockViewModal').style.display = 'flex';
}

function openRestockModal(item) {
    currentStockItem = item;
    document.getElementById('restock-id').value = item.id;
    document.getElementById('restock-current').value = item.quantity;
    document.getElementById('restock-qty').value = 0;
    document.getElementById('restock-total').textContent = item.quantity;
    document.getElementById('restock-qty').oninput = function() {
        document.getElementById('restock-total').textContent = item.quantity + parseInt(this.value || 0);
    };
    document.getElementById('restockModal').style.display = 'flex';
}

async function saveRestock() {
    const partId = document.getElementById('restock-id').value;
    const qtyToAdd = parseInt(document.getElementById('restock-qty').value || 0);
    if (qtyToAdd <= 0) { alert('Please enter a quantity to add.'); return; }
    const csrfToken = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content');
    if (!csrfToken) { alert('Security error: CSRF token not found'); return; }
    const response = await fetch("<?= $this->Url->build('/parts/restock') ?>", {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-Token": csrfToken },
        body: JSON.stringify({ part_id: partId, quantity_added: qtyToAdd })
    });
    const data = await response.json();
    if (data.success) { alert('✓ Stock updated successfully!'); document.getElementById('restockModal').style.display = 'none'; location.reload(); }
    else { alert('Error: ' + (data.error || 'Failed to update stock')); }
}

async function saveAddStock(e) {
    e.preventDefault();
    const partName = document.getElementById('add-part-name').value.trim();
    const category = document.getElementById('add-part-category').value.trim();
    const qty      = parseInt(document.getElementById('add-part-qty').value || 0);
    const minQty   = parseInt(document.getElementById('add-part-min').value || 0);
    const price    = parseFloat(document.getElementById('add-part-price').value || 0);
    if (!partName || !category || qty < 0 || minQty < 0 || price < 0) { alert('Please fill in all fields with valid values.'); return; }
    const csrfToken = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content');
    if (!csrfToken) { alert('Security error: CSRF token not found'); return; }
    const response = await fetch("<?= $this->Url->build('/parts/add') ?>", {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-Token": csrfToken },
        body: JSON.stringify({ part_name: partName, category: category, stock_quantity: qty, minimum_stock: minQty, unit_price: price })
    });
    const data = await response.json();
    if (data.success) { alert('✓ Stock item added successfully!'); document.getElementById('addStockModal').style.display = 'none'; location.reload(); }
    else { alert('Error: ' + (data.error || 'Failed to add stock item')); }
}

// Init on load
document.addEventListener('DOMContentLoaded', initPagination);
</script>