<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="card-title mb-2">
            <i class="fas fa-layer-group"></i>
            Manage Stacks
        </h1>
        <p class="text-muted mb-0">Create, manage, and track all prediction stacks</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('admin/stacks/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            <span>Create New Stack</span>
        </a>
        <a href="<?= base_url('admin/stacks/calculate-all-scores') ?>" class="btn btn-info" 
           onclick="return confirm('Calculate scores for all stacks that have actual scores?')">
            <i class="fas fa-calculator"></i>
            <span>Calculate All Scores</span>
        </a>
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

<!-- Stats Cards -->
<div class="grid grid-4 mb-4">
    <?php 
    $totalStacks = count($stacks);
    $activeStacks = count(array_filter($stacks, fn($s) => ($s['status'] ?? 'active') === 'active'));
    $wonStacks = count(array_filter($stacks, fn($s) => ($s['status'] ?? 'active') === 'won'));
    $totalParticipants = array_sum(array_column($stacks, 'participant_count'));
    ?>
    
    <div class="card">
        <div style="padding: 24px; text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 800; background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 8px;">
                <?= $totalStacks ?>
            </div>
            <div style="color: var(--text-secondary); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.9rem;">
                Total Stacks
            </div>
        </div>
    </div>
    
    <div class="card">
        <div style="padding: 24px; text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 800; background: var(--success-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 8px;">
                <?= $activeStacks ?>
            </div>
            <div style="color: var(--text-secondary); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.9rem;">
                Active Stacks
            </div>
        </div>
    </div>
    
    <div class="card">
        <div style="padding: 24px; text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 800; background: var(--info-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 8px;">
                <?= $wonStacks ?>
            </div>
            <div style="color: var(--text-secondary); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.9rem;">
                Won Stacks
            </div>
        </div>
    </div>
    
    <div class="card">
        <div style="padding: 24px; text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 800; background: var(--warning-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 8px;">
                <?= $totalParticipants ?>
            </div>
            <div style="color: var(--text-secondary); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.9rem;">
                Total Participants
            </div>
        </div>
    </div>
</div>

<!-- Stacks Table -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title mb-0">
            <i class="fas fa-table"></i>
            All Stacks
            <span class="badge badge-info" style="margin-left: 12px;"><?= count($stacks) ?></span>
        </h2>
    </div>
    
    <div class="table-container">
        <table class="table data-table" id="stacksTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Stack Details</th>
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
                    <td>
                        <div style="font-weight: 700; color: var(--text-primary); font-size: 1.1rem;">
                            #<?= $stack['id'] ?>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-start gap-2">
                            <?php if (!empty($stack['prize_image'])): ?>
                                <div style="flex-shrink: 0;">
                                    <div style="width: 60px; height: 60px; border-radius: var(--border-radius-sm); overflow: hidden; box-shadow: var(--shadow-sm);">
                                        <img src="<?= base_url('admin/uploads/' . $stack['prize_image']) ?>" 
                                             alt="Prize" 
                                             style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div style="min-width: 0; flex: 1;">
                                <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 4px; font-size: 1.1rem;">
                                    <?= esc($stack['title']) ?>
                                </div>
                                <?php if (!empty($stack['prize_description'])): ?>
                                    <div style="color: var(--text-secondary); font-size: 0.9rem; line-height: 1.4;">
                                        <?= esc($stack['prize_description']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-success" style="font-size: 0.9rem; padding: 8px 12px;">
                            <?= number_format($stack['entry_fee'], 0) ?> UGX
                        </span>
                    </td>
                    <td>
                        <?php 
                        $deadline = new DateTime($stack['deadline']);
                        $now = new DateTime();
                        $isExpired = $deadline < $now;
                        $timeRemaining = $deadline->diff($now);
                        ?>
                        <div style="<?= $isExpired ? 'color: #dc3545;' : 'color: #28a745;' ?> font-weight: 600;">
                            <?= $deadline->format('M j, Y') ?>
                        </div>
                        <div style="font-size: 0.85rem; color: var(--text-muted);">
                            <?= $deadline->format('g:i A') ?>
                        </div>
                        <?php if ($isExpired): ?>
                            <span class="badge badge-danger" style="margin-top: 4px;">Expired</span>
                        <?php else: ?>
                            <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 2px;">
                                <?php
                                if ($timeRemaining->d > 0) {
                                    echo $timeRemaining->d . ' days left';
                                } elseif ($timeRemaining->h > 0) {
                                    echo $timeRemaining->h . ' hours left';
                                } else {
                                    echo $timeRemaining->i . ' minutes left';
                                }
                                ?>
                            </div>
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
                                    echo '<span class="badge badge-danger">Locked</span>';
                                    echo '<div style="font-size: 0.8rem; color: #dc3545; margin-top: 4px;">Scores Updated</div>';
                                } else {
                                    echo '<span class="badge badge-success">Active</span>';
                                }
                                if ($resetCount > 0) {
                                    echo '<div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">Reset ' . $resetCount . 'x</div>';
                                }
                                break;
                            case 'reset':
                                echo '<span class="badge badge-warning">Reset</span>';
                                echo '<div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">Reset ' . $resetCount . 'x</div>';
                                break;
                            case 'won':
                                echo '<span class="badge badge-info">Won</span>';
                                if (!empty($stack['won_at'])) {
                                    echo '<div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">' . date('M j, Y', strtotime($stack['won_at'])) . '</div>';
                                }
                                break;
                            default:
                                echo '<span class="badge badge-secondary">Inactive</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <div class="text-center">
                            <div style="font-size: 1.5rem; font-weight: 800; background: var(--info-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                                <?= $stack['participant_count'] ?? 0 ?>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">
                                Users
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex gap-1" style="flex-wrap: wrap;">
                            <!-- Primary Actions -->
                            <button type="button" class="btn btn-sm btn-secondary" 
                                    onclick="viewStack(<?= $stack['id'] ?>)"
                                    title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="<?= base_url('admin/stacks/' . $stack['id'] . '/edit') ?>" 
                               class="btn btn-sm btn-warning"
                               title="Edit Stack">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-info" 
                                    onclick="viewLeaderboard(<?= $stack['id'] ?>)"
                                    title="View Leaderboard">
                                <i class="fas fa-trophy"></i>
                            </button>
                            
                            <?php 
                            $hasActualScores = !empty($stack['actual_scores_json']);
                            $isActive = $stack['status'] === 'active';
                            ?>
                            
                            <!-- Score Management Actions -->
                            <?php if ($isActive): ?>
                                <button type="button" class="btn btn-sm btn-primary" 
                                        onclick="updateScores(<?= $stack['id'] ?>)"
                                        title="Update Match Scores">
                                    <i class="fas fa-edit"></i>
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($hasActualScores && $isActive): ?>
                                <button type="button" class="btn btn-sm btn-success" 
                                        onclick="calculateScores(<?= $stack['id'] ?>)"
                                        title="Calculate User Scores">
                                    <i class="fas fa-calculator"></i>
                                </button>
                            <?php endif; ?>
                            
                            <!-- Stack Management Actions -->
                            <?php if ($hasActualScores && $isActive): ?>
                                <button type="button" class="btn btn-sm btn-warning" 
                                        onclick="resetStack(<?= $stack['id'] ?>)"
                                        title="Reset Stack">
                                    <i class="fas fa-redo"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-primary" 
                                        onclick="markAsWon(<?= $stack['id'] ?>)"
                                        title="Mark as Won"
                                        style="background: linear-gradient(135deg, #ffd700, #ffed4a); color: #212529;">
                                    <i class="fas fa-crown"></i>
                                </button>
                            <?php endif; ?>
                            
                            <!-- Winner Actions -->
                            <?php if ($stack['status'] === 'won'): ?>
                                <button type="button" class="btn btn-sm btn-success" 
                                        onclick="awardWinners(<?= $stack['id'] ?>)"
                                        title="Award Winners">
                                    <i class="fas fa-trophy"></i>
                                </button>
                            <?php endif; ?>
                            
                            <!-- Danger Actions -->
                            <?php if ($isActive): ?>
                                <button type="button" class="btn btn-sm btn-danger" 
                                        onclick="deleteStack(<?= $stack['id'] ?>)"
                                        title="Delete Stack">
                                    <i class="fas fa-trash"></i>
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

<!-- Stack Details Modal -->
<div class="modal fade" id="stackModal" tabindex="-1" style="display: none;">
    <div class="modal-dialog modal-xl">
        <div class="modal-content card" style="margin: 0; border-radius: var(--border-radius);">
            <div class="modal-header" style="background: var(--surface); border-bottom: 1px solid var(--border-color); padding: 24px; border-radius: var(--border-radius) var(--border-radius) 0 0;">
                <h5 class="modal-title card-title" style="margin: 0;">
                    <i class="fas fa-layer-group"></i>
                    Stack Details
                </h5>
                <button type="button" class="btn btn-sm btn-secondary" onclick="closeModal('stackModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="stackModalBody" style="padding: 32px;">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Leaderboard Modal -->
<div class="modal fade" id="leaderboardModal" tabindex="-1" style="display: none;">
    <div class="modal-dialog modal-xl">
        <div class="modal-content card" style="margin: 0; border-radius: var(--border-radius);">
            <div class="modal-header" style="background: var(--surface); border-bottom: 1px solid var(--border-color); padding: 24px; border-radius: var(--border-radius) var(--border-radius) 0 0;">
                <h5 class="modal-title card-title" style="margin: 0;">
                    <i class="fas fa-trophy"></i>
                    Stack Leaderboard
                </h5>
                <button type="button" class="btn btn-sm btn-secondary" onclick="closeModal('leaderboardModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="leaderboardModalBody" style="padding: 32px;">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Modal Backdrop -->
<div class="modal-backdrop" id="modalBackdrop" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(10px); z-index: 1040;"></div>

<script>
// Enhanced modal handling
function showModal(modalId) {
    document.getElementById('modalBackdrop').style.display = 'block';
    const modal = document.getElementById(modalId);
    modal.style.display = 'block';
    modal.style.zIndex = '1050';
    
    // Add animation
    setTimeout(() => {
        modal.style.opacity = '1';
        modal.style.transform = 'scale(1)';
    }, 10);
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.opacity = '0';
    modal.style.transform = 'scale(0.95)';
    
    setTimeout(() => {
        modal.style.display = 'none';
        document.getElementById('modalBackdrop').style.display = 'none';
    }, 200);
}

// Close modal when clicking backdrop
document.getElementById('modalBackdrop').addEventListener('click', function() {
    closeModal('stackModal');
    closeModal('leaderboardModal');
});

function viewStack(stackId) {
    // Show loading state
    document.getElementById('stackModalBody').innerHTML = `
        <div class="text-center" style="padding: 40px;">
            <div class="loading" style="margin: 0 auto;"></div>
            <p style="margin-top: 16px; color: var(--text-secondary);">Loading stack details...</p>
        </div>
    `;
    showModal('stackModal');
    
    // Load stack details via AJAX
    fetch(`<?= base_url('api/stacks') ?>/${stackId}`)
        .then(response => response.json())
        .then(response => {
            if (response.status === 'success') {
                const stack = response.data;
                let matchesHtml = '';
                
                if (stack.matches && stack.matches.length > 0) {
                    matchesHtml = `
                        <div class="card" style="margin-top: 24px;">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-futbol"></i>
                                    Matches (${stack.matches.length})
                                </h3>
                            </div>
                            <div class="table-container">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Home Team</th>
                                            <th>Away Team</th>
                                            <th>Match Time</th>
                                            <th>Actual Score</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                    `;
                    
                    stack.matches.forEach(function(match) {
                        const actualScore = match.actual_score || '<span class="badge badge-secondary">Not Set</span>';
                        const matchTime = new Date(match.match_time).toLocaleString();
                        matchesHtml += `
                            <tr>
                                <td style="font-weight: 600;">${match.home_team}</td>
                                <td style="font-weight: 600;">${match.away_team}</td>
                                <td>${matchTime}</td>
                                <td>${actualScore}</td>
                            </tr>
                        `;
                    });
                    
                    matchesHtml += '</tbody></table></div></div>';
                }
                
                const statusBadge = getStatusBadge(stack.status || 'active');
                const prizeImage = stack.prize_image ? 
                    `<div style="text-align: center; margin-bottom: 24px;">
                        <img src="<?= base_url('admin/uploads/') ?>${stack.prize_image}" 
                             alt="Prize" 
                             style="max-height: 300px; border-radius: var(--border-radius); box-shadow: var(--shadow-md);">
                     </div>` : 
                    '<div style="text-align: center; padding: 40px; color: var(--text-muted); border: 2px dashed var(--border-color); border-radius: var(--border-radius);">No prize image uploaded</div>';
                
                document.getElementById('stackModalBody').innerHTML = `
                    <div class="grid grid-2" style="gap: 32px;">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-info-circle"></i>
                                    Basic Information
                                </h3>
                            </div>
                            <div style="padding: 24px;">
                                <div class="grid" style="gap: 16px;">
                                    <div class="d-flex justify-content-between">
                                        <span style="font-weight: 600; color: var(--text-secondary);">ID:</span>
                                        <span style="font-weight: 700;">#${stack.id}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span style="font-weight: 600; color: var(--text-secondary);">Title:</span>
                                        <span style="font-weight: 600;">${stack.title}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span style="font-weight: 600; color: var(--text-secondary);">Prize:</span>
                                        <span>${stack.prize_description || 'Not specified'}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span style="font-weight: 600; color: var(--text-secondary);">Entry Fee:</span>
                                        <span class="badge badge-success">${parseFloat(stack.entry_fee).toLocaleString()} UGX</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span style="font-weight: 600; color: var(--text-secondary);">Deadline:</span>
                                        <span>${new Date(stack.deadline).toLocaleString()}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span style="font-weight: 600; color: var(--text-secondary);">Status:</span>
                                        ${statusBadge}
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span style="font-weight: 600; color: var(--text-secondary);">Reset Count:</span>
                                        <span class="badge badge-info">${stack.reset_count || 0} times</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span style="font-weight: 600; color: var(--text-secondary);">Created:</span>
                                        <span>${new Date(stack.created_at).toLocaleDateString()}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-image"></i>
                                    Prize Image & Stats
                                </h3>
                            </div>
                            <div style="padding: 24px;">
                                ${prizeImage}
                                
                                <div class="grid grid-2" style="gap: 16px; margin-top: 24px;">
                                    <div class="card" style="background: var(--primary-gradient); color: white; text-align: center;">
                                        <div style="padding: 20px;">
                                            <div style="font-size: 2rem; font-weight: 800; margin-bottom: 8px;">
                                                ${stack.participant_count || 0}
                                            </div>
                                            <div style="font-size: 0.9rem; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px;">
                                                Participants
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card" style="background: var(--success-gradient); color: white; text-align: center;">
                                        <div style="padding: 20px;">
                                            <div style="font-size: 2rem; font-weight: 800; margin-bottom: 8px;">
                                                ${stack.matches ? stack.matches.length : 0}
                                            </div>
                                            <div style="font-size: 0.9rem; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px;">
                                                Matches
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    ${matchesHtml}
                `;
            }
        })
        .catch(error => {
            document.getElementById('stackModalBody').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    Failed to load stack details. Please try again.
                </div>
            `;
        });
}

function viewLeaderboard(stackId) {
    // Show loading state
    document.getElementById('leaderboardModalBody').innerHTML = `
        <div class="text-center" style="padding: 40px;">
            <div class="loading" style="margin: 0 auto;"></div>
            <p style="margin-top: 16px; color: var(--text-secondary);">Loading leaderboard...</p>
        </div>
    `;
    showModal('leaderboardModal');
    
    // Load leaderboard via AJAX
    fetch(`<?= base_url('api/scores/leaderboard') ?>/${stackId}`)
        .then(response => response.json())
        .then(response => {
            if (response.status === 'success' && response.data.length > 0) {
                let html = `
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>User</th>
                                    <th>Exact</th>
                                    <th>Outcome</th>
                                    <th>Wrong</th>
                                    <th>Total Points</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                response.data.forEach(function(score, index) {
                    const rank = index + 1;
                    let rankIcon = '';
                    let rowClass = '';
                    
                    if (rank === 1) {
                        rankIcon = '<i class="fas fa-crown" style="color: #ffd700; margin-right: 6px;"></i>';
                        rowClass = 'style="background: linear-gradient(135deg, rgba(255, 215, 0, 0.1), rgba(255, 237, 74, 0.1));"';
                    } else if (rank === 2) {
                        rankIcon = '<i class="fas fa-medal" style="color: #c0c0c0; margin-right: 6px;"></i>';
                        rowClass = 'style="background: rgba(192, 192, 192, 0.1);"';
                    } else if (rank === 3) {
                        rankIcon = '<i class="fas fa-medal" style="color: #cd7f32; margin-right: 6px;"></i>';
                        rowClass = 'style="background: rgba(205, 127, 50, 0.1);"';
                    }
                    
                    html += `
                        <tr ${rowClass}>
                            <td>
                                <div style="font-weight: 700; font-size: 1.1rem;">
                                    ${rankIcon}#${rank}
                                </div>
                            </td>
                            <td style="font-weight: 600;">${score.full_name}</td>
                            <td><span class="badge badge-success">${score.exact_count}</span></td>
                            <td><span class="badge badge-info">${score.outcome_count}</span></td>
                            <td><span class="badge badge-danger">${score.wrong_count}</span></td>
                            <td>
                                <div style="font-weight: 800; font-size: 1.2rem; color: var(--text-primary);">
                                    ${score.total_points}
                                </div>
                            </td>
                        </tr>
                    `;
                });
                
                html += '</tbody></table></div>';
                document.getElementById('leaderboardModalBody').innerHTML = html;
            } else {
                document.getElementById('leaderboardModalBody').innerHTML = `
                    <div style="text-align: center; padding: 60px; color: var(--text-muted);">
                        <i class="fas fa-trophy" style="font-size: 4rem; margin-bottom: 24px; opacity: 0.3;"></i>
                        <h3 style="margin-bottom: 12px;">No Scores Yet</h3>
                        <p>No scores found for this stack. Participants need to submit predictions first.</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('leaderboardModalBody').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    Failed to load leaderboard. Please try again.
                </div>
            `;
        });
}

function getStatusBadge(status) {
    const badges = {
        'active': '<span class="badge badge-success">Active</span>',
        'reset': '<span class="badge badge-warning">Reset</span>',
        'won': '<span class="badge badge-info">Won</span>',
        'inactive': '<span class="badge badge-secondary">Inactive</span>'
    };
    return badges[status] || badges['active'];
}

function deleteStack(stackId) {
    if (confirm('⚠️ Are you sure you want to delete this stack?\n\nThis action cannot be undone and will permanently remove:\n• All stack data\n• User predictions\n• Match information\n• Statistics\n\nType "DELETE" to confirm.')) {
        const userInput = prompt('Please type "DELETE" to confirm deletion:');
        if (userInput === 'DELETE') {
            showToast('Deleting stack...', 'info');
            window.location.href = `<?= base_url('admin/stacks') ?>/${stackId}/delete`;
        } else {
            showToast('Deletion cancelled', 'warning');
        }
    }
}

function resetStack(stackId) {
    if (confirm('🔄 Reset this stack with new matches?\n\nThis will:\n• Clear current predictions\n• Add new matches\n• Increment reset counter\n• Notify participants\n\nAre you sure?')) {
        showToast('Resetting stack...', 'info');
        window.location.href = `<?= base_url('admin/stacks') ?>/${stackId}/reset`;
    }
}

function markAsWon(stackId) {
    if (confirm('🏆 Mark this stack as won?\n\nThis will:\n• Close the stack to new predictions\n• Create winner records\n• Prepare for prize distribution\n\nAre you sure?')) {
        showToast('Marking stack as won...', 'success');
        window.location.href = `<?= base_url('admin/stacks') ?>/${stackId}/mark-won`;
    }
}

function updateScores(stackId) {
    showToast('Redirecting to score update...', 'info');
    window.location.href = `<?= base_url('admin/stacks') ?>/${stackId}/update-scores`;
}

function calculateScores(stackId) {
    if (confirm('🧮 Calculate user scores for this stack?\n\nThis will:\n• Process all predictions\n• Compare against actual results\n• Update user scores\n• Generate leaderboard\n\nContinue?')) {
        showToast('Calculating scores...', 'info');
        window.location.href = `<?= base_url('admin/stacks') ?>/${stackId}/calculate-scores`;
    }
}

function awardWinners(stackId) {
    if (confirm('🎉 Award winners for this stack?\n\nThis will:\n• Find users with perfect scores\n• Identify top performers\n• Create winner records\n• Prepare notifications\n\nContinue?')) {
        showToast('Processing winners...', 'info');
        
        // Use fetch for better error handling
        fetch(`<?= base_url('api/scores/award-winners') ?>/${stackId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(response => {
            if (response.status === 'success') {
                showToast(`🎉 Winners awarded successfully!\nPerfect Winners: ${response.data.perfect_winners}\nTop Score Winners: ${response.data.top_score_winners}\nTotal: ${response.data.total_winners}`, 'success', 5000);
                // Reload the page to show updated status
                setTimeout(() => location.reload(), 2000);
            } else {
                showToast('Failed to award winners: ' + response.message, 'danger');
            }
        })
        .catch(error => {
            showToast('Failed to award winners. Please try again.', 'danger');
        });
    }
}

// Enhanced initialization
document.addEventListener('DOMContentLoaded', function() {
    // Add custom styles for modal animations
    const style = document.createElement('style');
    style.textContent = `
        .modal {
            opacity: 0;
            transform: scale(0.95);
            transition: all 0.2s ease;
        }
        
        .btn-sm {
            min-width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .table tbody tr:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }
        
        .badge {
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        .loading {
            animation: spin 1s ease-in-out infinite, pulse 2s ease-in-out infinite;
        }
    `;
    document.head.appendChild(style);
    
    // Initialize tooltips for action buttons
    const buttons = document.querySelectorAll('[title]');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
});
</script>

<style>
/* Custom styles for enhanced appearance */
.modal-backdrop {
    transition: opacity 0.2s ease;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.85rem;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
}

.btn-sm {
    transition: all 0.2s ease;
}

.btn-sm:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

/* Status-specific styling */
.badge-success {
    background: var(--success-gradient);
    color: white;
}

.badge-warning {
    background: var(--warning-gradient);
    color: #212529;
}

.badge-danger {
    background: var(--danger-gradient);
    color: white;
}

.badge-info {
    background: var(--info-gradient);
    color: white;
}

.badge-secondary {
    background: rgba(108, 117, 125, 0.15);
    color: #6c757d;
    border: 1px solid rgba(108, 117, 125, 0.3);
}

/* Responsive enhancements */
@media (max-width: 768px) {
    .grid-4 {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .btn-sm span {
        display: none;
    }
    
    .d-flex.gap-1 {
        flex-wrap: wrap;
        gap: 4px !important;
    }
    
    .table td {
        padding: 12px 8px;
        font-size: 0.9rem;
    }
}

@media (max-width: 480px) {
    .grid-4 {
        grid-template-columns: 1fr;
    }
    
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 16px;
    }
    
    .d-flex.gap-2 {
        flex-wrap: wrap;
        gap: 8px !important;
    }
}

/* Loading animation enhancement */
@keyframes shimmer {
    0% { opacity: 0.6; }
    50% { opacity: 1; }
    100% { opacity: 0.6; }
}

.loading-text {
    animation: shimmer 1.5s ease-in-out infinite;
}
</style>
<?= $this->endSection() ?>