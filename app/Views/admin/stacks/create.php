<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create New Stack</h1>
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
            <h6 class="m-0 font-weight-bold text-primary">Stack Information</h6>
        </div>
        <div class="card-body">
            <form action="<?= base_url('admin/stacks/create') ?>" method="POST" id="createStackForm" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="title" class="form-label">Stack Title *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?= old('title') ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="entry_fee" class="form-label">Entry Fee ($) *</label>
                            <input type="number" class="form-control" id="entry_fee" name="entry_fee" 
                                   value="<?= old('entry_fee', '5.00') ?>" step="0.01" min="0" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="prize_description" class="form-label">Prize Description</label>
                            <textarea class="form-control" id="prize_description" name="prize_description" 
                                      rows="3" placeholder="Describe the prize for this stack..."><?= old('prize_description') ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="prize_image" class="form-label">Prize Image</label>
                            <input type="file" class="form-control" id="prize_image" name="prize_image" 
                                   accept="image/*" onchange="previewImage(this)">
                            <small class="form-text text-muted">Upload an image of the prize (JPG, PNG, GIF - Max 2MB)</small>
                        </div>
                        <div id="imagePreview" class="mt-2" style="display: none;">
                            <img id="previewImg" src="" alt="Prize Preview" class="img-fluid rounded" style="max-height: 150px;">
                            <button type="button" class="btn btn-sm btn-danger mt-1" onclick="removeImage()">
                                <i class="fas fa-times"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="deadline" class="form-label">Prediction Deadline *</label>
                            <input type="datetime-local" class="form-control" id="deadline" name="deadline" 
                                   value="<?= old('deadline') ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="is_active" class="form-label">Status</label>
                            <select class="form-control" id="is_active" name="is_active">
                                <option value="1" <?= old('is_active', '1') == '1' ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= old('is_active') == '0' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <h6 class="font-weight-bold">Matches</h6>
                    <p class="text-muted">Add the matches for this stack. Users will predict scores for these matches.</p>
                    
                    <div id="matchesContainer">
                        <div class="match-row row mb-3" data-match-id="1">
                            <div class="col-md-4">
                                <label class="form-label">Home Team *</label>
                                <input type="text" class="form-control" name="matches[1][home_team]" 
                                       placeholder="e.g., Manchester United" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Away Team *</label>
                                <input type="text" class="form-control" name="matches[1][away_team]" 
                                       placeholder="e.g., Liverpool" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Match Time *</label>
                                <input type="datetime-local" class="form-control" name="matches[1][match_time]" required>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeMatch(1)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-success btn-sm" onclick="addMatch()" id="addMatchBtn">
                        <i class="fas fa-plus"></i> Add Another Match
                    </button>
                </div>

                <hr>

                <div class="d-flex justify-content-between">
                    <a href="<?= base_url('admin/stacks') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Stack
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let matchCounter = 1;

// Make function globally accessible
window.addMatch = function() {
    console.log('addMatch function called');
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
    
    const container = document.getElementById('matchesContainer');
    if (container) {
        container.insertAdjacentHTML('beforeend', matchHtml);
        console.log('Match added successfully');
    } else {
        console.error('matchesContainer not found');
    }
}

// Make removeMatch function globally accessible
window.removeMatch = function(matchId) {
    const matchRows = document.querySelectorAll('.match-row');
    if (matchRows.length > 1) {
        const matchToRemove = document.querySelector(`.match-row[data-match-id="${matchId}"]`);
        if (matchToRemove) {
            matchToRemove.remove();
            console.log('Match removed successfully');
        }
    } else {
        alert('You must have at least one match in the stack.');
    }
};

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing form...');
    
    // Set default deadline to 24 hours from now
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const tomorrowString = tomorrow.toISOString().slice(0, 16);
    const deadlineInput = document.getElementById('deadline');
    if (deadlineInput) {
        deadlineInput.value = tomorrowString;
    }
    
    // Set default match time to 48 hours from now
    const matchTime = new Date();
    matchTime.setDate(matchTime.getDate() + 2);
    const matchTimeString = matchTime.toISOString().slice(0, 16);
    const firstMatchTimeInput = document.querySelector('input[name="matches[1][match_time]"]');
    if (firstMatchTimeInput) {
        firstMatchTimeInput.value = matchTimeString;
    }
    
    // Form validation
    const form = document.getElementById('createStackForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const matchRows = document.querySelectorAll('.match-row');
            if (matchRows.length === 0) {
                e.preventDefault();
                alert('Please add at least one match to the stack.');
                return false;
            }
            
            // Validate that all match fields are filled
            let isValid = true;
            matchRows.forEach(function(row) {
                const homeTeam = row.querySelector('input[name*="[home_team]"]').value;
                const awayTeam = row.querySelector('input[name*="[away_team]"]').value;
                const matchTime = row.querySelector('input[name*="[match_time]"]').value;
                
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
    }
    
    console.log('Form initialization complete');
    
    // Button is already handled by onclick attribute, no need for additional listener
});

// Image preview functions
function previewImage(input) {
    const file = input.files[0];
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    if (file) {
        // Validate file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            alert('Image size must be less than 2MB');
            input.value = '';
            return;
        }
        
        // Validate file type
        if (!file.type.match('image.*')) {
            alert('Please select a valid image file');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
}

function removeImage() {
    const input = document.getElementById('prize_image');
    const preview = document.getElementById('imagePreview');
    
    input.value = '';
    preview.style.display = 'none';
}
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

/* Debug styles to ensure button is clickable */
#addMatchBtn {
    cursor: pointer !important;
    pointer-events: auto !important;
    z-index: 1000;
    position: relative;
}

#addMatchBtn:hover {
    background-color: #28a745 !important;
    transform: translateY(-1px);
}
</style>
<?= $this->endSection() ?> 