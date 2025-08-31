@extends('layouts.app')

@section('title', 'Payment Successful - Thank You! | FirmwareHub')
@section('meta_description', 'Your payment was successful. Welcome to FirmwareHub Premium!')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Success Animation -->
            <div class="text-center mb-5" data-aos="zoom-in">
                <div class="success-animation mb-4">
                    <div class="success-checkmark">
                        <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                    </div>
                </div>
                
                <h1 class="display-4 fw-bold mb-3">Payment Successful!</h1>
                <p class="lead text-muted">
                    Thank you for your purchase. Your subscription is now active.
                </p>
            </div>
            
            <!-- Order Details -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h2 class="h5 mb-0">
                        <i class="fas fa-receipt me-2"></i>Order Confirmation
                    </h2>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Order Number:</strong>
                        </div>
                        <div class="col-sm-8">
                            #{{ $order->order_number ?? 'ORD-' . str_pad($order->id, 8, '0', STR_PAD_LEFT) }}
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Transaction ID:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $order->transaction_id }}
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Date:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $order->created_at->format('F d, Y - h:i A') }}
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Amount Paid:</strong>
                        </div>
                        <div class="col-sm-8">
                            <span class="text-success fw-bold">
                                ${{ number_format($order->amount, 2) }} USD
                            </span>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Payment Method:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ ucfirst($order->payment_method) }}
                            @if($order->payment_method == 'stripe')
                                <i class="fas fa-credit-card ms-2"></i>
                            @elseif($order->payment_method == 'paypal')
                                <i class="fab fa-paypal ms-2"></i>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Subscription Details -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h2 class="h5 mb-0">
                        <i class="fas fa-crown me-2"></i>Your Subscription
                    </h2>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="h4 mb-2">{{ $subscription->package->name }} Plan</h3>
                            <p class="text-muted mb-3">
                                Your subscription is active and will renew on 
                                <strong>{{ $subscription->ends_at->format('F d, Y') }}</strong>
                            </p>
                            
                            <h4 class="h6 mb-2">What's Included:</h4>
                            <ul class="list-unstyled">
                                @foreach($subscription->package->features as $feature)
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>{{ $feature }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="badge-container">
                                @if($subscription->package->name == 'Premium')
                                <img src="https://cdn.example.com/premium-badge.png" 
                                     alt="Premium Member" 
                                     class="img-fluid"
                                     style="max-width: 150px;">
                                @elseif($subscription->package->name == 'Basic')
                                <img src="https://cdn.example.com/basic-badge.png" 
                                     alt="Basic Member" 
                                     class="img-fluid"
                                     style="max-width: 150px;">
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Next Steps -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h2 class="h5 mb-0">
                        <i class="fas fa-rocket me-2"></i>Get Started
                    </h2>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="fas fa-download fa-3x text-primary mb-3"></i>
                                <h3 class="h6">Start Downloading</h3>
                                <p class="small text-muted mb-3">
                                    Browse our library and download firmware files instantly
                                </p>
                                <a href="{{ route('browse') }}" class="btn btn-primary btn-sm">
                                    Browse Files
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="fas fa-user-circle fa-3x text-info mb-3"></i>
                                <h3 class="h6">Complete Profile</h3>
                                <p class="small text-muted mb-3">
                                    Add your details and preferences for a better experience
                                </p>
                                <a href="{{ route('profile.edit') }}" class="btn btn-info btn-sm text-white">
                                    Edit Profile
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="fas fa-headset fa-3x text-success mb-3"></i>
                                <h3 class="h6">Need Help?</h3>
                                <p class="small text-muted mb-3">
                                    Our support team is here to assist you 24/7
                                </p>
                                <a href="{{ route('support') }}" class="btn btn-success btn-sm">
                                    Get Support
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Email Confirmation -->
            <div class="alert alert-info">
                <i class="fas fa-envelope me-2"></i>
                <strong>Check Your Email!</strong> 
                We've sent a confirmation receipt to <strong>{{ $order->user->email }}</strong> 
                with your order details and invoice.
            </div>
            
            <!-- Action Buttons -->
            <div class="text-center mt-4">
                <a href="{{ route('user.dashboard') }}" class="btn btn-primary btn-lg me-2">
                    <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                </a>
                <a href="{{ route('browse') }}" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-search me-2"></i>Start Browsing
                </a>
            </div>
            
            <!-- Download Invoice -->
            <div class="text-center mt-3">
                <a href="{{ route('invoice.download', $order->id) }}" class="btn btn-link">
                    <i class="fas fa-file-pdf me-2"></i>Download Invoice (PDF)
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Confetti Animation -->
<canvas id="confetti-canvas" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 9999;"></canvas>

@endsection

@push('styles')
<style>
@keyframes checkmark {
    0% { transform: scale(0) rotate(45deg); opacity: 0; }
    50% { transform: scale(1.2) rotate(45deg); }
    100% { transform: scale(1) rotate(45deg); opacity: 1; }
}

.success-checkmark {
    animation: checkmark 0.5s ease-in-out;
}

.card {
    border: none;
    transition: transform 0.3s;
}

.card:hover {
    transform: translateY(-5px);
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
<script>
// Confetti animation on page load
document.addEventListener('DOMContentLoaded', function() {
    // Trigger confetti
    confetti({
        particleCount: 100,
        spread: 70,
        origin: { y: 0.6 }
    });
    
    // Play success sound (optional)
    const audio = new Audio('https://cdn.example.com/success-sound.mp3');
    audio.volume = 0.3;
    audio.play().catch(e => console.log('Audio play failed:', e));
    
    // Track conversion
    if (typeof gtag !== 'undefined') {
        gtag('event', 'conversion', {
            'send_to': 'AW-CONVERSION_ID/CONVERSION_LABEL',
            'value': {{ $order->amount }},
            'currency': 'USD',
            'transaction_id': '{{ $order->transaction_id }}'
        });
    }
    
    // Facebook Pixel (if applicable)
    if (typeof fbq !== 'undefined') {
        fbq('track', 'Purchase', {
            value: {{ $order->amount }},
            currency: 'USD',
            content_ids: ['{{ $subscription->package->id }}'],
            content_type: 'product'
        });
    }
});

// Auto-hide confetti canvas after animation
setTimeout(() => {
    document.getElementById('confetti-canvas').style.display = 'none';
}, 5000);
</script>
@endpush