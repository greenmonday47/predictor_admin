<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manage Users</h1>
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

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Users (<?= count($users) ?>)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="usersTable" width="100%" cellspacing="0">
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
                                <span class="badge bg-info"><?= esc($user['phone']) ?></span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= date('M j, Y', strtotime($user['created_at'])) ?>
                                </small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
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
$(document).ready(function() {
    $('#usersTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true
    });
});

function viewUser(userId) {
    // Load user details via AJAX
    $.get(`<?= base_url('api/auth/profile') ?>/${userId}`)
        .done(function(response) {
            if (response.status === 'success') {
                const user = response.data;
                $('#userModalBody').html(`
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Basic Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>ID:</strong></td><td>${user.id}</td></tr>
                                <tr><td><strong>Name:</strong></td><td>${user.full_name}</td></tr>
                                <tr><td><strong>Phone:</strong></td><td>${user.phone}</td></tr>
                                <tr><td><strong>Joined:</strong></td><td>${new Date(user.created_at).toLocaleDateString()}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Statistics</h6>
                            <div class="row">
                                <div class="col-6">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h4 id="totalPredictions">-</h4>
                                            <small>Total Predictions</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h4 id="totalWins">-</h4>
                                            <small>Total Wins</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
                
                // Load user statistics
                $.get(`<?= base_url('api/scores/stats') ?>/${userId}`)
                    .done(function(statsResponse) {
                        if (statsResponse.status === 'success') {
                            $('#totalPredictions').text(statsResponse.data.total_predictions || 0);
                            $('#totalWins').text(statsResponse.data.total_wins || 0);
                        }
                    });
                
                $('#userModal').modal('show');
            }
        })
        .fail(function() {
            alert('Failed to load user details');
        });
}

function viewUserPredictions(userId) {
    // Load user predictions via AJAX
    $.get(`<?= base_url('api/predictions/user') ?>/${userId}`)
        .done(function(response) {
            if (response.status === 'success') {
                let html = '<div class="table-responsive"><table class="table table-sm">';
                html += '<thead><tr><th>Stack</th><th>Predictions</th><th>Score</th><th>Date</th></tr></thead><tbody>';
                
                response.data.forEach(function(prediction) {
                    html += `<tr>
                        <td>${prediction.title}</td>
                        <td><small>${prediction.predictions_count} predictions</small></td>
                        <td><span class="badge bg-info">${prediction.total_points || 0} pts</span></td>
                        <td><small>${new Date(prediction.created_at).toLocaleDateString()}</small></td>
                    </tr>`;
                });
                
                html += '</tbody></table></div>';
                $('#predictionsModalBody').html(html);
                $('#predictionsModal').modal('show');
            } else {
                $('#predictionsModalBody').html('<p class="text-muted">No predictions found for this user.</p>');
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