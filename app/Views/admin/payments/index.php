<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Payments</h1>
        <a href="<?= base_url('admin') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Payment Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Payments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalPayments ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Successful Payments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $successfulPayments ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pending Payments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pendingPayments ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Failed Payments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $failedPayments ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Payments (<?= count($payments) ?>)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="paymentsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Stack</th>
                            <th>Amount</th>
                            <th>Transaction ID</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?= $payment['id'] ?></td>
                            <td>
                                <strong><?= esc($payment['full_name']) ?></strong>
                                <br><small class="text-muted"><?= esc($payment['phone']) ?></small>
                            </td>
                            <td>
                                <strong><?= esc($payment['title']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-success">$<?= number_format($payment['amount'], 2) ?></span>
                            </td>
                            <td>
                                <code><?= esc($payment['transaction_id']) ?></code>
                            </td>
                            <td>
                                <?php 
                                $statusClass = '';
                                $statusText = '';
                                switch ($payment['status']) {
                                    case 'SUCCESS':
                                        $statusClass = 'bg-success';
                                        $statusText = 'Success';
                                        break;
                                    case 'PENDING':
                                        $statusClass = 'bg-warning';
                                        $statusText = 'Pending';
                                        break;
                                    case 'FAILED':
                                        $statusClass = 'bg-danger';
                                        $statusText = 'Failed';
                                        break;
                                    default:
                                        $statusClass = 'bg-secondary';
                                        $statusText = ucfirst($payment['status']);
                                }
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= date('M j, Y g:i A', strtotime($payment['paid_at'])) ?>
                                </small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="viewPayment(<?= $payment['id'] ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <?php if ($payment['status'] === 'PENDING'): ?>
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                onclick="checkPaymentStatus('<?= $payment['transaction_id'] ?>')">
                                            <i class="fas fa-sync"></i> Check
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Payment Details Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="paymentModalBody">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#paymentsTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true
    });
});

function viewPayment(paymentId) {
    // Load payment details via AJAX
    $.get(`<?= base_url('api/payments') ?>/${paymentId}`)
        .done(function(response) {
            if (response.status === 'success') {
                const payment = response.data;
                $('#paymentModalBody').html(`
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Payment Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>ID:</strong></td><td>${payment.id}</td></tr>
                                <tr><td><strong>Amount:</strong></td><td>$${parseFloat(payment.amount).toFixed(2)}</td></tr>
                                <tr><td><strong>Transaction ID:</strong></td><td><code>${payment.transaction_id}</code></td></tr>
                                <tr><td><strong>Status:</strong></td><td><span class="badge bg-${getStatusClass(payment.status)}">${payment.status}</span></td></tr>
                                <tr><td><strong>Date:</strong></td><td>${new Date(payment.paid_at).toLocaleString()}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>User Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Name:</strong></td><td>${payment.full_name}</td></tr>
                                <tr><td><strong>Phone:</strong></td><td>${payment.phone}</td></tr>
                            </table>
                            
                            <h6>Stack Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Title:</strong></td><td>${payment.title}</td></tr>
                                <tr><td><strong>Entry Fee:</strong></td><td>$${parseFloat(payment.entry_fee || payment.amount).toFixed(2)}</td></tr>
                            </table>
                        </div>
                    </div>
                `);
                
                $('#paymentModal').modal('show');
            }
        })
        .fail(function() {
            alert('Failed to load payment details');
        });
}

function checkPaymentStatus(transactionId) {
    // Check payment status via AJAX
    $.get(`<?= base_url('api/payments/verify') ?>/${transactionId}`)
        .done(function(response) {
            if (response.status === 'success') {
                const status = response.data.status;
                alert(`Payment status: ${status}`);
                // Reload the page to show updated status
                location.reload();
            } else {
                alert('Failed to check payment status');
            }
        })
        .fail(function() {
            alert('Failed to check payment status');
        });
}

function getStatusClass(status) {
    switch (status) {
        case 'SUCCESS': return 'success';
        case 'PENDING': return 'warning';
        case 'FAILED': return 'danger';
        default: return 'secondary';
    }
}
</script>
<?= $this->endSection() ?> 