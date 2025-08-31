@extends('layouts.app')

@section('title', 'Pricing Plans - Choose Your Package | FirmwareHub')
@section('meta_description', 'Choose the perfect plan for your firmware download needs. Free trial, Basic, and Premium packages available with instant access.')
@section('meta_keywords', 'firmware subscription, pricing plans, premium downloads, unlimited firmware access')

@section('structured_data')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "OfferCatalog",
    "name": "FirmwareHub Subscription Plans",
    "itemListElement": [
        {
            "@type": "Offer",
            "name": "Free Plan",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Basic access with limited downloads"
        },
        {
            "@type": "Offer",
            "name": "Basic Plan",
            "price": "9.99",
            "priceCurrency": "USD",
            "description": "Enhanced access with more downloads"
        },
        {
            "@type": "Offer",
            "name": "Premium Plan",
            "price": "29.99",
            "priceCurrency": "USD",
            "description": "Unlimited downloads with premium features"
        }
    ]
}
</script>
@endsection

@section('content')
<!-- Pricing Header -->
<section class="pricing-header bg-gradient-primary text-white py-5">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3" data-aos="fade-up">
            Choose Your Plan
        </h1>
        <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
            Get instant access to thousands of firmware files
        </p>
        
        <!-- Billing Toggle -->
        <div class="billing-toggle d-inline-flex align-items-center bg-white rounded-pill p-1" data-aos="fade-up" data-aos-delay="200">
            <button class="btn btn-sm rounded-pill px-4 active" id="monthlyBtn" data-billing="monthly">
                Monthly
            </button>
            <button class="btn btn-sm rounded-pill px-4" id="yearlyBtn" data-billing="yearly">
                Yearly <span class="badge bg-danger ms-1">Save 20%</span>
            </button>
        </div>
    </div>
</section>

<!-- Pricing Tables -->
<section class="pricing-section py-5">
    <div class="container">
        <div class="row g-4 justify-content-center">
            
            <!-- Free Plan -->
            <div class="col-lg-4 col-md-6" data-aos="zoom-in" data-aos-delay="100">
                <div class="card pricing-card h-100 shadow-sm">
                    <div class="card-header text-center py-4 bg-light">
                        <h2 class="h4 mb-0">Free</h2>
                        <p class="text-muted mb-0">Get started</p>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="price-display">
                                <span class="currency">$</span>
                                <span class="price" data-monthly="0" data-yearly="0">0</span>
                                <span class="period">/month</span>
                            </div>
                        </div>
                        
                        <ul class="list-unstyled mb-4">
                            <li class="mb-3">
                                <i class="fas fa-check text-success me-2"></i>
                                <strong>2 downloads</strong> per day
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check text-success me-2"></i>
                                <strong>Basic search</strong> features
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check text-success me-2"></i>
                                <strong>Standard speed</strong> downloads
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check text-success me-2"></i>
                                Access to <strong>free files</strong>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-times text-muted me-2"></i>
                                <span class="text-muted">Premium files</span>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-times text-muted me-2"></i>
                                <span class="text-muted">Priority support</span>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-times text-muted me-2"></i>
                                <span class="text-muted">Download history</span>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-times text-muted me-2"></i>
                                <span class="text-muted">Batch downloads</span>
                            </li>
                        </ul>
                        
                        <div class="d-grid">
                            @guest
                            <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg">
                                Sign Up Free
                            </a>
                            @else
                                @if(!auth()->user()->subscription || auth()->user()->subscription->package_id != 1)
                                <form action="{{ route('subscription.change') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="package_id" value="1">
                                    <button type="submit" class="btn btn-outline-primary btn-lg w-100">
                                        Switch to Free
                                    </button>
                                </form>
                                @else
                                <button class="btn btn-secondary btn-lg" disabled>
                                    Current Plan
                                </button>
                                @endif
                            @endguest
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Basic Plan -->
            <div class="col-lg-4 col-md-6" data-aos="zoom-in" data-aos-delay="200">
                <div class="card pricing-card h-100 shadow border-primary">
                    <div class="ribbon">
                        <span>POPULAR</span>
                    </div>
                    <div class="card-header text-center py-4 bg-primary text-white">
                        <h2 class="h4 mb-0">Basic</h2>
                        <p class="mb-0">Most popular choice</p>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="price-display">
                                <span class="currency">$</span>
                                <span class="price" data-monthly="9.99" data-yearly="7.99">9.99</span>
                                <span class="period">/month</span>
                            </div>
                            <small class="text-muted yearly-save d-none">
                                Billed $95.88 yearly (Save $24)
                            </small>
                        </div>
                        
                        <ul class="list-unstyled mb-4">
                            <li class="mb-3">
                                <i class="fas fa-check text-success me-2"></i>
                                <strong>20 downloads</strong> per day
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check text-success me-2"></i>
                                <strong>Advanced search</strong> & filters
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check text-success me-2"></i>
                                <strong>High-speed</strong> downloads
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check text-success me-2"></i>
                                Access to <strong>80% of files</strong>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check text-success me-2"></i>
                                <strong>Email support</strong>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check text-success me-2"></i>
                                <strong>Download history</strong>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-times text-muted me-2"></i>
                                <span class="text-muted">Premium exclusive files</span>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-times text-muted me-2"></i>
                                <span class="text-muted">API access</span>
                            </li>
                        </ul>
                        
                        <div class="d-grid">
                            @guest
                            <a href="{{ route('register') }}?plan=basic" class="btn btn-primary btn-lg">
                                Start 7-Day Trial
                            </a>
                            @else
                                @if(!auth()->user()->subscription || auth()->user()->subscription->package_id != 2)
                                <a href="{{ route('checkout') }}?package=2" class="btn btn-primary btn-lg">
                                    Upgrade to Basic
                                </a>
                                @else
                                <button class="btn btn-secondary btn-lg" disabled>
                                    Current Plan
                                </button>
                                @endif
                            @endguest
                        </div>
                        <p class="text-center text-muted small mt-2 mb-0">
                            No credit card required for trial
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Premium Plan -->
            <div class="col-lg-4 col-md-6" data-aos="zoom-in" data-aos-delay="300">
                <div class="card pricing-card h-100 shadow-lg premium-card">
                    <div class="card-header text-center py-4 bg-gradient-premium text-white">
                        <h2 class="h4 mb-0">Premium</h2>
                        <p class="mb-0">Complete access</p>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="price-display">
                                <span class="currency">$</span>
                                <span class="price" data-monthly="29.99" data-yearly="23.99">29.99</span>
                                <span class="period">/month</span>
                            </div>
                            <small class="text-muted yearly-save d-none">
                                Billed $287.88 yearly (Save $72)
                            </small>
                        </div>
                        
                        <ul class="list-unstyled mb-4">
                            <li class="mb-3">
                                <i class="fas fa-check text-success me-2"></i>
                                <strong>Unlimited downloads</strong>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check text-success me-2"></i>
                                <strong>All search features</strong>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check text-success me-2"></i>
                                <strong>Maximum speed</strong> CDN
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check text-success me-2"></i>
                                Access to <strong>ALL files</strong>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check text-success me-2"></i>
                                <strong>Priority support</strong> 24/7
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check text-success me-2"></i>
                                <strong>Advanced analytics</strong>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check text-success me-2"></i>
                                <strong>Batch downloads</strong>
                            </li>
                            <li class="mb-3">
                                <i class="fas fa-check text-success me-2"></i>
                                <strong>API access</strong> included
                            </li>
                        </ul>
                        
                        <div class="d-grid">
                            @guest
                            <a href="{{ route('register') }}?plan=premium" class="btn btn-warning btn-lg">
                                <i class="fas fa-crown me-2"></i>Get Premium
                            </a>
                            @else
                                @if(!auth()->user()->subscription || auth()->user()->subscription->package_id != 3)
                                <a href="{{ route('checkout') }}?package=3" class="btn btn-warning btn-lg">
                                    <i class="fas fa-crown me-2"></i>Upgrade to Premium
                                </a>
                                @else
                                <button class="btn btn-secondary btn-lg" disabled>
                                    <i class="fas fa-crown me-2"></i>Current Plan
                                </button>
                                @endif
                            @endguest
                        </div>
                        <p class="text-center text-muted small mt-2 mb-0">
                            30-day money-back guarantee
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Enterprise CTA -->
        <div class="text-center mt-5">
            <p class="text-muted mb-3">Need a custom solution for your team?</p>
            <a href="{{ route('contact') }}" class="btn btn-outline-dark btn-lg">
                <i class="fas fa-building me-2"></i>Contact for Enterprise
            </a>
        </div>
    </div>
</section>

<!-- Features Comparison -->
<section class="comparison-section py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="h1 fw-bold">Compare Plans</h2>
            <p class="lead text-muted">Choose the plan that fits your needs</p>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-hover comparison-table">
                <thead class="table-dark">
                    <tr>
                        <th width="40%">Features</th>
                        <th class="text-center">Free</th>
                        <th class="text-center">Basic</th>
                        <th class="text-center">Premium</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Daily Downloads</strong></td>
                        <td class="text-center">2</td>
                        <td class="text-center">20</td>
                        <td class="text-center">Unlimited</td>
                    </tr>
                    <tr>
                        <td><strong>Monthly Bandwidth</strong></td>
                        <td class="text-center">1 GB</td>
                        <td class="text-center">50 GB</td>
                        <td class="text-center">Unlimited</td>
                    </tr>
                    <tr>
                        <td><strong>Download Speed</strong></td>
                        <td class="text-center">Standard</td>
                        <td class="text-center">Fast</td>
                        <td class="text-center">Maximum</td>
                    </tr>
                    <tr>
                        <td><strong>File Access</strong></td>
                        <td class="text-center">Free Files Only</td>
                        <td class="text-center">80% of Library</td>
                        <td class="text-center">100% Access</td>
                    </tr>
                    <tr>
                        <td><strong>Search Filters</strong></td>
                        <td class="text-center">Basic</td>
                        <td class="text-center">Advanced</td>
                        <td class="text-center">All Features</td>
                    </tr>
                    <tr>
                        <td><strong>Download History</strong></td>
                        <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                        <td class="text-center"><i class="fas fa-check text-success"></i></td>
                        <td class="text-center"><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr>
                        <td><strong>Priority Support</strong></td>
                        <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                        <td class="text-center">Email Only</td>
                        <td class="text-center">24/7 Priority</td>
                    </tr>
                    <tr>
                        <td><strong>API Access</strong></td>
                        <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                        <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                        <td class="text-center"><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr>
                        <td><strong>Batch Downloads</strong></td>
                        <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                        <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                        <td class="text-center"><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr>
                        <td><strong>Early Access</strong></td>
                        <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                        <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                        <td class="text-center"><i class="fas fa-check text-success"></i></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="faq-section py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="h1 fw-bold">Frequently Asked Questions</h2>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="pricingFAQ">
                    <div class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Can I change plans anytime?
                            </button>
                        </h3>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#pricingFAQ">
                            <div class="accordion-body">
                                Yes! You can upgrade or downgrade your plan at any time. When upgrading, you'll be charged a prorated amount. When downgrading, the change takes effect at the next billing cycle.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Is there a free trial?
                            </button>
                        </h3>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#pricingFAQ">
                            <div class="accordion-body">
                                Yes! Basic and Premium plans come with a 7-day free trial. No credit card required to start. You can cancel anytime during the trial period.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                What payment methods do you accept?
                            </button>
                        </h3>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#pricingFAQ">
                            <div class="accordion-body">
                                We accept all major credit cards (Visa, MasterCard, American Express), PayPal, and bank transfers for annual plans. All payments are processed securely through Stripe.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Can I cancel my subscription?
                            </button>
                        </h3>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#pricingFAQ">
                            <div class="accordion-body">
                                Absolutely! You can cancel your subscription at any time from your account dashboard. You'll continue to have access until the end of your current billing period.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('styles')
<style>
.pricing-header { 
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
}
.bg-gradient-premium {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}
.pricing-card {
    transition: transform 0.3s;
    position: relative;
}
.pricing-card:hover {
    transform: translateY(-10px);
}
.premium-card {
    border: 2px solid #f5576c;
}
.price-display {
    font-size: 3rem;
    font-weight: bold;
    color: #333;
}
.currency {
    font-size: 1.5rem;
    vertical-align: super;
}
.period {
    font-size: 1rem;
    color: #6c757d;
}
.ribbon {
    position: absolute;
    right: -5px;
    top: -5px;
    z-index: 1;
    overflow: hidden;
    width: 75px;
    height: 75px;
}
.ribbon span {
    font-size: 10px;
    font-weight: bold;
    color: white;
    text-align: center;
    line-height: 20px;
    transform: rotate(45deg);
    width: 100px;
    display: block;
    background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%);
    box-shadow: 0 3px 10px -5px rgba(0, 0, 0, 1);
    position: absolute;
    top: 19px;
    right: -21px;
}
.billing-toggle button.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
.comparison-table th {
    position: sticky;
    top: 0;
    z-index: 10;
}
</style>
@endpush

@push('scripts')
<script>
// Billing toggle
document.getElementById('monthlyBtn').addEventListener('click', function() {
    toggleBilling('monthly');
});

document.getElementById('yearlyBtn').addEventListener('click', function() {
    toggleBilling('yearly');
});

function toggleBilling(type) {
    // Update button states
    document.querySelectorAll('.billing-toggle button').forEach(btn => {
        btn.classList.remove('active');
    });
    document.getElementById(type + 'Btn').classList.add('active');
    
    // Update prices
    document.querySelectorAll('.price').forEach(price => {
        const monthlyPrice = price.dataset.monthly;
        const yearlyPrice = price.dataset.yearly;
        price.textContent = type === 'monthly' ? monthlyPrice : yearlyPrice;
    });
    
    // Update period text
    document.querySelectorAll('.period').forEach(period => {
        period.textContent = type === 'monthly' ? '/month' : '/month';
    });
    
    // Show/hide yearly save text
    document.querySelectorAll('.yearly-save').forEach(save => {
        if (type === 'yearly') {
            save.classList.remove('d-none');
        } else {
            save.classList.add('d-none');
        }
    });
}
</script>
@endpush