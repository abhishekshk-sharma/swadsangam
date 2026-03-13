<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ $tenant->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #232f3e;
            --secondary-color: #37475a;
            --accent-color: #ff9900;
            --hover-color: #2c3e50;
        }
        
        body {
            font-family: 'Amazon Ember', Arial, sans-serif;
            background-color: #f3f3f3;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: #fff;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
            z-index: 1000;
            border-right: 1px solid #e3e6e8;
        }
        
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: #f9f9f9;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: #d5d9d9;
            border-radius: 3px;
        }
        
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #bbb;
        }
        
        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid #e3e6e8;
            background: linear-gradient(135deg, #232f3e 0%, #37475a 100%);
        }
        
        .sidebar-brand {
            font-size: 22px;
            font-weight: 700;
            color: #fff;
            margin: 0;
            letter-spacing: -0.5px;
        }
        
        .sidebar-subtitle {
            font-size: 11px;
            color: #ff9900;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        
        .nav-section {
            padding: 16px 0;
        }
        
        .nav-section-title {
            padding: 0 20px 8px;
            font-size: 11px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 11px 20px;
            color: #232f3e;
            text-decoration: none;
            transition: all 0.15s ease;
            border-left: 3px solid transparent;
            font-size: 14px;
            font-weight: 500;
            margin: 2px 0;
        }
        
        .nav-link:hover {
            background-color: #f7f8f9;
            color: #ff9900;
            border-left-color: #ff9900;
        }
        
        .nav-link.active {
            background-color: #fff3e0;
            color: #ff9900;
            border-left-color: #ff9900;
            font-weight: 600;
        }
        
        .nav-link i {
            width: 20px;
            margin-right: 14px;
            font-size: 15px;
            color: #666;
            text-align: center;
        }
        
        .nav-link:hover i,
        .nav-link.active i {
            color: #ff9900;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        
        .top-bar {
            background: #fff;
            padding: 16px 32px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent-color), #ff6b00);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 600;
            font-size: 14px;
        }
        
        .content-area {
            padding: 32px;
        }
        
        .logout-btn {
            background: #fff;
            border: 1px solid #d5d9d9;
            color: #232f3e;
            padding: 10px 20px;
            border-radius: 4px;
            transition: all 0.2s ease;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
        }
        
        .logout-btn:hover {
            background: #f7f8f9;
            border-color: #ff9900;
            color: #ff9900;
        }
        
        .logout-btn i {
            font-size: 14px;
        }
        
        .sidebar-footer {
            position: sticky;
            bottom: 0;
            width: 100%;
            padding: 16px 20px;
            border-top: 1px solid #e3e6e8;
            background: #fff;
        }
        
        /* Global Component Styles */
        .content-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid #e3e6e8;
            margin-bottom: 24px;
        }
        
        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e3e6e8;
            background: #fafafa;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #232f3e;
            margin: 0;
        }
        
        .card-body {
            padding: 24px;
        }
        
        /* Table Styles */
        .table-custom {
            width: 100%;
        }
        
        .table-custom thead th {
            padding: 16px 24px;
            font-size: 12px;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e3e6e8;
            background: #fafafa;
        }
        
        .table-custom tbody td {
            padding: 16px 24px;
            border-bottom: 1px solid #f0f0f0;
            color: #232f3e;
            font-size: 14px;
        }
        
        .table-custom tbody tr:hover {
            background-color: #f9f9f9;
        }
        
        /* Badge Styles */
        .badge-custom {
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }
        
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-preparing { background: #cfe2ff; color: #084298; }
        .badge-ready { background: #d1e7dd; color: #0f5132; }
        .badge-served { background: #e7d6ff; color: #6f42c1; }
        .badge-paid { background: #15ff007d; color: #011802; }
        
        /* Button Styles */
        .btn-primary {
            background: #ff9900;
            border: 1px solid #ff9900;
            color: #fff;
            padding: 10px 24px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-primary:hover {
            background: #ec8b00;
            border-color: #ec8b00;
        }
        
        .btn-secondary {
            background: #fff;
            border: 1px solid #d5d9d9;
            color: #232f3e;
            padding: 10px 24px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-secondary:hover {
            background: #f7f7f7;
        }
        
        .btn-success {
            background: #067d62;
            border: 1px solid #067d62;
            color: #fff;
            padding: 10px 24px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-success:hover {
            background: #055a47;
        }
        
        .btn-danger {
            background: #d13212;
            border: 1px solid #d13212;
            color: #fff;
            padding: 10px 24px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-danger:hover {
            background: #b02a0f;
        }
        
        /* Form Styles */
        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #232f3e;
            margin-bottom: 8px;
            display: block;
        }
        
        .form-control, .form-select {
            width: 100%;
            border: 1px solid #d5d9d9;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #ff9900;
            box-shadow: 0 0 0 3px rgba(255,153,0,0.1);
            outline: none;
        }
        
        /* Alert Styles */
        .alert {
            padding: 16px 20px;
            border-radius: 4px;
            margin-bottom: 24px;
        }
        
        .alert-success {
            background: #d1e7dd;
            border: 1px solid #badbcc;
            color: #0f5132;
        }
        
        .alert-danger {
            background: #f8d7da;
            border: 1px solid #f5c2c7;
            color: #842029;
        }
        
        .alert-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        
        .alert-warning {
            background: #fff3cd;
            border: 1px solid #ffecb5;
            color: #856404;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h1 class="sidebar-brand">Swad Sangam</h1>
            <p class="sidebar-subtitle">{{ ucfirst(auth()->user()->role) }} Panel</p>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Main Menu</div>
            <a href="/admin/dashboard" class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="/admin/tables" class="nav-link {{ request()->is('admin/tables*') ? 'active' : '' }}">
                <i class="fas fa-table"></i> Tables
            </a>
            <a href="/admin/menu" class="nav-link {{ request()->is('admin/menu') ? 'active' : '' }}">
                <i class="fas fa-utensils"></i> Menu Items
            </a>
            <a href="/admin/cook" class="nav-link {{ request()->is('admin/cook*') ? 'active' : '' }}">
                <i class="fas fa-fire-burner"></i> Manage Orders
            </a>
            <a href="/admin/employees" class="nav-link {{ request()->is('admin/employees*') ? 'active' : '' }}">
                <i class="fas fa-users"></i> Employees
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Analytics & Reports</div>
            <a href="/admin/reports" class="nav-link {{ request()->is('admin/reports*') ? 'active' : '' }}">
                <i class="fas fa-file-chart-line"></i> Reports
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Settings</div>
            <a href="/admin/telegram" class="nav-link {{ request()->is('admin/telegram*') ? 'active' : '' }}">
                <i class="fab fa-telegram"></i> Telegram
            </a>
            <a href="/profile" class="nav-link {{ request()->is('profile*') ? 'active' : '' }}">
                <i class="fas fa-user-circle"></i> Profile
            </a>
            <a href="/admin/categories" class="nav-link {{ request()->is('admin/categories*') ? 'active' : '' }}">
                <i class="fas fa-layer-group"></i> Table Categories
            </a>
            <a href="/admin/menu-categories" class="nav-link {{ request()->is('admin/menu-categories*') ? 'active' : '' }}">
                <i class="fas fa-tags"></i> Menu Categories
            </a>
        </div>
        
        <div class="sidebar-footer">
            <form action="/logout" method="POST">
                @csrf
                <button type="submit" class="logout-btn w-100">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="top-bar">
            <div>
                <h5 class="mb-0" style="color: var(--primary-color); font-weight: 600;">@yield('title', 'Dashboard')</h5>
            </div>
            <div class="user-info">
                <div>
                    <div style="font-size: 14px; font-weight: 600; color: var(--primary-color);">{{ auth()->user()->name }}</div>
                    <div style="font-size: 12px; color: #666;">{{ auth()->user()->email }}</div>
                </div>
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            </div>
        </div>
        
        <div class="content-area">
            @yield('content')
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
