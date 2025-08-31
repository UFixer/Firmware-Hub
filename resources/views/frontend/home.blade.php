@extends('layouts.app')

@section('title', 'FirmwareHub - Premium Mobile Firmware Downloads')
@section('meta_description', 'Download latest mobile firmware, ROMs, and software updates. Unlimited downloads with premium subscription. Fast, secure, and reliable.')
@section('meta_keywords', 'firmware download, mobile firmware, ROM download, software updates, Android firmware, iOS firmware')

@section('structured_data')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "FirmwareHub",
    "url": "{{ url('/') }}",
    "description": "Premium mobile firmware download platform",
    "potentialAction": {
        "@type": "SearchAction",
        "target": "{{ url('/search') }}?q={search_term_string}",
        "query-input": "required name=search_term_string"
    }
}
</script>
@endsection

@section('content')
<!-- Hero Section -->
<section class="hero-section bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h1 class="display-4 fw-bold mb-3" data-aos="fade-up">
                    Download Premium Firmware Files
                </h1>
                <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                    Access thousands of mobile firmware files, ROMs, and software updates. 
                    Unlimited downloads with blazing-fast speeds.
                </p>
                <div class="d-flex flex-column flex-sm-row gap-3" data-aos="fade-up" data-aos-delay="200">
                    <a href="{{ route('browse') }}" class="btn btn-light btn-lg px-4">
                        <i class="fas fa-search me-2"></i>Browse Files
                    </a>
                    <a href="{{ route('packages') }}" class="btn btn-outline-light btn-lg px-4">
                        <i class="fas fa-crown me-2"></i>View Plans
                    </a>
                </div>
                
                <!-- Stats -->
                <div class="row mt-5 text-center text-sm-start" data-aos="fade-up" data-aos-delay="300">
                    <div class="col-4">
                        <h3 class="fw-bold">{{ number_format($stats['total_files'] ?? 5000) }}+</h3>
                        <p class="mb-0 small">Firmware Files</p>
                    </div>
                    <div class="col-4">
                        <h3 class="fw-bold">{{ number_format($stats['total_downloads'] ?? 50000) }}+</h3>
                        <p class="mb-0 small">Downloads</p>
                    </div>
                    <div class="col-4">
                        <h3 class="fw-bold">{{ number_format($stats['active_users'] ?? 1000) }}+</h3>
                        <p class="mb-0 small">Active Users</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <img src="https://cdn.example.com/hero-phones.svg" 
                     alt="Mobile Firmware Downloads" 
                     class="img-fluid" 
                     loading="lazy">
            </div>
        </div>
    </div>
</section>

<!-- Search Bar -->
<section class="search-section py-4 bg-light sticky-top">
    <div class="container">
        <form action="{{ route('search') }}" method="GET" class="search-form">
            <div class="input-group input-group-lg shadow-sm">
                <input type="text" 
                       name="q" 
                       class="form-control" 
                       placeholder="Search firmware, device model, or brand..."
                       aria-label="Search firmware"
                       required>
                <button class="btn btn-primary px-4" type="submit">
                    <i class="fas fa-search"></i>
                    <span class="d-none d-sm-inline ms-2">Search</span>
                </button>
            </div>
        </form>
    </div>
</section>

<!-- Categories Section -->
<section class="categories-section py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="h1 fw-bold">Browse by Category</h2>
            <p class="lead text-muted">Find firmware for your device brand</p>
        </div>
        
        <div class="row g-4">
            @foreach($categories as $category)
            <div class="col-6 col-md-4 col-lg-3" data-aos="zoom-in" data-aos-delay="{{ $loop->index * 50 }}">
                <a href="{{ route('category.show', $category->slug) }}" 
                   class="card h-100 text-decoration-none category-card">
                    <div class="card-body text-center p-4">
                        <img src="{{ $category->icon_url ?? 'https://cdn.example.com/category-default.png' }}" 
                             alt="{{ $category->name }}" 
                             class="mb-3"
                             width="60" 
                             height="60"
                             loading="lazy">
                        <h3 class="h5 mb-2">{{ $category->name }}</h3>
                        <p class="text-muted small mb-0">{{ $category->files_count }} files</p>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
        
        <div class="text-center mt-4">
            <a href="{{ route('categories.all') }}" class="btn btn-outline-primary">
                View All Categories <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Latest Files Section -->
<section class="latest-files-section py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h1 fw-bold mb-0">Latest Firmware</h2>
            <a href="{{ route('browse') }}" class="btn btn-link">View All</a>
        </div>
        
        <div class="row g-4">
            @foreach($latestFiles as $file)
            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                <div class="card h-100 file-card shadow-sm">
                    <img src="{{ $file->thumbnail_url ?? 'https://cdn.example.com/firmware-thumb.jpg' }}" 
                         class="card-img-top" 
                         alt="{{ $file->name }}"
                         height="180"
                         loading="lazy">
                    <div class="card-body">
                        <h3 class="h6 card-title text-truncate">
                            <a href="{{ route('file.show', $file->slug) }}" 
                               class="text-decoration-none text-dark stretched-link">
                                {{ $file->name }}
                            </a>
                        </h3>
                        <p class="text-muted small mb-2">
                            <i class="fas fa-folder me-1"></i> {{ $file->category->name }}
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-primary">{{ $file->version }}</span>
                            <small class="text-muted">
                                <i class="fas fa-download me-1"></i>{{ number_format($file->download_count) }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="h1 fw-bold">Why Choose FirmwareHub?</h2>
            <p class="lead text-muted">Premium features for power users</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="feature-box text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-bolt fa-3x text-primary"></i>
                    </div>
                    <h3 class="h5 mb-3">Lightning Fast</h3>
                    <p class="text-muted">High-speed CDN servers ensure maximum download speeds worldwide</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4">
                <div class="feature-box text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-infinity fa-3x text-primary"></i>
                    </div>
                    <h3 class="h5 mb-3">Unlimited Downloads</h3>
                    <p class="text-muted">Download as many files as you need with premium subscription</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4">
                <div class="feature-box text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-shield-alt fa-3x text-primary"></i>
                    </div>
                    <h3 class="h5 mb-3">Secure & Verified</h3>
                    <p class="text-muted">All firmware files are verified and scanned for your safety</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4">
                <div class="feature-box text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-mobile-alt fa-3x text-primary"></i>
                    </div>
                    <h3 class="h5 mb-3">Mobile Optimized</h3>
                    <p class="text-muted">Download directly to your device with our mobile-friendly interface</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4">
                <div class="feature-box text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-headset fa-3x text-primary"></i>
                    </div>
                    <h3 class="h5 mb-3">24/7 Support</h3>
                    <p class="text-muted">Get help anytime with our dedicated support team</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4">
                <div class="feature-box text-center p-4">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-sync fa-3x text-primary"></i>
                    </div>
                    <h3 class="h5 mb-3">Regular Updates</h3>
                    <p class="text-muted">New firmware added daily from official sources</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="h1 fw-bold mb-3">Ready to Get Started?</h2>
        <p class="lead mb-4">Join thousands of users downloading firmware files daily</p>
        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
            @guest
            <a href="{{ route('register') }}" class="btn btn-light btn-lg px-5">
                Start Free Trial
            </a>
            @else
            <a href="{{ route('packages') }}" class="btn btn-light btn-lg px-5">
                Upgrade to Premium
            </a>
            @endguest
            <a href="{{ route('browse') }}" class="btn btn-outline-light btn-lg px-5">
                Browse Files
            </a>
        </div>
    </div>
</section>

@endsection

@push('styles')
<style>
.hero-section { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.category-card:hover { transform: translateY(-5px); transition: all 0.3s; }
.file-card:hover { box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
.feature-box { transition: all 0.3s; }
.feature-box:hover { transform: translateY(-10px); }
</style>
@endpush