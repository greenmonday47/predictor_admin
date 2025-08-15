<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="card-title mb-2">
            <i class="fas fa-trophy"></i>
            Manage Winners
        </h1>
        <p class="text-muted mb-0">View and manage all winners and their achievements</p>
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

<!-- Winner Statistics -->
<div class="grid grid-4 mb-4">
    <div class="card p-0" style="background: var(--success-gradient); color: var(--text-white); overflow: hidden;">
        <div class="p-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <p class="mb-1" style="opacity: 0.9; font-size: 0.9rem; font-weight: 500;">Total Winners</p>
                    <h3 class="mb-0" style="font-size: 2.2rem; font-weight: 700;"><?= number_format($totalWinners ?? 0) ?></h3>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 12px; border-radius: 12px;">
                    <i class="fas fa-trophy" style="font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>
        <div style="height: 4px; background: linear-gradient(90deg, rgba(255,255,255,0.3), rgba(255,255,255,0.1));"></div>
    </div>
    
    <div class="card p-0" style="background: var(--warning-gradient); color: var(--text-white); overflow: hidden;">
        <div class="p-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <p class="mb-1" style="opacity: 0.9; font-size: 0.9rem; font-weight: 500;">Perfect Predictions</p>
                    <h3 class="mb-0" style="font-size: 2.2rem; font-weight: 700;"><?= number_format($perfectWinners ?? 0) ?></h3>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 12px; border-radius: 12px;">
                    <i class="fas fa-star" style="font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>
        <div style="height: 4px; background: linear-gradient(90deg, rgba(255,255,255,0.3), rgba(255,255,255,0.1));"></div>
    </div>
    
    <div class="card p-0" style="background: var(--info-gradient); color: var(--text-white); overflow: hidden;">
        <div class="p-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <p class="mb-1" style="opacity: 0.9; font-size: 0.9rem; font-weight: 500;">Top Scorers</p>
                    <h3 class="mb-0" style="font-size: 2.2rem; font-weight: 700;"><?= number_format($topScorers ?? 0) ?></h3>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 12px; border-radius: 12px;">
                    <i class="fas fa-medal" style="font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>
        <div style="height: 4px; background: linear-gradient(90deg, rgba(255,255,255,0.3), rgba(255,255,255,0.1));"></div>
    </div>
    
    <div class="card p-0" style="background: var(--primary-gradient); color: var(--text-white); overflow: hidden;">
        <div class="p-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <p class="mb-1" style="opacity: 0.9; font-size: 0.9rem; font-weight: 500;">This Month</p>
                    <h3 class="mb-0" style="font-size: 2.2rem; font-weight: 700;"><?= number_format($thisMonthWinners ?? 0) ?></h3>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 12px; border-radius: 12px;">
                    <i class="fas fa-calendar" style="font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>
        <div style="height: 4px; background: linear-gradient(90deg, rgba(255,255,255,0.3), rgba(255,255,255,0.1));"></div>
    </div>
</div>

<!-- Winners Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-trophy"></i>
            Won Stacks (<?= count($wonStacks) ?>)
        </h3>
    </div>
    
    <div class="table-container">
        <table class="table data-table" id="winnersTable">
            <thead>
                <tr>
                    <th>Stack</th>
                    <th>Winner</th>
                    <th>Score</th>
                    <th>Prize</th>
                    <th>Won Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($wonStacks as $winner): ?>
                <tr>
                    <td>
                        <strong><?= esc($winner['stack_title'] ?? 'Unknown Stack') ?></strong>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar" style="width: 32px; height: 32px; border-radius: 50%; background: var(--success-gradient); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.8rem;">
                                <?= strtoupper(substr($winner['winner_name'] ?? 'U', 0, 1)) ?>
                            </div>
                            <div>
                                <strong><?= esc($winner['winner_name'] ?? 'Unknown') ?></strong>
                                <br><small class="text-muted"><?= esc($winner['winner_phone'] ?? 'N/A') ?></small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-success"><?= number_format($winner['score'] ?? 0) ?> points</span>
                    </td>
                    <td>
                        <span class="badge badge-primary"><?= number_format($winner['prize_amount'] ?? 0, 0) ?> UGX</span>
                    </td>
                    <td>
                        <small class="text-muted">
                            <?= date('M j, Y', strtotime($winner['won_at'] ?? $winner['created_at'])) ?>
                        </small>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                    onclick="viewWinner(<?= $winner['id'] ?>)">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Winner Details Modal -->
<div class="modal fade" id="winnerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Winner Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="winnerModalBody">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewWinner(winnerId) {
    // Show loading state
    const modalBody = document.getElementById('winnerModalBody');
    modalBody.innerHTML = '<div class="text-center p-4"><div class="loading"></div><p class="mt-2">Loading winner details...</p></div>';
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('winnerModal'));
    modal.show();
    
    // Fetch winner details
    fetch(`<?= base_url('admin/winners') ?>/${winnerId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const winner = data.winner;
                modalBody.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">Winner Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Winner Name:</strong></td>
                                    <td>${winner.winner_name || 'Unknown'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td>${winner.winner_phone || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Score:</strong></td>
                                    <td><span class="badge badge-success">${Number(winner.score || 0).toLocaleString()} points</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Prize Amount:</strong></td>
                                    <td><span class="badge badge-primary">${Number(winner.prize_amount || 0).toLocaleString()} UGX</span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">Stack Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Stack Title:</strong></td>
                                    <td>${winner.stack_title || 'Unknown'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Won Date:</strong></td>
                                    <td>${new Date(winner.won_at || winner.created_at).toLocaleDateString()}</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Participants:</strong></td>
                                    <td>${winner.total_participants || 0}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                `;
            } else {
                modalBody.innerHTML = '<div class="alert alert-danger">Failed to load winner details.</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalBody.innerHTML = '<div class="alert alert-danger">An error occurred while loading winner details.</div>';
        });
}
</script>

<?= $this->endSection() ?> 