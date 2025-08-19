<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Stack</h1>
        <a href="<?= base_url('admin/stacks') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Stacks
        </a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Stack Information</h6>
        </div>
        <div class="card-body">
            <form action="<?= base_url('admin/stacks/' . $stack['id'] . '/edit') ?>" method="POST" id="editStackForm">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="title" class="form-label">Stack Title *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?= old('title', $stack['title']) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="entry_fee" class="form-label">Entry Fee ($) *</label>
                            <input type="number" class="form-control" id="entry_fee" name="entry_fee" 
                                   value="<?= old('entry_fee', $stack['entry_fee']) ?>" step="0.01" min="0" required>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="prize_description" class="form-label">Prize Description</label>
                    <textarea class="form-control" id="prize_description" name="prize_description" 
                              rows="3" placeholder="Describe the prize for this stack..."><?= old('prize_description', $stack['prize_description']) ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="deadline" class="form-label">Prediction Deadline *</label>
                            <input type="datetime-local" class="form-control" id="deadline" name="deadline" 
                                   value="<?= old('deadline', date('Y-m-d\TH:i', strtotime($stack['deadline']))) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="is_active" class="form-label">Status</label>
                            <select class="form-control" id="is_active" name="is_active">
                                <option value="1" <?= old('is_active', $stack['is_active']) == '1' ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= old('is_active', $stack['is_active']) == '0' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <h6 class="font-weight-bold">Matches</h6>
                    <p class="text-muted">Edit the matches for this stack. Users will predict scores for these matches.</p>
                    
                    <div id="matchesContainer">
                        <?php 
                        $matches = json_decode($stack['matches_json'], true) ?: [];
                        foreach ($matches as $index => $match): 
                            $matchId = $index + 1;
                        ?>
                        <div class="match-row row mb-3" data-match-id="<?= $matchId ?>">
                            <div class="col-md-4">
                                <label class="form-label">Home Team *</label>
                                <input type="text" class="form-control" name="matches[<?= $matchId ?>][home_team]" 
                                       value="<?= esc($match['home_team']) ?>" placeholder="e.g., Manchester United" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Away Team *</label>
                                <input type="text" class="form-control" name="matches[<?= $matchId ?>][away_team]" 
                                       value="<?= esc($match['away_team']) ?>" placeholder="e.g., Liverpool" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Match Time *</label>
                                <input type="datetime-local" class="form-control" name="matches[<?= $matchId ?>][match_time]" 
                                       value="<?= date('Y-m-d\TH:i', strtotime($match['match_time'])) ?>" required>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeMatch(<?= $matchId ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="button" class="btn btn-success btn-sm" onclick="addMatch()">
                        <i class="fas fa-plus"></i> Add Another Match
                    </button>
                </div>



                <div class="d-flex justify-content-between">
                    <a href="<?= base_url('admin/stacks') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Stack
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let matchCounter = <?= count($matches) ?>;

function addMatch() {
    matchCounter++;
    const matchHtml = `
        <div class="match-row row mb-3" data-match-id="${matchCounter}">
            <div class="col-md-4">
                <label class="form-label">Home Team *</label>
                <input type="text" class="form-control" name="matches[${matchCounter}][home_team]" 
                       placeholder="e.g., Manchester United" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Away Team *</label>
                <input type="text" class="form-control" name="matches[${matchCounter}][away_team]" 
                       placeholder="e.g., Liverpool" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Match Time *</label>
                <input type="datetime-local" class="form-control" name="matches[${matchCounter}][match_time]" required>
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeMatch(${matchCounter})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    $('#matchesContainer').append(matchHtml);
}

function removeMatch(matchId) {
    const matchRows = $('.match-row');
    if (matchRows.length > 1) {
        $(`.match-row[data-match-id="${matchId}"]`).remove();
    } else {
        alert('You must have at least one match in the stack.');
    }
}

$(document).ready(function() {
    // Form validation
    $('#editStackForm').on('submit', function(e) {
        const matchRows = $('.match-row');
        if (matchRows.length === 0) {
            e.preventDefault();
            alert('Please add at least one match to the stack.');
            return false;
        }
        
        // Validate that all match fields are filled
        let isValid = true;
        matchRows.each(function() {
            const homeTeam = $(this).find('input[name*="[home_team]"]').val();
            const awayTeam = $(this).find('input[name*="[away_team]"]').val();
            const matchTime = $(this).find('input[name*="[match_time]"]').val();
            
            if (!homeTeam || !awayTeam || !matchTime) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all match details.');
            return false;
        }
    });
});
</script>

<style>
.match-row {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border: 1px solid #dee2e6;
}

.match-row:hover {
    background-color: #e9ecef;
}
</style>
<?= $this->endSection() ?> 