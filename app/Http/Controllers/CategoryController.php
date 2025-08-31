<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * Items per page
     */
    protected $perPage = 20;
    
    /**
     * Cache duration (2 hours)
     */
    protected $cacheTime = 7200;
    
    /**
     * Display all categories.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get root categories with children (cached)
        $categories = Cache::remember('categories_tree', $this->cacheTime, function () {
            return Category::root()
                ->active()
                ->with(['children' => function ($query) {
                    $query->active()->orderBy('sort_order');
                }])
                ->withCount('activeFiles')
                ->orderBy('sort_order')
                ->get();
        });
        
        // Get category statistics (cached)
        $stats = Cache::remember('category_stats', $this->cacheTime, function () {
            return [
                'total_categories' => Category::active()->count(),
                'total_files' => File::public()->count(),
                'total_downloads' => DB::table('downloads')
                    ->where('status', 'completed')
                    ->count()
            ];
        });
        
        // Get featured categories
        $featuredCategories = Cache::remember('featured_categories', $this->cacheTime, function () {
            return Category::active()
                ->featured()
                ->withCount('activeFiles')
                ->limit(6)
                ->get();
        });
        
        return view('categories.index', compact('categories', 'stats', 'featuredCategories'));
    }
    
    /**
     * Display category details and files.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $slug
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)
            ->active()
            ->withCount('activeFiles')
            ->firstOrFail();
        
        // Check if authentication required
        if ($category->requires_auth && !\App\Helpers\AuthHelper::check()) {
            return redirect()->route('login')
                ->with('error', 'Please login to view this category.');
        }
        
        // Get breadcrumbs
        $breadcrumbs = $this->getBreadcrumbs($category);
        
        // Get subcategories
        $subcategories = $category->children()
            ->active()
            ->withCount('activeFiles')
            ->orderBy('sort_order')
            ->get();
        
        // Get files in this category
        $query = File::public()
            ->where('category_id', $category->id)
            ->with(['uploader']);
        
        // Apply filters
        if ($request->has('brand')) {
            $query->where('brand', $request->brand);
        }
        
        if ($request->has('model')) {
            $query->where('model', $request->model);
        }
        
        if ($request->has('platform')) {
            $query->where('platform', $request->platform);
        }
        
        // Sorting
        $sortBy = $request->get('sort', 'featured');
        switch ($sortBy) {
            case 'popular':
                $query->orderBy('download_count', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            default: // featured
                $query->orderBy('is_featured', 'desc')
                    ->orderBy('sort_order');
        }
        
        // Paginate results
        $files = $query->paginate($category->products_per_page ?: $this->perPage);
        
        // Get filters for this category
        $filters = $this->getCategoryFilters($category);
        
        // Update view count (throttled)
        $this->incrementViewCount($category);
        
        return view('categories.show', compact(
            'category',
            'breadcrumbs',
            'subcategories',
            'files',
            'filters'
        ));
    }
    
    /**
     * Display category tree structure.
     *
     * @return \Illuminate\View\View
     */
    public function tree()
    {
        $tree = Cache::remember('category_tree_full', $this->cacheTime, function () {
            return $this->buildTree();
        });
        
        return view('categories.tree', compact('tree'));
    }
    
    /**
     * Get popular categories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function popular()
    {
        $categories = Cache::remember('popular_categories_api', 3600, function () {
            return Category::active()
                ->withCount('activeFiles')
                ->orderBy('active_files_count', 'desc')
                ->limit(10)
                ->get(['id', 'name', 'slug', 'icon', 'image_url']);
        });
        
        return response()->json($categories);
    }
    
    /**
     * Search categories.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:50'
        ]);
        
        $query = $request->get('q');
        
        $categories = Category::active()
            ->where('name', 'LIKE', '%' . $query . '%')
            ->withCount('activeFiles')
            ->limit(10)
            ->get(['id', 'name', 'slug', 'icon', 'active_files_count']);
        
        return response()->json($categories);
    }
    
    /**
     * Get breadcrumbs for category.
     *
     * @param \App\Models\Category $category
     * @return array
     */
    protected function getBreadcrumbs($category)
    {
        $breadcrumbs = [];
        
        // Add root
        $breadcrumbs[] = [
            'name' => 'Home',
            'url' => route('home')
        ];
        
        $breadcrumbs[] = [
            'name' => 'Categories',
            'url' => route('categories.index')
        ];
        
        // Add parent categories
        if ($category->path) {
            $parentIds = explode('/', $category->path);
            $parents = Category::whereIn('id', $parentIds)
                ->select('id', 'name', 'slug')
                ->get()
                ->keyBy('id');
            
            foreach ($parentIds as $parentId) {
                if (isset($parents[$parentId])) {
                    $breadcrumbs[] = [
                        'name' => $parents[$parentId]->name,
                        'url' => route('categories.show', $parents[$parentId]->slug)
                    ];
                }
            }
        }
        
        // Add current category
        $breadcrumbs[] = [
            'name' => $category->name,
            'url' => null
        ];
        
        return $breadcrumbs;
    }
    
    /**
     * Get filters for category.
     *
     * @param \App\Models\Category $category
     * @return array
     */
    protected function getCategoryFilters($category)
    {
        $cacheKey = 'category_filters_' . $category->id;
        
        return Cache::remember($cacheKey, 3600, function () use ($category) {
            $files = File::where('category_id', $category->id)
                ->where('status', 'active')
                ->where('is_public', true);
            
            return [
                'brands' => (clone $files)
                    ->select('brand', DB::raw('COUNT(*) as count'))
                    ->whereNotNull('brand')
                    ->groupBy('brand')
                    ->orderBy('brand')
                    ->get(),
                'models' => (clone $files)
                    ->select('model', DB::raw('COUNT(*) as count'))
                    ->whereNotNull('model')
                    ->groupBy('model')
                    ->orderBy('model')
                    ->get(),
                'platforms' => (clone $files)
                    ->select('platform', DB::raw('COUNT(*) as count'))
                    ->whereNotNull('platform')
                    ->groupBy('platform')
                    ->get(),
                'versions' => (clone $files)
                    ->select('version', DB::raw('COUNT(*) as count'))
                    ->whereNotNull('version')
                    ->groupBy('version')
                    ->orderBy('version', 'desc')
                    ->limit(10)
                    ->get(),
                'price_range' => [
                    'min' => (clone $files)->min('price') ?: 0,
                    'max' => (clone $files)->max('price') ?: 0
                ]
            ];
        });
    }
    
    /**
     * Build category tree.
     *
     * @param int|null $parentId
     * @return array
     */
    protected function buildTree($parentId = null)
    {
        $categories = Category::active()
            ->where('parent_id', $parentId)
            ->withCount('activeFiles')
            ->orderBy('sort_order')
            ->get();
        
        $tree = [];
        
        foreach ($categories as $category) {
            $node = [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'icon' => $category->icon,
                'file_count' => $category->active_files_count,
                'children' => []
            ];
            
            if ($category->children_count > 0) {
                $node['children'] = $this->buildTree($category->id);
            }
            
            $tree[] = $node;
        }
        
        return $tree;
    }
    
    /**
     * Increment category view count.
     *
     * @param \App\Models\Category $category
     * @return void
     */
    protected function incrementViewCount($category)
    {
        $sessionKey = 'category_viewed_' . $category->id;
        
        if (!session()->has($sessionKey)) {
            DB::table('categories')
                ->where('id', $category->id)
                ->increment('view_count');
            
            session()->put($sessionKey, true);
        }
    }
}