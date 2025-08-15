<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="card-title mb-2">
            <i class="fas fa-users"></i>
            Manage Users
        </h1>
        <p class="text-muted mb-0">View and manage all registered users</p>
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

<!-- Users Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-users"></i>
            All Users (<?= count($users) ?>)
        </h3>
    </div>
    
    <div class="table-container">
        <table class="table data-table" id="usersTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Phone</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td>
                        <strong><?= esc($user['full_name']) ?></strong>
                    </td>
                    <td>
                        <span class="badge badge-info"><?= esc($user['phone']) ?></span>
                    </td>
                    <td>
                        <small class="text-muted">
                            <?= date('M j, Y', strtotime($user['created_at'])) ?>
                        </small>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                    onclick="viewUser(<?= $user['id'] ?>)">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-info" 
                                    onclick="viewUserPredictions(<?= $user['id'] ?>)">
                                <i class="fas fa-chart-line"></i> Predictions
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- User Details Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="userModalBody">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- User Predictions Modal -->
<div class="modal fade" id="predictionsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Predictions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="predictionsModalBody">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewUser(userId) {
    // Show loading state
    const modalBody = document.getElementById('userModalBody');
    modalBody.innerHTML = '<div class="text-center p-4"><div class="loading"></div><p class="mt-2">Loading user details...</p></div>';
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('userModal'));
    modal.show();
    
    // Fetch user details
    fetch(`<?= base_url('admin/users') ?>/${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                modalBody.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">Personal Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Full Name:</strong></td>
                                    <td>${data.user.full_name}</td>
                                </tr>
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td>${data.user.phone}</td>
                                </tr>
                                <tr>
                                    <td><strong>Joined:</strong></td>
                                    <td>${new Date(data.user.created_at).toLocaleDateString()}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">Statistics</h6>
                            <div class="row">
                                <div class="col-6">
                                    <div class="text-center p-3" style="background: var(--surface-secondary); border-radius: var(--border-radius-sm);">
                                        <div class="h4 mb-0 text-primary">${data.stats.total_predictions || 0}</div>
                                        <small class="text-muted">Total Predictions</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-3" style="background: var(--surface-secondary); border-radius: var(--border-radius-sm);">
                                        <div class="h4 mb-0 text-success">${data.stats.won_predictions || 0}</div>
                                        <small class="text-muted">Won Predictions</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                modalBody.innerHTML = '<div class="alert alert-danger">Failed to load user details.</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalBody.innerHTML = '<div class="alert alert-danger">An error occurred while loading user details.</div>';
        });
}

function viewUserPredictions(userId) {
    // Show loading state
    const modalBody = document.getElementById('predictionsModalBody');
    modalBody.innerHTML = '<div class="text-center p-4"><div class="loading"></div><p class="mt-2">Loading predictions...</p></div>';
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('predictionsModal'));
    modal.show();
    
    // Fetch user predictions
    fetch(`<?= base_url('admin/users') ?>/${userId}/predictions`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let predictionsHtml = '';
                if (data.predictions.length > 0) {
                    predictionsHtml = `
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Stack</th>
                                        <th>Prediction</th>
                                        <th>Actual Score</th>
                                        <th>Points</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.predictions.map(pred => `
                                        <tr>
                                            <td>${pred.stack_name}</td>
                                            <td>${pred.predicted_score}</td>
                                            <td>${pred.actual_score || 'N/A'}</td>
                                            <td>${pred.points || 0}</td>
                                            <td>
                                                <span class="badge ${pred.status === 'won' ? 'badge-success' : pred.status === 'lost' ? 'badge-danger' : 'badge-secondary'}">
                                                    ${pred.status}
                                                </span>
                                            </td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    `;
                } else {
                    predictionsHtml = '<div class="text-center p-4"><p class="text-muted">No predictions found for this user.</p></div>';
                }
                modalBody.innerHTML = predictionsHtml;
            } else {
                modalBody.innerHTML = '<div class="alert alert-danger">Failed to load predictions.</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalBody.innerHTML = '<div class="alert alert-danger">An error occurred while loading predictions.</div>';
        });
}
</script>

<?= $this->endSection() ?> 