<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Category;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Cache duration in seconds (2 hours)
     */
    protected $cacheTime = 7200;
    
    /**
     * Display the home page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get featured files (cached)
        $featuredFiles = Cache::remember('home_featured_files', $this->cacheTime, function () {
            return File::featured()
                ->public()
                ->with(['category', 'uploader'])
                ->limit(8)
                ->get();
        });
        
        // Get latest files (cached)
        $latestFiles = Cache::remember('home_latest_files', $this->cacheTime, function () {
            return File::public()
                ->with(['category'])
                ->orderBy('created_at', 'desc')
                ->limit(12)
                ->get();
        });
        
        // Get popular categories (cached)
        $popularCategories = Cache::remember('home_popular_categories', $this->cacheTime, function () {
            return Category::active()
                ->where('show_in_homepage', true)
                ->withCount('activeFiles')
                ->orderBy('active_files_count', 'desc')
                ->limit(6)
                ->get();
        });
        
        // Get featured packages (cached)
        $featuredPackages = Cache::remember('home_featured_packages', $this->cacheTime, function () {
            return Package::featured()
                ->available()
                ->orderBy('sort_order')
                ->limit(3)
                ->get();
        });
        
        // Get statistics (cached for 24 hours)
        $stats = Cache::remember('home_stats', 86400, function () {
            return [
                'total_files' => File::public()->count(),
                'total_downloads' => DB::table('downloads')->where('status', 'completed')->count(),
                'total_users' => User::where('status', 'active')->count(),
                'total_categories' => Category::active()->count()
            ];
        });
        
        // Get recent reviews/testimonials (cached)
        $testimonials = Cache::remember('home_testimonials', $this->cacheTime, function () {
            return DB::table('reviews')
                ->join('users', 'reviews.user_id', '=', 'users.id')
                ->where('reviews.rating', '>=', 4)
                ->where('reviews.is_featured', true)
                ->select(
                    'reviews.*',
                    'users.first_name',
                    'users.last_name',
                    'users.avatar_url'
                )
                ->orderBy('reviews.created_at', 'desc')
                ->limit(3)
                ->get();
        });
        
        // Get brands list (cached)
        $brands = Cache::remember('home_brands', $this->cacheTime, function () {
            return DB::table('files')
                ->select('brand', DB::raw('COUNT(*) as count'))
                ->whereNotNull('brand')
                ->where('status', 'active')
                ->where('is_public', true)
                ->groupBy('brand')
                ->orderBy('count', 'desc')
                ->limit(12)
                ->get();
        });
        
        return view('home.index', compact(
            'featuredFiles',
            'latestFiles',
            'popularCategories',
            'featuredPackages',
            'stats',
            'testimonials',
            'brands'
        ));
    }
    
    /**
     * Display the about page.
     *
     * @return \Illuminate\View\View
     */
    public function about()
    {
        $stats = Cache::remember('about_stats', 86400, function () {
            return [
                'years_experience' => date('Y') - 2020,
                'total_files' => File::public()->count(),
                'happy_customers' => User::where('status', 'active')->count(),
                'total_downloads' => DB::table('downloads')->where('status', 'completed')->count()
            ];
        });
        
        return view('pages.about', compact('stats'));
    }
    
    /**
     * Display the contact page.
     *
     * @return \Illuminate\View\View
     */
    public function contact()
    {
        return view('pages.contact');
    }
    
    /**
     * Handle contact form submission.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:1000',
            'g-recaptcha-response' => 'nullable'
        ]);
        
        // Store contact message in database
        DB::table('contact_messages')->insert([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now()
        ]);
        
        // Queue email to admin
        DB::table('email_queue')->insert([
            'to' => config('app.admin.email'),
            'subject' => 'New Contact Message: ' . $validated['subject'],
            'template' => 'emails.contact',
            'data' => json_encode($validated),
            'priority' => 'normal',
            'created_at' => now()
        ]);
        
        return redirect()->back()->with('success', 'Thank you for contacting us. We will respond within 24 hours.');
    }
    
    /**
     * Display FAQ page.
     *
     * @return \Illuminate\View\View
     */
    public function faq()
    {
        $faqs = Cache::remember('faqs', 86400, function () {
            return DB::table('faqs')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->groupBy('category');
        });
        
        return view('pages.faq', compact('faqs'));
    }
    
    /**
     * Display pricing/packages page.
     *
     * @return \Illuminate\View\View
     */
    public function pricing()
    {
        $packages = Cache::remember('pricing_packages', $this->cacheTime, function () {
            return Package::active()
                ->available()
                ->orderBy('tier_level')
                ->orderBy('price')
                ->get();
        });
        
        return view('pages.pricing', compact('packages'));
    }
    
    /**
     * Clear home page cache (admin use).
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearCache()
    {
        // Check if user is admin
        if (!\App\Helpers\AuthHelper::isAdmin()) {
            abort(403);
        }
        
        // Clear home page caches
        Cache::forget('home_featured_files');
        Cache::forget('home_latest_files');
        Cache::forget('home_popular_categories');
        Cache::forget('home_featured_packages');
        Cache::forget('home_stats');
        Cache::forget('home_testimonials');
        Cache::forget('home_brands');
        Cache::forget('about_stats');
        Cache::forget('faqs');
        Cache::forget('pricing_packages');
        
        return redirect()->back()->with('success', 'Home page cache cleared successfully.');
    }
}