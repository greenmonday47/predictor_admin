<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="card-title">
                <i class="fas fa-edit"></i>
                Update Scores: <?= esc($stack['title']) ?>
            </h1>
            <a href="<?= base_url('admin/stacks') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Stacks
            </a>
        </div>
    </div>

    <div class="card-body">
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

        <!-- Stack Information -->
        <div class="alert alert-info mb-4">
            <h6><i class="fas fa-info-circle"></i> Stack Information</h6>
            <div class="row">
                <div class="col-md-3">
                    <strong>Title:</strong> <?= esc($stack['title']) ?>
                </div>
                <div class="col-md-3">
                    <strong>Entry Fee:</strong> $<?= number_format($stack['entry_fee'], 2) ?>
                </div>
                <div class="col-md-3">
                    <strong>Deadline:</strong> <?= date('M j, Y g:i A', strtotime($stack['deadline'])) ?>
                </div>
                <div class="col-md-3">
                    <strong>Status:</strong> 
                    <?php if ($stack['is_active']): ?>
                        <span class="badge badge-success">Active</span>
                    <?php else: ?>
                        <span class="badge badge-warning">Locked</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Score Update Form -->
        <form id="scoreUpdateForm" action="<?= base_url('admin/stacks/' . $stack['id'] . '/process-score-update') ?>" method="post">
            
            <div class="table-container">
                <table class="table">
                    <thead>
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
                                       class="form-control score-input" 
                                       name="scores[<?= $match['match_id'] ?>][home_score]" 
                                       value="<?= $match['home_score'] ?? '' ?>"
                                       min="0" 
                                       max="99"
                                       placeholder="0"
                                       data-match-id="<?= $match['match_id'] ?>"
                                       data-score-type="home">
                            </td>
                            <td>
                                <input type="number" 
                                       class="form-control score-input" 
                                       name="scores[<?= $match['match_id'] ?>][away_score]" 
                                       value="<?= $match['away_score'] ?? '' ?>"
                                       min="0" 
                                       max="99"
                                       placeholder="0"
                                       data-match-id="<?= $match['match_id'] ?>"
                                       data-score-type="away">
                            </td>
                            <td>
                                <?php if ($match['score_updated']): ?>
                                    <span class="badge badge-success">Updated</span>
                                    <?php if (!empty($match['updated_at'])): ?>
                                        <br><small class="text-muted"><?= date('M j, g:i', strtotime($match['updated_at'])) ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Pending</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="alert alert-warning mt-4">
                <h6><i class="fas fa-exclamation-triangle"></i> Important Notes</h6>
                <ul class="mb-0">
                    <li>Only fill in scores for matches that have been played</li>
                    <li>Leave empty for matches that haven't been played yet</li>
                    <li>You can update scores multiple times as matches are played</li>
                    <li><strong>The stack will be automatically locked when any score is updated</strong></li>
                </ul>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="<?= base_url('admin/stacks') ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save"></i> Update Scores
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('scoreUpdateForm');
    const submitBtn = document.getElementById('submitBtn');
    const scoreInputs = document.querySelectorAll('.score-input');

    // Debug form submission
    form.addEventListener('submit', function(e) {
        console.log('=== FORM SUBMISSION DEBUG ===');
        console.log('Form action:', this.action);
        console.log('Form method:', this.method);
        
        // Show loading state
        submitBtn.innerHTML = '<span class="loading"></span> Updating...';
        submitBtn.disabled = true;
        
        // Log form data
        const formData = new FormData(this);
        console.log('Form data:');
        for (let [key, value] of formData.entries()) {
            console.log(key + ': ' + value);
        }
        
        console.log('Form will submit to:', this.action);
    });

    // Score input validation
    scoreInputs.forEach(input => {
        input.addEventListener('input', function() {
            const matchId = this.dataset.matchId;
            const scoreType = this.dataset.scoreType;
            const row = this.closest('tr');
            const homeInput = row.querySelector('input[data-score-type="home"]');
            const awayInput = row.querySelector('input[data-score-type="away"]');
            
            console.log(`Score input changed: Match ${matchId}, ${scoreType} score: ${this.value}`);
            
            // If one score is filled, require the other
            if (homeInput.value && !awayInput.value) {
                awayInput.setAttribute('required', 'required');
                console.log('Away score now required');
            } else if (awayInput.value && !homeInput.value) {
                homeInput.setAttribute('required', 'required');
                console.log('Home score now required');
            } else if (!homeInput.value && !awayInput.value) {
                homeInput.removeAttribute('required');
                awayInput.removeAttribute('required');
                console.log('No scores required');
            }
        });
    });
});
</script>

<?= $this->endSection() ?> 