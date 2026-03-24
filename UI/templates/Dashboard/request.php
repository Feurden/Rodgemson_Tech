<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Orders Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body { background-color: #f5f7fa; }
    .card { border-radius: 12px; }
    .badge { font-size: 0.8rem; }
</style>
</head>

<body>

<div class="container mt-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>📦 Orders Management</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOrderModal">
            + New Order
        </button>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-md-4">
            <input type="text" class="form-control" placeholder="Search customer / part...">
        </div>
        <div class="col-md-3">
            <select class="form-select">
                <option>All Status</option>
                <option>Pending</option>
                <option>Ordered</option>
                <option>Received</option>
            </select>
        </div>
    </div>

    <!-- Table -->
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">

                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Part</th>
                        <th>Qty</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <!-- SAMPLE DATA -->
                    <tr>
                        <td>1</td>
                        <td>John Doe</td>
                        <td>iPhone 11</td>
                        <td>LCD Screen</td>
                        <td>2</td>
                        <td><span class="badge bg-warning">Pending</span></td>
                        <td>Urgent repair</td>
                        <td>2026-03-23</td>
                        <td>
                            <button class="btn btn-sm btn-info">Edit</button>
                            <button class="btn btn-sm btn-success">Status</button>
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </td>
                    </tr>

                    <tr>
                        <td>2</td>
                        <td>Jane Smith</td>
                        <td>Samsung A50</td>
                        <td>Battery</td>
                        <td>1</td>
                        <td><span class="badge bg-primary">Ordered</span></td>
                        <td>-</td>
                        <td>2026-03-22</td>
                        <td>
                            <button class="btn btn-sm btn-info">Edit</button>
                            <button class="btn btn-sm btn-success">Status</button>
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ADD ORDER MODAL -->
<div class="modal fade" id="addOrderModal">
<div class="modal-dialog">
<div class="modal-content">

    <div class="modal-header">
        <h5>Add New Order</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
    </div>

    <div class="modal-body">

        <form>

            <div class="mb-2">
                <label>Customer Name</label>
                <input type="text" class="form-control">
            </div>

            <div class="mb-2">
                <label>Phone Model</label>
                <input type="text" class="form-control">
            </div>

            <div class="mb-2">
                <label>Part Name</label>
                <input type="text" class="form-control">
            </div>

            <div class="mb-2">
                <label>Quantity</label>
                <input type="number" class="form-control" value="1">
            </div>

            <div class="mb-2">
                <label>Status</label>
                <select class="form-select">
                    <option>Pending</option>
                    <option>Ordered</option>
                    <option>Received</option>
                </select>
            </div>

            <div class="mb-2">
                <label>Notes</label>
                <textarea class="form-control"></textarea>
            </div>

        </form>

    </div>

    <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary">Save Order</button>
    </div>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>