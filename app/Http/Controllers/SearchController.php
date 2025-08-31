<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    /**
     * Items per page
     */
    protected $perPage = 20;
    
    /**
     * Minimum search query length
     */
    protected $minQueryLength = 3;
    
    /**
     * Maximum search query length
     */
    protected $maxQueryLength = 100;
    
    /**
     * Perform global search.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|min:' . $this->minQueryLength . '|max:' . $this->maxQueryLength,
            'type' => 'nullable|in:all,files,categories,brands'
        ]);
        
        $query = $validated['q'];
        $type = $validated['type'] ?? 'all';
        
        // Clean and prepare search query
        $searchQuery = $this->prepareSearchQuery($query);
        
        // Log search query
        $this->logSearch($query, $type);
        
        $results = [];
        
        // Search based on type
        switch ($type) {
            case 'files':
                $results['files'] = $this->searchFiles($searchQuery);
                break;
            case 'categories':
                $results['categories'] = $this->searchCategories($searchQuery);
                break;
            case 'brands':
                $results['brands'] = $this->searchBrands($searchQuery);
                break;
            default: // all
                $results = $this->searchAll($searchQuery);
        }
        
        // Get search suggestions
        $suggestions = $this->getSearchSuggestions($query);
        
        // Get popular searches
        $popularSearches = $this->getPopularSearches();
        
        return view('search.results', compact(
            'results',
            'query',
            'type',
            'suggestions',
            'popularSearches'
        ));
    }
    
    /**
     * AJAX autocomplete search.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function autocomplete(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:50'
        ]);
        
        $query = $request->get('q');
        
        // Get cached suggestions if available
        $cacheKey = 'search_autocomplete_' . md5($query);
        $suggestions = Cache::remember($cacheKey, 3600, function () use ($query) {
            $suggestions = [];
            
            // Search files
            $files = File::public()
                ->where('name', 'LIKE', $query . '%')
                ->select('id', 'name', 'slug', 'brand', 'model')
                ->limit(5)
                ->get();
            
            foreach ($files as $file) {
                $suggestions[] = [
                    'type' => 'file',
                    'title' => $file->name,
                    'subtitle' => $file->brand . ' ' . $file->model,
                    'url' => route('files.show', $file->slug)
                ];
            }
            
            // Search categories
            $categories = Category::active()
                ->where('name', 'LIKE', $query . '%')
                ->select('id', 'name', 'slug')
                ->limit(3)
                ->get();
            
            foreach ($categories as $category) {
                $suggestions[] = [
                    'type' => 'category',
                    'title' => $category->name,
                    'subtitle' => 'Category',
                    'url' => route('categories.show', $category->slug)
                ];
            }
            
            // Search brands
            $brands = DB::table('files')
                ->select('brand', DB::raw('COUNT(*) as count'))
                ->where('brand', 'LIKE', $query . '%')
                ->whereNotNull('brand')
                ->where('status', 'active')
                ->where('is_public', true)
                ->groupBy('brand')
                ->orderBy('count', 'desc')
                ->limit(3)
                ->get();
            
            foreach ($brands as $brand) {
                $suggestions[] = [
                    'type' => 'brand',
                    'title' => $brand->brand,
                    'subtitle' => $brand->count . ' files',
                    'url' => route('files.index', ['brand' => $brand->brand])
                ];
            }
            
            return $suggestions;
        });
        
        return response()->json($suggestions);
    }
    
    /**
     * Advanced search page.
     *
     * @return \Illuminate\View\View
     */
    public function advanced()
    {
        // Get filter options
        $filters = [
            'categories' => Category::active()
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
            'brands' => DB::table('files')
                ->select('brand')
                ->whereNotNull('brand')
                ->where('status', 'active')
                ->where('is_public', true)
                ->distinct()
                ->orderBy('brand')
                ->pluck('brand'),
            'platforms' => ['android', 'ios', 'windows', 'other'],
            'file_types' => ['firmware', 'rom', 'tool', 'driver', 'documentation']
        ];
        
        return view('search.advanced', compact('filters'));
    }
    
    /**
     * Process advanced search.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function advancedSearch(Request $request)
    {
        $validated = $request->validate([
            'keyword' => 'nullable|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'platform' => 'nullable|in:android,ios,windows,other',
            'file_type' => 'nullable|string',
            'version' => 'nullable|string|max:50',
            'min_date' => 'nullable|date',
            'max_date' => 'nullable|date|after_or_equal:min_date',
            'min_size' => 'nullable|numeric|min:0',
            'max_size' => 'nullable|numeric|min:0',
            'free_only' => 'nullable|boolean'
        ]);
        
        $query = File::public()->with(['category']);
        
        // Apply filters
        if (!empty($validated['keyword'])) {
            $keyword = $validated['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('description', 'LIKE', '%' . $keyword . '%');
            });
        }
        
        if (!empty($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }
        
        if (!empty($validated['brand'])) {
            $query->where('brand', $validated['brand']);
        }
        
        if (!empty($validated['model'])) {
            $query->where('model', 'LIKE', '%' . $validated['model'] . '%');
        }
        
        if (!empty($validated['platform'])) {
            $query->where('platform', $validated['platform']);
        }
        
        if (!empty($validated['file_type'])) {
            $query->where('file_type', $validated['file_type']);
        }
        
        if (!empty($validated['version'])) {
            $query->where('version', 'LIKE', '%' . $validated['version'] . '%');
        }
        
        if (!empty($validated['min_date'])) {
            $query->where('created_at', '>=', $validated['min_date']);
        }
        
        if (!empty($validated['max_date'])) {
            $query->where('created_at', '<=', $validated['max_date']);
        }
        
        if (!empty($validated['min_size'])) {
            $query->where('size', '>=', $validated['min_size'] * 1048576); // Convert MB to bytes
        }
        
        if (!empty($validated['max_size'])) {
            $query->where('size', '<=', $validated['max_size'] * 1048576);
        }
        
        if (!empty($validated['free_only'])) {
            $query->where('is_premium', false);
        }
        
        // Get results
        $results = $query->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
        
        // Log advanced search
        $this->logSearch(json_encode($validated), 'advanced');
        
        return view('search.results', [
            'results' => ['files' => $results],
            'query' => $validated['keyword'] ?? 'Advanced Search',
            'type' => 'advanced',
            'filters' => $validated
        ]);
    }
    
    /**
     * Search files.
     *
     * @param string $query
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    protected function searchFiles($query)
    {
        return File::public()
            ->where(function ($q) use ($query) {
                // Basic search
                $q->where('name', 'LIKE', '%' . $query . '%')
                    ->orWhere('description', 'LIKE', '%' . $query . '%')
                    ->orWhere('brand', 'LIKE', '%' . $query . '%')
                    ->orWhere('model', 'LIKE', '%' . $query . '%')
                    ->orWhere('version', 'LIKE', '%' . $query . '%');
                
                // Search in JSON fields
                $q->orWhereRaw("JSON_SEARCH(tags, 'one', ?) IS NOT NULL", [$query]);
                $q->orWhereRaw("JSON_SEARCH(compatible_models, 'one', ?) IS NOT NULL", [$query]);
            })
            ->with(['category'])
            ->orderByRaw("
                CASE 
                    WHEN name LIKE ? THEN 1
                    WHEN name LIKE ? THEN 2
                    WHEN description LIKE ? THEN 3
                    ELSE 4
                END
            ", [$query . '%', '%' . $query . '%', '%' . $query . '%'])
            ->paginate($this->perPage);
    }
    
    /**
     * Search categories.
     *
     * @param string $query
     * @return \Illuminate\Support\Collection
     */
    protected function searchCategories($query)
    {
        return Category::active()
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', '%' . $query . '%')
                    ->orWhere('description', 'LIKE', '%' . $query . '%');
            })
            ->withCount('activeFiles')
            ->orderBy('active_files_count', 'desc')
            ->limit(10)
            ->get();
    }
    
    /**
     * Search brands.
     *
     * @param string $query
     * @return \Illuminate\Support\Collection
     */
    protected function searchBrands($query)
    {
        return DB::table('files')
            ->select('brand', DB::raw('COUNT(*) as file_count'))
            ->where('brand', 'LIKE', '%' . $query . '%')
            ->whereNotNull('brand')
            ->where('status', 'active')
            ->where('is_public', true)
            ->groupBy('brand')
            ->orderBy('file_count', 'desc')
            ->limit(10)
            ->get();
    }
    
    /**
     * Search all types.
     *
     * @param string $query
     * @return array
     */
    protected function searchAll($query)
    {
        return [
            'files' => $this->searchFiles($query),
            'categories' => $this->searchCategories($query),
            'brands' => $this->searchBrands($query)
        ];
    }
    
    /**
     * Prepare search query.
     *
     * @param string $query
     * @return string
     */
    protected function prepareSearchQuery($query)
    {
        // Remove special characters and trim
        $query = preg_replace('/[^a-zA-Z0-9\s\-\_\.]/', '', $query);
        $query = trim($query);
        
        return $query;
    }
    
    /**
     * Log search query.
     *
     * @param string $query
     * @param string $type
     * @return void
     */
    protected function logSearch($query, $type)
    {
        DB::table('search_logs')->insert([
            'query' => $query,
            'type' => $type,
            'user_id' => \App\Helpers\AuthHelper::id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now()
        ]);
    }
    
    /**
     * Get search suggestions.
     *
     * @param string $query
     * @return array
     */
    protected function getSearchSuggestions($query)
    {
        // Get similar searches from logs
        return DB::table('search_logs')
            ->select('query', DB::raw('COUNT(*) as count'))
            ->where('query', 'LIKE', $query . '%')
            ->where('query', '!=', $query)
            ->groupBy('query')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->pluck('query')
            ->toArray();
    }
    
    /**
     * Get popular searches.
     *
     * @return array
     */
    protected function getPopularSearches()
    {
        return Cache::remember('popular_searches', 3600, function () {
            return DB::table('search_logs')
                ->select('query', DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('query')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->pluck('query')
                ->toArray();
        });
    }
}