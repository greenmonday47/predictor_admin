<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Winners</h1>
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

    <!-- Winner Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Winners</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalWinners ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-trophy fa-2x text-gray-300"></i>
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
                                Perfect Predictions</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $perfectWinners ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Top Scorers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $topScorers ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-medal fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                This Month</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $thisMonthWinners ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Won Stacks (<?= count($wonStacks) ?>)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="winnersTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Winner</th>
                            <th>Stack</th>
                            <th>Win Type</th>
                            <th>Score Details</th>
                            <th>Awarded</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($winners as $winner): ?>
                        <tr>
                            <td><?= $winner['id'] ?></td>
                            <td>
                                <strong><?= esc($winner['full_name']) ?></strong>
                                <br><small class="text-muted"><?= esc($winner['phone']) ?></small>
                            </td>
                            <td>
                                <strong><?= esc($winner['title']) ?></strong>
                                <br><small class="text-muted">Entry Fee: $<?= number_format($winner['entry_fee'], 2) ?></small>
                            </td>
                            <td>
                                <?php if ($winner['win_type'] === 'full-correct'): ?>
                                    <span class="badge bg-warning">
                                        <i class="fas fa-star"></i> Perfect Prediction
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-info">
                                        <i class="fas fa-medal"></i> Top Score
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-4">
                                        <small class="text-success">Exact: <?= $winner['exact_count'] ?></small>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-info">Outcome: <?= $winner['outcome_count'] ?></small>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-danger">Wrong: <?= $winner['wrong_count'] ?></small>
                                    </div>
                                </div>
                                <strong>Total: <?= $winner['total_points'] ?> pts</strong>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= date('M j, Y g:i A', strtotime($winner['awarded_at'])) ?>
                                </small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="viewWinner(<?= $winner['id'] ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                            onclick="viewWinnerPredictions(<?= $winner['user_id'] ?>, <?= $winner['stack_id'] ?>)">
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
    </div>

    <!-- Recent Winners Section -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Winners</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach (array_slice($winners, 0, 6) as $winner): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card border-left-<?= $winner['win_type'] === 'full-correct' ? 'warning' : 'info' ?> shadow h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-<?= $winner['win_type'] === 'full-correct' ? 'warning' : 'info' ?> text-uppercase mb-1">
                                                <?= $winner['win_type'] === 'full-correct' ? 'Perfect Winner' : 'Top Scorer' ?>
                                            </div>
                                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                                <?= esc($winner['full_name']) ?>
                                            </div>
                                            <div class="text-xs text-muted">
                                                <?= esc($winner['title']) ?>
                                            </div>
                                            <div class="text-xs text-muted">
                                                <?= $winner['total_points'] ?> points • <?= date('M j, Y', strtotime($winner['awarded_at'])) ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <?php if ($winner['win_type'] === 'full-correct'): ?>
                                                <i class="fas fa-star fa-2x text-warning"></i>
                                            <?php else: ?>
                                                <i class="fas fa-medal fa-2x text-info"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
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

<!-- Winner Predictions Modal -->
<div class="modal fade" id="predictionsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Winner Predictions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="predictionsModalBody">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#winnersTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true
    });
});

function viewWinner(winnerId) {
    // Load winner details via AJAX
    $.get(`<?= base_url('api/scores/winners') ?>/${winnerId}`)
        .done(function(response) {
            if (response.status === 'success') {
                const winner = response.data;
                $('#winnerModalBody').html(`
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Winner Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>ID:</strong></td><td>${winner.id}</td></tr>
                                <tr><td><strong>Name:</strong></td><td>${winner.full_name}</td></tr>
                                <tr><td><strong>Phone:</strong></td><td>${winner.phone}</td></tr>
                                <tr><td><strong>Win Type:</strong></td><td><span class="badge bg-${winner.win_type === 'full-correct' ? 'warning' : 'info'}">${winner.win_type === 'full-correct' ? 'Perfect Prediction' : 'Top Score'}</span></td></tr>
                                <tr><td><strong>Awarded:</strong></td><td>${new Date(winner.awarded_at).toLocaleString()}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Stack Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Title:</strong></td><td>${winner.title}</td></tr>
                                <tr><td><strong>Entry Fee:</strong></td><td>$${parseFloat(winner.entry_fee).toFixed(2)}</td></tr>
                                <tr><td><strong>Prize:</strong></td><td>${winner.prize_description || 'Not specified'}</td></tr>
                            </table>
                            
                            <h6>Score Breakdown</h6>
                            <div class="row">
                                <div class="col-4">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h4>${winner.exact_count}</h4>
                                            <small>Exact</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="card bg-info text-white">
                                        <div class="card-body text-center">
                                            <h4>${winner.outcome_count}</h4>
                                            <small>Outcome</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="card bg-danger text-white">
                                        <div class="card-body text-center">
                                            <h4>${winner.wrong_count}</h4>
                                            <small>Wrong</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <h4><strong>Total Points: ${winner.total_points}</strong></h4>
                            </div>
                        </div>
                    </div>
                `);
                
                $('#winnerModal').modal('show');
            }
        })
        .fail(function() {
            alert('Failed to load winner details');
        });
}

function viewWinnerPredictions(userId, stackId) {
    // Load winner predictions via AJAX
    $.get(`<?= base_url('api/predictions/user') ?>/${userId}/stack/${stackId}`)
        .done(function(response) {
            if (response.status === 'success') {
                const prediction = response.data;
                let html = '<div class="table-responsive"><table class="table table-sm">';
                html += '<thead><tr><th>Match</th><th>Prediction</th><th>Actual Score</th><th>Points</th></tr></thead><tbody>';
                
                if (prediction.predictions && prediction.predictions.length > 0) {
                    prediction.predictions.forEach(function(pred) {
                        const points = pred.points || 0;
                        const pointsClass = points > 0 ? 'text-success' : 'text-danger';
                        html += `<tr>
                            <td><strong>${pred.home_team} vs ${pred.away_team}</strong></td>
                            <td>${pred.predicted_score}</td>
                            <td>${pred.actual_score || 'Not set'}</td>
                            <td class="${pointsClass}"><strong>${points} pts</strong></td>
                        </tr>`;
                    });
                } else {
                    html += '<tr><td colspan="4" class="text-center">No predictions found</td></tr>';
                }
                
                html += '</tbody></table></div>';
                $('#predictionsModalBody').html(html);
                $('#predictionsModal').modal('show');
            } else {
                $('#predictionsModalBody').html('<p class="text-muted">No predictions found for this winner.</p>');
                $('#predictionsModal').modal('show');
            }
        })
        .fail(function() {
            $('#predictionsModalBody').html('<p class="text-danger">Failed to load predictions.</p>');
            $('#predictionsModal').modal('show');
        });
}
</script>
<?= $this->endSection() ?> 