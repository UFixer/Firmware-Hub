{{-- Package Card Component --}}
@props([
    'package',
    'featured' => false,
    'currentPlan' => false,
    'billingCycle' => 'monthly'
])

<div class="package-card {{ $featured ? 'featured' : '' }} {{ $currentPlan ? 'current-plan' : '' }}">
    @if($featured)
    <div class="ribbon-wrapper">
        <div class="ribbon">MOST POPULAR</div>
    </div>
    @endif
    
    <div class="card h-100 shadow-sm {{ $featured ? 'border-primary border-2' : '' }}">
        <div class="card-header text-center py-4 {{ $featured ? 'bg-primary text-white' : 'bg-light' }}">
            <h3 class="h4 mb-0">{{ $package->name }}</h3>
            <p class="mb-0 {{ $featured ? 'text-white-50' : 'text-muted' }}">
                {{ $package->tagline }}
            </p>
        </div>
        
        <div class="card-body">
            <!-- Price Display -->
            <div class="text-center mb-4">
                <div class="price-wrapper">
                    <span class="currency">$</span>
                    <span class="price" 
                          data-monthly="{{ $package->monthly_price }}"
                          data-yearly="{{ $package->yearly_price }}">
                        {{ $billingCycle == 'yearly' ? $package->yearly_price : $package->monthly_price }}
                    </span>
                    <span class="period">/{{ $billingCycle == 'yearly' ? 'mo' : 'month' }}</span>
                </div>
                
                @if($billingCycle == 'yearly' && $package->yearly_savings > 0)
                <div class="savings-badge">
                    Save ${{ number_format($package->yearly_savings, 0) }}/year
                </div>
                @endif
                
                @if($package->trial_days > 0)
                <p class="text-muted small mt-2 mb-0">
                    {{ $package->trial_days }}-day free trial
                </p>
                @endif
            </div>
            
            <!-- Features List -->
            <ul class="features-list list-unstyled mb-4">
                @foreach($package->getFeatures() as $feature)
                <li class="mb-3 d-flex align-items-start">
                    @if($feature['included'])
                        <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                        <span>{{ $feature['text'] }}</span>
                    @else
                        <i class="fas fa-times-circle text-muted me-2 mt-1"></i>
                        <span class="text-muted">{{ $feature['text'] }}</span>
                    @endif
                </li>
                @endforeach
            </ul>
            
            <!-- Highlights -->
            @if($package->highlights)
            <div class="highlights mb-3">
                @foreach($package->highlights as $highlight)
                <span class="badge bg-light text-dark me-1 mb-1">
                    {{ $highlight }}
                </span>
                @endforeach
            </div>
            @endif
            
            <!-- CTA Button -->
            <div class="d-grid">
                @if($currentPlan)
                    <button class="btn btn-secondary btn-lg" disabled>
                        <i class="fas fa-check me-2"></i>Current Plan
                    </button>
                @else
                    @guest
                        @if($package->price == 0)
                            <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg">
                                Sign Up Free
                            </a>
                        @else
                            <a href="{{ route('register', ['plan' => $package->slug]) }}" 
                               class="btn {{ $featured ? 'btn-warning' : 'btn-primary' }} btn-lg">
                                @if($package->trial_days > 0)
                                    Start Free Trial
                                @else
                                    Get Started
                                @endif
                            </a>
                        @endif
                    @else
                        @if(auth()->user()->subscription && auth()->user()->subscription->package_id > $package->id)
                            <button class="btn btn-outline-secondary btn-lg" disabled>
                                <i class="fas fa-arrow-down me-2"></i>Downgrade Plan
                            </button>
                        @else
                            <a href="{{ route('checkout', ['package' => $package->id, 'billing' => $billingCycle]) }}" 
                               class="btn {{ $featured ? 'btn-warning' : 'btn-primary' }} btn-lg">
                                @if(auth()->user()->subscription)
                                    <i class="fas fa-arrow-up me-2"></i>Upgrade Now
                                @else
                                    @if($package->trial_days > 0)
                                        Start {{ $package->trial_days }}-Day Trial
                                    @else
                                        Subscribe Now
                                    @endif
                                @endif
                            </a>
                        @endif
                    @endguest
                </div>
            @endif
            
            <!-- Money Back Guarantee -->
            @if($package->guarantee_days > 0)
            <p class="text-center text-muted small mt-3 mb-0">
                <i class="fas fa-shield-alt me-1"></i>
                {{ $package->guarantee_days }}-day money-back guarantee
            </p>
            @endif
        </div>
        
        @if($featured)
        <div class="card-footer bg-primary text-white text-center py-2">
            <small>⭐ Recommended for most users</small>
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
.package-card {
    transition: transform 0.3s ease;
}

.package-card:hover {
    transform: translateY(-10px);
}

.package-card.featured {
    position: relative;
    transform: scale(1.05);
}

.ribbon-wrapper {
    position: absolute;
    right: -5px;
    top: -5px;
    z-index: 10;
    overflow: hidden;
    width: 85px;
    height: 85px;
}

.ribbon {
    font-size: 10px;
    font-weight: bold;
    color: white;
    text-align: center;
    line-height: 20px;
    transform: rotate(45deg);
    width: 120px;
    display: block;
    background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%);
    box-shadow: 0 3px 10px -5px rgba(0, 0, 0, 0.5);
    position: absolute;
    top: 25px;
    right: -30px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.price-wrapper {
    font-size: 3.5rem;
    font-weight: 700;
    color: #2c3e50;
    line-height: 1;
}

.price-wrapper .currency {
    font-size: 1.8rem;
    vertical-align: super;
    margin-right: 2px;
}

.price-wrapper .period {
    font-size: 1.2rem;
    color: #6c757d;
    font-weight: 400;
}

.savings-badge {
    display: inline-block;
    background: #28a745;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.875rem;
    margin-top: 10px;
}

.features-list li {
    font-size: 0.95rem;
    line-height: 1.4;
}

.highlights .badge {
    font-weight: 500;
    padding: 6px 10px;
    border: 1px solid #dee2e6;
}

.current-plan .card {
    background: #f8f9fa;
    border: 2px solid #6c757d;
}
</style>
@endpush