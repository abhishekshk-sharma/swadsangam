<!DOCTYPE html>
<html lang="en" style="height:100%;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Super Admin') · Swad Sangam</title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
    <style>
        :root {
            --white:#fff;--gray-50:#f9fafb;--gray-100:#f3f4f6;--gray-200:#e5e7eb;
            --gray-300:#d1d5db;--gray-400:#9ca3af;--gray-500:#6b7280;--gray-600:#4b5563;
            --gray-700:#374151;--gray-800:#1f2937;--gray-900:#111827;
            --blue-50:#eff6ff;--blue-100:#dbeafe;--blue-200:#bfdbfe;
            --blue-500:#3b82f6;--blue-600:#2563eb;--blue-700:#1d4ed8;
            --success:#059669;--success-light:#ecfdf5;
            --error:#dc2626;--error-light:#fef2f2;
            --warning:#d97706;--warning-light:#fffbeb;
            --sidebar-width:260px;--header-height:64px;--card-radius:1rem;
            --shadow-xs:0 1px 2px rgba(0,0,0,.05);--shadow-md:0 4px 6px -1px rgba(0,0,0,.1);
            --transition:200ms cubic-bezier(.4,0,.2,1);
        }
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
        html,body{height:100vh;overflow:hidden;font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:var(--gray-50);color:var(--gray-900);-webkit-font-smoothing:antialiased;}
        .app-shell{display:flex;height:100vh;width:100vw;overflow:hidden;}
        .scrollbar{scrollbar-width:thin;scrollbar-color:var(--gray-300) transparent;}
        .scrollbar::-webkit-scrollbar{width:4px;}.scrollbar::-webkit-scrollbar-thumb{background:var(--gray-300);border-radius:20px;}

        /* Sidebar */
        .sidebar{width:var(--sidebar-width);background:var(--white);border-right:1px solid var(--gray-200);display:flex;flex-direction:column;height:100vh;z-index:30;box-shadow:var(--shadow-xs);}
        .sidebar-header{padding:1.25rem 1rem;border-bottom:1px solid var(--gray-200);height:var(--header-height);display:flex;align-items:center;}
        .brand{display:flex;align-items:center;gap:.75rem;}
        .brand-icon{width:32px;height:32px;background:#fef3c7;border:1px solid #fde68a;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#d97706;font-weight:700;font-size:1rem;}
        .brand-name{font-weight:700;font-size:.95rem;color:var(--gray-900);}
        .brand-role{font-size:.7rem;color:#d97706;text-transform:uppercase;letter-spacing:.3px;font-weight:600;}
        .sidebar-nav{flex:1;overflow-y:auto;padding:1rem 0;}
        .nav-section{margin-bottom:1rem;}
        .nav-section-title{padding:.5rem 1rem;font-size:.65rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--gray-400);}
        .nav-link{display:flex;align-items:center;gap:.75rem;padding:.5rem 1rem;margin:2px .5rem;border-radius:.5rem;color:var(--gray-600);text-decoration:none;font-size:.9rem;font-weight:500;transition:all var(--transition);}
        .nav-link i{width:20px;text-align:center;color:var(--gray-400);transition:color var(--transition);}
        .nav-link:hover{background:var(--gray-50);color:var(--gray-900);}
        .nav-link:hover i{color:#d97706;}
        .nav-link.active{background:#fffbeb;color:#92400e;}
        .nav-link.active i{color:#d97706;}
        .nav-badge{margin-left:auto;background:var(--gray-100);color:var(--gray-600);font-size:.6rem;font-weight:600;padding:.15rem .5rem;border-radius:20px;border:1px solid var(--gray-200);}
        .sidebar-footer{padding:1rem;border-top:1px solid var(--gray-200);}
        .logout-btn{display:flex;align-items:center;gap:.75rem;width:100%;padding:.5rem .75rem;border-radius:.5rem;background:var(--white);border:1px solid var(--gray-200);color:var(--gray-600);font-size:.9rem;font-weight:500;cursor:pointer;transition:all var(--transition);}
        .logout-btn:hover{background:var(--error-light);border-color:var(--error);color:var(--error);}

        /* Main */
        .main-pane{flex:1;display:flex;flex-direction:column;overflow:hidden;background:var(--gray-50);}
        .top-bar{height:var(--header-height);background:var(--white);border-bottom:1px solid var(--gray-200);display:flex;align-items:center;justify-content:space-between;padding:0 1.5rem;}
        .top-bar-left{display:flex;align-items:center;gap:1rem;}
        .hamburger{display:none;width:40px;height:40px;border:none;background:transparent;border-radius:.5rem;color:var(--gray-500);font-size:1.25rem;cursor:pointer;align-items:center;justify-content:center;}
        .page-title{font-size:1.1rem;font-weight:600;color:var(--gray-800);}
        .user-profile{display:flex;align-items:center;gap:.75rem;padding:.25rem .5rem;border-radius:2rem;cursor:pointer;}
        .user-avatar{width:36px;height:36px;border-radius:36px;background:#fef3c7;border:2px solid var(--white);box-shadow:var(--shadow-xs);display:flex;align-items:center;justify-content:center;color:#d97706;font-weight:700;font-size:.9rem;}
        .user-name{font-size:.9rem;font-weight:500;color:var(--gray-700);}
        .content-area{flex:1;overflow-y:auto;padding:1.5rem;}

        /* Cards & Tables */
        .content-card{background:var(--white);border:1px solid var(--gray-200);border-radius:var(--card-radius);margin-bottom:1.25rem;overflow:hidden;}
        .card-header{padding:1rem 1.5rem;border-bottom:1px solid var(--gray-200);display:flex;align-items:center;justify-content:space-between;}
        .card-title{font-size:.95rem;font-weight:600;color:var(--gray-800);display:flex;align-items:center;gap:.5rem;}
        .card-title i{color:#d97706;}
        .alert{padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;font-size:.9rem;border:1px solid transparent;}
        .alert-success{background:var(--success-light);border-color:var(--success);color:var(--success);}
        .alert-error{background:var(--error-light);border-color:var(--error);color:var(--error);}

        @media(max-width:768px){
            .sidebar{position:fixed;transform:translateX(-100%);box-shadow:var(--shadow-md);}
            .sidebar.open{transform:translateX(0);}
            .hamburger{display:flex;}
            .content-area{padding:1rem;}
            .user-name{display:none;}
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="sidebar-overlay" id="sidebarOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.3);z-index:25;"></div>

<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="brand">
                <div class="brand-icon"><i class="fas fa-crown"></i></div>
                <div>
                    <div class="brand-name">Swad Sangam</div>
                    <div class="brand-role">Super Admin</div>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav scrollbar">
            @php
                $navGroups = [
                    'Overview' => [
                        ['icon'=>'fa-chart-pie',      'label'=>'Dashboard',        'path'=>'superadmin/dashboard'],
                    ],
                    'Platform' => [
                        ['icon'=>'fa-building',       'label'=>'Tenants',          'path'=>'superadmin/tenants'],
                        ['icon'=>'fa-store',          'label'=>'Branches',         'path'=>'superadmin/branches'],
                        ['icon'=>'fa-user-shield',    'label'=>'Restaurant Admins','path'=>'superadmin/users'],
                        ['icon'=>'fa-users',          'label'=>'All Staff',        'path'=>'superadmin/staff'],
                        ['icon'=>'fa-crown',          'label'=>'Super Admins',     'path'=>'superadmin/profile'],
                    ],
                    'Configuration' => [
                        ['icon'=>'fa-layer-group',    'label'=>'Table Categories', 'path'=>'superadmin/table-categories'],
                        ['icon'=>'fa-tags',           'label'=>'Menu Categories',  'path'=>'superadmin/menu-categories'],
                        ['icon'=>'fa-percent',        'label'=>'GST Slabs',        'path'=>'superadmin/gst-slabs'],
                    ],
                    'Reports' => [
                        ['icon'=>'fa-chart-bar',      'label'=>'Reports',          'path'=>'superadmin/reports'],
                    ],
                ];
            @endphp

            @foreach($navGroups as $groupName => $links)
            <div class="nav-section">
                <div class="nav-section-title">{{ $groupName }}</div>
                @foreach($links as $link)
                <a href="/{{ $link['path'] }}"
                   class="nav-link {{ request()->is($link['path'].'*') ? 'active' : '' }}">
                    <i class="fas {{ $link['icon'] }}"></i>
                    <span>{{ $link['label'] }}</span>
                </a>
                @endforeach
            </div>
            @endforeach
        </nav>

        <div class="sidebar-footer">
            <form action="{{ route('superadmin.logout') }}" method="POST" onsubmit="return confirm('Sign out?');">
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
                <button class="hamburger" id="hamburger"><i class="fas fa-bars"></i></button>
                <h1 class="page-title">@yield('title', 'Dashboard')</h1>
            </div>
            <div class="user-profile">
                <div class="user-avatar">{{ strtoupper(substr(Auth::guard('super_admin')->user()->name ?? 'S', 0, 1)) }}</div>
                <span class="user-name">{{ Auth::guard('super_admin')->user()->name ?? 'Super Admin' }}</span>
            </div>
        </header>

        <main class="content-area scrollbar">
            @if(session('success'))
                <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</div>

<script>
(function(){
    var sidebar=document.getElementById('sidebar'),overlay=document.getElementById('sidebarOverlay'),hamburger=document.getElementById('hamburger');
    function toggle(){var open=sidebar.classList.contains('open');sidebar.classList.toggle('open',!open);overlay.style.display=open?'none':'block';}
    if(hamburger)hamburger.addEventListener('click',toggle);
    if(overlay)overlay.addEventListener('click',toggle);
    window.addEventListener('resize',function(){if(window.innerWidth>768){sidebar.classList.remove('open');overlay.style.display='none';}});
})();
</script>
<script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
