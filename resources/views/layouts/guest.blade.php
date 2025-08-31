<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'FirmwareHub - Your trusted source for mobile firmware downloads')">
    <meta name="keywords" content="@yield('meta_keywords', 'firmware, mobile, ROM, download, Android, iOS')">
    <title>@yield('title', 'Welcome') - FirmwareHub</title>
    
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
            background-color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar { background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .navbar-brand { font-weight: bold; color: var(--primary-color) !important; }
        .main-content { flex: 1; }
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        .btn-primary { background: var(--primary-color); border: none; }
        .btn-primary:hover { background: #5a67d8; }
        .btn-outline-primary { color: var(--primary-color); border-color: var(--primary-color); }
        .btn-outline-primary:hover { background: var(--primary-color); border-color: var(--primary-color); }
        .feature-card {
            border: none;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            transition: transform 0.3s;
        }
        .feature-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .feature-icon { font-size: 3rem; color: var(--primary-color); margin-bottom: 20px; }
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            .hero-section { padding: 50px 0; }
            .hero-section h1 { font-size: 2rem; }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Guest Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="bi bi-cpu-fill"></i> FirmwareHub
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarGuest">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarGuest">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="/"><i class="bi bi-house"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/products"><i class="bi bi-grid"></i> Browse</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/packages"><i class="bi bi-box"></i> Packages</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/about"><i class="bi bi-info-circle"></i> About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/contact"><i class="bi bi-envelope"></i> Contact</a>
                    </li>
                </ul>
                <div class="d-flex">
                    @if(\App\Helpers\AuthHelper::check())
                        <a href="/account" class="btn btn-outline-primary me-2">
                            <i class="bi bi-person-circle"></i> Dashboard
                        </a>
                        <form method="POST" action="/logout" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-secondary">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </button>
                        </form>
                    @else
                        <a href="/login" class="btn btn-outline-primary me-2">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                        <a href="/register" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i> Sign Up
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Alert Messages -->
        @if(session('success'))
            <div class="container mt-3">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif
        
        @if(session('error'))
            <div class="container mt-3">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif
        
        @yield('content')
    </main>
    
    <!-- Footer -->
    @include('components.footer')
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Common JS -->
    <script>
        // Auto-hide alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
    
    @stack('scripts')
</body>
</html>