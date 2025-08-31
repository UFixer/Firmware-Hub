@extends('layouts.app')

@section('title', $category->name . ' Firmware Downloads - FirmwareHub')
@section('meta_description', 'Download ' . $category->name . ' firmware files. Browse ' . $category->files_count . ' files for ' . $category->name . ' devices.')
@section('meta_keywords', $category->name . ' firmware, ' . $category->name . ' ROM, mobile firmware download')

@section('structured_data')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "CollectionPage",
    "name": "{{ $category->name }} Firmware",
    "description": "{{ $category->description }}",
    "url": "{{ url()->current() }}",
    "numberOfItems": {{ $category->files_count }},
    "breadcrumb": {
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "Home",
                "item": "{{ route('home') }}"
            },
            {
                "@type": "ListItem",
                "position": 2,
                "name": "Categories",
                "item": "{{ route('categories.all') }}"
            },
            {
                "@type": "ListItem",
                "position": 3,
                "name": "{{ $category->name }}",
                "item": "{{ url()->current() }}"
            }
        ]
    }
}
</script>
@endsection

@section('content')
<!-- Category Header -->
<section class="category-header bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent p-0 mb-3">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('categories.all') }}" class="text-white-50">Categories</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">{{ $category->name }}</li>
                    </ol>
                </nav>
                <h1 class="display-5 fw-bold mb-3">{{ $category->name }} Firmware</h1>
                <p class="lead mb-0">{{ $category->description }}</p>
                <p class="mt-2 mb-0">
                    <span class="badge bg-white text-primary me-2">
                        <i class="fas fa-file me-1"></i>{{ number_format($category->files_count) }} Files
                    </span>
                    <span class="badge bg-white text-primary">
                        <i class="fas fa-download me-1"></i>{{ number_format($totalDownloads) }} Downloads
                    </span>
                </p>
            </div>
            <div class="col-md-4 text-center">
                @if($category->icon_url)
                <img src="{{ $category->icon_url }}" 
                     alt="{{ $category->name }}" 
                     class="img-fluid"
                     style="max-width: 150px;">
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Filter Bar -->
<section class="filter-bar bg-light py-3 sticky-top border-bottom">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <form class="d-flex" method="GET" action="{{ url()->current() }}">
                    <input type="text" 
                           name="search" 
                           class="form-control me-2" 
                           placeholder="Search in {{ $category->name }}..."
                           value="{{ request('search') }}">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            <div class="col-md-6 mt-3 mt-md-0">
                <div class="d-flex justify-content-md-end gap-2">
                    <select name="sort" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest</option>
                        <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Popular</option>
                        <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name</option>
                    </select>
                    <select name="model" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        <option value="">All Models</option>
                        @foreach($models as $model)
                        <option value="{{ $model }}" {{ request('model') == $model ? 'selected' : '' }}>
                            {{ $model }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<div class="container py-4">
    <div class="row">
        <!-- Subcategories Sidebar (if any) -->
        @if($subcategories->count() > 0)
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Subcategories</h2>
                </div>
                <div class="list-group list-group-flush">
                    @foreach($subcategories as $sub)
                    <a href="{{ route('category.show', $sub->slug) }}" 
                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        {{ $sub->name }}
                        <span class="badge bg-primary rounded-pill">{{ $sub->files_count }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
            
            <!-- Popular Models -->
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Popular Models</h2>
                </div>
                <div class="list-group list-group-flush">
                    @foreach($popularModels as $model)
                    <a href="?model={{ $model->model }}" 
                       class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>{{ $model->model }}</span>
                            <small class="text-muted">{{ $model->count }} files</small>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        
        <!-- Files Grid -->
        <div class="{{ $subcategories->count() > 0 ? 'col-lg-9' : 'col-12' }}">
            <!-- Results Info -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 mb-0">
                    {{ request('search') ? 'Search Results' : 'All Files' }}
                    <span class="text-muted">({{ $files->total() }})</span>
                </h2>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-secondary btn-sm active" data-view="grid">
                        <i class="fas fa-th"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-view="list">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
            
            <!-- Files Grid -->
            <div class="row g-3" id="filesContainer">
                @forelse($files as $file)
                <div class="col-6 col-md-4 col-xl-3">
                    <article class="card h-100 shadow-sm file-card">
                        <a href="{{ route('file.show', $file->slug) }}">
                            <img src="{{ $file->thumbnail_url ?? 'https://cdn.example.com/default-thumb.jpg' }}" 
                                 class="card-img-top" 
                                 alt="{{ $file->name }}"
                                 height="150"
                                 loading="lazy">
                        </a>
                        <div class="card-body p-3">
                            <h3 class="h6 card-title mb-2">
                                <a href="{{ route('file.show', $file->slug) }}" 
                                   class="text-decoration-none text-dark stretched-link">
                                    {{ Str::limit($file->name, 50) }}
                                </a>
                            </h3>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary">{{ $file->version }}</span>
                                <small class="text-muted">{{ $file->formatted_size }}</small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-warning small">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= floor($file->rating ?? 4))
                                            <i class="fas fa-star"></i>
                                        @else
                                            <i class="far fa-star"></i>
                                        @endif
                                    @endfor
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-download"></i> {{ number_format($file->download_count) }}
                                </small>
                            </div>
                        </div>
                    </article>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-info text-center py-5">
                        <i class="fas fa-info-circle fa-3x mb-3"></i>
                        <h3 class="h5">No files found</h3>
                        <p class="mb-0">
                            @if(request('search'))
                                No files matching "{{ request('search') }}" in {{ $category->name }}.
                            @else
                                No files available in this category yet.
                            @endif
                        </p>
                        <a href="{{ route('category.show', $category->slug) }}" class="btn btn-primary mt-3">
                            Clear Filters
                        </a>
                    </div>
                </div>
                @endforelse
            </div>
            
            <!-- Pagination -->
            <div class="mt-4">
                {{ $files->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
            
            <!-- SEO Content -->
            @if($category->seo_content)
            <div class="card mt-5 border-0 bg-light">
                <div class="card-body">
                    <h2 class="h4 mb-3">About {{ $category->name }} Firmware</h2>
                    <div class="prose">
                        {!! $category->seo_content !!}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.category-header { 
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
}
.file-card { 
    transition: transform 0.2s, box-shadow 0.2s; 
}
.file-card:hover { 
    transform: translateY(-5px); 
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; 
}
.filter-bar {
    z-index: 100;
}
</style>
@endpush

@push('scripts')
<script>
// View toggle functionality
document.querySelectorAll('[data-view]').forEach(btn => {
    btn.addEventListener('click', function() {
        const view = this.dataset.view;
        const container = document.getElementById('filesContainer');
        
        // Update active button
        document.querySelectorAll('[data-view]').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        // Toggle view classes
        if (view === 'list') {
            container.classList.remove('row', 'g-3');
            container.classList.add('list-group');
            // Add list view classes to items
            container.querySelectorAll('.col-6').forEach(col => {
                col.classList.remove('col-6', 'col-md-4', 'col-xl-3');
                col.classList.add('list-group-item');
            });
        } else {
            container.classList.add('row', 'g-3');
            container.classList.remove('list-group');
            // Restore grid classes
            container.querySelectorAll('.list-group-item').forEach(item => {
                item.classList.add('col-6', 'col-md-4', 'col-xl-3');
                item.classList.remove('list-group-item');
            });
        }
    });
});
</script>
@endpush