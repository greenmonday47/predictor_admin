<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="card-title mb-2">
            <i class="fas fa-tachometer-alt"></i>
            Admin Dashboard
        </h1>
        <p class="text-muted mb-0">Welcome back! Here's what's happening with your platform today.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('admin/stacks/create') ?>" class="btn btn-success">
            <i class="fas fa-plus"></i>
            <span>Create Stack</span>
        </a>
        <a href="<?= base_url('admin/users') ?>" class="btn btn-primary">
            <i class="fas fa-users"></i>
            <span>Manage Users</span>
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-4 mb-4">
    <div class="card p-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; overflow: hidden;">
        <div class="p-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <p class="mb-1" style="opacity: 0.9; font-size: 0.9rem; font-weight: 500;">Total Users</p>
                    <h3 class="mb-0" style="font-size: 2.2rem; font-weight: 700;"><?= number_format($totalUsers ?? 0) ?></h3>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 12px; border-radius: 12px;">
                    <i class="fas fa-users" style="font-size: 1.5rem;"></i>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge" style="background: rgba(255,255,255,0.2); color: white; font-size: 0.75rem;">
                    <i class="fas fa-arrow-up"></i> +12%
                </span>
                <small style="opacity: 0.8;">vs last month</small>
            </div>
        </div>
        <div style="height: 4px; background: linear-gradient(90deg, rgba(255,255,255,0.3), rgba(255,255,255,0.1));"></div>
    </div>
    
    <div class="card p-0" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; overflow: hidden;">
        <div class="p-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <p class="mb-1" style="opacity: 0.9; font-size: 0.9rem; font-weight: 500;">Active Stacks</p>
                    <h3 class="mb-0" style="font-size: 2.2rem; font-weight: 700;"><?= number_format($activeStacks ?? 0) ?></h3>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 12px; border-radius: 12px;">
                    <i class="fas fa-layer-group" style="font-size: 1.5rem;"></i>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge" style="background: rgba(255,255,255,0.2); color: white; font-size: 0.75rem;">
                    <i class="fas fa-arrow-up"></i> +5%
                </span>
                <small style="opacity: 0.8;">vs last week</small>
            </div>
        </div>
        <div style="height: 4px; background: linear-gradient(90deg, rgba(255,255,255,0.3), rgba(255,255,255,0.1));"></div>
    </div>
    
    <div class="card p-0" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); color: white; overflow: hidden;">
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
            <div class="d-flex align-items-center gap-2">
                <span class="badge" style="background: rgba(255,255,255,0.2); color: white; font-size: 0.75rem;">
                    <i class="fas fa-arrow-up"></i> +28%
                </span>
                <small style="opacity: 0.8;">vs last month</small>
            </div>
        </div>
        <div style="height: 4px; background: linear-gradient(90deg, rgba(255,255,255,0.3), rgba(255,255,255,0.1));"></div>
    </div>
    
    <div class="card p-0" style="background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%); color: white; overflow: hidden;">
        <div class="p-3">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <p class="mb-1" style="opacity: 0.9; font-size: 0.9rem; font-weight: 500;">Total Winners</p>
                    <h3 class="mb-0" style="font-size: 2.2rem; font-weight: 700;"><?= number_format($totalWinners ?? 0) ?></h3>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 12px; border-radius: 12px;">
                    <i class="fas fa-trophy" style="font-size: 1.5rem;"></i>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge" style="background: rgba(255,255,255,0.2); color: white; font-size: 0.75rem;">
                    <i class="fas fa-arrow-up"></i> +15%
                </span>
                <small style="opacity: 0.8;">vs last month</small>
            </div>
        </div>
        <div style="height: 4px; background: linear-gradient(90deg, rgba(255,255,255,0.3), rgba(255,255,255,0.1));"></div>
    </div>
</div>

<!-- Chart Section -->
<div class="grid grid-2 mb-4">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-area"></i>
                User Growth
            </h3>
            <div class="d-flex gap-1">
                <button class="btn btn-sm btn-secondary active" data-period="7d">7D</button>
                <button class="btn btn-sm btn-secondary" data-period="30d">30D</button>
                <button class="btn btn-sm btn-secondary" data-period="90d">90D</button>
            </div>
        </div>
        <div class="chart-container" style="height: 250px; position: relative;">
            <canvas id="userGrowthChart" style="width: 100%; height: 100%;"></canvas>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-pie"></i>
                Prediction Categories
            </h3>
            <select class="form-control" style="width: auto; display: inline-block;">
                <option>Last 30 days</option>
                <option>Last 7 days</option>
                <option>Last 90 days</option>
            </select>
        </div>
        <div class="chart-container" style="height: 250px; position: relative;">
            <canvas id="categoriesChart" style="width: 100%; height: 100%;"></canvas>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="grid grid-2 mb-4">
    <!-- Recent Users -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-clock"></i>
                Recent Users
            </h3>
            <a href="<?= base_url('admin/users') ?>" class="btn btn-sm btn-primary">
                View All
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <?php if (!empty($recentUsers)): ?>
            <div class="table-container" style="max-height: 400px; overflow-y: auto;">
                <table class="table">
                    <tbody>
                        <?php foreach ($recentUsers as $user): ?>
                            <tr style="border-left: 4px solid #667eea;">
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                            <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0" style="font-weight: 600;"><?= esc($user['full_name']) ?></h6>
                                            <small class="text-muted">
                                                <i class="fas fa-phone"></i>
                                                <?= esc($user['phone']) ?>
                                            </small>
                                        </div>
                                        <div class="text-right">
                                            <small class="text-muted d-block"><?= date('M j, Y', strtotime($user['created_at'])) ?></small>
                                            <small class="text-muted"><?= date('g:i A', strtotime($user['created_at'])) ?></small>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center p-4" style="color: #a0aec0;">
                <div class="mb-3" style="font-size: 3rem; opacity: 0.5;">
                    <i class="fas fa-users"></i>
                </div>
                <h6 style="color: #718096;">No users yet</h6>
                <p class="mb-0" style="font-size: 0.9rem;">Users will appear here once they start registering</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Recent Stacks -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-layer-group"></i>
                Recent Stacks
            </h3>
            <a href="<?= base_url('admin/stacks') ?>" class="btn btn-sm btn-primary">
                View All
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <?php if (!empty($recentStacks)): ?>
            <div style="max-height: 400px; overflow-y: auto;">
                <?php foreach ($recentStacks as $index => $stack): ?>
                    <div class="p-3" style="border-bottom: 1px solid #e2e8f0; <?= $index === 0 ? 'border-top: 1px solid #e2e8f0;' : '' ?>">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="flex-grow-1">
                                <h6 class="mb-1" style="font-weight: 600;"><?= esc($stack['title']) ?></h6>
                                <p class="mb-2" style="color: #718096; font-size: 0.9rem;">
                                    <i class="fas fa-gift"></i>
                                    <?= esc($stack['prize_description']) ?>
                                </p>
                            </div>
                            <span class="badge badge-<?= $stack['is_active'] ? 'success' : 'secondary' ?>">
                                <?= $stack['is_active'] ? 'Active' : 'Completed' ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-calendar"></i>
                                Created <?= date('M j, Y', strtotime($stack['created_at'])) ?>
                            </small>
                            <div class="d-flex gap-1">
                                <a href="<?= base_url('admin/stacks/edit/' . $stack['id']) ?>" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?= base_url('admin/stacks/view/' . $stack['id']) ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center p-4" style="color: #a0aec0;">
                <div class="mb-3" style="font-size: 3rem; opacity: 0.5;">
                    <i class="fas fa-layer-group"></i>
                </div>
                <h6 style="color: #718096;">No stacks yet</h6>
                <p class="mb-3" style="font-size: 0.9rem;">Create your first prediction stack to get started</p>
                <a href="<?= base_url('admin/stacks/create') ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-plus"></i>
                    Create Stack
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-bolt"></i>
            Quick Actions
        </h3>
        <small class="text-muted">Frequently used admin tasks</small>
    </div>
    
    <div class="grid grid-5">
        <a href="<?= base_url('admin/stacks/create') ?>" class="card quick-action" style="text-decoration: none; text-align: center; padding: 24px; border: 2px dashed #e2e8f0; background: #f8f9fa; transition: all 0.3s ease; border-radius: 16px;">
            <div class="quick-action-icon mb-3" style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; margin: 0 auto; color: white; font-size: 1.5rem;">
                <i class="fas fa-plus"></i>
            </div>
            <h5 class="mb-2" style="color: #2d3748; font-weight: 600;">Create Stack</h5>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">Add new prediction stack</p>
        </a>
        
        <a href="<?= base_url('admin/users') ?>" class="card quick-action" style="text-decoration: none; text-align: center; padding: 24px; border: 2px dashed #e2e8f0; background: #f8f9fa; transition: all 0.3s ease; border-radius: 16px;">
            <div class="quick-action-icon mb-3" style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #28a745, #20c997); display: flex; align-items: center; justify-content: center; margin: 0 auto; color: white; font-size: 1.5rem;">
                <i class="fas fa-users-cog"></i>
            </div>
            <h5 class="mb-2" style="color: #2d3748; font-weight: 600;">Manage Users</h5>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">View and manage users</p>
        </a>
        
        <a href="<?= base_url('admin/payments') ?>" class="card quick-action" style="text-decoration: none; text-align: center; padding: 24px; border: 2px dashed #e2e8f0; background: #f8f9fa; transition: all 0.3s ease; border-radius: 16px;">
            <div class="quick-action-icon mb-3" style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #ffc107, #fd7e14); display: flex; align-items: center; justify-content: center; margin: 0 auto; color: white; font-size: 1.5rem;">
                <i class="fas fa-credit-card"></i>
            </div>
            <h5 class="mb-2" style="color: #2d3748; font-weight: 600;">Payment History</h5>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">View payment records</p>
        </a>
        
        <a href="<?= base_url('admin/topup-transactions') ?>" class="card quick-action" style="text-decoration: none; text-align: center; padding: 24px; border: 2px dashed #e2e8f0; background: #f8f9fa; transition: all 0.3s ease; border-radius: 16px;">
            <div class="quick-action-icon mb-3" style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #17a2b8, #6f42c1); display: flex; align-items: center; justify-content: center; margin: 0 auto; color: white; font-size: 1.5rem;">
                <i class="fas fa-wallet"></i>
            </div>
            <h5 class="mb-2" style="color: #2d3748; font-weight: 600;">Top-Up Transactions</h5>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">View wallet top-ups</p>
        </a>
        
        <a href="<?= base_url('admin/reports') ?>" class="card quick-action" style="text-decoration: none; text-align: center; padding: 24px; border: 2px dashed #e2e8f0; background: #f8f9fa; transition: all 0.3s ease; border-radius: 16px;">
            <div class="quick-action-icon mb-3" style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #dc3545, #e83e8c); display: flex; align-items: center; justify-content: center; margin: 0 auto; color: white; font-size: 1.5rem;">
                <i class="fas fa-chart-bar"></i>
            </div>
            <h5 class="mb-2" style="color: #2d3748; font-weight: 600;">Reports</h5>
            <p class="text-muted mb-0" style="font-size: 0.85rem;">View analytics & reports</p>
        </a>
    </div>
</div>

<!-- System Status -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-server"></i>
            System Status
        </h3>
        <div class="d-flex align-items-center gap-2">
            <div class="status-indicator" style="width: 8px; height: 8px; border-radius: 50%; background: #28a745; animation: pulse 2s infinite;"></div>
            <small class="text-success">All systems operational</small>
        </div>
    </div>
    
    <div class="grid grid-4">
        <div class="status-card" style="background: linear-gradient(135deg, rgba(40, 167, 69, 0.1), rgba(32, 201, 151, 0.1)); padding: 20px; border-radius: 12px; border: 1px solid rgba(40, 167, 69, 0.2);">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(40, 167, 69, 0.2); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-database" style="color: #28a745; font-size: 1.2rem;"></i>
                </div>
                <div>
                    <h6 class="mb-0" style="color: #155724; font-weight: 600;">Database</h6>
                    <small style="color: #155724; opacity: 0.8;">Connected and operational</small>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <small style="color: #155724;">Response Time</small>
                <small style="color: #155724; font-weight: 600;">12ms</small>
            </div>
        </div>
        
        <div class="status-card" style="background: linear-gradient(135deg, rgba(40, 167, 69, 0.1), rgba(32, 201, 151, 0.1)); padding: 20px; border-radius: 12px; border: 1px solid rgba(40, 167, 69, 0.2);">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(40, 167, 69, 0.2); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-plug" style="color: #28a745; font-size: 1.2rem;"></i>
                </div>
                <div>
                    <h6 class="mb-0" style="color: #155724; font-weight: 600;">API</h6>
                    <small style="color: #155724; opacity: 0.8;">All endpoints functional</small>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <small style="color: #155724;">Uptime</small>
                <small style="color: #155724; font-weight: 600;">99.9%</small>
            </div>
        </div>
        
        <div class="status-card" style="background: linear-gradient(135deg, rgba(40, 167, 69, 0.1), rgba(32, 201, 151, 0.1)); padding: 20px; border-radius: 12px; border: 1px solid rgba(40, 167, 69, 0.2);">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(40, 167, 69, 0.2); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-memory" style="color: #28a745; font-size: 1.2rem;"></i>
                </div>
                <div>
                    <h6 class="mb-0" style="color: #155724; font-weight: 600;">Cache</h6>
                    <small style="color: #155724; opacity: 0.8;">Cache system active</small>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <small style="color: #155724;">Hit Rate</small>
                <small style="color: #155724; font-weight: 600;">94.2%</small>
            </div>
        </div>
        
        <div class="status-card" style="background: linear-gradient(135deg, rgba(40, 167, 69, 0.1), rgba(32, 201, 151, 0.1)); padding: 20px; border-radius: 12px; border: 1px solid rgba(40, 167, 69, 0.2);">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(40, 167, 69, 0.2); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-server" style="color: #28a745; font-size: 1.2rem;"></i>
                </div>
                <div>
                    <h6 class="mb-0" style="color: #155724; font-weight: 600;">Server</h6>
                    <small style="color: #155724; opacity: 0.8;">Performance optimal</small>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <small style="color: #155724;">Load</small>
                <small style="color: #155724; font-weight: 600;">23%</small>
            </div>
        </div>
    </div>
</div>

<style>
    .quick-action:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 48px rgba(0, 0, 0, 0.15) !important;
        border-color: #667eea !important;
    }
    
    .quick-action:hover .quick-action-icon {
        transform: scale(1.1);
    }
    
    .avatar {
        position: relative;
    }
    
    .avatar::before {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        z-index: -1;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    tr:hover .avatar::before {
        opacity: 1;
    }
    
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
        }
    }
    
    .chart-container {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        color: #718096;
        font-weight: 500;
    }
    
    .chart-container::before {
        content: 'Chart will be loaded here';
        position: absolute;
        font-size: 0.9rem;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers for chart period buttons
    document.querySelectorAll('[data-period]').forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons in the same group
            this.parentNode.querySelectorAll('.btn').forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            // Here you would typically reload the chart with new data
            const period = this.dataset.period;
            console.log('Loading data for period:', period);
            
            // Show loading state
            showToast(`Loading ${period} data...`, 'info', 1000);
        });
    });
    
    // Simulate real-time updates for statistics
    function updateStats() {
        const stats = document.querySelectorAll('.grid-4 h3');
        stats.forEach(stat => {
            const currentValue = parseInt(stat.textContent.replace(/,/g, ''));
            // Simulate small random updates (±5%)
            const change = Math.floor(Math.random() * (currentValue * 0.1)) - (currentValue * 0.05);
            const newValue = Math.max(0, currentValue + Math.floor(change));
            
            // Animate the number change
            animateNumber(stat, currentValue, newValue, 1000);
        });
    }
    
    // Number animation function
    function animateNumber(element, start, end, duration) {
        const startTime = performance.now();
        const difference = end - start;
        
        function updateNumber(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easeOutQuart = 1 - Math.pow(1 - progress, 4);
            
            const currentValue = Math.floor(start + (difference * easeOutQuart));
            element.textContent = currentValue.toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(updateNumber);
            }
        }
        
        requestAnimationFrame(updateNumber);
    }
    
    // Update stats every 30 seconds (for demo purposes)
    // setInterval(updateStats, 30000);
    
    // Add hover effects to quick action cards
    const quickActions = document.querySelectorAll('.quick-action');
    quickActions.forEach(action => {
        action.addEventListener('mouseenter', function() {
            this.style.borderColor = '#667eea';
            this.style.background = 'linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05))';
        });
        
        action.addEventListener('mouseleave', function() {
            this.style.borderColor = '#e2e8f0';
            this.style.background = '#f8f9fa';
        });
    });
    
    // Add click animation to statistics cards
    const statCards = document.querySelectorAll('.grid-4 .card');
    statCards.forEach(card => {
        card.addEventListener('click', function() {
            this.style.transform = 'scale(0.98)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
        });
        
        // Make cards clickable (you can add navigation logic here)
        card.style.cursor = 'pointer';
    });
    
    // Auto-refresh system status indicators
    function updateSystemStatus() {
        const indicators = document.querySelectorAll('.status-card');
        indicators.forEach(indicator => {
            // Simulate status updates with slight variations
            const responseTimeEl = indicator.querySelector('small:last-child');
            if (responseTimeEl && responseTimeEl.textContent.includes('ms')) {
                const currentTime = parseInt(responseTimeEl.textContent);
                const newTime = Math.max(5, currentTime + Math.floor(Math.random() * 10 - 5));
                responseTimeEl.textContent = newTime + 'ms';
            }
            
            if (responseTimeEl && responseTimeEl.textContent.includes('%')) {
                const currentPercent = parseFloat(responseTimeEl.textContent);
                const newPercent = Math.max(80, Math.min(100, currentPercent + (Math.random() * 2 - 1)));
                responseTimeEl.textContent = newPercent.toFixed(1) + '%';
            }
        });
    }
    
    // Update system status every 10 seconds
    setInterval(updateSystemStatus, 10000);
    
    // Add loading states for navigation
    document.querySelectorAll('a[href*="admin/"]').forEach(link => {
        link.addEventListener('click', function(e) {
            if (!this.classList.contains('btn-danger')) {
                const icon = this.querySelector('i');
                if (icon) {
                    const originalClass = icon.className;
                    icon.className = 'fas fa-spinner fa-spin';
                    
                    // Restore original icon after navigation
                    setTimeout(() => {
                        icon.className = originalClass;
                    }, 2000);
                }
            }
        });
    });
    
    // Initialize tooltips for better UX (if you add a tooltip library later)
    function initTooltips() {
        const elements = document.querySelectorAll('[title]');
        elements.forEach(el => {
            el.addEventListener('mouseenter', function() {
                // You can implement custom tooltips here
            });
        });
    }
    
    initTooltips();
    
    // Keyboard shortcuts for admin actions
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + N = New Stack
        if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
            e.preventDefault();
            window.location.href = '<?= base_url('admin/stacks/create') ?>';
        }
        
        // Ctrl/Cmd + U = Users
        if ((e.ctrlKey || e.metaKey) && e.key === 'u') {
            e.preventDefault();
            window.location.href = '<?= base_url('admin/users') ?>';
        }
        
        // Ctrl/Cmd + R = Reports
        if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
            e.preventDefault();
            window.location.href = '<?= base_url('admin/reports') ?>';
        }
    });
    
    // Show keyboard shortcuts hint
    const shortcutsHint = document.createElement('div');
    shortcutsHint.innerHTML = `
        <div style="position: fixed; bottom: 20px; right: 20px; background: rgba(0,0,0,0.8); color: white; padding: 10px 15px; border-radius: 8px; font-size: 0.8rem; z-index: 1000; opacity: 0; transition: opacity 0.3s ease;">
            <strong>Keyboard Shortcuts:</strong><br>
            Ctrl+N: New Stack<br>
            Ctrl+U: Users<br>
            Ctrl+R: Reports
        </div>
    `;
    
    // Show shortcuts on Ctrl+? or Cmd+?
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === '?') {
            e.preventDefault();
            const hint = shortcutsHint.firstElementChild;
            document.body.appendChild(hint);
            hint.style.opacity = '1';
            
            setTimeout(() => {
                hint.style.opacity = '0';
                setTimeout(() => hint.remove(), 300);
            }, 3000);
        }
    });
});
</script>

<?= $this->endSection() ?>