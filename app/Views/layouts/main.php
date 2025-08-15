<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Score Predictor - Admin Dashboard' ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <style>
        :root {
            /* Enhanced Color Palette with Better Contrast */
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --primary-light: #6366f1;
            --primary-dark: #3730a3;
            
            --success-gradient: linear-gradient(135deg, #059669 0%, #10b981 100%);
            --success-light: #34d399;
            --success-dark: #047857;
            
            --warning-gradient: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
            --warning-light: #fbbf24;
            --warning-dark: #b45309;
            
            --danger-gradient: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
            --danger-light: #f87171;
            --danger-dark: #b91c1c;
            
            --info-gradient: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
            --info-light: #22d3ee;
            --info-dark: #0e7490;
            
            --secondary-gradient: linear-gradient(135deg, #6b7280 0%, #9ca3af 100%);
            --secondary-light: #d1d5db;
            --secondary-dark: #4b5563;
            
            /* Surface Colors with Better Contrast */
            --surface: rgba(255, 255, 255, 0.98);
            --surface-secondary: rgba(249, 250, 251, 0.95);
            --surface-tertiary: rgba(243, 244, 246, 0.9);
            --glass: rgba(255, 255, 255, 0.15);
            --glass-dark: rgba(0, 0, 0, 0.1);
            
            /* Text Colors with Better Contrast */
            --text-primary: #111827;
            --text-secondary: #374151;
            --text-muted: #6b7280;
            --text-light: #9ca3af;
            --text-white: #ffffff;
            --text-dark: #1f2937;
            
            /* Border Colors */
            --border-color: #e5e7eb;
            --border-light: #f3f4f6;
            --border-dark: #d1d5db;
            
            /* Shadows */
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07), 0 2px 4px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1), 0 4px 6px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px rgba(0, 0, 0, 0.1), 0 10px 10px rgba(0, 0, 0, 0.04);
            
            /* Border Radius */
            --border-radius: 12px;
            --border-radius-sm: 6px;
            --border-radius-lg: 16px;
            --border-radius-xl: 20px;
            
            /* Transitions */
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-fast: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
            min-height: 100vh;
            color: var(--text-primary);
            line-height: 1.6;
            font-weight: 400;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(120, 219, 255, 0.2) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Header Styles */
        .header {
            background: var(--surface);
            backdrop-filter: blur(20px);
            box-shadow: var(--shadow-sm);
            border-bottom: 1px solid var(--border-color);
            padding: 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 80px;
        }

        .logo {
            font-size: 1.75rem;
            font-weight: 800;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: var(--transition);
        }

        .logo i {
            font-size: 2rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .logo:hover {
            transform: translateY(-1px);
        }

        .nav-links {
            display: flex;
            gap: 8px;
            list-style: none;
            align-items: center;
        }

        .nav-links li {
            position: relative;
        }

        .nav-links a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            text-decoration: none;
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.95rem;
            border-radius: var(--border-radius-sm);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .nav-links a::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--primary-gradient);
            opacity: 0;
            transition: var(--transition);
            z-index: -1;
        }

        .nav-links a:hover::before,
        .nav-links a.active::before {
            opacity: 0.1;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--primary-dark);
            transform: translateY(-1px);
        }

        .nav-links a i {
            font-size: 1.1rem;
        }

        /* Mobile Menu Toggle */
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 8px;
            border-radius: var(--border-radius-sm);
            transition: var(--transition);
        }

        .menu-toggle:hover {
            background: rgba(79, 70, 229, 0.1);
        }

        .main-content {
            padding: 32px 0;
            min-height: calc(100vh - 80px);
        }

        /* Enhanced Card Styles */
        .card {
            background: var(--surface);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            padding: 32px;
            margin-bottom: 24px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
            opacity: 0;
            transition: var(--transition);
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
        }

        .card:hover::before {
            opacity: 1;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border-color);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0;
        }

        .card-title i {
            font-size: 1.3rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Enhanced Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: var(--border-radius-sm);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            white-space: nowrap;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: var(--transition);
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: var(--text-white);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(79, 70, 229, 0.4);
            color: var(--text-white);
        }

        .btn-success {
            background: var(--success-gradient);
            color: var(--text-white);
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(5, 150, 105, 0.4);
            color: var(--text-white);
        }

        .btn-warning {
            background: var(--warning-gradient);
            color: var(--text-white);
            box-shadow: 0 4px 12px rgba(217, 119, 6, 0.3);
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(217, 119, 6, 0.4);
            color: var(--text-white);
        }

        .btn-danger {
            background: var(--danger-gradient);
            color: var(--text-white);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(220, 38, 38, 0.4);
            color: var(--text-white);
        }

        .btn-info {
            background: var(--info-gradient);
            color: var(--text-white);
            box-shadow: 0 4px 12px rgba(8, 145, 178, 0.3);
        }

        .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(8, 145, 178, 0.4);
            color: var(--text-white);
        }

        .btn-secondary {
            background: var(--surface-secondary);
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
            backdrop-filter: blur(10px);
        }

        .btn-secondary:hover {
            background: var(--surface-tertiary);
            transform: translateY(-1px);
            color: var(--text-primary);
        }

        /* Outline Button Variants */
        .btn-outline-primary {
            background: transparent;
            color: var(--primary-dark);
            border: 2px solid var(--primary-light);
        }

        .btn-outline-primary:hover {
            background: var(--primary-gradient);
            color: var(--text-white);
            border-color: transparent;
        }

        .btn-outline-success {
            background: transparent;
            color: var(--success-dark);
            border: 2px solid var(--success-light);
        }

        .btn-outline-success:hover {
            background: var(--success-gradient);
            color: var(--text-white);
            border-color: transparent;
        }

        .btn-outline-warning {
            background: transparent;
            color: var(--warning-dark);
            border: 2px solid var(--warning-light);
        }

        .btn-outline-warning:hover {
            background: var(--warning-gradient);
            color: var(--text-white);
            border-color: transparent;
        }

        .btn-outline-danger {
            background: transparent;
            color: var(--danger-dark);
            border: 2px solid var(--danger-light);
        }

        .btn-outline-danger:hover {
            background: var(--danger-gradient);
            color: var(--text-white);
            border-color: transparent;
        }

        .btn-outline-info {
            background: transparent;
            color: var(--info-dark);
            border: 2px solid var(--info-light);
        }

        .btn-outline-info:hover {
            background: var(--info-gradient);
            color: var(--text-white);
            border-color: transparent;
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 0.875rem;
        }

        .btn-lg {
            padding: 16px 32px;
            font-size: 1.1rem;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius-sm);
            font-size: 0.95rem;
            font-family: inherit;
            transition: var(--transition);
            background: var(--surface);
            color: var(--text-primary);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
            background: var(--surface);
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        /* Alert Styles */
        .alert {
            padding: 16px 20px;
            border-radius: var(--border-radius-sm);
            margin-bottom: 24px;
            border-left: 4px solid;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            backdrop-filter: blur(10px);
        }

        .alert-success {
            background: rgba(209, 250, 229, 0.95);
            color: var(--success-dark);
            border-left-color: var(--success-dark);
        }

        .alert-danger {
            background: rgba(254, 226, 226, 0.95);
            color: var(--danger-dark);
            border-left-color: var(--danger-dark);
        }

        .alert-info {
            background: rgba(207, 250, 254, 0.95);
            color: var(--info-dark);
            border-left-color: var(--info-dark);
        }

        .alert-warning {
            background: rgba(254, 243, 199, 0.95);
            color: var(--warning-dark);
            border-left-color: var(--warning-dark);
        }

        /* Table Styles */
        .table-container {
            background: var(--surface);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            background: transparent;
        }

        .table th,
        .table td {
            padding: 16px 20px;
            text-align: left;
            border-bottom: 1px solid var(--border-light);
        }

        .table th {
            background: var(--surface-secondary);
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table tbody tr {
            transition: var(--transition);
        }

        .table tbody tr:hover {
            background: rgba(79, 70, 229, 0.05);
        }

        /* Badge Styles */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-success {
            background: var(--success-gradient);
            color: var(--text-white);
        }

        .badge-warning {
            background: var(--warning-gradient);
            color: var(--text-white);
        }

        .badge-danger {
            background: var(--danger-gradient);
            color: var(--text-white);
        }

        .badge-info {
            background: var(--info-gradient);
            color: var(--text-white);
        }

        .badge-primary {
            background: var(--primary-gradient);
            color: var(--text-white);
        }

        .badge-secondary {
            background: var(--secondary-gradient);
            color: var(--text-white);
        }

        /* Legacy Bootstrap Badge Support */
        .bg-success { background: var(--success-gradient) !important; color: var(--text-white) !important; }
        .bg-warning { background: var(--warning-gradient) !important; color: var(--text-white) !important; }
        .bg-danger { background: var(--danger-gradient) !important; color: var(--text-white) !important; }
        .bg-info { background: var(--info-gradient) !important; color: var(--text-white) !important; }
        .bg-primary { background: var(--primary-gradient) !important; color: var(--text-white) !important; }
        .bg-secondary { background: var(--secondary-gradient) !important; color: var(--text-white) !important; }

        /* Text Color Utilities */
        .text-primary { color: var(--primary-dark) !important; }
        .text-secondary { color: var(--text-secondary) !important; }
        .text-success { color: var(--success-dark) !important; }
        .text-warning { color: var(--warning-dark) !important; }
        .text-danger { color: var(--danger-dark) !important; }
        .text-info { color: var(--info-dark) !important; }
        .text-muted { color: var(--text-muted) !important; }
        .text-white { color: var(--text-white) !important; }
        .text-dark { color: var(--text-dark) !important; }

        /* Legacy Bootstrap Text Support */
        .text-gray-800 { color: var(--text-primary) !important; }

        /* Grid System */
        .grid {
            display: grid;
            gap: 24px;
        }

        .grid-2 {
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        }

        .grid-3 {
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }

        .grid-4 {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        }

        .grid-5 {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        /* Utility Classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }

        .mb-0 { margin-bottom: 0; }
        .mb-1 { margin-bottom: 8px; }
        .mb-2 { margin-bottom: 16px; }
        .mb-3 { margin-bottom: 24px; }
        .mb-4 { margin-bottom: 32px; }

        .mt-0 { margin-top: 0; }
        .mt-1 { margin-top: 8px; }
        .mt-2 { margin-top: 16px; }
        .mt-3 { margin-top: 24px; }
        .mt-4 { margin-top: 32px; }

        .p-0 { padding: 0; }
        .p-1 { padding: 8px; }
        .p-2 { padding: 16px; }
        .p-3 { padding: 24px; }
        .p-4 { padding: 32px; }

        .d-flex { display: flex; }
        .align-items-center { align-items: center; }
        .justify-content-between { justify-content: space-between; }
        .gap-1 { gap: 8px; }
        .gap-2 { gap: 16px; }
        .gap-3 { gap: 24px; }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .container {
                padding: 0 20px;
            }
            
            .card {
                padding: 24px;
            }
            
            .grid-4,
            .grid-5 {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: var(--surface);
                backdrop-filter: blur(20px);
                flex-direction: column;
                padding: 20px;
                box-shadow: var(--shadow-lg);
                border-top: 1px solid var(--border-color);
            }
            
            .nav-links.active {
                display: flex;
            }
            
            .nav-links a {
                width: 100%;
                padding: 16px;
                justify-content: flex-start;
            }
            
            .menu-toggle {
                display: block;
            }
            
            .card {
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .card-title {
                font-size: 1.3rem;
            }
            
            .btn {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
            
            .grid-2,
            .grid-3,
            .grid-4,
            .grid-5 {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .main-content {
                padding: 20px 0;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 16px;
            }
            
            .card {
                padding: 16px;
            }
            
            .nav {
                height: 70px;
            }
            
            .logo {
                font-size: 1.5rem;
            }
            
            .logo i {
                font-size: 1.7rem;
            }
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(79, 70, 229, 0.3);
            border-radius: 4px;
            transition: var(--transition);
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(79, 70, 229, 0.5);
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <a href="<?= base_url('/admin') ?>" class="logo">
                    <i class="fas fa-trophy"></i>
                    <span>Score Predictor</span>
                </a>
                
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <ul class="nav-links" id="navLinks">
                    <li><a href="<?= base_url('/admin') ?>" class="<?= uri_string() == 'admin' ? 'active' : '' ?>">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a></li>
                    <li><a href="<?= base_url('/admin/users') ?>" class="<?= strpos(uri_string(), 'admin/users') !== false ? 'active' : '' ?>">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                    </a></li>
                    <li><a href="<?= base_url('/admin/stacks') ?>" class="<?= strpos(uri_string(), 'admin/stacks') !== false ? 'active' : '' ?>">
                        <i class="fas fa-layer-group"></i>
                        <span>Stacks</span>
                    </a></li>
                    <li><a href="<?= base_url('/admin/payments') ?>" class="<?= strpos(uri_string(), 'admin/payments') !== false ? 'active' : '' ?>">
                        <i class="fas fa-credit-card"></i>
                        <span>Payments</span>
                    </a></li>
                    <li><a href="<?= base_url('/admin/winners') ?>" class="<?= strpos(uri_string(), 'admin/winners') !== false ? 'active' : '' ?>">
                        <i class="fas fa-trophy"></i>
                        <span>Winners</span>
                    </a></li>
                    <li><a href="<?= base_url('/admin/reports') ?>" class="<?= strpos(uri_string(), 'admin/reports') !== false ? 'active' : '' ?>">
                        <i class="fas fa-chart-line"></i>
                        <span>Reports</span>
                    </a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <?= $this->renderSection('content') ?>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const menuToggle = document.getElementById('menuToggle');
            const navLinks = document.getElementById('navLinks');
            
            if (menuToggle && navLinks) {
                menuToggle.addEventListener('click', function() {
                    navLinks.classList.toggle('active');
                    const icon = menuToggle.querySelector('i');
                    icon.classList.toggle('fa-bars');
                    icon.classList.toggle('fa-times');
                });

                // Close mobile menu when clicking outside
                document.addEventListener('click', function(e) {
                    if (!menuToggle.contains(e.target) && !navLinks.contains(e.target)) {
                        navLinks.classList.remove('active');
                        const icon = menuToggle.querySelector('i');
                        icon.classList.add('fa-bars');
                        icon.classList.remove('fa-times');
                    }
                });
            }

            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });

            // Add loading state to buttons when clicked
            const buttons = document.querySelectorAll('.btn[type="submit"], .btn[data-loading]');
            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    if (!this.classList.contains('btn-danger')) { // Don't add loading to delete buttons
                        const originalText = this.innerHTML;
                        this.innerHTML = '<span class="loading"></span> Loading...';
                        this.disabled = true;
                        
                        // Re-enable button after 3 seconds (fallback)
                        setTimeout(() => {
                            this.innerHTML = originalText;
                            this.disabled = false;
                        }, 3000);
                    }
                });
            });

            // Initialize DataTables if present
            if (typeof $.fn.DataTable !== 'undefined') {
                $('.data-table').each(function() {
                    $(this).DataTable({
                        responsive: true,
                        pageLength: 25,
                        language: {
                            search: "Search:",
                            lengthMenu: "Show _MENU_ entries per page",
                            info: "Showing _START_ to _END_ of _TOTAL_ entries",
                            paginate: {
                                first: "First",
                                last: "Last",
                                next: "Next",
                                previous: "Previous"
                            }
                        },
                        dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rtip'
                    });
                });
            }

            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Add animation to cards on scroll (Intersection Observer)
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observe all cards for animation
            document.querySelectorAll('.card').forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(card);
            });
        });

        // Utility function for showing toast notifications
        function showToast(message, type = 'info', duration = 3000) {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type}`;
            toast.style.position = 'fixed';
            toast.style.top = '20px';
            toast.style.right = '20px';
            toast.style.zIndex = '9999';
            toast.style.minWidth = '300px';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            toast.style.transition = 'all 0.3s ease';
            
            const icons = {
                success: 'fa-check-circle',
                danger: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };
            
            toast.innerHTML = `<i class="fas ${icons[type] || icons.info}"></i> ${message}`;
            
            document.body.appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateX(0)';
            }, 100);
            
            // Animate out and remove
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }

        // Make showToast globally available
        window.showToast = showToast;
    </script>
</body>
</html>