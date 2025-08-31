<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - FirmwareHub</title>
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .register-container { max-width: 500px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
        .btn-primary { background: #667eea; border: none; padding: 12px; }
        .btn-primary:hover { background: #5a67d8; }
        .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25); }
        .password-strength { height: 5px; border-radius: 3px; transition: all 0.3s; }
    </style>
</head>
<body class="d-flex align-items-center py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6">
                <div class="register-container mx-auto">
                    <!-- Logo -->
                    <div class="text-center mb-4">
                        <h1 class="text-white fw-bold">
                            <i class="bi bi-cpu-fill"></i> FirmwareHub
                        </h1>
                        <p class="text-white-50">Create your account to get started</p>
                    </div>
                    
                    <!-- Register Card -->
                    <div class="card">
                        <div class="card-body p-4 p-md-5">
                            <h3 class="card-title text-center mb-4">Sign Up</h3>
                            
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
                            
                            <form method="POST" action="{{ route('register') }}">
                                @csrf
                                
                                <!-- Name Row -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                               id="first_name" name="first_name" value="{{ old('first_name') }}" 
                                               placeholder="John" required>
                                        @error('first_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                               id="last_name" name="last_name" value="{{ old('last_name') }}" 
                                               placeholder="Doe" required>
                                        @error('last_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <!-- Email -->
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               id="email" name="email" value="{{ old('email') }}" 
                                               placeholder="john@example.com" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <!-- Phone (Optional) -->
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number <small class="text-muted">(Optional)</small></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                               id="phone" name="phone" value="{{ old('phone') }}" 
                                               placeholder="+1 234 567 8900">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <!-- Password -->
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                               id="password" name="password" placeholder="Min 8 characters" required>
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="password-strength bg-danger mt-2" id="passwordStrength"></div>
                                </div>
                                
                                <!-- Confirm Password -->
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                        <input type="password" class="form-control" id="password_confirmation" 
                                               name="password_confirmation" placeholder="Repeat password" required>
                                    </div>
                                </div>
                                
                                <!-- Terms & Newsletter -->
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                        <label class="form-check-label" for="terms">
                                            I agree to the <a href="/terms" target="_blank">Terms of Service</a> 
                                            and <a href="/privacy" target="_blank">Privacy Policy</a>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="newsletter" name="newsletter">
                                        <label class="form-check-label" for="newsletter">
                                            Send me updates and newsletters
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Referral Code -->
                                <div class="mb-3">
                                    <label for="referral_code" class="form-label">
                                        Referral Code <small class="text-muted">(Optional)</small>
                                    </label>
                                    <input type="text" class="form-control" id="referral_code" 
                                           name="referral_code" value="{{ old('referral_code') }}" 
                                           placeholder="Enter if you have one">
                                </div>
                                
                                <!-- Submit Button -->
                                <button type="submit" class="btn btn-primary w-100 mb-3">
                                    <i class="bi bi-person-plus me-2"></i> Create Account
                                </button>
                                
                                <!-- Login Link -->
                                <div class="text-center">
                                    <span class="text-muted">Already have an account?</span>
                                    <a href="{{ route('login') }}" class="text-decoration-none fw-bold">Sign In</a>
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
        // Toggle Password
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
        
        // Password Strength Indicator
        document.getElementById('password').addEventListener('input', function() {
            const strength = document.getElementById('passwordStrength');
            const value = this.value;
            let score = 0;
            
            if (value.length >= 8) score++;
            if (/[a-z]/.test(value)) score++;
            if (/[A-Z]/.test(value)) score++;
            if (/[0-9]/.test(value)) score++;
            if (/[^a-zA-Z0-9]/.test(value)) score++;
            
            strength.className = 'password-strength mt-2';
            if (score <= 2) {
                strength.classList.add('bg-danger');
                strength.style.width = '33%';
            } else if (score <= 3) {
                strength.classList.add('bg-warning');
                strength.style.width = '66%';
            } else {
                strength.classList.add('bg-success');
                strength.style.width = '100%';
            }
        });
    </script>
</body>
</html>