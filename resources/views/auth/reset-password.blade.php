<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - FirmwareHub</title>
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .reset-container { max-width: 450px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
        .btn-primary { background: #667eea; border: none; padding: 12px; }
        .btn-primary:hover { background: #5a67d8; }
        .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25); }
        .key-icon { font-size: 3rem; color: #667eea; }
        .password-requirements { font-size: 0.875rem; }
        .requirement { color: #dc3545; }
        .requirement.met { color: #198754; }
    </style>
</head>
<body class="d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6">
                <div class="reset-container mx-auto">
                    <!-- Logo -->
                    <div class="text-center mb-4">
                        <h1 class="text-white fw-bold">
                            <i class="bi bi-cpu-fill"></i> FirmwareHub
                        </h1>
                        <p class="text-white-50">Create your new password</p>
                    </div>
                    
                    <!-- Reset Password Card -->
                    <div class="card">
                        <div class="card-body p-4 p-md-5">
                            <!-- Key Icon -->
                            <div class="text-center mb-4">
                                <div class="key-icon">
                                    <i class="bi bi-key"></i>
                                </div>
                                <h3 class="card-title mt-3">Reset Password</h3>
                                <p class="text-muted">Enter your new password below</p>
                            </div>
                            
                            @if($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                            
                            <form method="POST" action="{{ route('password.update') }}">
                                @csrf
                                <input type="hidden" name="token" value="{{ $token }}">
                                
                                <!-- Email (Read-only) -->
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               id="email" name="email" value="{{ $email ?? old('email') }}" 
                                               readonly required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <!-- New Password -->
                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                               id="password" name="password" placeholder="Enter new password" required>
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <!-- Password Requirements -->
                                <div class="password-requirements mb-3 p-3 bg-light rounded">
                                    <p class="mb-2 fw-bold">Password must contain:</p>
                                    <ul class="mb-0">
                                        <li class="requirement" id="length">At least 8 characters</li>
                                        <li class="requirement" id="lowercase">One lowercase letter</li>
                                        <li class="requirement" id="uppercase">One uppercase letter</li>
                                        <li class="requirement" id="number">One number</li>
                                        <li class="requirement" id="symbol">One special character</li>
                                    </ul>
                                </div>
                                
                                <!-- Confirm Password -->
                                <div class="mb-4">
                                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                        <input type="password" class="form-control" id="password_confirmation" 
                                               name="password_confirmation" placeholder="Repeat new password" required>
                                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirm">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback" id="confirmError" style="display: none;">
                                        Passwords do not match
                                    </div>
                                </div>
                                
                                <!-- Submit Button -->
                                <button type="submit" class="btn btn-primary w-100 mb-3">
                                    <i class="bi bi-check-circle me-2"></i> Reset Password
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
    
    <!-- Custom JS -->
    <script>
        // Toggle Password Visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
        
        document.getElementById('toggleConfirm').addEventListener('click', function() {
            const confirm = document.getElementById('password_confirmation');
            const icon = this.querySelector('i');
            if (confirm.type === 'password') {
                confirm.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                confirm.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
        
        // Password Requirements Check
        document.getElementById('password').addEventListener('input', function() {
            const value = this.value;
            
            // Check length
            document.getElementById('length').classList.toggle('met', value.length >= 8);
            
            // Check lowercase
            document.getElementById('lowercase').classList.toggle('met', /[a-z]/.test(value));
            
            // Check uppercase
            document.getElementById('uppercase').classList.toggle('met', /[A-Z]/.test(value));
            
            // Check number
            document.getElementById('number').classList.toggle('met', /[0-9]/.test(value));
            
            // Check symbol
            document.getElementById('symbol').classList.toggle('met', /[^a-zA-Z0-9]/.test(value));
        });
        
        // Confirm Password Match
        document.getElementById('password_confirmation').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirm = this.value;
            const error = document.getElementById('confirmError');
            
            if (confirm && password !== confirm) {
                this.classList.add('is-invalid');
                error.style.display = 'block';
            } else {
                this.classList.remove('is-invalid');
                error.style.display = 'none';
            }
        });
    </script>
</body>
</html>