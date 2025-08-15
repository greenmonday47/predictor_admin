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
        <button type="button" class="btn btn-warning" onclick="testButton()">
            <i class="fas fa-bug"></i>
            Test JavaScript
        </button>
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
                                    onclick="viewUser(<?= $user['id'] ?>)" style="cursor: pointer;">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-info" 
                                    onclick="viewUserPredictions(<?= $user['id'] ?>)" style="cursor: pointer;">
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
// Test function to verify JavaScript is working
function testButton() {
    alert('Button click is working!');
}

function viewUser(userId) {
    console.log('viewUser called with userId:', userId);
    
    // Show loading state
    const modalBody = document.getElementById('userModalBody');
    modalBody.innerHTML = '<div class="text-center p-4"><div class="loading"></div><p class="mt-2">Loading user details...</p></div>';
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('userModal'));
    modal.show();
    
    // Test if fetch is working
    console.log('Testing fetch functionality...');
    
    // Fetch user details
    const url = `<?= base_url('admin/users') ?>/${userId}`;
    console.log('Fetching from URL:', url);
    console.log('Base URL:', '<?= base_url() ?>');
    console.log('Admin base URL:', '<?= base_url('admin') ?>');
    console.log('Current page URL:', window.location.href);
    console.log('Current page pathname:', window.location.pathname);
    
    // Try both absolute and relative URLs
    const relativeUrl = `admin/users/${userId}`;
    console.log('Relative URL:', relativeUrl);
    
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Received data:', data);
            if (data.success) {
                const user = data.user;
                const stats = data.stats;
                
                modalBody.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">Personal Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Full Name:</strong></td>
                                    <td>${user.full_name}</td>
                                </tr>
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td><span class="badge badge-info">${user.phone}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Joined:</strong></td>
                                    <td>${new Date(user.created_at).toLocaleDateString()}</td>
                                </tr>
                                <tr>
                                    <td><strong>Member Since:</strong></td>
                                    <td>${Math.floor((new Date() - new Date(user.created_at)) / (1000 * 60 * 60 * 24))} days</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">Performance Statistics</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="text-center p-3" style="background: var(--surface-secondary); border-radius: var(--border-radius-sm);">
                                        <div class="h4 mb-0 text-primary">${stats.total_predictions || 0}</div>
                                        <small class="text-muted">Total Predictions</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-3" style="background: var(--surface-secondary); border-radius: var(--border-radius-sm);">
                                        <div class="h4 mb-0 text-success">${stats.total_wins || 0}</div>
                                        <small class="text-muted">Total Wins</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-3" style="background: var(--surface-secondary); border-radius: var(--border-radius-sm);">
                                        <div class="h4 mb-0 text-warning">${stats.total_points || 0}</div>
                                        <small class="text-muted">Total Points</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-3" style="background: var(--surface-secondary); border-radius: var(--border-radius-sm);">
                                        <div class="h4 mb-0 text-info">${stats.perfect_scores || 0}</div>
                                        <small class="text-muted">Perfect Scores</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <h6 class="text-primary mb-2">Recent Activity</h6>
                                ${stats.recent_predictions && stats.recent_predictions.length > 0 ? 
                                    `<div class="list-group list-group-flush">
                                        ${stats.recent_predictions.map(pred => `
                                            <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                                                <small class="text-muted">${new Date(pred.created_at).toLocaleDateString()}</small>
                                                <span class="badge badge-primary">New Prediction</span>
                                            </div>
                                        `).join('')}
                                    </div>` : 
                                    '<p class="text-muted small">No recent activity</p>'
                                }
                            </div>
                        </div>
                    </div>
                `;
            } else {
                modalBody.innerHTML = `<div class="alert alert-danger">Failed to load user details: ${data.message || 'Unknown error'}</div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalBody.innerHTML = `<div class="alert alert-danger">An error occurred while loading user details: ${error.message}</div>`;
        });
}

function viewUserPredictions(userId) {
    console.log('viewUserPredictions called with userId:', userId);
    
    // Show loading state
    const modalBody = document.getElementById('predictionsModalBody');
    modalBody.innerHTML = '<div class="text-center p-4"><div class="loading"></div><p class="mt-2">Loading predictions...</p></div>';
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('predictionsModal'));
    modal.show();
    
    // Fetch user predictions
    const url = `<?= base_url('admin/users') ?>/${userId}/predictions`;
    console.log('Fetching predictions from URL:', url);
    
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Received predictions data:', data);
            if (data.success) {
                const user = data.user;
                const predictions = data.predictions;
                
                let predictionsHtml = '';
                
                if (Object.keys(predictions).length > 0) {
                    predictionsHtml = `
                        <div class="mb-3">
                            <h6 class="text-primary">${user.full_name}'s Predictions</h6>
                            <p class="text-muted small">Showing predictions grouped by stacks</p>
                        </div>
                    `;
                    
                    Object.keys(predictions).forEach(stackId => {
                        const stackData = predictions[stackId];
                        const stackInfo = stackData.stack_info;
                        const stackPredictions = stackData.predictions;
                        
                        predictionsHtml += `
                            <div class="card mb-3">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">${stackInfo.title}</h6>
                                            <small class="text-muted">
                                                Entry Fee: ${Number(stackInfo.entry_fee).toLocaleString()} UGX | 
                                                Status: <span class="badge badge-${stackInfo.status === 'active' ? 'success' : stackInfo.status === 'won' ? 'warning' : 'secondary'}">${stackInfo.status}</span>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted">Deadline: ${new Date(stackInfo.deadline).toLocaleDateString()}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    ${stackPredictions.map(prediction => {
                                        const matchPredictions = prediction.match_predictions || [];
                                        const actualScores = prediction.actual_scores || {};
                                        
                                        let matchDetailsHtml = '';
                                        if (matchPredictions.length > 0) {
                                            matchDetailsHtml = `
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>Match</th>
                                                                <th>Prediction</th>
                                                                <th>Actual Score</th>
                                                                <th>Result</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            ${matchPredictions.map(match => {
                                                                const actualScore = actualScores[match.match_id];
                                                                const isCorrect = actualScore && 
                                                                    match.home_score == actualScore.home_score && 
                                                                    match.away_score == actualScore.away_score;
                                                                const isOutcomeCorrect = actualScore && 
                                                                    ((match.home_score > match.away_score && actualScore.home_score > actualScore.away_score) ||
                                                                     (match.home_score < match.away_score && actualScore.home_score < actualScore.away_score) ||
                                                                     (match.home_score == match.away_score && actualScore.home_score == actualScore.away_score));
                                                                
                                                                let resultBadge = '';
                                                                if (actualScore) {
                                                                    if (isCorrect) {
                                                                        resultBadge = '<span class="badge badge-success">Exact</span>';
                                                                    } else if (isOutcomeCorrect) {
                                                                        resultBadge = '<span class="badge badge-warning">Outcome</span>';
                                                                    } else {
                                                                        resultBadge = '<span class="badge badge-danger">Wrong</span>';
                                                                    }
                                                                } else {
                                                                    resultBadge = '<span class="badge badge-secondary">Pending</span>';
                                                                }
                                                                
                                                                return `
                                                                    <tr>
                                                                        <td>${match.home_team} vs ${match.away_team}</td>
                                                                        <td>${match.home_score} - ${match.away_score}</td>
                                                                        <td>${actualScore ? `${actualScore.home_score} - ${actualScore.away_score}` : 'N/A'}</td>
                                                                        <td>${resultBadge}</td>
                                                                    </tr>
                                                                `;
                                                            }).join('')}
                                                        </tbody>
                                                    </table>
                                                </div>
                                            `;
                                        }
                                        
                                        return `
                                            <div class="border rounded p-3 mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <div>
                                                        <strong>Prediction #${prediction.id}</strong>
                                                        <br><small class="text-muted">Created: ${new Date(prediction.created_at).toLocaleString()}</small>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="h5 mb-0 text-primary">${prediction.total_points || 0} points</div>
                                                        <small class="text-muted">
                                                            ${prediction.exact_count || 0} exact, ${prediction.outcome_count || 0} outcome, ${prediction.wrong_count || 0} wrong
                                                        </small>
                                                    </div>
                                                </div>
                                                ${matchDetailsHtml}
                                            </div>
                                        `;
                                    }).join('')}
                                </div>
                            </div>
                        `;
                    });
                } else {
                    predictionsHtml = `
                        <div class="text-center p-4">
                            <i class="fas fa-chart-line" style="font-size: 3rem; opacity: 0.3; color: var(--text-muted);"></i>
                            <p class="mt-3 text-muted">No predictions found for ${user.full_name}.</p>
                        </div>
                    `;
                }
                
                modalBody.innerHTML = predictionsHtml;
            } else {
                modalBody.innerHTML = `<div class="alert alert-danger">Failed to load predictions: ${data.message || 'Unknown error'}</div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalBody.innerHTML = `<div class="alert alert-danger">An error occurred while loading predictions: ${error.message}</div>`;
        });
}
</script>

<?= $this->endSection() ?> 