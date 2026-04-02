<!DOCTYPE html>
<html lang="en" style="height:100%;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') · {{ $tenant->name ?? 'Restaurant' }}</title>
    
    <!-- Core CSS -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
    
    <!-- Professional Light Theme CSS -->
    <style>
        /* Professional Light Theme - No Gradients, Clean & Minimal */
        :root {
            /* Neutral Palette - Sophisticated Grays */
            --white: #ffffff;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            
            /* Accent Colors - Professional Blues */
            --blue-50: #eff6ff;
            --blue-100: #dbeafe;
            --blue-200: #bfdbfe;
            --blue-300: #93c5fd;
            --blue-400: #60a5fa;
            --blue-500: #3b82f6;
            --blue-600: #2563eb;
            --blue-700: #1d4ed8;
            
            /* Semantic Colors - Muted & Professional */
            --success: #059669;
            --success-light: #ecfdf5;
            --warning: #d97706;
            --warning-light: #fffbeb;
            --error: #dc2626;
            --error-light: #fef2f2;
            --info: #2563eb;
            --info-light: #eff6ff;
            
            /* Spacing */
            --space-1: 0.25rem;
            --space-2: 0.5rem;
            --space-3: 0.75rem;
            --space-4: 1rem;
            --space-5: 1.25rem;
            --space-6: 1.5rem;
            --space-8: 2rem;
            --space-10: 2.5rem;
            
            /* Layout */
            --sidebar-width: 260px;
            --header-height: 64px;
            --card-radius: 1rem;
            --button-radius: 0.5rem;
            
            /* Shadows - Subtle & Professional */
            --shadow-xs: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
            
            /* Transitions */
            --transition: 200ms cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Reset & Base */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100vh;
            height: 100dvh;
            overflow: hidden;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--gray-50);
            color: var(--gray-900);
            -webkit-font-smoothing: antialiased;
        }

        /* Font */
        @font-face {
            font-family: 'Inter';
            font-style: normal;
            font-weight: 400 600 700;
            font-display: swap;
            src: url(https://fonts.gstatic.com/s/inter/v12/UcC73FwrK3iLTeHuS_fvQtMwCp50KnMa1ZL7.woff2) format('woff2');
        }

        /* Layout */
        .app-shell {
            display: flex;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
        }

        /* Scrollbar */
        .scrollbar {
            scrollbar-width: thin;
            scrollbar-color: var(--gray-300) transparent;
        }
        
        .scrollbar::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        
        .scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .scrollbar::-webkit-scrollbar-thumb {
            background: var(--gray-300);
            border-radius: 20px;
        }
        
        .scrollbar::-webkit-scrollbar-thumb:hover {
            background: var(--gray-400);
        }

        /* ===== SIDEBAR - Light & Professional ===== */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--white);
            border-right: 1px solid var(--gray-200);
            display: flex;
            flex-direction: column;
            height: 100vh;
            transition: transform var(--transition);
            z-index: 30;
            box-shadow: var(--shadow-xs);
        }

        .sidebar-header {
            padding: var(--space-5) var(--space-4);
            border-bottom: 1px solid var(--gray-200);
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: var(--space-3);
        }

        .brand-icon {
            width: 32px;
            height: 32px;
            background: var(--blue-50);
            border: 1px solid var(--blue-200);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--blue-600);
            font-weight: 600;
            font-size: 1rem;
        }

        .brand-info {
            line-height: 1.3;
        }

        .brand-name {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--gray-900);
        }

        .brand-role {
            font-size: 0.7rem;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Navigation */
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: var(--space-4) 0;
        }

        .nav-section {
            margin-bottom: var(--space-4);
        }

        .nav-section-title {
            padding: var(--space-2) var(--space-4);
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--gray-400);
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-2) var(--space-4);
            margin: 2px var(--space-2);
            border-radius: 0.5rem;
            color: var(--gray-600);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all var(--transition);
            position: relative;
        }

        .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 1rem;
            color: var(--gray-400);
            transition: color var(--transition);
        }

        .nav-link:hover {
            background: var(--gray-50);
            color: var(--gray-900);
        }

        .nav-link:hover i {
            color: var(--blue-500);
        }

        .nav-link.active {
            background: var(--blue-50);
            color: var(--blue-700);
        }

        .nav-link.active i {
            color: var(--blue-500);
        }

        .nav-badge {
            margin-left: auto;
            background: var(--gray-100);
            color: var(--gray-600);
            font-size: 0.6rem;
            font-weight: 600;
            padding: 0.15rem 0.5rem;
            border-radius: 20px;
            border: 1px solid var(--gray-200);
        }

        .nav-link.active .nav-badge {
            background: var(--white);
            border-color: var(--blue-200);
            color: var(--blue-600);
        }

        .sidebar-footer {
            padding: var(--space-4);
            border-top: 1px solid var(--gray-200);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            width: 100%;
            padding: var(--space-2) var(--space-3);
            border-radius: 0.5rem;
            background: var(--white);
            border: 1px solid var(--gray-200);
            color: var(--gray-600);
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition);
        }

        .logout-btn:hover {
            background: var(--error-light);
            border-color: var(--error);
            color: var(--error);
        }

        .logout-btn:hover i {
            color: var(--error);
        }

        /* ===== MAIN CONTENT ===== */
        .main-pane {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: var(--gray-50);
        }

        /* Top Bar */
        .top-bar {
            height: var(--header-height);
            background: var(--white);
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 var(--space-6);
        }

        .top-bar-left {
            display: flex;
            align-items: center;
            gap: var(--space-4);
        }

        .hamburger {
            display: none;
            width: 40px;
            height: 40px;
            border: none;
            background: transparent;
            border-radius: 0.5rem;
            color: var(--gray-500);
            font-size: 1.25rem;
            cursor: pointer;
            transition: background var(--transition);
            align-items: center;
            justify-content: center;
        }

        .hamburger:hover {
            background: var(--gray-100);
        }

        .page-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--gray-800);
            letter-spacing: -0.01em;
        }

        /* User Menu */
        .user-menu {
            display: flex;
            align-items: center;
            gap: var(--space-4);
        }

        .notification-btn {
            width: 40px;
            height: 40px;
            border: none;
            background: var(--gray-50);
            border-radius: 0.5rem;
            color: var(--gray-500);
            font-size: 1.1rem;
            cursor: pointer;
            transition: all var(--transition);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .notification-btn:hover {
            background: var(--gray-100);
            color: var(--gray-700);
        }

        .notification-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 8px;
            height: 8px;
            background: var(--error);
            border: 2px solid var(--white);
            border-radius: 50%;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-1) var(--space-2);
            border-radius: 2rem;
            cursor: pointer;
            transition: background var(--transition);
        }

        .user-profile:hover {
            background: var(--gray-100);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 36px;
            background: var(--blue-100);
            border: 2px solid var(--white);
            box-shadow: var(--shadow-xs);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--blue-600);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .user-details {
            line-height: 1.3;
        }

        .user-name {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--gray-700);
        }

        .user-email {
            font-size: 0.7rem;
            color: var(--gray-500);
        }

        /* Content Area */
        .content-area {
            flex: 1;
            overflow-y: auto;
            padding: var(--space-6);
        }

        /* ===== PROFESSIONAL STAT CARDS ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: var(--space-5);
            margin-bottom: var(--space-6);
        }

        .stat-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--card-radius);
            padding: var(--space-5);
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            transition: all var(--transition);
        }

        .stat-card:hover {
            border-color: var(--blue-200);
            box-shadow: var(--shadow-md);
        }

        .stat-info {
            flex: 1;
        }

        .stat-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--gray-500);
            margin-bottom: var(--space-2);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 600;
            color: var(--gray-900);
            line-height: 1.2;
            margin-bottom: var(--space-1);
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: var(--space-1);
            font-size: 0.7rem;
        }

        .trend-up {
            color: var(--success);
        }

        .trend-down {
            color: var(--error);
        }

        .trend-neutral {
            color: var(--gray-500);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--blue-500);
            font-size: 1.25rem;
            transition: all var(--transition);
        }

        .stat-card:hover .stat-icon {
            background: var(--blue-50);
            border-color: var(--blue-200);
            color: var(--blue-600);
        }

        /* ===== CONTENT CARDS ===== */
        .content-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--card-radius);
            margin-bottom: var(--space-5);
            overflow: hidden;
        }

        .card-header {
            padding: var(--space-4) var(--space-6);
            border-bottom: 1px solid var(--gray-200);
            background: var(--white);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }

        .card-title i {
            color: var(--blue-500);
            font-size: 1rem;
        }

        .card-body {
            padding: var(--space-6);
        }

        /* ===== TABLES ===== */
        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            padding: var(--space-3) var(--space-4);
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--gray-500);
            background: var(--gray-50);
            border-bottom: 1px solid var(--gray-200);
            text-align: left;
            white-space: nowrap;
        }

        .table td {
            padding: var(--space-4);
            font-size: 0.9rem;
            color: var(--gray-600);
            border-bottom: 1px solid var(--gray-100);
        }

        .table tbody tr:hover td {
            background: var(--gray-50);
        }

        /* ===== BADGES ===== */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.75rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
            border: 1px solid transparent;
        }

        .badge-success {
            background: var(--success-light);
            border-color: var(--success);
            color: var(--success);
        }

        .badge-warning {
            background: var(--warning-light);
            border-color: var(--warning);
            color: var(--warning);
        }

        .badge-error {
            background: var(--error-light);
            border-color: var(--error);
            color: var(--error);
        }

        .badge-info {
            background: var(--info-light);
            border-color: var(--info);
            color: var(--info);
        }

        .badge-neutral {
            background: var(--gray-100);
            border-color: var(--gray-300);
            color: var(--gray-600);
        }

        /* ===== BUTTONS ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-2);
            padding: 0.5rem 1rem;
            border-radius: var(--button-radius);
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition);
            border: 1px solid transparent;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--blue-600);
            color: white;
            padding: 10px;
            border-radius: 4px;
        }
        .btn-primary:hover {
            background: var(--blue-700);
        }

        .btn-secondary {
            background: var(--white);
            border-color: var(--gray-300);
            color: var(--gray-700);
        }
        .btn-secondary:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--gray-300);
            color: var(--gray-600);
        }
        .btn-outline:hover {
            background: var(--gray-50);
            border-color: var(--gray-400);
        }

        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.8rem;
        }

        /* ===== FORMS ===== */
        .form-group {
            margin-bottom: var(--space-4);
        }

        .form-label {
            display: block;
            margin-bottom: var(--space-2);
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--gray-600);
        }

        .form-control, .form-select {
            width: 100%;
            padding: 0.6rem 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--button-radius);
            font-size: 0.9rem;
            color: var(--gray-800);
            background: var(--white);
            transition: all var(--transition);
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--blue-400);
            box-shadow: 0 0 0 3px var(--blue-100);
        }

        /* ===== ALERTS ===== */
        .alert {
            padding: var(--space-3) var(--space-4);
            border-radius: var(--button-radius);
            margin-bottom: var(--space-4);
            font-size: 0.9rem;
            border: 1px solid transparent;
        }

        .alert-success {
            background: var(--success-light);
            border-color: var(--success);
            color: var(--success);
        }

        .alert-error {
            background: var(--error-light);
            border-color: var(--error);
            color: var(--error);
        }

        .alert-warning {
            background: var(--warning-light);
            border-color: var(--warning);
            color: var(--warning);
        }

        .alert-info {
            background: var(--info-light);
            border-color: var(--info);
            color: var(--info);
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: var(--space-10);
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--gray-300);
            margin-bottom: var(--space-4);
        }

        .empty-state h4 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: var(--space-2);
        }

        .empty-state p {
            font-size: 0.9rem;
            color: var(--gray-500);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1280px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 1024px) {
            :root {
                --sidebar-width: 240px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                transform: translateX(-100%);
                box-shadow: var(--shadow-lg);
                margin-bottom: 3rem;
                padding-bottom: 5rem;
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .hamburger {
                display: flex;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: var(--space-3);
            }
            
            .content-area {
                padding: var(--space-4);
                margin-bottom: 3rem;
                padding-bottom: 5rem;
            }
            
            .user-details {
                display: none;
            }
            
            .card-header {
                padding: var(--space-3) var(--space-4);
            }
            
            .card-body {
                padding: var(--space-4);
            }
        }

        /* ===== UTILITIES ===== */
        .stat-card-report {
            background: var(--white);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid var(--gray-200);
            text-align: center;
        }
        .stat-card-report h5 {
            font-size: 12px;
            color: var(--gray-500);
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        .stat-card-report h2 {
            font-size: 28px;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0;
        }
        .btn-danger {
            background: var(--error);
            color: #fff;
            border: 1px solid var(--error);
            padding: 6px 14px;
            border-radius: var(--button-radius);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-danger:hover { background: #b91c1c; }

        .d-flex { display: flex; }
        .align-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-2 { gap: var(--space-2); }
        .gap-4 { gap: var(--space-4); }
        .mb-4 { margin-bottom: var(--space-4); }
        .mt-4 { margin-top: var(--space-4); }
        .text-right { text-align: right; }
        .text-muted { color: var(--gray-500); }
        .w-100 { width: 100%; }
    </style>
    
    @stack('styles')
</head>
<body>

<!-- Mobile Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.3); z-index: 25;"></div>

<div class="app-shell">
    
    <!-- SIDEBAR - Light & Professional -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="brand">
                <div class="brand-icon">
                    <i class="fas fa-store"></i>
                </div>
                <div class="brand-info">
                    <div class="brand-name">{{ $tenant->name ?? 'Restaurant' }}</div>
        @php $authUser = auth()->guard('admin')->user() ?? auth()->guard('employee')->user(); @endphp
                    <div class="brand-role">{{ ucfirst($authUser->role ?? 'Admin') }}</div>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav scrollbar">
            @php
                $menuGroups = [
                    'Overview' => [
                        ['icon' => 'fa-chart-pie', 'label' => 'Dashboard', 'route' => 'admin.dashboard', 'badge' => null],
                    ],
                    'Operations' => [
                        ['icon' => 'fa-clipboard-list', 'label' => 'Orders',       'route' => 'admin.cook.index',      'badge' => $pendingOrders ?? 0],
                        ['icon' => 'fa-concierge-bell', 'label' => 'Waiter Panel', 'route' => 'admin.orders.index',    'badge' => null],
                        ['icon' => 'fa-utensils',       'label' => 'Tables',       'route' => 'admin.tables.index',    'badge' => $activeTables ?? 0],
                        ['icon' => 'fa-book-open',      'label' => 'Menu',         'route' => 'admin.menu.index',      'badge' => null],
                        ['icon' => 'fa-user-tie',       'label' => 'Managers',     'route' => 'admin.managers.index',  'badge' => null],
                        ['icon' => 'fa-users',          'label' => 'Staff',        'route' => 'admin.staff.index',     'badge' => null],
                        ['icon' => 'fa-store',          'label' => 'Branches',     'route' => 'admin.branches.index',  'badge' => null],
                    ],
                    'Management' => [
                        ['icon' => 'fa-layer-group', 'label' => 'Categories', 'route' => 'admin.categories.index',      'badge' => null],
                        ['icon' => 'fa-tags',        'label' => 'Menu Cats',  'route' => 'admin.menu-categories.index', 'badge' => null],
                        ['icon' => 'fa-camera',      'label' => 'Menu OCR',   'route' => 'admin.menu-ocr.index',        'badge' => null],
                    ],
                    'Reports' => [
                        ['icon' => 'fa-chart-bar',       'label' => 'Reports',          'route' => 'admin.reports.index',          'badge' => null],
                        ['icon' => 'fa-file-invoice',    'label' => 'Bills',            'route' => 'admin.bills.index',            'badge' => null],
                        ['icon' => 'fa-exchange-alt',    'label' => 'Assignment Logs',  'route' => 'admin.assignment-logs.index',  'badge' => null],
                        ['icon' => 'fa-cash-register',   'label' => 'Handover',         'route' => 'admin.handover.index',         'badge' => null],
                    ],
                    'Settings' => [
                        ['icon' => 'fa-telegram',    'label' => 'Telegram', 'route' => 'admin.telegram.index', 'badge' => null],
                        ['icon' => 'fa-user-circle', 'label' => 'Profile',  'route' => 'profile.show',         'badge' => null],
                    ],
                ];
            @endphp

            @foreach($menuGroups as $groupName => $links)
                <div class="nav-section">
                    <div class="nav-section-title">{{ $groupName }}</div>
                    @foreach($links as $link)
                        <a href="{{ route($link['route']) }}"
                           class="nav-link {{ request()->routeIs($link['route']) ? 'active' : '' }}">
                            <i class="fas {{ $link['icon'] }}"></i>
                            <span>{{ $link['label'] }}</span>
                            @if(isset($link['badge']) && $link['badge'] > 0)
                                <span class="nav-badge">{{ $link['badge'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>

        <div class="sidebar-footer">
            <form action="/logout" method="POST" id="logoutForm" onsubmit="return confirm('Sign out?');">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Sign out</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-pane">
        
        <!-- Top Bar -->
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="hamburger" id="hamburger" aria-label="Menu">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title">@yield('title', 'Dashboard')</h1>
            </div>

            <div class="user-menu">
                <button class="notification-btn" aria-label="Notifications">
                    <i class="far fa-bell"></i>
                    @if(($notifications ?? 0) > 0)
                        <span class="notification-badge"></span>
                    @endif
                </button>
                
                <div class="user-profile">
                    <div class="user-avatar">
                        {{ strtoupper(substr($authUser->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="user-details">
                        <div class="user-name">{{ $authUser->name ?? 'User' }}</div>
                        <div class="user-email">{{ $authUser->email ?? '' }}</div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <main class="content-area scrollbar">
            
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('warning') }}
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ session('info') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<!-- Simple JavaScript -->
<script>
    (function() {
        'use strict';
        
        // Cache DOM elements
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const hamburger = document.getElementById('hamburger');
        
        // Toggle sidebar function
        function toggleSidebar() {
            if (window.innerWidth <= 768) {
                const isOpen = sidebar.classList.contains('open');
                if (isOpen) {
                    sidebar.classList.remove('open');
                    overlay.style.display = 'none';
                    document.body.style.overflow = '';
                } else {
                    sidebar.classList.add('open');
                    overlay.style.display = 'block';
                    document.body.style.overflow = 'hidden';
                }
            }
        }
        
        // Close sidebar function
        function closeSidebar() {
            if (window.innerWidth <= 768 && sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
                overlay.style.display = 'none';
                document.body.style.overflow = '';
            }
        }
        
        // Event listeners
        if (hamburger) {
            hamburger.addEventListener('click', toggleSidebar);
        }
        
        if (overlay) {
            overlay.addEventListener('click', closeSidebar);
        }
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                closeSidebar();
            }
        });
        
        // Close sidebar when clicking a nav link on mobile
        if (sidebar) {
            sidebar.querySelectorAll('.nav-link').forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        setTimeout(closeSidebar, 150);
                    }
                });
            });
        }
        
        // Mark active nav link based on current URL
        function setActiveNavLink() {
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.nav-link');
            
            navLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href && currentPath.startsWith(href) && href !== '/') {
                    link.classList.add('active');
                } else if (href === '/' && currentPath === '/') {
                    link.classList.add('active');
                }
            });
        }
        
        // Call on load
        setActiveNavLink();
        
    })();
</script>

<script>window.ORDER_POLL = { panel: 'admin' };</script>
<script>
// Disable generic order-poll.js on waiter panel — it has its own inline polling
if (window.location.pathname.indexOf('/admin/orders') === 0 && !window.location.pathname.includes('/create')) {
    window.ORDER_POLL = { panel: 'disabled' };
}
</script>
<script src="/js/order-poll.js"></script>
<!-- Bootstrap (deferred) -->
<script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')

<!-- Pass data to JavaScript if needed -->
@isset($chartData)
<script>
    window.chartData = @json($chartData);
</script>
@endisset

</body>
</html>