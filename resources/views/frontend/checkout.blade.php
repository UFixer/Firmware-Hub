@extends('layouts.app')

@section('title', 'Checkout - Secure Payment | FirmwareHub')
@section('meta_description', 'Complete your purchase securely. SSL encrypted checkout with multiple payment options.')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Progress Bar -->
            <div class="checkout-progress mb-4">
                <div class="progress" style="height: 3px;">
                    <div class="progress-bar" role="progressbar" style="width: 66%"></div>
                </div>
                <div class="d-flex justify-content-between mt-2">
                    <small class="text-primary"><i class="fas fa-check-circle"></i> Package Selected</small>
                    <small class="text-primary"><strong>Payment Details</strong></small>
                    <small class="text-muted">Confirmation</small>
                </div>
            </div>
            
            <div class="row">
                <!-- Checkout Form -->
                <div class="col-md-8 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h1 class="h4 mb-0">
                                <i class="fas fa-lock text-success me-2"></i>Secure Checkout
                            </h1>
                        </div>
                        <div class="card-body">
                            <form id="checkoutForm" action="{{ route('payment.process') }}" method="POST">
                                @csrf
                                <input type="hidden" name="package_id" value="{{ $package->id }}">
                                
                                <!-- Billing Information -->
                                <h2 class="h5 mb-3">Billing Information</h2>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               name="first_name" 
                                               class="form-control @error('first_name') is-invalid @enderror" 
                                               value="{{ old('first_name', auth()->user()->first_name ?? '') }}"
                                               required>
                                        @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               name="last_name" 
                                               class="form-control @error('last_name') is-invalid @enderror" 
                                               value="{{ old('last_name', auth()->user()->last_name ?? '') }}"
                                               required>
                                        @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" 
                                           name="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           value="{{ old('email', auth()->user()->email ?? '') }}"
                                           required>
                                    <small class="text-muted">Receipt will be sent to this email</small>
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Country <span class="text-danger">*</span></label>
                                    <select name="country" class="form-select @error('country') is-invalid @enderror" required>
                                        <option value="">Select Country</option>
                                        <option value="US" {{ old('country') == 'US' ? 'selected' : '' }}>United States</option>
                                        <option value="GB" {{ old('country') == 'GB' ? 'selected' : '' }}>United Kingdom</option>
                                        <option value="CA" {{ old('country') == 'CA' ? 'selected' : '' }}>Canada</option>
                                        <option value="AU" {{ old('country') == 'AU' ? 'selected' : '' }}>Australia</option>
                                        <option value="IN" {{ old('country') == 'IN' ? 'selected' : '' }}>India</option>
                                        <!-- Add more countries as needed -->
                                    </select>
                                    @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Payment Method -->
                                <h2 class="h5 mb-3 mt-4">Payment Method</h2>
                                
                                <div class="payment-methods mb-3">
                                    <div class="form-check payment-option mb-2">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="payment_method" 
                                               id="stripe" 
                                               value="stripe"
                                               checked>
                                        <label class="form-check-label w-100" for="stripe">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>
                                                    <i class="fas fa-credit-card me-2"></i>Credit/Debit Card
                                                </span>
                                                <div>
                                                    <img src="https://cdn.example.com/visa.svg" alt="Visa" height="20" class="me-1">
                                                    <img src="https://cdn.example.com/mastercard.svg" alt="Mastercard" height="20" class="me-1">
                                                    <img src="https://cdn.example.com/amex.svg" alt="Amex" height="20">
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                    
                                    <div class="form-check payment-option mb-2">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="payment_method" 
                                               id="paypal" 
                                               value="paypal">
                                        <label class="form-check-label w-100" for="paypal">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>PayPal</span>
                                                <img src="https://cdn.example.com/paypal.svg" alt="PayPal" height="20">
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Card Details (Stripe) -->
                                <div id="cardDetails" class="card-details">
                                    <div class="mb-3">
                                        <label class="form-label">Card Number</label>
                                        <div id="card-number" class="form-control"></div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <label class="form-label">Expiry Date</label>
                                            <div id="card-expiry" class="form-control"></div>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label">CVC</label>
                                            <div id="card-cvc" class="form-control"></div>
                                        </div>
                                    </div>
                                    
                                    <div id="card-errors" class="alert alert-danger d-none" role="alert"></div>
                                </div>
                                
                                <!-- Coupon Code -->
                                <div class="mb-3">
                                    <label class="form-label">Coupon Code (Optional)</label>
                                    <div class="input-group">
                                        <input type="text" 
                                               name="coupon_code" 
                                               id="couponCode"
                                               class="form-control" 
                                               placeholder="Enter coupon code">
                                        <button type="button" 
                                                class="btn btn-outline-secondary" 
                                                onclick="applyCoupon()">
                                            Apply
                                        </button>
                                    </div>
                                    <div id="couponMessage" class="mt-2"></div>
                                </div>
                                
                                <!-- Terms & Conditions -->
                                <div class="form-check mb-3">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="terms" 
                                           name="terms"
                                           required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="{{ route('terms') }}" target="_blank">Terms of Service</a> 
                                        and <a href="{{ route('privacy') }}" target="_blank">Privacy Policy</a>
                                    </label>
                                </div>
                                
                                <!-- Submit Button -->
                                <button type="submit" 
                                        class="btn btn-primary btn-lg w-100" 
                                        id="submitBtn">
                                    <i class="fas fa-lock me-2"></i>
                                    Complete Purchase - ${{ number_format($finalAmount, 2) }}
                                </button>
                                
                                <!-- Security Badges -->
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-shield-alt text-success me-1"></i>
                                        SSL Encrypted • PCI Compliant • Secure Checkout
                                    </small>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="col-md-4">
                    <div class="card shadow-sm sticky-top" style="top: 20px;">
                        <div class="card-header bg-light">
                            <h2 class="h5 mb-0">Order Summary</h2>
                        </div>
                        <div class="card-body">
                            <!-- Package Details -->
                            <div class="d-flex justify-content-between mb-3">
                                <div>
                                    <h3 class="h6 mb-1">{{ $package->name }} Plan</h3>
                                    <small class="text-muted">
                                        {{ $billingCycle == 'yearly' ? 'Annual' : 'Monthly' }} Subscription
                                    </small>
                                </div>
                                <div class="text-end">
                                    <strong>${{ number_format($package->price, 2) }}</strong>
                                    @if($billingCycle == 'yearly')
                                    <br><small class="text-muted">/month</small>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Features -->
                            <ul class="list-unstyled small mb-3">
                                @foreach($package->features as $feature)
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>{{ $feature }}
                                </li>
                                @endforeach
                            </ul>
                            
                            <hr>
                            
                            <!-- Price Breakdown -->
                            <div class="price-breakdown">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal</span>
                                    <span>${{ number_format($subtotal, 2) }}</span>
                                </div>
                                
                                @if($discount > 0)
                                <div class="d-flex justify-content-between mb-2 text-success">
                                    <span>Discount</span>
                                    <span>-${{ number_format($discount, 2) }}</span>
                                </div>
                                @endif
                                
                                @if($tax > 0)
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tax</span>
                                    <span>${{ number_format($tax, 2) }}</span>
                                </div>
                                @endif
                                
                                <hr>
                                
                                <div class="d-flex justify-content-between">
                                    <strong>Total</strong>
                                    <strong class="text-primary">${{ number_format($finalAmount, 2) }}</strong>
                                </div>
                                
                                @if($billingCycle == 'yearly')
                                <div class="alert alert-success mt-3 py-2">
                                    <small>
                                        <i class="fas fa-tag me-1"></i>
                                        You're saving ${{ number_format($yearlySavings, 2) }} with annual billing!
                                    </small>
                                </div>
                                @endif
                            </div>
                            
                            <!-- Money Back Guarantee -->
                            <div class="guarantee-box mt-3 p-3 bg-light rounded">
                                <div class="text-center">
                                    <i class="fas fa-shield-alt text-success fa-2x mb-2"></i>
                                    <h4 class="h6 mb-1">30-Day Money Back Guarantee</h4>
                                    <small class="text-muted">
                                        Not satisfied? Get a full refund within 30 days.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.payment-option {
    border: 2px solid #dee2e6;
    padding: 15px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
}
.payment-option:hover {
    border-color: #0d6efd;
}
.form-check-input:checked ~ .form-check-label .payment-option,
.payment-option.selected {
    border-color: #0d6efd;
    background-color: #f0f8ff;
}
.guarantee-box {
    border: 1px dashed #28a745;
}
</style>
@endpush

@push('scripts')
<!-- Stripe.js -->
<script src="https://js.stripe.com/v3/"></script>
<script>
// Initialize Stripe
const stripe = Stripe('{{ env("STRIPE_PUBLIC_KEY") }}');
const elements = stripe.elements();

// Create card elements
const cardNumber = elements.create('cardNumber');
const cardExpiry = elements.create('cardExpiry');
const cardCvc = elements.create('cardCvc');

// Mount elements
cardNumber.mount('#card-number');
cardExpiry.mount('#card-expiry');
cardCvc.mount('#card-cvc');

// Handle errors
cardNumber.on('change', handleCardError);
cardExpiry.on('change', handleCardError);
cardCvc.on('change', handleCardError);

function handleCardError(event) {
    const errorElement = document.getElementById('card-errors');
    if (event.error) {
        errorElement.textContent = event.error.message;
        errorElement.classList.remove('d-none');
    } else {
        errorElement.classList.add('d-none');
    }
}

// Payment method toggle
document.querySelectorAll('input[name="payment_method"]').forEach(input => {
    input.addEventListener('change', function() {
        if (this.value === 'stripe') {
            document.getElementById('cardDetails').style.display = 'block';
        } else {
            document.getElementById('cardDetails').style.display = 'none';
        }
    });
});

// Apply coupon
function applyCoupon() {
    const code = document.getElementById('couponCode').value;
    const messageDiv = document.getElementById('couponMessage');
    
    if (!code) {
        messageDiv.innerHTML = '<div class="alert alert-warning py-2">Please enter a coupon code</div>';
        return;
    }
    
    fetch('/api/coupon/validate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ code: code })
    })
    .then(response => response.json())
    .then(data => {
        if (data.valid) {
            messageDiv.innerHTML = `<div class="alert alert-success py-2">Coupon applied! You saved $${data.discount}</div>`;
            // Update price display
            location.reload();
        } else {
            messageDiv.innerHTML = `<div class="alert alert-danger py-2">${data.message}</div>`;
        }
    });
}

// Form submission
document.getElementById('checkoutForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
    
    if (document.querySelector('input[name="payment_method"]:checked').value === 'stripe') {
        // Create payment method with Stripe
        const { paymentMethod, error } = await stripe.createPaymentMethod({
            type: 'card',
            card: cardNumber,
            billing_details: {
                name: document.querySelector('[name="first_name"]').value + ' ' + 
                      document.querySelector('[name="last_name"]').value,
                email: document.querySelector('[name="email"]').value,
            }
        });
        
        if (error) {
            handleCardError({ error });
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-lock me-2"></i>Complete Purchase';
            return;
        }
        
        // Add payment method ID to form
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'payment_method_id';
        input.value = paymentMethod.id;
        this.appendChild(input);
    }
    
    // Submit form
    this.submit();
});
</script>
@endpush