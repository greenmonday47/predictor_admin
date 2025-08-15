<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Reset Stack: <?= esc($stack['title']) ?></h1>
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
        <!-- Current Stack Info -->
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Current Stack Info</h6>
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
                        <?php if (!empty($stack['prize_image'])): ?>
                        <tr>
                            <td><strong>Prize Image:</strong></td>
                            <td>
                                <img src="<?= base_url('admin/uploads/' . $stack['prize_image']) ?>" 
                                     alt="Prize" 
                                     class="img-thumbnail" 
                                     style="max-height: 100px;">
                            </td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td><strong>Entry Fee:</strong></td>
                            <td>$<?= number_format($stack['entry_fee'], 2) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Reset Count:</strong></td>
                            <td><?= $stack['reset_count'] ?? 0 ?> times</td>
                        </tr>
                        <tr>
                            <td><strong>Current Deadline:</strong></td>
                            <td><?= date('M j, Y g:i A', strtotime($stack['deadline'])) ?></td>
                        </tr>
                    </table>

                    <h6 class="mt-3">Current Matches:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Home</th>
                                    <th>Away</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($currentMatches as $match): ?>
                                <tr>
                                    <td><?= esc($match['home_team']) ?></td>
                                    <td><?= esc($match['away_team']) ?></td>
                                    <td><?= date('M j, g:i A', strtotime($match['match_time'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reset Form -->
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Reset with New Matches</h6>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('admin/stacks/' . $stack['id'] . '/process-reset') ?>" method="post">
                        <div class="mb-3">
                            <label for="deadline" class="form-label">New Deadline</label>
                            <input type="datetime-local" class="form-control" id="deadline" name="deadline" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">New Matches</label>
                            <div id="matchesContainer">
                                <div class="match-row border rounded p-3 mb-3">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label">Home Team</label>
                                            <input type="text" class="form-control" name="matches[0][home_team]" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Away Team</label>
                                            <input type="text" class="form-control" name="matches[0][away_team]" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Match Time</label>
                                            <input type="datetime-local" class="form-control" name="matches[0][match_time]" required>
                                        </div>
                                        <div class="col-md-1">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="button" class="btn btn-danger btn-sm d-block" onclick="removeMatch(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-info btn-sm" onclick="addMatch()">
                                <i class="fas fa-plus"></i> Add Another Match
                            </button>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Warning:</strong> Resetting this stack will:
                            <ul class="mb-0 mt-2">
                                <li>Clear all current predictions</li>
                                <li>Clear all payment records (users must pay again)</li>
                                <li>Clear all score records</li>
                                <li>Add new matches with a new deadline</li>
                                <li>Increment the reset counter</li>
                                <li>Allow users to make new predictions</li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= base_url('admin/stacks') ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-redo"></i> Reset Stack
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let matchCount = 1;

function addMatch() {
    const container = document.getElementById('matchesContainer');
    const newMatch = document.createElement('div');
    newMatch.className = 'match-row border rounded p-3 mb-3';
    newMatch.innerHTML = `
        <div class="row">
            <div class="col-md-4">
                <label class="form-label">Home Team</label>
                <input type="text" class="form-control" name="matches[${matchCount}][home_team]" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Away Team</label>
                <input type="text" class="form-control" name="matches[${matchCount}][away_team]" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Match Time</label>
                <input type="datetime-local" class="form-control" name="matches[${matchCount}][match_time]" required>
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-danger btn-sm d-block" onclick="removeMatch(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    container.appendChild(newMatch);
    matchCount++;
}

function removeMatch(button) {
    const matchRow = button.closest('.match-row');
    if (document.querySelectorAll('.match-row').length > 1) {
        matchRow.remove();
    } else {
        alert('At least one match is required');
    }
}

// Set default deadline to tomorrow
document.addEventListener('DOMContentLoaded', function() {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    tomorrow.setHours(23, 59, 0, 0);
    
    const deadlineInput = document.getElementById('deadline');
    deadlineInput.value = tomorrow.toISOString().slice(0, 16);
});
</script>

<?= $this->endSection() ?> 