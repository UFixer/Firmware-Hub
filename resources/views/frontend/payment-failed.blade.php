@extends('layouts.app')

@section('title', 'Payment Failed - Please Try Again | FirmwareHub')
@section('meta_description', 'Your payment could not be processed. Please try again or contact support.')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Error Animation -->
            <div class="text-center mb-5" data-aos="fade-in">
                <div class="error-animation mb-4">
                    <i class="fas fa-times-circle text-danger" style="font-size: 5rem;"></i>
                </div>
                
                <h1 class="display-4 fw-bold mb-3">Payment Failed</h1>
                <p class="lead text-muted">
                    We couldn't process your payment. Please try again or use a different payment method.
                </p>
            </div>
            
            <!-- Error Details -->
            <div class="card shadow-sm mb-4 border-danger">
                <div class="card-header bg-danger text-white">
                    <h2 class="h5 mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>What Went Wrong?
                    </h2>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger mb-3">
                        <strong>Error Code:</strong> {{ $errorCode ?? 'PAYMENT_DECLINED' }}<br>
                        <strong>Message:</strong> {{ $errorMessage ?? 'Your payment was declined by the payment processor.' }}
                    </div>
                    
                    <h3 class="h6 mb-3">Common reasons for payment failure:</h3>
                    <ul class="mb-0">
                        <li class="mb-2">Insufficient funds in your account</li>
                        <li class="mb-2">Incorrect card details entered</li>
                        <li class="mb-2">Card expired or not activated for online payments</li>
                        <li class="mb-2">Payment blocked by your bank for security reasons</li>
                        <li class="mb-2">Daily transaction limit exceeded</li>
                    </ul>
                </div>
            </div>
            
            <!-- Troubleshooting Steps -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning">
                    <h2 class="h5 mb-0">
                        <i class="fas fa-tools me-2"></i>How to Fix This
                    </h2>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="step-item">
                                <div class="step-number">1</div>
                                <h3 class="h6">Verify Card Details</h3>
                                <p class="small text-muted">
                                    Double-check your card number, expiry date, and CVV code
                                </p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="step-item">
                                <div class="step-number">2</div>
                                <h3 class="h6">Check Card Balance</h3>
                                <p class="small text-muted">
                                    Ensure you have sufficient funds available
                                </p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="step-item">
                                <div class="step-number">3</div>
                                <h3 class="h6">Contact Your Bank</h3>
                                <p class="small text-muted">
                                    Your bank may have blocked the transaction for security
                                </p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="step-item">
                                <div class="step-number">4</div>
                                <h3 class="h6">Try Another Method</h3>
                                <p class="small text-muted">
                                    Use a different card or PayPal if available
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary (If Available) -->
            @if(isset($attemptedOrder))
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h2 class="h5 mb-0">
                        <i class="fas fa-shopping-cart me-2"></i>Your Order
                    </h2>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="h6 mb-1">{{ $attemptedOrder->package->name }} Plan</h3>
                            <p class="text-muted mb-0">
                                {{ $attemptedOrder->billing_cycle == 'yearly' ? 'Annual' : 'Monthly' }} Subscription
                            </p>
                        </div>
                        <div class="text-end">
                            <strong class="text-primary">
                                ${{ number_format($attemptedOrder->amount, 2) }}
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Action Buttons -->
            <div class="text-center">
                <a href="{{ route('checkout', ['package' => $packageId ?? request('package')]) }}" 
                   class="btn btn-primary btn-lg me-2">
                    <i class="fas fa-redo me-2"></i>Try Again
                </a>
                
                <button type="button" 
                        class="btn btn-outline-primary btn-lg"
                        data-bs-toggle="modal" 
                        data-bs-target="#alternativePayment">
                    <i class="fas fa-credit-card me-2"></i>Use Different Payment Method
                </button>
            </div>
            
            <!-- Support Section -->
            <div class="alert alert-info mt-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="h6 mb-1">
                            <i class="fas fa-headset me-2"></i>Need Help?
                        </h3>
                        <p class="mb-0 small">
                            Our support team is standing by to help you complete your purchase.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-2 mt-md-0">
                        <a href="{{ route('support') }}" class="btn btn-info btn-sm text-white">
                            Contact Support
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Alternative Options -->
            <div class="card mt-4 border-0 bg-light">
                <div class="card-body">
                    <h3 class="h6 mb-3">Alternative Options:</h3>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <a href="{{ route('packages') }}" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="fas fa-arrow-left me-2"></i>View Other Plans
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="fas fa-home me-2"></i>Go to Homepage
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('browse') }}" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="fas fa-search me-2"></i>Browse Free Files
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alternative Payment Modal -->
<div class="modal fade" id="alternativePayment" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Choose Payment Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('checkout', ['package' => $packageId ?? request('package'), 'method' => 'stripe']) }}" 
                       class="btn btn-outline-primary">
                        <i class="fas fa-credit-card me-2"></i>Credit/Debit Card
                    </a>
                    <a href="{{ route('checkout', ['package' => $packageId ?? request('package'), 'method' => 'paypal']) }}" 
                       class="btn btn-outline-primary">
                        <i class="fab fa-paypal me-2"></i>PayPal
                    </a>
                    <a href="{{ route('checkout', ['package' => $packageId ?? request('package'), 'method' => 'bank']) }}" 
                       class="btn btn-outline-primary">
                        <i class="fas fa-university me-2"></i>Bank Transfer
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
    20%, 40%, 60%, 80% { transform: translateX(10px); }
}

.error-animation {
    animation: shake 0.5s;
}

.step-item {
    position: relative;
    padding-left: 50px;
    min-height: 60px;
}

.step-number {
    position: absolute;
    left: 0;
    top: 0;
    width: 35px;
    height: 35px;
    background: #ffc107;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.card.border-danger {
    border-width: 2px !important;
}
</style>
@endpush

@push('scripts')
<script>
// Track failed payment attempt
document.addEventListener('DOMContentLoaded', function() {
    // Google Analytics
    if (typeof gtag !== 'undefined') {
        gtag('event', 'payment_failed', {
            'error_code': '{{ $errorCode ?? "UNKNOWN" }}',
            'package_id': '{{ $packageId ?? "" }}',
            'amount': '{{ $attemptedOrder->amount ?? 0 }}'
        });
    }
    
    // Store cart data in session storage to preserve it
    const cartData = {
        package: '{{ $packageId ?? request("package") }}',
        billing: '{{ request("billing", "monthly") }}'
    };
    sessionStorage.setItem('pending_order', JSON.stringify(cartData));
});

// Retry payment with stored data
function retryPayment() {
    const pendingOrder = JSON.parse(sessionStorage.getItem('pending_order') || '{}');
    if (pendingOrder.package) {
        window.location.href = `/checkout?package=${pendingOrder.package}&billing=${pendingOrder.billing}`;
    }
}
</script>
@endpush