<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Top-Up Transactions</h1>
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

    <!-- Top-Up Transaction Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Transactions</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalTransactions ?></div>
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
                                Successful Top-Ups</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $successfulTransactions ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
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
                                Pending Top-Ups</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $pendingTransactions ?></div>
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
                                Failed Top-Ups</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $failedTransactions ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Amount Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Amount Credited</div>
                            <div class="h3 mb-0 font-weight-bold text-gray-800">UGX <?= number_format($totalAmount, 0) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wallet fa-3x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Top-Up Transactions (<?= count($transactions) ?>)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="topupTransactionsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Phone Number</th>
                            <th>Amount</th>
                            <th>Transaction ID</th>
                            <th>Status</th>
                            <th>GM Pay Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?= $transaction['id'] ?></td>
                            <td>
                                <strong><?= esc($transaction['full_name']) ?></strong>
                                <br><small class="text-muted"><?= esc($transaction['phone']) ?></small>
                            </td>
                            <td>
                                <code><?= esc($transaction['msisdn']) ?></code>
                            </td>
                            <td>
                                <span class="badge bg-success">UGX <?= number_format($transaction['amount'], 0) ?></span>
                            </td>
                            <td>
                                <code><?= esc($transaction['transaction_id']) ?></code>
                            </td>
                            <td>
                                <?php 
                                $statusClass = '';
                                $statusText = '';
                                switch ($transaction['status']) {
                                    case 'success':
                                        $statusClass = 'bg-success';
                                        $statusText = 'Success';
                                        break;
                                    case 'pending':
                                        $statusClass = 'bg-warning';
                                        $statusText = 'Pending';
                                        break;
                                    case 'failed':
                                        $statusClass = 'bg-danger';
                                        $statusText = 'Failed';
                                        break;
                                    default:
                                        $statusClass = 'bg-secondary';
                                        $statusText = ucfirst($transaction['status']);
                                }
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                            </td>
                            <td>
                                <?php 
                                $gmpayStatusClass = '';
                                $gmpayStatusText = '';
                                switch ($transaction['gmpay_status']) {
                                    case 'SUCCESS':
                                        $gmpayStatusClass = 'bg-success';
                                        $gmpayStatusText = 'SUCCESS';
                                        break;
                                    case 'PENDING':
                                        $gmpayStatusClass = 'bg-warning';
                                        $gmpayStatusText = 'PENDING';
                                        break;
                                    case 'FAILED':
                                        $gmpayStatusClass = 'bg-danger';
                                        $gmpayStatusText = 'FAILED';
                                        break;
                                    default:
                                        $gmpayStatusClass = 'bg-secondary';
                                        $gmpayStatusText = $transaction['gmpay_status'];
                                }
                                ?>
                                <span class="badge <?= $gmpayStatusClass ?>"><?= $gmpayStatusText ?></span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= date('M j, Y g:i A', strtotime($transaction['created_at'])) ?>
                                </small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="viewTransaction(<?= $transaction['id'] ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <?php if ($transaction['status'] === 'pending'): ?>
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                onclick="checkTransactionStatus('<?= $transaction['transaction_id'] ?>')">
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

<!-- Transaction Details Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Top-Up Transaction Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="transactionModalBody">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#topupTransactionsTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true
    });
});

function viewTransaction(transactionId) {
    // Load transaction details via AJAX
    $.get(`<?= base_url('api/wallet/topup/details') ?>/${transactionId}`)
        .done(function(response) {
            if (response.status === 'success') {
                const transaction = response.data;
                $('#transactionModalBody').html(`
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Transaction Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>ID:</strong></td><td>${transaction.id}</td></tr>
                                <tr><td><strong>Amount:</strong></td><td>UGX ${parseFloat(transaction.amount).toLocaleString()}</td></tr>
                                <tr><td><strong>Transaction ID:</strong></td><td><code>${transaction.transaction_id}</code></td></tr>
                                <tr><td><strong>Status:</strong></td><td><span class="badge bg-${getStatusClass(transaction.status)}">${transaction.status}</span></td></tr>
                                <tr><td><strong>GM Pay Status:</strong></td><td><span class="badge bg-${getGMPayStatusClass(transaction.gmpay_status)}">${transaction.gmpay_status}</span></td></tr>
                                <tr><td><strong>Date:</strong></td><td>${new Date(transaction.created_at).toLocaleString()}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>User Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Name:</strong></td><td>${transaction.full_name}</td></tr>
                                <tr><td><strong>Phone:</strong></td><td>${transaction.phone}</td></tr>
                                <tr><td><strong>MSISDN:</strong></td><td><code>${transaction.msisdn}</code></td></tr>
                            </table>
                            
                            <h6>GM Pay Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Reference:</strong></td><td>${transaction.gmpay_reference || 'Not available'}</td></tr>
                                <tr><td><strong>Last Updated:</strong></td><td>${transaction.updated_at ? new Date(transaction.updated_at).toLocaleString() : 'Not available'}</td></tr>
                            </table>
                        </div>
                    </div>
                `);
                
                $('#transactionModal').modal('show');
            }
        })
        .fail(function() {
            alert('Failed to load transaction details');
        });
}

function checkTransactionStatus(transactionId) {
    // Check transaction status via AJAX
    $.get(`<?= base_url('api/cron/check-pending-topups') ?>`)
        .done(function(response) {
            if (response.status === 'success') {
                alert(`Status check completed!\nProcessed: ${response.data.processed}\nSuccessful: ${response.data.successful}\nFailed: ${response.data.failed}`);
                // Reload the page to show updated status
                location.reload();
            } else {
                alert('Failed to check transaction status');
            }
        })
        .fail(function() {
            alert('Failed to check transaction status');
        });
}

function getStatusClass(status) {
    switch (status) {
        case 'success': return 'success';
        case 'pending': return 'warning';
        case 'failed': return 'danger';
        default: return 'secondary';
    }
}

function getGMPayStatusClass(status) {
    switch (status) {
        case 'SUCCESS': return 'success';
        case 'PENDING': return 'warning';
        case 'FAILED': return 'danger';
        default: return 'secondary';
    }
}
</script>
<?= $this->endSection() ?>
