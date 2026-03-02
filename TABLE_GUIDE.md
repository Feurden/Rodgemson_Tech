/* 
TABLE REFERENCE - RODGEMSON CELLPHONE REPAIR SHOP DATABASE
============================================================

CORE TABLES (Essential - Keep these):
─────────────────────────────────────

1. devices (Repair Jobs)
   - Stores ALL repair jobs
   - Columns: id, customer_id, brand, model, imei, issue_description, status, priority_level, date_received, date_released
   - Links to: customers (customer_id) → who brought the phone
   - Links to: repair_parts (device_id) → what parts were used
   - THIS IS YOUR MAIN TABLE FOR REPAIR JOBS

2. customers (Phone Owners)
   - Stores customer/owner information
   - Columns: id, full_name, customer_info, contact_no, phone_model, phone_issue, diagnostic, suggested_part_replacement
   - Links from: devices (customer_id) → each device belongs to a customer
   - USE: When creating a repair job, customer should already exist OR create customer FIRST, then link to device

3. parts (Spare Parts Inventory)
   - Stores all spare parts in stock
   - Columns: id, part_name, category, stock_quantity, minimum_stock, unit_price
   - Links to: repair_parts → which parts are used in which repairs
   - USE: "Add Stock" button, "Restock" button

4. repair_parts (Junction Table)
   - Links devices to parts (many-to-many relationship)
   - Columns: id, device_id, part_id, quantity_used
   - Links: device_id → devices, part_id → parts
   - USE: Record which parts were used in each repair job

5. users (Technicians)
   - Stores technician accounts
   - Columns: id, username, password, role, created, modified
   - USE: Login system, user authentication

────────────────────────────────────────────────────────────

OPTIONAL TABLES (Can keep if needed):
────────────────────────────────────

6. notifications (System Alerts)
   - Stores system notifications
   - Columns: id, message, type, is_read, created
   - Types: 'Low Stock', 'Pending Repair', 'Priority Alert'
   - USE: Alert technicians about low parts or pending repairs (dashboard feature)

7. orders (Parts Procurement)
   - Tracks parts orders from suppliers
   - Columns: id, part_name, quantity, status, created, modified
   - Status: 'Pending', 'Ordered', 'Received'
   - USE: Track when you order parts from suppliers (future feature)

────────────────────────────────────────────────────────────

UNNECESSARY/DUPLICATE TABLES (Can delete):
──────────────────────────────────────────

❌ repairs - appears to be a duplicate/old version of devices
❌ stock_categories - probably unnecessary if using parts.category
❌ stock_transactions - tracking individual stock movements (overkill for MVP)
❌ v_monthly_repairs - VIEW (database view, auto-generated)
❌ v_weekly_repairs - VIEW (database view, auto-generated)
❌ v_repair_summary - VIEW (database view, auto-generated)
❌ v_repairs - VIEW (database view of devices)
❌ v_stocks - VIEW (database view of parts)

Views are read-only summaries generated from core tables - they're auto-updated
so they're not essential for data entry.

════════════════════════════════════════════════════════════

CURRENT ISSUE IN YOUR CODE:
═══════════════════════════

In DevicesController::add(), we're creating a `customer` record:

    $customer = $customersTable->newEntity([
        'full_name' => 'Customer',
        'phone_model' => ($data['brand'] ?? 'Unknown') . ' ' . ($data['model'] ?? 'Unknown'),
        'phone_issue' => $data['issue_description'] ?? '',
    ]);

This is CORRECT - we need a customer to link to the device.
But the issue is the form doesn't ask for customer details.

BETTER WORKFLOW:
════════════════
1. User selects/creates a customer FIRST (or enters customer name)
2. Then user creates a repair job for that customer
3. The repair job (device) is linked to the customer

OR if user wants it simple:
1. When creating repair job, just use "Customer" as a generic placeholder
2. Later edit the device and assign to actual customer

════════════════════════════════════════════════════════════

RECOMMENDATION:
═══════════════

KEEP these 7 core tables:
✅ customers
✅ devices  
✅ parts
✅ repair_parts
✅ users
✅ notifications (optional but useful)
✅ orders (optional but useful)

DELETE these (unnecessary duplicates/views):
❌ repairs
❌ stock_categories
❌ stock_transactions
❌ v_monthly_repairs
❌ v_weekly_repairs
❌ v_repair_summary
❌ v_repairs
❌ v_stocks
*/
