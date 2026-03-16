<!DOCTYPE html>
<html lang="en" style="height:100%;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ $tenant->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sidebar-w:    240px;
            --topbar-h:     56px;
            --primary:      #1e3a5f;
            --primary-dark: #152b47;
            --primary-mid:  #2a4f7c;
            --accent:       #3b82f6;
            --accent-h:     #2563eb;
            --bg:           #f0f4f8;
            --surface:      #ffffff;
            --border:       #dde3ec;
            --text:         #1e293b;
            --muted:        #64748b;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            height: 100dvh;
            overflow: hidden;
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        /* ── Bootstrap modal overrides ── */
        body.modal-open { overflow: hidden !important; }
        .modal { overflow-x: hidden; overflow-y: auto; }
        .modal-backdrop { z-index: 1040 !important; }
        .modal { z-index: 1050 !important; }
        .modal-dialog { z-index: 1060 !important; }
        .modal .btn-secondary { background:#6c757d; border:1px solid #6c757d; color:#fff; padding:6px 16px; font-size:14px; }
        .modal .btn-secondary:hover { background:#5c636a; border-color:#565e64; color:#fff; }
        .modal .btn-success { background:#059669; border:1px solid #059669; color:#fff; padding:6px 16px; font-size:14px; }
        .modal .btn-success:hover { background:#047857; color:#fff; }
        .modal .alert { margin-bottom:0; }
        .modal .alert-success { background:#d1fae5; border-color:#6ee7b7; color:#065f46; }
        .modal .alert-danger  { background:#fee2e2; border-color:#fca5a5; color:#991b1b; }
        .modal .alert-primary { background:#dbeafe; border-color:#93c5fd; color:#1e40af; }
        .modal .alert-warning { background:#fef3c7; border-color:#fcd34d; color:#92400e; }
        .modal .form-control  { display:block; }

        /* ══════════════════════════════
           APP SHELL
        ══════════════════════════════ */
        .app-shell {
            display: flex;
            height: 100vh;
            height: 100dvh;
            width: 100vw;
            overflow: hidden;
        }

        /* ── Sidebar overlay (mobile) ── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 1200;
        }
        .sidebar-overlay.active { display: block; }

        /* ── Sidebar ── */
        .sidebar {
            width: var(--sidebar-w);
            flex-shrink: 0;
            background: var(--primary);
            display: flex;
            flex-direction: column;
            height: 100vh;
            height: 100dvh;
            overflow: hidden;
            z-index: 1210;
            transition: transform 0.25s ease;
        }

        .sidebar-header {
            padding: 20px 18px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .sidebar-brand {
            font-size: 17px;
            font-weight: 800;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-subtitle {
            font-size: 10px;
            color: #93c5fd;
            margin-top: 3px;
            text-transform: uppercase;
            letter-spacing: 1.1px;
            font-weight: 600;
        }
        /* Close button inside sidebar (mobile only) */
        .sidebar-close {
            display: none;
            background: none;
            border: none;
            color: #93c5fd;
            font-size: 20px;
            cursor: pointer;
            padding: 0 4px;
            flex-shrink: 0;
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 8px 0;
        }
        .sidebar-nav::-webkit-scrollbar { width: 3px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: var(--primary-mid); border-radius: 3px; }

        .nav-section { padding: 10px 0 4px; }
        .nav-section-title {
            padding: 0 16px 5px;
            font-size: 9.5px;
            color: #7dd3fc;
            text-transform: uppercase;
            letter-spacing: 1.3px;
            font-weight: 700;
        }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 9px 16px;
            color: #bfdbfe;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: background 0.12s, color 0.12s, border-color 0.12s;
            white-space: nowrap;
        }
        .nav-link i { width: 16px; text-align: center; font-size: 13px; flex-shrink: 0; }
        .nav-link:hover  { background: rgba(255,255,255,0.07); color: #fff; border-left-color: #60a5fa; }
        .nav-link.active { background: rgba(59,130,246,0.22); color: #fff; border-left-color: #3b82f6; font-weight: 600; }
        .nav-link.active i { color: #60a5fa; }

        .sidebar-footer {
            flex-shrink: 0;
            padding: 12px 16px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }
        .logout-btn {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%;
            background: rgba(239,68,68,0.13);
            border: 1px solid rgba(239,68,68,0.28);
            color: #fca5a5;
            padding: 9px 14px;
            border-radius: 8px;
            font-size: 13px; font-weight: 600;
            cursor: pointer;
            transition: background 0.12s, color 0.12s;
        }
        .logout-btn:hover { background: rgba(239,68,68,0.28); color: #fff; }

        /* ── Right pane ── */
        .main-pane {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-width: 0;
        }

        /* ── Top bar ── */
        .top-bar {
            height: var(--topbar-h);
            flex-shrink: 0;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            gap: 12px;
        }
        .top-bar-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }
        /* Hamburger button (hidden on desktop) */
        .hamburger {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            color: var(--primary);
            font-size: 18px;
            flex-shrink: 0;
            transition: background 0.12s;
        }
        .hamburger:hover { background: #f1f5f9; }

        .top-bar-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-chip {
            display: flex; align-items: center; gap: 10px;
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 40px;
            padding: 5px 14px 5px 6px;
            flex-shrink: 0;
        }
        .user-avatar {
            width: 32px; height: 32px; border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--primary));
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 700; font-size: 13px; flex-shrink: 0;
        }
        .user-name  { font-size: 13px; font-weight: 600; color: var(--text); }
        .user-role  { font-size: 11px; color: var(--muted); }
        /* Hide user text on very small screens */
        .user-info  { display: block; }

        /* ── Scrollable content ── */
        .content-area {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 24px;
            min-height: 0;
        }
        .content-area::-webkit-scrollbar { width: 6px; }
        .content-area::-webkit-scrollbar-track { background: transparent; }
        .content-area::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 6px; }
        .content-area::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* ══════════════════════════════
           GLOBAL COMPONENTS
        ══════════════════════════════ */
        .content-card {
            background: var(--surface);
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            border: 1px solid var(--border);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .card-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            background: #f8fafc;
            display: flex; align-items: center; justify-content: space-between;
        }
        .card-title { font-size: 14px; font-weight: 700; color: var(--primary); margin: 0; }
        .card-body  { padding: 20px; }

        .table-custom { width: 100%; border-collapse: collapse; }
        .table-custom thead th {
            padding: 11px 16px;
            font-size: 11px; font-weight: 700;
            color: var(--muted);
            text-transform: uppercase; letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border);
            background: #f8fafc;
            white-space: nowrap;
        }
        .table-custom tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13.5px;
            color: var(--text);
        }
        .table-custom tbody tr:hover { background: #f8fafc; }
        .table-custom tbody tr:last-child td { border-bottom: none; }

        .badge-custom { padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; text-transform:capitalize; display:inline-block; }
        .badge-pending    { background:#fef3c7; color:#92400e; }
        .badge-preparing  { background:#dbeafe; color:#1e40af; }
        .badge-processing { background:#dbeafe; color:#1e40af; }
        .badge-ready      { background:#d1fae5; color:#065f46; }
        .badge-served     { background:#ede9fe; color:#5b21b6; }
        .badge-checkout   { background:#cffafe; color:#164e63; }
        .badge-paid       { background:#dcfce7; color:#14532d; }
        .badge-cancelled  { background:#fee2e2; color:#991b1b; }
        .badge-completed  { background:#dcfce7; color:#14532d; }

        .btn-primary {
            background:var(--accent); border:none; color:#fff;
            padding:9px 20px; border-radius:8px;
            font-weight:600; font-size:13.5px; cursor:pointer;
            transition:background 0.12s;
            display:inline-flex; align-items:center; gap:6px;
            text-decoration:none;
        }
        .btn-primary:hover { background:var(--accent-h); color:#fff; }

        .btn-secondary {
            background:var(--surface); border:1px solid var(--border); color:var(--text);
            padding:9px 20px; border-radius:8px;
            font-weight:600; font-size:13.5px; cursor:pointer;
            transition:all 0.12s;
            display:inline-flex; align-items:center; gap:6px;
            text-decoration:none;
        }
        .btn-secondary:hover { background:#f1f5f9; border-color:#94a3b8; color:var(--text); }

        .btn-success {
            background:#059669; border:none; color:#fff;
            padding:9px 20px; border-radius:8px;
            font-weight:600; font-size:13.5px; cursor:pointer;
            transition:background 0.12s;
            display:inline-flex; align-items:center; gap:6px;
            text-decoration:none;
        }
        .btn-success:hover { background:#047857; color:#fff; }

        .btn-danger {
            background:#dc2626; border:none; color:#fff;
            padding:9px 20px; border-radius:8px;
            font-weight:600; font-size:13.5px; cursor:pointer;
            transition:background 0.12s;
            display:inline-flex; align-items:center; gap:6px;
            text-decoration:none;
        }
        .btn-danger:hover { background:#b91c1c; color:#fff; }

        .form-label { font-size:12.5px; font-weight:600; color:var(--text); margin-bottom:5px; display:block; }
        .form-control, .form-select {
            width:100%;
            border:1px solid var(--border); border-radius:8px;
            padding:8px 12px; font-size:13.5px;
            color:var(--text); background:var(--surface);
            transition:border-color 0.12s, box-shadow 0.12s;
        }
        .form-control:focus, .form-select:focus {
            border-color:var(--accent);
            box-shadow:0 0 0 3px rgba(59,130,246,0.14);
            outline:none;
        }

        .alert { padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:13.5px; }
        .alert-success { background:#f0fdf4; border:1px solid #86efac; color:#166534; }
        .alert-danger  { background:#fef2f2; border:1px solid #fca5a5; color:#991b1b; }
        .alert-info    { background:#eff6ff; border:1px solid #93c5fd; color:#1e40af; }
        .alert-warning { background:#fffbeb; border:1px solid #fcd34d; color:#92400e; }

        .section-title { font-size:18px; font-weight:800; color:var(--primary); margin:0; }
        .empty-state { text-align:center; padding:40px 20px; color:var(--muted); }
        .empty-state i { font-size:40px; color:#cbd5e1; margin-bottom:12px; display:block; }

        /* ══════════════════════════════
           MOBILE RESPONSIVE
        ══════════════════════════════ */
        @media (max-width: 768px) {

            /* Sidebar slides off-screen by default */
            .sidebar {
                position: fixed;
                top: 0; left: 0;
                height: 100vh;
                height: 100dvh;
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .sidebar-close { display: block; }

            /* Hamburger visible */
            .hamburger { display: flex; align-items: center; justify-content: center; }

            /* Top bar tighter */
            .top-bar { padding: 0 14px; }
            .top-bar-title { font-size: 14px; }

            /* Hide user name/role text, show only avatar */
            .user-info { display: none; }
            .user-chip { padding: 4px; border-radius: 50%; }

            /* Content padding smaller */
            .content-area { padding: 14px; }

            /* Tables scroll horizontally */
            .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }

            /* Stack filter rows */
            .row.g-3 > [class*="col-md"] { width: 100% !important; }

            /* Smaller headings */
            h2.fw-bold { font-size: 18px; }

            /* Stat cards full width */
            .col-md-3, .col-md-4, .col-md-6 { width: 100%; }
        }

        @media (max-width: 480px) {
            .top-bar-title { font-size: 13px; }
            .content-area  { padding: 10px; }
            .content-card  { border-radius: 8px; }
        }
    </style>
</head>
<body>

<!-- Sidebar overlay (mobile tap-to-close) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="app-shell">

    <!-- ── Sidebar ── -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div>
                <div class="sidebar-brand">{{ $tenant->name }}</div>
                <div class="sidebar-subtitle">{{ ucfirst(current_user()->role ?? 'Admin') }} Panel</div>
            </div>
            <button class="sidebar-close" id="sidebarClose" aria-label="Close menu">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <a href="/admin/dashboard" class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
                <a href="/admin/cook" class="nav-link {{ request()->is('admin/cook*') ? 'active' : '' }}">
                    <i class="fas fa-fire-burner"></i> Manage Orders
                </a>
                <a href="/admin/tables" class="nav-link {{ request()->is('admin/tables*') ? 'active' : '' }}">
                    <i class="fas fa-table"></i> Tables
                </a>
                <a href="/admin/menu" class="nav-link {{ request()->is('admin/menu') ? 'active' : '' }}">
                    <i class="fas fa-utensils"></i> Menu Items
                </a>
                <a href="/admin/employees" class="nav-link {{ request()->is('admin/employees*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> Employees
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Analytics</div>
                <a href="/admin/reports" class="nav-link {{ request()->is('admin/reports*') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
                <a href="/admin/handover" class="nav-link {{ request()->is('admin/handover*') ? 'active' : '' }}">
                    <i class="fas fa-hand-holding-usd"></i> Cash Handovers
                </a>
                <a href="/admin/menu-ocr" class="nav-link {{ request()->is('admin/menu-ocr*') ? 'active' : '' }}">
                    <i class="fas fa-camera"></i> Menu OCR
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Settings</div>
                <a href="/admin/categories" class="nav-link {{ request()->is('admin/categories*') ? 'active' : '' }}">
                    <i class="fas fa-layer-group"></i> Table Categories
                </a>
                <a href="/admin/menu-categories" class="nav-link {{ request()->is('admin/menu-categories*') ? 'active' : '' }}">
                    <i class="fas fa-tags"></i> Menu Categories
                </a>
                <a href="/admin/telegram" class="nav-link {{ request()->is('admin/telegram*') ? 'active' : '' }}">
                    <i class="fab fa-telegram"></i> Telegram
                </a>
                <a href="/profile" class="nav-link {{ request()->is('profile*') ? 'active' : '' }}">
                    <i class="fas fa-user-circle"></i> Profile
                </a>
            </div>
        </nav>

        <div class="sidebar-footer">
            <form action="/logout" method="POST">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- ── Right pane ── -->
    <div class="main-pane">

        <!-- Top bar -->
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="hamburger" id="hamburger" aria-label="Open menu">
                    <i class="fas fa-bars"></i>
                </button>
                <span class="top-bar-title">@yield('title', 'Dashboard')</span>
            </div>
            <div class="user-chip">
                <div class="user-avatar">{{ strtoupper(substr(current_user()->name ?? 'U', 0, 1)) }}</div>
                <div class="user-info">
                    <div class="user-name">{{ current_user()->name ?? 'User' }}</div>
                    <div class="user-role">{{ ucfirst(current_user()->role ?? 'Admin') }}</div>
                </div>
            </div>
        </header>

        <!-- Scrollable content -->
        <main class="content-area">
            @yield('content')
        </main>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    var sidebar  = document.getElementById('sidebar');
    var overlay  = document.getElementById('sidebarOverlay');
    var hamburger = document.getElementById('hamburger');
    var closeBtn  = document.getElementById('sidebarClose');

    function openSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    hamburger.addEventListener('click', openSidebar);
    closeBtn.addEventListener('click', closeSidebar);
    overlay.addEventListener('click', closeSidebar);

    // Close sidebar on nav link click (mobile)
    sidebar.querySelectorAll('.nav-link').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 768) closeSidebar();
        });
    });
}());
</script>
@stack('scripts')
</body>
</html>
