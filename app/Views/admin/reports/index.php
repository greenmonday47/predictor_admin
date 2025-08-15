<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="card-title mb-2">
            <i class="fas fa-chart-line"></i>
            Reports & Analytics
        </h1>
        <p class="text-muted mb-0">Comprehensive analytics and insights about your platform</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('admin') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Dashboard</span>
        </a>
    </div>
</div>

<!-- Overview Statistics -->
<div class="grid grid-4 mb-4">
    <div class="card p-0" style="background: var(--primary-gradient); color: var(--text-white); overflow: hidden;">
        <div class="p-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <p class="mb-1" style="opacity: 0.9; font-size: 0.9rem; font-weight: 500;">Total Revenue</p>
                    <h3 class="mb-0" style="font-size: 2.2rem; font-weight: 700;"><?= number_format($totalRevenue ?? 0, 0) ?> UGX</h3>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 12px; border-radius: 12px;">
                    <i class="fas fa-money-bill-wave" style="font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>
        <div style="height: 4px; background: linear-gradient(90deg, rgba(255,255,255,0.3), rgba(255,255,255,0.1));"></div>
    </div>
    
    <div class="card p-0" style="background: var(--success-gradient); color: var(--text-white); overflow: hidden;">
        <div class="p-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <p class="mb-1" style="opacity: 0.9; font-size: 0.9rem; font-weight: 500;">Active Users</p>
                    <h3 class="mb-0" style="font-size: 2.2rem; font-weight: 700;"><?= number_format($activeUsers ?? 0) ?></h3>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 12px; border-radius: 12px;">
                    <i class="fas fa-users" style="font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>
        <div style="height: 4px; background: linear-gradient(90deg, rgba(255,255,255,0.3), rgba(255,255,255,0.1));"></div>
    </div>
    
    <div class="card p-0" style="background: var(--info-gradient); color: var(--text-white); overflow: hidden;">
        <div class="p-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <p class="mb-1" style="opacity: 0.9; font-size: 0.9rem; font-weight: 500;">Total Predictions</p>
                    <h3 class="mb-0" style="font-size: 2.2rem; font-weight: 700;"><?= number_format($totalPredictions ?? 0) ?></h3>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 12px; border-radius: 12px;">
                    <i class="fas fa-chart-line" style="font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>
        <div style="height: 4px; background: linear-gradient(90deg, rgba(255,255,255,0.3), rgba(255,255,255,0.1));"></div>
    </div>
    
    <div class="card p-0" style="background: var(--warning-gradient); color: var(--text-white); overflow: hidden;">
        <div class="p-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <p class="mb-1" style="opacity: 0.9; font-size: 0.9rem; font-weight: 500;">Active Stacks</p>
                    <h3 class="mb-0" style="font-size: 2.2rem; font-weight: 700;"><?= number_format($activeStacks ?? 0) ?></h3>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 12px; border-radius: 12px;">
                    <i class="fas fa-trophy" style="font-size: 1.5rem;"></i>
                </div>
            </div>
        </div>
        <div style="height: 4px; background: linear-gradient(90deg, rgba(255,255,255,0.3), rgba(255,255,255,0.1));"></div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-2 mb-4">
    <!-- Monthly Revenue Chart -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-area"></i>
                Monthly Revenue
            </h3>
        </div>
        <div class="chart-container" style="height: 300px; position: relative;">
            <canvas id="revenueChart" style="width: 100%; height: 100%;"></canvas>
        </div>
    </div>
    
    <!-- Top Performing Stacks -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-trophy"></i>
                Top Performing Stacks
            </h3>
        </div>
        <div class="chart-container" style="height: 300px; position: relative;">
            <canvas id="stacksChart" style="width: 100%; height: 100%;"></canvas>
        </div>
    </div>
</div>

<!-- Additional Analytics -->
<div class="grid grid-2 mb-4">
    <!-- User Growth Chart -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-users"></i>
                User Growth
            </h3>
        </div>
        <div class="chart-container" style="height: 250px; position: relative;">
            <canvas id="userGrowthChart" style="width: 100%; height: 100%;"></canvas>
        </div>
    </div>
    
    <!-- Prediction Categories -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-pie"></i>
                Prediction Categories
            </h3>
        </div>
        <div class="chart-container" style="height: 250px; position: relative;">
            <canvas id="categoriesChart" style="width: 100%; height: 100%;"></canvas>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-clock"></i>
            Recent Activity
        </h3>
    </div>
    
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Activity</th>
                    <th>User</th>
                    <th>Details</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recentActivity)): ?>
                    <?php foreach ($recentActivity as $activity): ?>
                        <tr>
                            <td>
                                <span class="badge badge-primary"><?= esc($activity['type'] ?? 'Unknown') ?></span>
                            </td>
                            <td>
                                <strong><?= esc($activity['user_name'] ?? 'System') ?></strong>
                            </td>
                            <td><?= esc($activity['description'] ?? 'No description available') ?></td>
                            <td>
                                <small class="text-muted">
                                    <?= isset($activity['created_at']) ? date('M j, Y g:i A', strtotime($activity['created_at'])) : 'N/A' ?>
                                </small>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            <i class="fas fa-info-circle"></i>
                            No recent activity to display
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Revenue',
                data: [12000, 19000, 15000, 25000, 22000, 30000],
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Stacks Chart
    const stacksCtx = document.getElementById('stacksChart').getContext('2d');
    new Chart(stacksCtx, {
        type: 'doughnut',
        data: {
            labels: ['Football', 'Basketball', 'Tennis', 'Other'],
            datasets: [{
                data: [45, 25, 20, 10],
                backgroundColor: [
                    '#4f46e5',
                    '#10b981',
                    '#f59e0b',
                    '#6b7280'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // User Growth Chart
    const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
    new Chart(userGrowthCtx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'New Users',
                data: [65, 89, 80, 81, 56, 95],
                backgroundColor: '#10b981'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Categories Chart
    const categoriesCtx = document.getElementById('categoriesChart').getContext('2d');
    new Chart(categoriesCtx, {
        type: 'pie',
        data: {
            labels: ['Exact Score', 'Correct Outcome', 'Wrong'],
            datasets: [{
                data: [30, 45, 25],
                backgroundColor: [
                    '#10b981',
                    '#f59e0b',
                    '#ef4444'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>

<?= $this->endSection() ?> 