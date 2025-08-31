<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - FirmwareHub Admin</title>
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Admin Panel CSS -->
    <style>
        :root {
            --admin-primary: #2c3e50;
            --admin-secondary: #34495e;
            --admin-success: #27ae60;
            --admin-danger: #e74c3c;
            --admin-warning: #f39c12;
            --admin-info: #3498db;
        }
        body { background: #ecf0f1; min-height: 100vh; }
        
        /* Sidebar */
        .admin-sidebar {
            min-height: 100vh;
            background: var(--admin-primary);
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
            transition: all 0.3s;
        }
        .admin-sidebar.collapsed { width: 60px; }
        .admin-sidebar .logo {
            padding: 20px;
            background: var(--admin-secondary);
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .admin-sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 15px 20px;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        .admin-sidebar .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: var(--admin-info);
        }
        .admin-sidebar .nav-link.active {
            background: rgba(255,255,255,0.15);
            color: white;
            border-left-color: var(--admin-success);
        }
        .admin-sidebar .nav-link i { width: 20px; margin-right: 10px; }
        .admin-sidebar.collapsed .nav-link span { display: none; }
        
        /* Main Content */
        .admin-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }
        .admin-content.expanded { margin-left: 60px; }
        
        /* Top Bar */
        .admin-topbar {
            background: white;
            padding: 15px 20px;
            margin: -20px -20px 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        /* Cards */
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border-left: 4px solid var(--admin-info);
        }
        .stat-card.success { border-left-color: var(--admin-success); }
        .stat-card.danger { border-left-color: var(--admin-danger); }
        .stat-card.warning { border-left-color: var(--admin-warning); }
        .stat-card h3 { font-size: 2rem; margin: 0; }
        .stat-card p { margin: 5px 0 0 0; color: #7f8c8d; }
        
        /* Tables */
        .admin-table { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .admin-table .table { margin-bottom: 0; }
        .admin-table thead { background: var(--admin-secondary); color: white; }
        
        /* Mobile */
        @media (max-width: 768px) {
            .admin-sidebar { transform: translateX(-100%); }
            .admin-sidebar.show { transform: translateX(0); }
            .admin-content { margin-left: 0 !important; }
            .mobile-toggle { display: block !important; }
        }
        .mobile-toggle { display: none; }
        
        /* Badges */
        .badge-admin-success { background: var(--admin-success); }
        .badge-admin-danger { background: var(--admin-danger); }
        .badge-admin-warning { background: var(--admin-warning); }
        .badge-admin-info { background: var(--admin-info); }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <nav class="admin-sidebar" id="adminSidebar">
        <div class="logo">
            <h5 class="mb-0"><i class="bi bi-gear-fill"></i> <span>Admin Panel</span></h5>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin') ? 'active' : '' }}" href="/admin">
                    <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}" href="/admin/users">
                    <i class="bi bi-people"></i> <span>Users</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/files*') ? 'active' : '' }}" href="/admin/files">
                    <i class="bi bi-file-earmark"></i> <span>Files</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/categories*') ? 'active' : '' }}" href="/admin/categories">
                    <i class="bi bi-tags"></i> <span>Categories</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/packages*') ? 'active' : '' }}" href="/admin/packages">
                    <i class="bi bi-box"></i> <span>Packages</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/subscriptions*') ? 'active' : '' }}" href="/admin/subscriptions">
                    <i class="bi bi-credit-card"></i> <span>Subscriptions</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/orders*') ? 'active' : '' }}" href="/admin/orders">
                    <i class="bi bi-cart"></i> <span>Orders</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/coupons*') ? 'active' : '' }}" href="/admin/coupons">
                    <i class="bi bi-ticket-perforated"></i> <span>Coupons</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/reports*') ? 'active' : '' }}" href="/admin/reports">
                    <i class="bi bi-graph-up"></i> <span>Reports</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/settings*') ? 'active' : '' }}" href="/admin/settings">
                    <i class="bi bi-gear"></i> <span>Settings</span>
                </a>
            </li>
            <li class="nav-item mt-auto">
                <a class="nav-link" href="/" target="_blank">
                    <i class="bi bi-arrow-left"></i> <span>View Site</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- Main Content -->
    <div class="admin-content" id="adminContent">
        <!-- Top Bar -->
        <div class="admin-topbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-link text-dark mobile-toggle" onclick="toggleSidebar()">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <h4 class="mb-0">@yield('page_title', 'Dashboard')</h4>
            </div>
            <div class="d-flex align-items-center">
                <!-- Notifications -->
                <div class="dropdown me-3">
                    <button class="btn btn-link text-dark position-relative" data-bs-toggle="dropdown">
                        <i class="bi bi-bell fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            3
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">Notifications</h6></li>
                        <li><a class="dropdown-item" href="#">New order received</a></li>
                        <li><a class="dropdown-item" href="#">User registration</a></li>
                        <li><a class="dropdown-item" href="#">Low stock alert</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/admin/notifications">View all</a></li>
                    </ul>
                </div>
                
                <!-- User Menu -->
                <div class="dropdown">
                    <button class="btn btn-link text-dark d-flex align-items-center" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle fs-5 me-2"></i>
                        <span>{{ session('user_name', 'Admin') }}</span>
                        <i class="bi bi-chevron-down ms-2"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/admin/profile"><i class="bi bi-person"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="/admin/settings"><i class="bi bi-gear"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="/logout">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        <!-- Page Content -->
        @yield('content')
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Admin JS -->
    <script>
        // Toggle Sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const content = document.getElementById('adminContent');
            sidebar.classList.toggle('collapsed');
            sidebar.classList.toggle('show');
            content.classList.toggle('expanded');
        }
        
        // Auto-hide alerts
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                new bootstrap.Alert(alert).close();
            });
        }, 5000);
    </script>
    
    @stack('scripts')
</body>
</html>