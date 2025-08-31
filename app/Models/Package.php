<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Package extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'packages';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'sku',
        'description',
        'short_description',
        'type',
        'tier',
        'tier_level',
        'price',
        'original_price',
        'currency',
        'setup_fee',
        'is_free',
        'billing_cycle',
        'billing_frequency',
        'trial_days',
        'auto_renew',
        'daily_download_limit',
        'monthly_download_limit',
        'monthly_bandwidth_bytes',
        'monthly_bandwidth_formatted',
        'max_concurrent_downloads',
        'download_speed_kbps',
        'allowed_categories',
        'allowed_file_types',
        'allowed_brands',
        'access_premium_files',
        'access_beta_files',
        'early_access',
        'early_access_days',
        'features',
        'ad_free',
        'priority_support',
        'fast_download_servers',
        'request_files',
        'max_devices',
        'commercial_use',
        'api_access',
        'api_rate_limit',
        'api_monthly_calls',
        'download_history_days',
        'cloud_backup',
        'favorites_limit',
        'collections_limit',
        'badge_text',
        'badge_color',
        'icon_url',
        'highlight_color',
        'sort_order',
        'is_featured',
        'is_popular',
        'is_recommended',
        'discount_percent',
        'promo_starts_at',
        'promo_ends_at',
        'promo_code',
        'max_subscriptions',
        'can_upgrade_to',
        'can_downgrade_to',
        'prorate_on_upgrade',
        'prorate_on_downgrade',
        'cancellation_fee',
        'status',
        'available_from',
        'available_until',
        'available_countries',
        'blocked_countries',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'selling_points',
        'total_subscriptions',
        'active_subscriptions',
        'total_revenue',
        'average_rating',
        'total_reviews',
        'terms',
        'restrictions',
        'requires_agreement',
        'custom_fields',
        'admin_notes',
        'created_by',
        'updated_by'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'tier_level' => 'integer',
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'is_free' => 'boolean',
        'auto_renew' => 'boolean',
        'daily_download_limit' => 'integer',
        'monthly_download_limit' => 'integer',
        'monthly_bandwidth_bytes' => 'integer',
        'max_concurrent_downloads' => 'integer',
        'download_speed_kbps' => 'integer',
        'allowed_categories' => 'array',
        'allowed_file_types' => 'array',
        'allowed_brands' => 'array',
        'access_premium_files' => 'boolean',
        'access_beta_files' => 'boolean',
        'early_access' => 'boolean',
        'early_access_days' => 'integer',
        'features' => 'array',
        'ad_free' => 'boolean',
        'priority_support' => 'boolean',
        'fast_download_servers' => 'boolean',
        'request_files' => 'boolean',
        'max_devices' => 'integer',
        'commercial_use' => 'boolean',
        'api_access' => 'boolean',
        'api_rate_limit' => 'integer',
        'api_monthly_calls' => 'integer',
        'download_history_days' => 'integer',
        'cloud_backup' => 'boolean',
        'favorites_limit' => 'integer',
        'collections_limit' => 'integer',
        'sort_order' => 'integer',
        'is_featured' => 'boolean',
        'is_popular' => 'boolean',
        'is_recommended' => 'boolean',
        'discount_percent' => 'decimal:2',
        'promo_starts_at' => 'datetime',
        'promo_ends_at' => 'datetime',
        'max_subscriptions' => 'integer',
        'can_upgrade_to' => 'array',
        'can_downgrade_to' => 'array',
        'prorate_on_upgrade' => 'boolean',
        'prorate_on_downgrade' => 'boolean',
        'cancellation_fee' => 'decimal:2',
        'available_from' => 'datetime',
        'available_until' => 'datetime',
        'available_countries' => 'array',
        'blocked_countries' => 'array',
        'selling_points' => 'array',
        'total_subscriptions' => 'integer',
        'active_subscriptions' => 'integer',
        'total_revenue' => 'decimal:2',
        'average_rating' => 'decimal:2',
        'total_reviews' => 'integer',
        'requires_agreement' => 'boolean',
        'custom_fields' => 'array'
    ];
    
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'admin_notes'
    ];
    
    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        // Generate slug and format bandwidth on creation
        static::creating(function ($package) {
            if (empty($package->slug)) {
                $package->slug = static::generateSlug($package->name);
            }
            if (empty($package->sku)) {
                $package->sku = static::generateSku($package);
            }
            if ($package->monthly_bandwidth_bytes && empty($package->monthly_bandwidth_formatted)) {
                $package->monthly_bandwidth_formatted = static::formatBytes($package->monthly_bandwidth_bytes);
            }
        });
        
        // Update formatted bandwidth when bytes change
        static::updating(function ($package) {
            if ($package->isDirty('monthly_bandwidth_bytes')) {
                $package->monthly_bandwidth_formatted = static::formatBytes($package->monthly_bandwidth_bytes);
            }
        });
    }
    
    /**
     * Generate a unique slug.
     *
     * @param string $name
     * @return string
     */
    protected static function generateSlug($name)
    {
        $slug = Str::slug($name);
        $count = static::where('slug', 'LIKE', "{$slug}%")->count();
        
        return $count ? "{$slug}-{$count}" : $slug;
    }
    
    /**
     * Generate a unique SKU.
     *
     * @param Package $package
     * @return string
     */
    protected static function generateSku($package)
    {
        $prefix = strtoupper(substr($package->tier, 0, 3));
        $suffix = str_pad($package->tier_level, 2, '0', STR_PAD_LEFT);
        $random = strtoupper(substr(md5(uniqid()), 0, 4));
        
        return "{$prefix}-{$suffix}-{$random}";
    }
    
    /**
     * Format bytes to human readable format.
     *
     * @param int $bytes
     * @return string
     */
    protected static function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        
        return sprintf("%.2f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }
    
    /**
     * Scope for active packages.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    /**
     * Scope for featured packages.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)
                    ->orderBy('sort_order');
    }
    
    /**
     * Scope for packages available for purchase.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable($query)
    {
        $now = now();
        return $query->where('status', 'active')
                    ->where(function ($q) use ($now) {
                        $q->whereNull('available_from')
                          ->orWhere('available_from', '<=', $now);
                    })
                    ->where(function ($q) use ($now) {
                        $q->whereNull('available_until')
                          ->orWhere('available_until', '>=', $now);
                    });
    }
    
    /**
     * Check if package is currently on promotion.
     *
     * @return bool
     */
    public function isOnPromotion()
    {
        if (!$this->discount_percent || $this->discount_percent <= 0) {
            return false;
        }
        
        $now = now();
        
        if ($this->promo_starts_at && $this->promo_starts_at->isFuture()) {
            return false;
        }
        
        if ($this->promo_ends_at && $this->promo_ends_at->isPast()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get the current price (with discount if applicable).
     *
     * @return float
     */
    public function getCurrentPriceAttribute()
    {
        if ($this->isOnPromotion()) {
            return $this->price * (1 - $this->discount_percent / 100);
        }
        
        return $this->price;
    }
    
    /**
     * Check if package can be upgraded to another package.
     *
     * @param int $packageId
     * @return bool
     */
    public function canUpgradeTo($packageId)
    {
        if (!$this->can_upgrade_to) {
            return false;
        }
        
        return in_array($packageId, $this->can_upgrade_to);
    }
    
    /**
     * Check if package can be downgraded to another package.
     *
     * @param int $packageId
     * @return bool
     */
    public function canDowngradeTo($packageId)
    {
        if (!$this->can_downgrade_to) {
            return false;
        }
        
        return in_array($packageId, $this->can_downgrade_to);
    }
    
    /**
     * Check if package is available in a country.
     *
     * @param string $countryCode
     * @return bool
     */
    public function isAvailableInCountry($countryCode)
    {
        if ($this->blocked_countries && in_array($countryCode, $this->blocked_countries)) {
            return false;
        }
        
        if ($this->available_countries && !in_array($countryCode, $this->available_countries)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if package has reached subscription limit.
     *
     * @return bool
     */
    public function hasReachedLimit()
    {
        if (!$this->max_subscriptions) {
            return false;
        }
        
        return $this->active_subscriptions >= $this->max_subscriptions;
    }
    
    // ==================== RELATIONSHIPS ====================
    
    /**
     * Get the subscriptions for this package.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    
    /**
     * Get active subscriptions for this package.
     */
    public function activeSubscriptions()
    {
        return $this->hasMany(Subscription::class)
            ->where('status', 'active');
    }
    
    /**
     * Get the orders for this package.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    
    /**
     * Get the reviews for this package.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    
    /**
     * Get the user who created this package.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get the user who last updated this package.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}