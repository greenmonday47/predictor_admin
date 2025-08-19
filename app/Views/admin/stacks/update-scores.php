<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Update Scores: <?= esc($stack['title']) ?></h1>
        <div>
            <a href="<?= base_url('admin/stacks') ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Stacks
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

    <div class="row">
        <!-- Stack Info -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Stack Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Title:</strong></td>
                            <td><?= esc($stack['title']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Prize:</strong></td>
                            <td><?= esc($stack['prize_description'] ?? 'Not specified') ?></td>
                        </tr>
                        <tr>
                            <td><strong>Entry Fee:</strong></td>
                            <td>$<?= number_format($stack['entry_fee'], 2) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Deadline:</strong></td>
                            <td><?= date('M j, Y g:i A', strtotime($stack['deadline'])) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <?php 
                                $status = $stack['status'] ?? 'active';
                                switch ($status) {
                                    case 'active':
                                        echo '<span class="badge bg-success">Active</span>';
                                        break;
                                    case 'reset':
                                        echo '<span class="badge bg-warning">Reset</span>';
                                        break;
                                    case 'won':
                                        echo '<span class="badge bg-primary">Won</span>';
                                        break;
                                    default:
                                        echo '<span class="badge bg-secondary">Inactive</span>';
                                }
                                ?>
                            </td>
                        </tr>
                    </table>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> You can update scores for individual matches as they are played. 
                        Matches can be played on different dates and times.
                    </div>
                </div>
            </div>
        </div>

        <!-- Score Update Form -->
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Update Match Scores</h6>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('admin/stacks/' . $stack['id'] . '/process-score-update') ?>" method="post">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Match</th>
                                        <th>Home Team</th>
                                        <th>Away Team</th>
                                        <th>Match Time</th>
                                        <th>Home Score</th>
                                        <th>Away Score</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($matches as $index => $match): ?>
                                    <tr class="<?= $match['score_updated'] ? 'table-success' : '' ?>">
                                        <td>
                                            <strong>Match <?= $index + 1 ?></strong>
                                            <br><small class="text-muted"><?= $match['match_id'] ?></small>
                                        </td>
                                        <td><?= esc($match['home_team']) ?></td>
                                        <td><?= esc($match['away_team']) ?></td>
                                        <td>
                                            <?= date('M j, g:i A', strtotime($match['match_time'])) ?>
                                            <br>
                                            <small class="text-muted">
                                                <?php 
                                                $matchTime = new DateTime($match['match_time']);
                                                $now = new DateTime();
                                                if ($matchTime < $now) {
                                                    echo '<span class="text-danger">Played</span>';
                                                } else {
                                                    echo '<span class="text-warning">Upcoming</span>';
                                                }
                                                ?>
                                            </small>
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   class="form-control form-control-sm" 
                                                   name="scores[<?= $match['match_id'] ?>][home_score]" 
                                                   value="<?= $match['home_score'] ?? '' ?>"
                                                   min="0" 
                                                   max="99"
                                                   placeholder="0">
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   class="form-control form-control-sm" 
                                                   name="scores[<?= $match['match_id'] ?>][away_score]" 
                                                   value="<?= $match['away_score'] ?? '' ?>"
                                                   min="0" 
                                                   max="99"
                                                   placeholder="0">
                                        </td>
                                        <td>
                                            <?php if ($match['score_updated']): ?>
                                                <span class="badge bg-success">Updated</span>
                                                <?php if (!empty($match['updated_at'])): ?>
                                                    <br><small class="text-muted"><?= date('M j, g:i', strtotime($match['updated_at'])) ?></small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Important:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Only fill in scores for matches that have been played</li>
                                <li>Leave empty for matches that haven't been played yet</li>
                                <li>You can update scores multiple times as matches are played</li>
                                <li>All scores will be saved when you submit the form</li>
                            </ul>
                        </div>

                        <div class="alert alert-danger">
                            <i class="fas fa-lock"></i>
                            <strong>Security Notice:</strong>
                            <ul class="mb-0 mt-2">
                                <li><strong>The stack will be automatically locked when any score is updated</strong></li>
                                <li>This prevents users from making predictions after they know the actual results</li>
                                <li>Once locked, no new predictions can be submitted for this stack</li>
                                <li>This is a security measure to ensure fair play</li>
                                <li><strong>Note:</strong> You can still update additional scores even after the stack is locked</li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= base_url('admin/stacks') ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Scores
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Add validation to ensure both home and away scores are filled together
document.addEventListener('DOMContentLoaded', function() {
    const scoreInputs = document.querySelectorAll('input[name*="[home_score]"], input[name*="[away_score]"]');
    
    scoreInputs.forEach(input => {
        input.addEventListener('input', function() {
            const row = this.closest('tr');
            const homeInput = row.querySelector('input[name*="[home_score]"]');
            const awayInput = row.querySelector('input[name*="[away_score]"]');
            
            // If one score is filled, require the other
            if (homeInput.value && !awayInput.value) {
                awayInput.setAttribute('required', 'required');
            } else if (awayInput.value && !homeInput.value) {
                homeInput.setAttribute('required', 'required');
            } else if (!homeInput.value && !awayInput.value) {
                homeInput.removeAttribute('required');
                awayInput.removeAttribute('required');
            }
        });
    });
});
</script>

<?= $this->endSection() ?> 