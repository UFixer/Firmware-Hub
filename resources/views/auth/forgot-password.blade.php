<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - FirmwareHub</title>
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .forgot-container { max-width: 450px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
        .btn-primary { background: #667eea; border: none; padding: 12px; }
        .btn-primary:hover { background: #5a67d8; }
        .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25); }
        .lock-icon { font-size: 3rem; color: #667eea; }
    </style>
</head>
<body class="d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6">
                <div class="forgot-container mx-auto">
                    <!-- Logo -->
                    <div class="text-center mb-4">
                        <h1 class="text-white fw-bold">
                            <i class="bi bi-cpu-fill"></i> FirmwareHub
                        </h1>
                        <p class="text-white-50">Reset your password</p>
                    </div>
                    
                    <!-- Forgot Password Card -->
                    <div class="card">
                        <div class="card-body p-4 p-md-5">
                            <!-- Lock Icon -->
                            <div class="text-center mb-4">
                                <div class="lock-icon">
                                    <i class="bi bi-shield-lock"></i>
                                </div>
                                <h3 class="card-title mt-3">Forgot Password?</h3>
                                <p class="text-muted">No worries! Enter your email and we'll send you reset instructions.</p>
                            </div>
                            
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bi bi-check-circle me-2"></i>
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                            
                            @if(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-circle me-2"></i>
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                            
                            @if(session('info'))
                                <div class="alert alert-info alert-dismissible fade show" role="alert">
                                    <i class="bi bi-info-circle me-2"></i>
                                    {{ session('info') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                            
                            <form method="POST" action="{{ route('password.email') }}">
                                @csrf
                                
                                <!-- Email -->
                                <div class="mb-4">
                                    <label for="email" class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               id="email" name="email" value="{{ old('email') }}" 
                                               placeholder="Enter your registered email" required autofocus>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="text-muted">
                                        We'll send a password reset link to this email address.
                                    </small>
                                </div>
                                
                                <!-- Security Notice -->
                                <div class="alert alert-info small">
                                    <i class="bi bi-shield-check me-1"></i>
                                    <strong>Security Notice:</strong> For your protection, password reset links expire after 60 minutes.
                                </div>
                                
                                <!-- Submit Button -->
                                <button type="submit" class="btn btn-primary w-100 mb-3">
                                    <i class="bi bi-send me-2"></i> Send Reset Link
                                </button>
                                
                                <!-- Back to Login -->
                                <div class="text-center">
                                    <a href="{{ route('login') }}" class="text-decoration-none">
                                        <i class="bi bi-arrow-left me-1"></i> Back to Login
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Help Section -->
                    <div class="card mt-3">
                        <div class="card-body p-3">
                            <h6 class="mb-2">Need Help?</h6>
                            <ul class="small mb-0">
                                <li>Make sure you're using the email associated with your account</li>
                                <li>Check your spam folder for the reset email</li>
                                <li>Contact <a href="{{ route('contact') }}">support</a> if you need assistance</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="text-center mt-4">
                        <p class="text-white-50 small">
                            &copy; {{ date('Y') }} FirmwareHub. All rights reserved.
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