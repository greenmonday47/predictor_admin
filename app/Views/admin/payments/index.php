<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="card-title mb-2">
            <i class="fas fa-credit-card"></i>
            Manage Payments
        </h1>
        <p class="text-muted mb-0">View and manage all payment transactions</p>
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

<!-- Payment Statistics -->
<div class="grid grid-4 mb-4">
    <div class="card p-0" style="background: var(--primary-gradient); color: var(--text-white); overflow: hidden;">
        <div class="p-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <p class="mb-1" style="opacity: 0.9; font-size: 0.9rem; font-weight: 500;">Total Payments</p>
                    <h3 class="mb-0" style="font-size: 2.2rem; font-weight: 700;"><?= number_format($totalPayments ?? 0) ?></h3>
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
                    <p class="mb-1" style="opacity: 0.9; font-size: 0.9rem; font-weight: 500;">Successful Payments</p>
                    <h3 class="mb-0" style="font-size: 2.2rem; font-weight: 700;"><?= number_format($successfulPayments ?? 0) ?></h3>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 12px; border-radius: 12px;">
                    <i class="fas fa-dollar-sign" style="font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>
        <div style="height: 4px; background: linear-gradient(90deg, rgba(255,255,255,0.3), rgba(255,255,255,0.1));"></div>
    </div>
    
    <div class="card p-0" style="background: var(--warning-gradient); color: var(--text-white); overflow: hidden;">
        <div class="p-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <p class="mb-1" style="opacity: 0.9; font-size: 0.9rem; font-weight: 500;">Pending Payments</p>
                    <h3 class="mb-0" style="font-size: 2.2rem; font-weight: 700;"><?= number_format($pendingPayments ?? 0) ?></h3>
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
                    <p class="mb-1" style="opacity: 0.9; font-size: 0.9rem; font-weight: 500;">Failed Payments</p>
                    <h3 class="mb-0" style="font-size: 2.2rem; font-weight: 700;"><?= number_format($failedPayments ?? 0) ?></h3>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 12px; border-radius: 12px;">
                    <i class="fas fa-times-circle" style="font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>
        <div style="height: 4px; background: linear-gradient(90deg, rgba(255,255,255,0.3), rgba(255,255,255,0.1));"></div>
    </div>
</div>

<!-- Payments Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-credit-card"></i>
            All Payments (<?= count($payments ?? []) ?>)
        </h3>
    </div>
    
    <div class="table-container">
        <table class="table data-table" id="paymentsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Stack</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Transaction ID</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($payments)): ?>
                    <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?= $payment['id'] ?></td>
                        <td>
                            <div>
                                <strong><?= esc($payment['full_name'] ?? 'Unknown') ?></strong>
                                <br><small class="text-muted"><?= esc($payment['phone'] ?? 'N/A') ?></small>
                            </div>
                        </td>
                        <td>
                            <strong><?= esc($payment['title'] ?? 'Unknown Stack') ?></strong>
                            <br><small class="text-muted">Entry: ₦<?= number_format($payment['entry_fee'] ?? 0, 2) ?></small>
                        </td>
                        <td>
                            <span class="badge badge-primary">₦<?= number_format($payment['amount'], 2) ?></span>
                        </td>
                        <td>
                            <?php
                            $statusClass = 'badge-secondary';
                            $statusText = 'Unknown';
                            
                            switch (strtoupper($payment['status'])) {
                                case 'SUCCESS':
                                    $statusClass = 'badge-success';
                                    $statusText = 'Success';
                                    break;
                                case 'PENDING':
                                    $statusClass = 'badge-warning';
                                    $statusText = 'Pending';
                                    break;
                                case 'FAILED':
                                    $statusClass = 'badge-danger';
                                    $statusText = 'Failed';
                                    break;
                                default:
                                    $statusClass = 'badge-secondary';
                                    $statusText = ucfirst(strtolower($payment['status']));
                            }
                            ?>
                            <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                        </td>
                        <td>
                            <code style="font-size: 0.8rem;"><?= esc($payment['transaction_id'] ?? 'N/A') ?></code>
                        </td>
                        <td>
                            <small class="text-muted">
                                <?= date('M j, Y', strtotime($payment['paid_at'] ?? $payment['created_at'] ?? 'now')) ?>
                            </small>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                        onclick="viewPayment(<?= $payment['id'] ?>)">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            <i class="fas fa-info-circle"></i>
                            No payments found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
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
function viewPayment(paymentId) {
    // Show loading state
    const modalBody = document.getElementById('paymentModalBody');
    modalBody.innerHTML = '<div class="text-center p-4"><div class="loading"></div><p class="mt-2">Loading payment details...</p></div>';
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
    modal.show();
    
    // For now, we'll show a simple message since the API endpoint might not exist
    modalBody.innerHTML = `
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            Payment details API endpoint not implemented yet. Payment ID: ${paymentId}
        </div>
        <div class="text-center">
            <p class="text-muted">This feature will be implemented to show detailed payment information.</p>
        </div>
    `;
    
    // Uncomment this when the API endpoint is implemented
    /*
    fetch(`<?= base_url('admin/payments') ?>/${paymentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const payment = data.payment;
                let statusClass = 'badge-secondary';
                let statusText = 'Unknown';
                
                switch (payment.status.toUpperCase()) {
                    case 'SUCCESS':
                        statusClass = 'badge-success';
                        statusText = 'Success';
                        break;
                    case 'PENDING':
                        statusClass = 'badge-warning';
                        statusText = 'Pending';
                        break;
                    case 'FAILED':
                        statusClass = 'badge-danger';
                        statusText = 'Failed';
                        break;
                }
                
                modalBody.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">Payment Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Payment ID:</strong></td>
                                    <td>${payment.id}</td>
                                </tr>
                                <tr>
                                    <td><strong>Amount:</strong></td>
                                    <td><span class="badge badge-primary">₦${Number(payment.amount).toLocaleString()}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td><span class="badge ${statusClass}">${statusText}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Transaction ID:</strong></td>
                                    <td><code>${payment.transaction_id || 'N/A'}</code></td>
                                </tr>
                                <tr>
                                    <td><strong>Date:</strong></td>
                                    <td>${new Date(payment.paid_at || payment.created_at).toLocaleString()}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">User Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>User Name:</strong></td>
                                    <td>${payment.full_name || 'Unknown'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td>${payment.phone || 'N/A'}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                `;
            } else {
                modalBody.innerHTML = '<div class="alert alert-danger">Failed to load payment details.</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalBody.innerHTML = '<div class="alert alert-danger">An error occurred while loading payment details.</div>';
        });
    */
}
</script>

<?= $this->endSection() ?> 