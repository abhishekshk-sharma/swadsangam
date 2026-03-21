<!DOCTYPE html>
<html lang="en" style="height:100%;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') · {{ $tenant->name ?? 'Restaurant' }}</title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
    <style>
        :root {
            --white: #ffffff; --gray-50: #f9fafb; --gray-100: #f3f4f6; --gray-200: #e5e7eb;
            --gray-300: #d1d5db; --gray-400: #9ca3af; --gray-500: #6b7280; --gray-600: #4b5563;
            --gray-700: #374151; --gray-800: #1f2937; --gray-900: #111827;
            --blue-50: #eff6ff; --blue-100: #dbeafe; --blue-200: #bfdbfe; --blue-500: #3b82f6;
            --blue-600: #2563eb; --blue-700: #1d4ed8;
            --success: #059669; --success-light: #ecfdf5; --warning: #d97706; --warning-light: #fffbeb;
            --error: #dc2626; --error-light: #fef2f2; --info: #2563eb; --info-light: #eff6ff;
            --sidebar-width: 260px; --header-height: 64px; --card-radius: 1rem; --button-radius: 0.5rem;
            --shadow-xs: 0 1px 2px 0 rgba(0,0,0,.05); --shadow-sm: 0 1px 3px 0 rgba(0,0,0,.1);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,.1); --shadow-lg: 0 10px 15px -3px rgba(0,0,0,.1);
            --transition: 200ms cubic-bezier(.4,0,.2,1);
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100vh; overflow: hidden; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: var(--gray-50); color: var(--gray-900); -webkit-font-smoothing: antialiased; }
        .app-shell { display: flex; height: 100vh; width: 100vw; overflow: hidden; }
        .scrollbar { scrollbar-width: thin; scrollbar-color: var(--gray-300) transparent; }
        .scrollbar::-webkit-scrollbar { width: 4px; } .scrollbar::-webkit-scrollbar-thumb { background: var(--gray-300); border-radius: 20px; }
        .sidebar { width: var(--sidebar-width); background: var(--white); border-right: 1px solid var(--gray-200); display: flex; flex-direction: column; height: 100vh; transition: transform var(--transition); z-index: 30; box-shadow: var(--shadow-xs); }
        .sidebar-header { padding: 1.25rem 1rem; border-bottom: 1px solid var(--gray-200); height: var(--header-height); display: flex; align-items: center; }
        .brand { display: flex; align-items: center; gap: .75rem; }
        .brand-icon { width: 32px; height: 32px; background: var(--blue-50); border: 1px solid var(--blue-200); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--blue-600); font-weight: 600; }
        .brand-name { font-weight: 600; font-size: .95rem; color: var(--gray-900); }
        .brand-role { font-size: .7rem; color: var(--gray-500); text-transform: uppercase; letter-spacing: .3px; }
        .sidebar-nav { flex: 1; overflow-y: auto; padding: 1rem 0; }
        .nav-section { margin-bottom: 1rem; }
        .nav-section-title { padding: .5rem 1rem; font-size: .65rem; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; color: var(--gray-400); }
        .nav-link { display: flex; align-items: center; gap: .75rem; padding: .5rem 1rem; margin: 2px .5rem; border-radius: .5rem; color: var(--gray-600); text-decoration: none; font-size: .9rem; font-weight: 500; transition: all var(--transition); }
        .nav-link i { width: 20px; text-align: center; color: var(--gray-400); transition: color var(--transition); }
        .nav-link:hover { background: var(--gray-50); color: var(--gray-900); }
        .nav-link:hover i { color: var(--blue-500); }
        .nav-link.active { background: var(--blue-50); color: var(--blue-700); }
        .nav-link.active i { color: var(--blue-500); }
        .nav-badge { margin-left: auto; background: var(--gray-100); color: var(--gray-600); font-size: .6rem; font-weight: 600; padding: .15rem .5rem; border-radius: 20px; border: 1px solid var(--gray-200); }
        .sidebar-footer { padding: 1rem; border-top: 1px solid var(--gray-200); }
        .logout-btn { display: flex; align-items: center; gap: .75rem; width: 100%; padding: .5rem .75rem; border-radius: .5rem; background: var(--white); border: 1px solid var(--gray-200); color: var(--gray-600); font-size: .9rem; font-weight: 500; cursor: pointer; transition: all var(--transition); }
        .logout-btn:hover { background: var(--error-light); border-color: var(--error); color: var(--error); }
        .main-pane { flex: 1; display: flex; flex-direction: column; overflow: hidden; background: var(--gray-50); }
        .top-bar { height: var(--header-height); background: var(--white); border-bottom: 1px solid var(--gray-200); display: flex; align-items: center; justify-content: space-between; padding: 0 1.5rem; }
        .top-bar-left { display: flex; align-items: center; gap: 1rem; }
        .hamburger { display: none; width: 40px; height: 40px; border: none; background: transparent; border-radius: .5rem; color: var(--gray-500); font-size: 1.25rem; cursor: pointer; align-items: center; justify-content: center; }
        .page-title { font-size: 1.1rem; font-weight: 600; color: var(--gray-800); }
        .user-menu { display: flex; align-items: center; gap: 1rem; }
        .user-profile { display: flex; align-items: center; gap: .75rem; padding: .25rem .5rem; border-radius: 2rem; cursor: pointer; transition: background var(--transition); }
        .user-profile:hover { background: var(--gray-100); }
        .user-avatar { width: 36px; height: 36px; border-radius: 36px; background: var(--blue-100); border: 2px solid var(--white); box-shadow: var(--shadow-xs); display: flex; align-items: center; justify-content: center; color: var(--blue-600); font-weight: 600; font-size: .9rem; }
        .user-name { font-size: .9rem; font-weight: 500; color: var(--gray-700); }
        .user-email { font-size: .7rem; color: var(--gray-500); }
        .content-area { flex: 1; overflow-y: auto; padding: 1.5rem; }
        .content-card { background: var(--white); border: 1px solid var(--gray-200); border-radius: var(--card-radius); margin-bottom: 1.25rem; overflow: hidden; }
        .card-header { padding: 1rem 1.5rem; border-bottom: 1px solid var(--gray-200); background: var(--white); display: flex; align-items: center; justify-content: space-between; }
        .card-title { font-size: .95rem; font-weight: 600; color: var(--gray-800); display: flex; align-items: center; gap: .5rem; }
        .card-title i { color: var(--blue-500); }
        .card-body { padding: 1.5rem; }
        .table { width: 100%; border-collapse: collapse; }
        .table th { padding: .75rem 1rem; font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; color: var(--gray-500); background: var(--gray-50); border-bottom: 1px solid var(--gray-200); text-align: left; }
        .table td { padding: 1rem; font-size: .9rem; color: var(--gray-600); border-bottom: 1px solid var(--gray-100); }
        .table tbody tr:hover td { background: var(--gray-50); }
        .badge { display: inline-flex; align-items: center; padding: .2rem .75rem; border-radius: 20px; font-size: .7rem; font-weight: 500; border: 1px solid transparent; }
        .badge-success { background: var(--success-light); border-color: var(--success); color: var(--success); }
        .badge-warning { background: var(--warning-light); border-color: var(--warning); color: var(--warning); }
        .badge-error { background: var(--error-light); border-color: var(--error); color: var(--error); }
        .badge-info { background: var(--info-light); border-color: var(--info); color: var(--info); }
        .badge-neutral { background: var(--gray-100); border-color: var(--gray-300); color: var(--gray-600); }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; padding: .5rem 1rem; border-radius: var(--button-radius); font-size: .9rem; font-weight: 500; cursor: pointer; transition: all var(--transition); border: 1px solid transparent; text-decoration: none; }
        .btn-primary { background: var(--blue-600); color: white; } .btn-primary:hover { background: var(--blue-700); }
        .btn-secondary { background: var(--white); border-color: var(--gray-300); color: var(--gray-700); } .btn-secondary:hover { background: var(--gray-50); color: rgb(0, 9, 112);}
        .btn-sm { padding: .25rem .75rem; font-size: .8rem; }
        .form-group { margin-bottom: 1rem; }
        .form-label { display: block; margin-bottom: .5rem; font-size: .8rem; font-weight: 500; color: var(--gray-600); }
        .form-control, .form-select { width: 100%; padding: .6rem .75rem; border: 1px solid var(--gray-300); border-radius: var(--button-radius); font-size: .9rem; color: var(--gray-800); background: var(--white); transition: all var(--transition); }
        .form-control:focus, .form-select:focus { outline: none; border-color: var(--blue-400); box-shadow: 0 0 0 3px var(--blue-100); }
        .alert { padding: .75rem 1rem; border-radius: var(--button-radius); margin-bottom: 1rem; font-size: .9rem; border: 1px solid transparent; }
        .alert-success { background: var(--success-light); border-color: var(--success); color: var(--success); }
        .alert-error { background: var(--error-light); border-color: var(--error); color: var(--error); }
        .alert-warning { background: var(--warning-light); border-color: var(--warning); color: var(--warning); }
        .alert-info { background: var(--info-light); border-color: var(--info); color: var(--info); }
        .empty-state { text-align: center; padding: 2.5rem; }
        .empty-state i { font-size: 3rem; color: var(--gray-300); margin-bottom: 1rem; }
        @media (max-width: 768px) {
            .sidebar { position: fixed; transform: translateX(-100%); box-shadow: var(--shadow-lg); }
            .sidebar.open { transform: translateX(0); }
            .hamburger { display: flex; }
            .content-area { padding: 1rem; margin-bottom: 5rem;
                padding-bottom: 5rem;}
            .user-details { display: none; }
        }
        .d-flex { display: flex; } .align-center { align-items: center; } .justify-between { justify-content: space-between; }
        .gap-2 { gap: .5rem; } .gap-4 { gap: 1rem; } .mb-4 { margin-bottom: 1rem; } .mt-4 { margin-top: 1rem; }
        .text-right { text-align: right; } .text-muted { color: var(--gray-500); } .w-100 { width: 100%; }
    </style>
    @stack('styles')
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.3);z-index:25;"></div>

<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="brand">
                <div class="brand-icon"><i class="fas fa-store"></i></div>
                <div>
                    <div class="brand-name">{{ $tenant->name ?? 'Restaurant' }}</div>
                    @php $authUser = auth()->guard('employee')->user(); @endphp
                    <div class="brand-role">Manager</div>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav scrollbar">
            @php
                $navGroups = [
                    'Overview' => [
                        ['icon' => 'fa-chart-pie',      'label' => 'Dashboard', 'route' => 'manager.dashboard'],
                    ],
                    'Operations' => [
                        ['icon' => 'fa-clipboard-list', 'label' => 'Orders',       'route' => 'manager.cook.index',             'badge' => $pendingOrders ?? 0],
                        ['icon' => 'fa-utensils',       'label' => 'Tables',       'route' => 'manager.tables.index'],
                        
                        ['icon' => 'fa-book-open',      'label' => 'Menu',         'route' => 'manager.menu.index'],
                        
                        ['icon' => 'fa-users',          'label' => 'Staff',        'route' => 'manager.staff.index'],
                    ],
                    'Reports' => [
                        ['icon' => 'fa-chart-bar',      'label' => 'Reports',   'route' => 'manager.reports.index'],
                        ['icon' => 'fa-cash-register',  'label' => 'Handover',  'route' => 'manager.handover.index'],
                    ],
                    'Settings' => [
                        ['icon' => 'fa-layer-group',    'label' => 'Table Cats',   'route' => 'manager.table-categories.index'],
                        ['icon' => 'fa-tags',           'label' => 'Menu Cats',    'route' => 'manager.menu-categories.index'],
                        ['icon' => 'fa-camera',         'label' => 'Menu OCR',     'route' => 'manager.menu-ocr.index'],
                        ['icon' => 'fa-user-circle',    'label' => 'Profile',   'route' => 'profile.show'],
                    ],
                ];
            @endphp

            @foreach($navGroups as $groupName => $links)
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
            <form action="/logout" method="POST" onsubmit="return confirm('Sign out?');">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Sign out</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="main-pane">
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="hamburger" id="hamburger" aria-label="Menu"><i class="fas fa-bars"></i></button>
                <h1 class="page-title">@yield('title', 'Dashboard')</h1>
                @php $branch = $authUser->branch_id ? \App\Models\Branch::find($authUser->branch_id) : null; @endphp
                @if($branch)
                    <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;background:var(--blue-50);border:1px solid var(--blue-200);border-radius:20px;font-size:0.78rem;font-weight:600;color:var(--blue-700);">
                        <i class="fas fa-store" style="font-size:0.75rem;"></i>
                        {{ $branch->name }}
                    </span>
                @endif
            </div>
            <div class="user-menu">
                <div class="user-profile">
                    <div class="user-avatar">{{ strtoupper(substr($authUser->name ?? 'M', 0, 1)) }}</div>
                    <div class="user-details">
                        <div class="user-name">{{ $authUser->name ?? 'Manager' }}</div>
                        <div class="user-email">{{ $authUser->email ?? '' }}</div>
                    </div>
                </div>
            </div>
        </header>

        <main class="content-area scrollbar">
            @if(session('success'))
                <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}</div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}</div>
            @endif
            @if(session('info'))
                <div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>{{ session('info') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script>
(function() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const hamburger = document.getElementById('hamburger');
    function toggleSidebar() {
        if (window.innerWidth > 768) return;
        const open = sidebar.classList.contains('open');
        sidebar.classList.toggle('open', !open);
        overlay.style.display = open ? 'none' : 'block';
    }
    if (hamburger) hamburger.addEventListener('click', toggleSidebar);
    if (overlay) overlay.addEventListener('click', toggleSidebar);
    window.addEventListener('resize', () => { if (window.innerWidth > 768) { sidebar.classList.remove('open'); overlay.style.display = 'none'; } });
})();
</script>
<script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
