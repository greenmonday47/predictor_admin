<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="card-title mb-2">
            <i class="fas fa-wallet"></i>
            Manage Top-Up Transactions
        </h1>
        <p class="text-muted mb-0">View and manage all wallet top-up transactions</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('admin') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Dashboard</span>
        </a>
    </div>
</div>

<!-- Flash Messages -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<!-- Overview Statistics -->
<div class="grid grid-4 mb-4">
    <div class="card p-0" style="background: var(--primary-gradient); color: var(--text-white); overflow: hidden;">
        <div class="p-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <p class="mb-1" style="opacity: 0.9; font-size: 0.9rem; font-weight: 500;">Total Transactions</p>
                    <h3 class="mb-0" style="font-size: 2.2rem; font-weight: 700;"><?= number_format($totalTransactions ?? 0) ?></h3>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 12px; border-radius: 12px;">
                    <i class="fas fa-calendar" style="font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>
        <div style="height: 4px; background: linear-gradient(90deg, rgba(255,255,255,0.3), rgba(255,255,255,0.1));"></div>
    </div>
    
    <div class="card p-0" style="background: var(--success-gradient); color: var(--text-white); overflow: hidden;">
        <div class="p-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <p class="mb-1" style="opacity: 0.9; font-size: 0.9rem; font-weight: 500;">Successful Top-Ups</p>
                    <h3 class="mb-0" style="font-size: 2.2rem; font-weight: 700;"><?= number_format($successfulTransactions ?? 0) ?></h3>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 12px; border-radius: 12px;">
                    <i class="fas fa-money-bill-wave" style="font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>
        <div style="height: 4px; background: linear-gradient(90deg, rgba(255,255,255,0.3), rgba(255,255,255,0.1));"></div>
    </div>
    
    <div class="card p-0" style="background: var(--warning-gradient); color: var(--text-white); overflow: hidden;">
        <div class="p-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <p class="mb-1" style="opacity: 0.9; font-size: 0.9rem; font-weight: 500;">Pending Top-Ups</p>
                    <h3 class="mb-0" style="font-size: 2.2rem; font-weight: 700;"><?= number_format($pendingTransactions ?? 0) ?></h3>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 12px; border-radius: 12px;">
                    <i class="fas fa-clock" style="font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>
        <div style="height: 4px; background: linear-gradient(90deg, rgba(255,255,255,0.3), rgba(255,255,255,0.1));"></div>
    </div>
    
    <div class="card p-0" style="background: var(--danger-gradient); color: var(--text-white); overflow: hidden;">
        <div class="p-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <p class="mb-1" style="opacity: 0.9; font-size: 0.9rem; font-weight: 500;">Failed Top-Ups</p>
                    <h3 class="mb-0" style="font-size: 2.2rem; font-weight: 700;"><?= number_format($failedTransactions ?? 0) ?></h3>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 12px; border-radius: 12px;">
                    <i class="fas fa-times-circle" style="font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>
        <div style="height: 4px; background: linear-gradient(90deg, rgba(255,255,255,0.3), rgba(255,255,255,0.1));"></div>
    </div>
</div>

<!-- Total Amount Card -->
<div class="grid grid-1 mb-4">
    <div class="card p-0" style="background: var(--info-gradient); color: var(--text-white); overflow: hidden;">
        <div class="p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1" style="opacity: 0.9; font-size: 0.9rem; font-weight: 500;">Total Amount Credited</p>
                    <h3 class="mb-0" style="font-size: 2.5rem; font-weight: 700;">UGX <?= number_format($totalAmount ?? 0, 0) ?></h3>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 16px; border-radius: 16px;">
                    <i class="fas fa-wallet" style="font-size: 2rem;"></i>
                </div>
            </div>
        </div>
        <div style="height: 4px; background: linear-gradient(90deg, rgba(255,255,255,0.3), rgba(255,255,255,0.1));"></div>
    </div>
</div>

    <!-- Transactions Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                All Top-Up Transactions (<?= count($transactions) ?>)
            </h3>
        </div>
        
        <div class="table-container">
            <table class="table data-table" id="topupTransactionsTable">
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
    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#topupTransactionsTable')) {
        $('#topupTransactionsTable').DataTable().destroy();
    }
    
    // Initialize DataTable
    $('#topupTransactionsTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        language: {
            search: "Search transactions:",
            lengthMenu: "Show _MENU_ transactions per page",
            info: "Showing _START_ to _END_ of _TOTAL_ transactions",
            infoEmpty: "Showing 0 to 0 of 0 transactions",
            infoFiltered: "(filtered from _MAX_ total transactions)",
            emptyTable: "No transactions found",
            zeroRecords: "No matching transactions found"
        }
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
