@extends('layouts.app')

@section('title', $file->name . ' - Download Firmware | FirmwareHub')
@section('meta_description', $file->description ?? 'Download ' . $file->name . ' firmware file. Version ' . $file->version . '. Fast and secure download.')
@section('meta_keywords', $file->tags ?? $file->name . ', firmware download, ' . $file->category->name)

@section('structured_data')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "SoftwareApplication",
    "name": "{{ $file->name }}",
    "description": "{{ $file->description }}",
    "applicationCategory": "Firmware",
    "operatingSystem": "{{ $file->android_version ? 'Android ' . $file->android_version : 'Mobile OS' }}",
    "fileSize": "{{ $file->formatted_size }}",
    "dateModified": "{{ $file->updated_at->toIso8601String() }}",
    "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "{{ $file->rating ?? 4.5 }}",
        "reviewCount": "{{ $file->reviews_count ?? 0 }}"
    },
    "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "USD"
    }
}
</script>
@endsection

@section('content')
<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0" itemscope itemtype="https://schema.org/BreadcrumbList">
            <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <a href="{{ route('home') }}" itemprop="item">
                    <span itemprop="name">Home</span>
                </a>
                <meta itemprop="position" content="1" />
            </li>
            <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <a href="{{ route('category.show', $file->category->slug) }}" itemprop="item">
                    <span itemprop="name">{{ $file->category->name }}</span>
                </a>
                <meta itemprop="position" content="2" />
            </li>
            <li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <span itemprop="name">{{ $file->name }}</span>
                <meta itemprop="position" content="3" />
            </li>
        </ol>
    </div>
</nav>

<div class="container py-4">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- File Header -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <img src="{{ $file->thumbnail_url ?? 'https://cdn.example.com/default-firmware.jpg' }}" 
                                 alt="{{ $file->name }}" 
                                 class="img-fluid rounded">
                        </div>
                        <div class="col-md-8">
                            <h1 class="h3 mb-3">{{ $file->name }}</h1>
                            
                            <!-- File Info -->
                            <div class="mb-3">
                                <span class="badge bg-primary me-2">{{ $file->version }}</span>
                                <span class="badge bg-secondary me-2">{{ $file->category->name }}</span>
                                @if($file->is_verified)
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>Verified
                                </span>
                                @endif
                            </div>
                            
                            <!-- Ratings -->
                            <div class="mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="text-warning me-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= floor($file->rating ?? 4.5))
                                                <i class="fas fa-star"></i>
                                            @elseif($i - 0.5 <= ($file->rating ?? 4.5))
                                                <i class="fas fa-star-half-alt"></i>
                                            @else
                                                <i class="far fa-star"></i>
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="text-muted">
                                        {{ number_format($file->rating ?? 4.5, 1) }} 
                                        ({{ number_format($file->reviews_count ?? 0) }} reviews)
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Download Button -->
                            <div class="d-grid gap-2 d-md-block">
                                @auth
                                    @if(auth()->user()->hasActiveSubscription())
                                    <button class="btn btn-success btn-lg" onclick="startDownload({{ $file->id }})">
                                        <i class="fas fa-download me-2"></i>Download Now
                                    </button>
                                    @else
                                    <a href="{{ route('packages') }}" class="btn btn-warning btn-lg">
                                        <i class="fas fa-crown me-2"></i>Upgrade to Download
                                    </a>
                                    @endif
                                @else
                                <a href="{{ route('login') }}" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login to Download
                                </a>
                                @endauth
                                
                                <button class="btn btn-outline-secondary btn-lg" onclick="addToCart({{ $file->id }})">
                                    <i class="fas fa-cart-plus me-2"></i>Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- File Details Tabs -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#description">Description</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#specifications">Specifications</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#changelog">Changelog</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#reviews">Reviews</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Description Tab -->
                        <div class="tab-pane fade show active" id="description">
                            <h2 class="h5 mb-3">About This Firmware</h2>
                            <div class="prose">
                                {!! nl2br(e($file->description)) !!}
                            </div>
                            
                            @if($file->features)
                            <h3 class="h6 mt-4 mb-3">Key Features</h3>
                            <ul class="list-unstyled">
                                @foreach(explode("\n", $file->features) as $feature)
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>{{ $feature }}
                                </li>
                                @endforeach
                            </ul>
                            @endif
                        </div>
                        
                        <!-- Specifications Tab -->
                        <div class="tab-pane fade" id="specifications">
                            <h2 class="h5 mb-3">Technical Specifications</h2>
                            <table class="table table-striped">
                                <tbody>
                                    <tr>
                                        <th width="200">File Name</th>
                                        <td>{{ $file->filename }}</td>
                                    </tr>
                                    <tr>
                                        <th>Version</th>
                                        <td>{{ $file->version }}</td>
                                    </tr>
                                    <tr>
                                        <th>File Size</th>
                                        <td>{{ $file->formatted_size }}</td>
                                    </tr>
                                    <tr>
                                        <th>Android Version</th>
                                        <td>{{ $file->android_version ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Build Date</th>
                                        <td>{{ $file->build_date?->format('M d, Y') ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>MD5 Checksum</th>
                                        <td><code>{{ $file->md5_hash ?? 'N/A' }}</code></td>
                                    </tr>
                                    <tr>
                                        <th>Downloads</th>
                                        <td>{{ number_format($file->download_count) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Upload Date</th>
                                        <td>{{ $file->created_at->format('M d, Y') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Changelog Tab -->
                        <div class="tab-pane fade" id="changelog">
                            <h2 class="h5 mb-3">Version History</h2>
                            @if($file->changelog)
                                <div class="changelog">
                                    {!! nl2br(e($file->changelog)) !!}
                                </div>
                            @else
                                <p class="text-muted">No changelog available for this version.</p>
                            @endif
                        </div>
                        
                        <!-- Reviews Tab -->
                        <div class="tab-pane fade" id="reviews">
                            <h2 class="h5 mb-3">User Reviews</h2>
                            
                            @auth
                            <div class="card bg-light mb-4">
                                <div class="card-body">
                                    <h3 class="h6 mb-3">Write a Review</h3>
                                    <form action="{{ route('file.review', $file->id) }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <div class="rating-input">
                                                @for($i = 5; $i >= 1; $i--)
                                                <input type="radio" name="rating" value="{{ $i }}" id="star{{ $i }}">
                                                <label for="star{{ $i }}"><i class="fas fa-star"></i></label>
                                                @endfor
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <textarea name="comment" 
                                                      class="form-control" 
                                                      rows="3" 
                                                      placeholder="Share your experience..."></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Submit Review</button>
                                    </form>
                                </div>
                            </div>
                            @endauth
                            
                            <!-- Reviews List -->
                            <div class="reviews-list">
                                @forelse($file->reviews as $review)
                                <div class="review-item mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between mb-2">
                                        <div>
                                            <strong>{{ $review->user->name }}</strong>
                                            <div class="text-warning small">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= $review->rating)
                                                        <i class="fas fa-star"></i>
                                                    @else
                                                        <i class="far fa-star"></i>
                                                    @endif
                                                @endfor
                                            </div>
                                        </div>
                                        <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-0">{{ $review->comment }}</p>
                                </div>
                                @empty
                                <p class="text-muted">No reviews yet. Be the first to review!</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Download Info -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h5 mb-3">Download Information</h2>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-server text-primary me-2"></i>
                            <strong>High-Speed Servers</strong>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-shield-alt text-success me-2"></i>
                            <strong>Virus Scanned</strong>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-lock text-warning me-2"></i>
                            <strong>Secure Download</strong>
                        </li>
                        <li>
                            <i class="fas fa-headset text-info me-2"></i>
                            <strong>24/7 Support</strong>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Related Files -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Related Files</h2>
                </div>
                <div class="list-group list-group-flush">
                    @foreach($relatedFiles as $related)
                    <a href="{{ route('file.show', $related->slug) }}" 
                       class="list-group-item list-group-item-action">
                        <div class="d-flex align-items-center">
                            <img src="{{ $related->thumbnail_url ?? 'https://cdn.example.com/thumb.jpg' }}" 
                                 alt="{{ $related->name }}"
                                 width="50" 
                                 height="50" 
                                 class="rounded me-3">
                            <div class="flex-grow-1">
                                <h3 class="h6 mb-1 text-truncate">{{ $related->name }}</h3>
                                <small class="text-muted">{{ $related->formatted_size }}</small>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function startDownload(fileId) {
    // Show loading state
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Preparing...';
    
    // Initiate download
    window.location.href = `/download/${fileId}`;
    
    // Reset button after delay
    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-download me-2"></i>Download Now';
    }, 3000);
}

function addToCart(fileId) {
    fetch('/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            type: 'file',
            id: fileId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            alert('Added to cart!');
        }
    });
}
</script>
@endpush