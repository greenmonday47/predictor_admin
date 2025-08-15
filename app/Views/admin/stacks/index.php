<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Stacks</h1>
        <div>
            <a href="<?= base_url('admin/stacks/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Create New Stack
            </a>
            <a href="<?= base_url('admin/stacks/calculate-all-scores') ?>" class="btn btn-info btn-sm" 
               onclick="return confirm('Calculate scores for all stacks that have actual scores?')">
                <i class="fas fa-calculator"></i> Calculate All Scores
            </a>
            <a href="<?= base_url('admin') ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
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

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Stacks (<?= count($stacks) ?>)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="stacksTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Entry Fee</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Participants</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stacks as $stack): ?>
                        <tr>
                            <td><?= $stack['id'] ?></td>
                            <td>
                                <div class="d-flex align-items-start">
                                    <?php if (!empty($stack['prize_image'])): ?>
                                        <div class="me-3">
                                            <img src="<?= base_url('admin/uploads/' . $stack['prize_image']) ?>" 
                                                 alt="Prize" 
                                                 class="img-thumbnail" 
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?= esc($stack['title']) ?></strong>
                                        <?php if (!empty($stack['prize_description'])): ?>
                                            <br><small class="text-muted"><?= esc($stack['prize_description']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-success">$<?= number_format($stack['entry_fee'], 2) ?></span>
                            </td>
                            <td>
                                <?php 
                                $deadline = new DateTime($stack['deadline']);
                                $now = new DateTime();
                                $isExpired = $deadline < $now;
                                ?>
                                <span class="<?= $isExpired ? 'text-danger' : 'text-success' ?>">
                                    <?= $deadline->format('M j, Y g:i A') ?>
                                </span>
                                <?php if ($isExpired): ?>
                                    <br><small class="text-danger">Expired</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $status = $stack['status'] ?? 'active';
                                $resetCount = $stack['reset_count'] ?? 0;
                                $hasActualScores = !empty($stack['actual_scores_json']);
                                $isLocked = !$stack['is_active'] && $hasActualScores;
                                
                                switch ($status) {
                                    case 'active':
                                        if ($isLocked) {
                                            echo '<span class="badge bg-danger">Locked</span>';
                                            echo '<br><small class="text-danger">Scores Updated</small>';
                                        } else {
                                            echo '<span class="badge bg-success">Active</span>';
                                        }
                                        if ($resetCount > 0) {
                                            echo '<br><small class="text-muted">Reset ' . $resetCount . 'x</small>';
                                        }
                                        break;
                                    case 'reset':
                                        echo '<span class="badge bg-warning">Reset</span>';
                                        echo '<br><small class="text-muted">Reset ' . $resetCount . 'x</small>';
                                        break;
                                    case 'won':
                                        echo '<span class="badge bg-primary">Won</span>';
                                        if (!empty($stack['won_at'])) {
                                            echo '<br><small class="text-muted">' . date('M j, Y', strtotime($stack['won_at'])) . '</small>';
                                        }
                                        break;
                                    default:
                                        echo '<span class="badge bg-secondary">Inactive</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <span class="badge bg-info"><?= $stack['participant_count'] ?? 0 ?></span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="viewStack(<?= $stack['id'] ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <a href="<?= base_url('admin/stacks/' . $stack['id'] . '/edit') ?>" 
                                       class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                            onclick="viewLeaderboard(<?= $stack['id'] ?>)">
                                        <i class="fas fa-trophy"></i> Leaderboard
                                    </button>
                                    
                                    <?php 
                                    $hasActualScores = !empty($stack['actual_scores_json']);
                                    $isActive = $stack['status'] === 'active';
                                    ?>
                                    
                                    <?php if ($isActive): ?>
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                onclick="updateScores(<?= $stack['id'] ?>)">
                                            <i class="fas fa-edit"></i> Update Scores
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($hasActualScores && $isActive): ?>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                onclick="calculateScores(<?= $stack['id'] ?>)">
                                            <i class="fas fa-calculator"></i> Calculate Scores
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($hasActualScores && $isActive): ?>
                                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                                onclick="resetStack(<?= $stack['id'] ?>)">
                                            <i class="fas fa-redo"></i> Reset
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                onclick="markAsWon(<?= $stack['id'] ?>)">
                                            <i class="fas fa-crown"></i> Mark Won
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($stack['status'] === 'won'): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="awardWinners(<?= $stack['id'] ?>)">
                                            <i class="fas fa-trophy"></i> Award Winners
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($isActive): ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteStack(<?= $stack['id'] ?>)">
                                            <i class="fas fa-trash"></i> Delete
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

<!-- Stack Details Modal -->
<div class="modal fade" id="stackModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Stack Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="stackModalBody">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Leaderboard Modal -->
<div class="modal fade" id="leaderboardModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Stack Leaderboard</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="leaderboardModalBody">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#stacksTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true
    });
});

function viewStack(stackId) {
    // Load stack details via AJAX
    $.get(`<?= base_url('api/stacks') ?>/${stackId}`)
        .done(function(response) {
            if (response.status === 'success') {
                const stack = response.data;
                let matchesHtml = '';
                
                if (stack.matches && stack.matches.length > 0) {
                    matchesHtml = '<h6>Matches:</h6><div class="table-responsive"><table class="table table-sm">';
                    matchesHtml += '<thead><tr><th>Home Team</th><th>Away Team</th><th>Time</th><th>Actual Score</th></tr></thead><tbody>';
                    
                    stack.matches.forEach(function(match) {
                        const actualScore = match.actual_score || 'Not set';
                        matchesHtml += `<tr>
                            <td>${match.home_team}</td>
                            <td>${match.away_team}</td>
                            <td>${new Date(match.match_time).toLocaleString()}</td>
                            <td><strong>${actualScore}</strong></td>
                        </tr>`;
                    });
                    
                    matchesHtml += '</tbody></table></div>';
                }
                
                $('#stackModalBody').html(`
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Basic Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>ID:</strong></td><td>${stack.id}</td></tr>
                                <tr><td><strong>Title:</strong></td><td>${stack.title}</td></tr>
                                <tr><td><strong>Prize:</strong></td><td>${stack.prize_description || 'Not specified'}</td></tr>
                                <tr><td><strong>Entry Fee:</strong></td><td>$${parseFloat(stack.entry_fee).toFixed(2)}</td></tr>
                                <tr><td><strong>Deadline:</strong></td><td>${new Date(stack.deadline).toLocaleString()}</td></tr>
                                <tr><td><strong>Status:</strong></td><td><span class="badge ${stack.status === 'active' ? 'bg-success' : stack.status === 'reset' ? 'bg-warning' : stack.status === 'won' ? 'bg-primary' : 'bg-secondary'}">${stack.status || 'Active'}</span></td></tr>
                                <tr><td><strong>Reset Count:</strong></td><td>${stack.reset_count || 0} times</td></tr>
                                <tr><td><strong>Created:</strong></td><td>${new Date(stack.created_at).toLocaleDateString()}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Prize Image</h6>
                            ${stack.prize_image ? 
                                `<img src="<?= base_url('admin/uploads/') ?>${stack.prize_image}" alt="Prize" class="img-fluid rounded" style="max-height: 200px;">` : 
                                '<p class="text-muted">No prize image uploaded</p>'
                            }
                            
                            <h6 class="mt-3">Statistics</h6>
                            <div class="row">
                                <div class="col-6">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h4 id="participantCount">${stack.participant_count || 0}</h4>
                                            <small>Participants</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h4 id="predictionCount">-</h4>
                                            <small>Predictions</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    ${matchesHtml}
                `);
                
                $('#stackModal').modal('show');
            }
        })
        .fail(function() {
            alert('Failed to load stack details');
        });
}

function viewLeaderboard(stackId) {
    // Load leaderboard via AJAX
    $.get(`<?= base_url('api/scores/leaderboard') ?>/${stackId}`)
        .done(function(response) {
            if (response.status === 'success') {
                let html = '<div class="table-responsive"><table class="table table-sm">';
                html += '<thead><tr><th>Rank</th><th>User</th><th>Exact</th><th>Outcome</th><th>Wrong</th><th>Total Points</th></tr></thead><tbody>';
                
                response.data.forEach(function(score, index) {
                    const rank = index + 1;
                    const rankClass = rank === 1 ? 'bg-warning' : rank === 2 ? 'bg-secondary' : rank === 3 ? 'bg-bronze' : '';
                    html += `<tr class="${rankClass}">
                        <td><strong>#${rank}</strong></td>
                        <td>${score.full_name}</td>
                        <td><span class="badge bg-success">${score.exact_count}</span></td>
                        <td><span class="badge bg-info">${score.outcome_count}</span></td>
                        <td><span class="badge bg-danger">${score.wrong_count}</span></td>
                        <td><strong>${score.total_points}</strong></td>
                    </tr>`;
                });
                
                html += '</tbody></table></div>';
                $('#leaderboardModalBody').html(html);
                $('#leaderboardModal').modal('show');
            } else {
                $('#leaderboardModalBody').html('<p class="text-muted">No scores found for this stack.</p>');
                $('#leaderboardModal').modal('show');
            }
        })
        .fail(function() {
            $('#leaderboardModalBody').html('<p class="text-danger">Failed to load leaderboard.</p>');
            $('#leaderboardModal').modal('show');
        });
}

function deleteStack(stackId) {
    if (confirm('Are you sure you want to delete this stack? This action cannot be undone.')) {
        window.location.href = `<?= base_url('admin/stacks') ?>/${stackId}/delete`;
    }
}

function resetStack(stackId) {
    if (confirm('Are you sure you want to reset this stack with new matches? This will clear current predictions and add new matches.')) {
        window.location.href = `<?= base_url('admin/stacks') ?>/${stackId}/reset`;
    }
}

function markAsWon(stackId) {
    if (confirm('Are you sure you want to mark this stack as won? This will close the stack and create a winner record.')) {
        window.location.href = `<?= base_url('admin/stacks') ?>/${stackId}/mark-won`;
    }
}

function updateScores(stackId) {
    window.location.href = `<?= base_url('admin/stacks') ?>/${stackId}/update-scores`;
}

function calculateScores(stackId) {
    if (confirm('Calculate user scores for this stack? This will process all predictions against actual match results.')) {
        window.location.href = `<?= base_url('admin/stacks') ?>/${stackId}/calculate-scores`;
    }
}

function awardWinners(stackId) {
    if (confirm('Award winners for this stack? This will find users with perfect scores and top performers.')) {
        // Use AJAX to call the award winners API
        $.post(`<?= base_url('api/scores/award-winners') ?>/${stackId}`)
            .done(function(response) {
                if (response.status === 'success') {
                    alert(`Winners awarded successfully!\nPerfect Winners: ${response.data.perfect_winners}\nTop Score Winners: ${response.data.top_score_winners}\nTotal: ${response.data.total_winners}`);
                    // Reload the page to show updated status
                    location.reload();
                } else {
                    alert('Failed to award winners: ' + response.message);
                }
            })
            .fail(function() {
                alert('Failed to award winners. Please try again.');
            });
    }
}
</script>

<style>
.bg-bronze {
    background-color: #cd7f32 !important;
    color: white !important;
}
</style>
<?= $this->endSection() ?> 