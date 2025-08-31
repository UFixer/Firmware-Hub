@extends('layouts.app')

@section('title', 'Browse Firmware Files - FirmwareHub')
@section('meta_description', 'Browse and download thousands of mobile firmware files. Filter by brand, model, and version. Fast and secure downloads.')

@section('content')
<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Browse Files</li>
        </ol>
    </div>
</nav>

<!-- Page Header -->
<section class="page-header py-4 bg-white border-bottom">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h2 mb-0 fw-bold">Browse Firmware Files</h1>
                <p class="text-muted mb-0">{{ number_format($totalFiles) }} files available</p>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <div class="btn-group" role="group" aria-label="View options">
                    <button type="button" class="btn btn-outline-secondary active" data-view="grid">
                        <i class="fas fa-th"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-view="list">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container py-4">
    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0 d-flex justify-content-between align-items-center">
                        Filters
                        <button class="btn btn-sm btn-link text-danger" id="clearFilters">Clear</button>
                    </h2>
                </div>
                <div class="card-body">
                    <form id="filterForm" method="GET" action="{{ route('browse') }}">
                        <!-- Search in results -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Search</label>
                            <input type="text" 
                                   name="search" 
                                   class="form-control form-control-sm" 
                                   placeholder="Search files..."
                                   value="{{ request('search') }}">
                        </div>
                        
                        <!-- Category Filter -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Category</label>
                            <select name="category" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                        {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }} ({{ $category->files_count }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Brand Filter -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Brand</label>
                            <select name="brand" class="form-select form-select-sm">
                                <option value="">All Brands</option>
                                @foreach($brands as $brand)
                                <option value="{{ $brand }}" 
                                        {{ request('brand') == $brand ? 'selected' : '' }}>
                                    {{ $brand }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Android Version -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Android Version</label>
                            <select name="android" class="form-select form-select-sm">
                                <option value="">Any Version</option>
                                <option value="14" {{ request('android') == '14' ? 'selected' : '' }}>Android 14</option>
                                <option value="13" {{ request('android') == '13' ? 'selected' : '' }}>Android 13</option>
                                <option value="12" {{ request('android') == '12' ? 'selected' : '' }}>Android 12</option>
                                <option value="11" {{ request('android') == '11' ? 'selected' : '' }}>Android 11</option>
                                <option value="10" {{ request('android') == '10' ? 'selected' : '' }}>Android 10</option>
                            </select>
                        </div>
                        
                        <!-- File Size -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">File Size</label>
                            <select name="size" class="form-select form-select-sm">
                                <option value="">Any Size</option>
                                <option value="0-500" {{ request('size') == '0-500' ? 'selected' : '' }}>< 500 MB</option>
                                <option value="500-1000" {{ request('size') == '500-1000' ? 'selected' : '' }}>500 MB - 1 GB</option>
                                <option value="1000-2000" {{ request('size') == '1000-2000' ? 'selected' : '' }}>1 - 2 GB</option>
                                <option value="2000+" {{ request('size') == '2000+' ? 'selected' : '' }}>> 2 GB</option>
                            </select>
                        </div>
                        
                        <!-- Sort By -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Sort By</label>
                            <select name="sort" class="form-select form-select-sm">
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                                <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Most Popular</option>
                                <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name (A-Z)</option>
                                <option value="size" {{ request('sort') == 'size' ? 'selected' : '' }}>File Size</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-filter me-2"></i>Apply Filters
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Mobile Filter Toggle -->
            <button class="btn btn-primary w-100 d-lg-none mt-3" 
                    type="button" 
                    data-bs-toggle="offcanvas" 
                    data-bs-target="#filterOffcanvas">
                <i class="fas fa-filter me-2"></i>Show Filters
            </button>
        </div>
        
        <!-- Files Grid/List -->
        <div class="col-lg-9">
            <!-- Results Info -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="text-muted mb-0">
                    Showing {{ $files->firstItem() }}-{{ $files->lastItem() }} of {{ $files->total() }} files
                </p>
                <div class="d-none d-md-block">
                    {{ $files->links('pagination::bootstrap-5') }}
                </div>
            </div>
            
            <!-- Files Grid View -->
            <div class="row g-3" id="filesGrid">
                @forelse($files as $file)
                <div class="col-6 col-md-4 col-xl-3 file-item">
                    <div class="card h-100 shadow-sm">
                        <a href="{{ route('file.show', $file->slug) }}" class="text-decoration-none">
                            <img src="{{ $file->thumbnail_url ?? 'https://cdn.example.com/default-thumb.jpg' }}" 
                                 class="card-img-top" 
                                 alt="{{ $file->name }}"
                                 height="150"
                                 loading="lazy">
                        </a>
                        <div class="card-body p-3">
                            <h3 class="h6 card-title mb-2 text-truncate">
                                <a href="{{ route('file.show', $file->slug) }}" 
                                   class="text-decoration-none text-dark">
                                    {{ $file->name }}
                                </a>
                            </h3>
                            <p class="small text-muted mb-2">
                                <i class="fas fa-folder me-1"></i>{{ $file->category->name }}
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    {{ $file->formatted_size }}
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-download"></i> {{ number_format($file->download_count) }}
                                </small>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0 pt-0">
                            <div class="d-grid">
                                @auth
                                    @if(auth()->user()->hasActiveSubscription())
                                    <a href="{{ route('file.download', $file->id) }}" 
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-download me-1"></i>Download
                                    </a>
                                    @else
                                    <a href="{{ route('packages') }}" 
                                       class="btn btn-warning btn-sm">
                                        <i class="fas fa-crown me-1"></i>Upgrade
                                    </a>
                                    @endif
                                @else
                                <a href="{{ route('login') }}" 
                                   class="btn btn-outline-primary btn-sm">
                                    Login to Download
                                </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No files found matching your criteria. Try adjusting your filters.
                    </div>
                </div>
                @endforelse
            </div>
            
            <!-- Pagination -->
            <div class="mt-4">
                {{ $files->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Mobile Filter Offcanvas -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="filterOffcanvas">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Filters</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <!-- Copy filter form here for mobile -->
    </div>
</div>

@endsection

@push('scripts')
<script>
// View toggle
document.querySelectorAll('[data-view]').forEach(btn => {
    btn.addEventListener('click', function() {
        const view = this.dataset.view;
        document.querySelectorAll('[data-view]').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const grid = document.getElementById('filesGrid');
        if (view === 'list') {
            grid.classList.remove('row', 'g-3');
            grid.classList.add('list-view');
        } else {
            grid.classList.add('row', 'g-3');
            grid.classList.remove('list-view');
        }
    });
});

// Clear filters
document.getElementById('clearFilters')?.addEventListener('click', function() {
    window.location.href = '{{ route("browse") }}';
});
</script>
@endpush