<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'FirmwareHub - Download mobile firmware, ROMs, and tools')">
    <meta name="keywords" content="@yield('meta_keywords', 'firmware, mobile, ROM, download, tools')">
    <title>@yield('title', 'Dashboard') - FirmwareHub</title>
    
    <!-- Preconnect for faster loading -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --dark-color: #1a202c;
            --light-bg: #f7fafc;
        }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--light-bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar-brand { font-weight: bold; color: var(--primary-color) !important; }
        .main-content { flex: 1; padding: 20px 0; }
        .sidebar { background: white; border-radius: 10px; padding: 20px; margin-bottom: 20px; }
        .card { border: none; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .btn-primary { background: var(--primary-color); border: none; }
        .btn-primary:hover { background: #5a67d8; }
        .badge-subscription { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); }
        .nav-link.active { color: var(--primary-color) !important; font-weight: 500; }
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            .sidebar { margin-bottom: 15px; padding: 15px; }
            .main-content { padding: 15px 0; }
        }
        
        /* Loading spinner */
        .spinner-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255,255,255,0.9); z-index: 9999;
            display: none; align-items: center; justify-content: center;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Loading Spinner -->
    <div class="spinner-overlay" id="loadingSpinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Navbar -->
    @include('components.navbar')
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Alert Messages -->
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
            
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            <div class="row">
                <!-- Sidebar (if needed) -->
                @hasSection('sidebar')
                    <div class="col-lg-3 col-md-4">
                        <aside class="sidebar">
                            @yield('sidebar')
                        </aside>
                    </div>
                    <div class="col-lg-9 col-md-8">
                        @yield('content')
                    </div>
                @else
                    <div class="col-12">
                        @yield('content')
                    </div>
                @endif
            </div>
        </div>
    </main>
    
    <!-- Footer -->
    @include('components.footer')
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Common JS -->
    <script>
        // Show loading spinner for AJAX requests
        function showLoading() {
            document.getElementById('loadingSpinner').style.display = 'flex';
        }
        function hideLoading() {
            document.getElementById('loadingSpinner').style.display = 'none';
        }
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Tooltip initialization
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
    
    @stack('scripts')
</body>
</html>