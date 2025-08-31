@extends('layouts.app')

@section('title', 'Search Results for "' . $query . '" - FirmwareHub')
@section('meta_description', 'Search results for ' . $query . '. Find firmware files, ROMs, and software updates.')

@section('content')
<!-- Search Header -->
<section class="search-header bg-light py-4 border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Search Results</li>
            </ol>
        </nav>
        
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="h3 mb-3">
                    Search Results for 
                    <span class="text-primary">"{{ $query }}"</span>
                </h1>
                <p class="text-muted mb-0">
                    Found <strong>{{ $results->total() }}</strong> results 
                    @if($filters)
                        with filters:
                        @foreach($filters as $key => $value)
                            <span class="badge bg-secondary ms-1">{{ ucfirst($key) }}: {{ $value }}</span>
                        @endforeach
                    @endif
                </p>
            </div>
            <div class="col-lg-4">
                <!-- Search Form -->
                <form method="GET" action="{{ route('search') }}" class="search-form">
                    <div class="input-group">
                        <input type="text" 
                               name="q" 
                               class="form-control" 
                               placeholder="Search again..."
                               value="{{ $query }}"
                               required>
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<div class="container py-4">
    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Refine Search</h2>
                    @if($hasFilters)
                    <a href="{{ route('search', ['q' => $query]) }}" class="btn btn-sm btn-link text-danger p-0">
                        Clear
                    </a>
                    @endif
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('search') }}">
                        <input type="hidden" name="q" value="{{ $query }}">
                        
                        <!-- Category Filter -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Category</label>
                            @foreach($facets['categories'] as $category)
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="categories[]" 
                                       value="{{ $category->id }}"
                                       id="cat_{{ $category->id }}"
                                       {{ in_array($category->id, request('categories', [])) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="cat_{{ $category->id }}">
                                    {{ $category->name }} 
                                    <span class="text-muted">({{ $category->count }})</span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                        
                        <!-- Type Filter -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">File Type</label>
                            @foreach($facets['types'] as $type => $count)
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="types[]" 
                                       value="{{ $type }}"
                                       id="type_{{ $type }}"
                                       {{ in_array($type, request('types', [])) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="type_{{ $type }}">
                                    {{ strtoupper($type) }} 
                                    <span class="text-muted">({{ $count }})</span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                        
                        <!-- Date Range -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Upload Date</label>
                            <select name="date_range" class="form-select form-select-sm">
                                <option value="">Any time</option>
                                <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="week" {{ request('date_range') == 'week' ? 'selected' : '' }}>This week</option>
                                <option value="month" {{ request('date_range') == 'month' ? 'selected' : '' }}>This month</option>
                                <option value="year" {{ request('date_range') == 'year' ? 'selected' : '' }}>This year</option>
                            </select>
                        </div>
                        
                        <!-- File Size -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">File Size</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="size[]" value="small" id="size_small"
                                       {{ in_array('small', request('size', [])) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="size_small">
                                    < 500 MB
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="size[]" value="medium" id="size_medium"
                                       {{ in_array('medium', request('size', [])) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="size_medium">
                                    500 MB - 2 GB
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="size[]" value="large" id="size_large"
                                       {{ in_array('large', request('size', [])) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="size_large">
                                    > 2 GB
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            Apply Filters
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Search Tips -->
            <div class="card mt-3 border-0 bg-light">
                <div class="card-body">
                    <h3 class="h6 mb-2">
                        <i class="fas fa-lightbulb text-warning me-2"></i>Search Tips
                    </h3>
                    <ul class="small mb-0 ps-3">
                        <li>Use model numbers for exact matches</li>
                        <li>Try brand names for broader results</li>
                        <li>Include version numbers when needed</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Search Results -->
        <div class="col-lg-9">
            <!-- Sort Options -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center">
                    <label class="me-2 text-muted small">Sort by:</label>
                    <select class="form-select form-select-sm w-auto" onchange="updateSort(this.value)">
                        <option value="relevance" {{ request('sort') == 'relevance' ? 'selected' : '' }}>Relevance</option>
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest</option>
                        <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Most Popular</option>
                        <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name (A-Z)</option>
                    </select>
                </div>
                
                <!-- View Toggle -->
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-secondary btn-sm active" data-view="grid">
                        <i class="fas fa-th"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-view="list">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
            
            <!-- Results Grid -->
            <div class="row g-3" id="searchResults">
                @forelse($results as $file)
                <div class="col-12 search-result-item">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2 text-center mb-3 mb-md-0">
                                    <img src="{{ $file->thumbnail_url ?? 'https://cdn.example.com/default-thumb.jpg' }}" 
                                         alt="{{ $file->name }}"
                                         class="img-fluid rounded"
                                         style="max-width: 100px;">
                                </div>
                                <div class="col-md-7">
                                    <h3 class="h5 mb-2">
                                        <a href="{{ route('file.show', $file->slug) }}" 
                                           class="text-decoration-none">
                                            {!! highlightSearchTerm($file->name, $query) !!}
                                        </a>
                                    </h3>
                                    <p class="text-muted small mb-2">
                                        {!! highlightSearchTerm(Str::limit($file->description, 150), $query) !!}
                                    </p>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-folder me-1"></i>{{ $file->category->name }}
                                        </span>
                                        <span class="badge bg-info">{{ $file->version }}</span>
                                        <span class="badge bg-light text-dark">{{ $file->formatted_size }}</span>
                                        @if($file->is_verified)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>Verified
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="mb-2">
                                        <div class="text-warning">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= floor($file->rating ?? 4))
                                                    <i class="fas fa-star"></i>
                                                @else
                                                    <i class="far fa-star"></i>
                                                @endif
                                            @endfor
                                        </div>
                                        <small class="text-muted">
                                            {{ number_format($file->download_count) }} downloads
                                        </small>
                                    </div>
                                    @auth
                                        @if(auth()->user()->hasActiveSubscription())
                                        <a href="{{ route('file.download', $file->id) }}" 
                                           class="btn btn-primary btn-sm w-100">
                                            <i class="fas fa-download me-1"></i>Download
                                        </a>
                                        @else
                                        <a href="{{ route('file.show', $file->slug) }}" 
                                           class="btn btn-outline-primary btn-sm w-100">
                                            View Details
                                        </a>
                                        @endif
                                    @else
                                    <a href="{{ route('login') }}" 
                                       class="btn btn-outline-primary btn-sm w-100">
                                        Login to Download
                                    </a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-warning text-center py-5">
                        <i class="fas fa-search fa-3x mb-3 text-warning"></i>
                        <h3 class="h4">No results found</h3>
                        <p class="mb-3">
                            We couldn't find any files matching "<strong>{{ $query }}</strong>"
                        </p>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="{{ route('browse') }}" class="btn btn-primary">
                                Browse All Files
                            </a>
                            <button type="button" class="btn btn-outline-primary" onclick="showSearchSuggestions()">
                                Search Suggestions
                            </button>
                        </div>
                    </div>
                    
                    <!-- Suggestions -->
                    <div id="searchSuggestions" class="card mt-4 d-none">
                        <div class="card-body">
                            <h4 class="h5 mb-3">Try searching for:</h4>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($suggestions as $suggestion)
                                <a href="{{ route('search', ['q' => $suggestion]) }}" 
                                   class="btn btn-outline-secondary btn-sm">
                                    {{ $suggestion }}
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endforelse
            </div>
            
            <!-- Pagination -->
            @if($results->hasPages())
            <div class="mt-4">
                {{ $results->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
            @endif
            
            <!-- Related Searches -->
            @if($relatedSearches->count() > 0)
            <div class="card mt-4 border-0 bg-light">
                <div class="card-body">
                    <h3 class="h5 mb-3">Related Searches</h3>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($relatedSearches as $related)
                        <a href="{{ route('search', ['q' => $related]) }}" 
                           class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-search me-1"></i>{{ $related }}
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Update sort parameter
function updateSort(value) {
    const url = new URL(window.location);
    url.searchParams.set('sort', value);
    window.location = url.toString();
}

// Show search suggestions
function showSearchSuggestions() {
    document.getElementById('searchSuggestions').classList.remove('d-none');
}

// View toggle
document.querySelectorAll('[data-view]').forEach(btn => {
    btn.addEventListener('click', function() {
        const view = this.dataset.view;
        const container = document.getElementById('searchResults');
        
        document.querySelectorAll('[data-view]').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        if (view === 'grid') {
            container.classList.add('row', 'g-3');
            document.querySelectorAll('.search-result-item').forEach(item => {
                item.classList.remove('col-12');
                item.classList.add('col-md-6', 'col-lg-4');
            });
        } else {
            container.classList.remove('row', 'g-3');
            document.querySelectorAll('.search-result-item').forEach(item => {
                item.classList.add('col-12');
                item.classList.remove('col-md-6', 'col-lg-4');
            });
        }
    });
});

// Highlight search terms (PHP helper function needed)
@php
function highlightSearchTerm($text, $term) {
    return preg_replace('/(' . preg_quote($term, '/') . ')/i', '<mark>$1</mark>', $text);
}
@endphp
</script>
@endpush

@push('styles')
<style>
mark {
    background-color: #fff3cd;
    padding: 0.1em 0.2em;
    font-weight: 500;
}
.search-result-item .card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
}
</style>
@endpush