<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - FirmwareHub</title>
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .verify-container { max-width: 500px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
        .btn-primary { background: #667eea; border: none; padding: 12px; }
        .btn-primary:hover { background: #5a67d8; }
        .email-icon { font-size: 4rem; color: #667eea; }
    </style>
</head>
<body class="d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6">
                <div class="verify-container mx-auto">
                    <!-- Logo -->
                    <div class="text-center mb-4">
                        <h1 class="text-white fw-bold">
                            <i class="bi bi-cpu-fill"></i> FirmwareHub
                        </h1>
                    </div>
                    
                    <!-- Verify Card -->
                    <div class="card">
                        <div class="card-body p-4 p-md-5 text-center">
                            <!-- Email Icon -->
                            <div class="email-icon mb-4">
                                <i class="bi bi-envelope-check"></i>
                            </div>
                            
                            <h3 class="card-title mb-3">Verify Your Email Address</h3>
                            
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show text-start" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                            
                            @if(session('warning'))
                                <div class="alert alert-warning alert-dismissible fade show text-start" role="alert">
                                    {{ session('warning') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                            
                            @if(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show text-start" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                            
                            <p class="text-muted mb-4">
                                We've sent a verification email to <strong>{{ session('user_email') ?? 'your email address' }}</strong>. 
                                Please check your inbox and click the verification link to activate your account.
                            </p>
                            
                            <div class="bg-light rounded p-3 mb-4">
                                <h6 class="fw-bold mb-2">Didn't receive the email?</h6>
                                <ul class="text-start text-muted small mb-0">
                                    <li>Check your spam or junk folder</li>
                                    <li>Make sure you entered the correct email</li>
                                    <li>Wait a few minutes and try again</li>
                                </ul>
                            </div>
                            
                            <!-- Resend Form -->
                            <form method="POST" action="{{ route('verification.resend') }}" class="mb-3">
                                @csrf
                                <input type="hidden" name="email" value="{{ session('user_email') }}">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-arrow-clockwise me-2"></i> Resend Verification Email
                                </button>
                            </form>
                            
                            <!-- Additional Actions -->
                            <div class="d-grid gap-2">
                                <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-box-arrow-in-right me-2"></i> Back to Login
                                </a>
                                
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-link text-muted text-decoration-none w-100">
                                        Use a different email address
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Help Text -->
                    <div class="text-center mt-4">
                        <p class="text-white-50 small">
                            Need help? <a href="{{ route('contact') }}" class="text-white">Contact Support</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>