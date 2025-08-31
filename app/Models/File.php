<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class File extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'files';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'name',
        'original_name',
        'slug',
        'file_url',
        'backup_url',
        'thumbnail_url',
        'preview_url',
        'file_type',
        'mime_type',
        'extension',
        'size',
        'size_formatted',
        'version',
        'build_number',
        'release_date',
        'md5_hash',
        'sha1_hash',
        'sha256_hash',
        'is_verified',
        'verified_at',
        'brand',
        'model',
        'device_code',
        'compatible_models',
        'region',
        'carrier',
        'android_version',
        'ios_version',
        'baseband_version',
        'security_patch',
        'platform',
        'is_public',
        'requires_auth',
        'is_premium',
        'price',
        'access_token',
        'token_expires_at',
        'download_count',
        'view_count',
        'last_downloaded_at',
        'bandwidth_used',
        'status',
        'is_featured',
        'is_recommended',
        'sort_order',
        'available_from',
        'expires_at',
        'max_downloads',
        'retention_days',
        'changelog',
        'installation_notes',
        'known_issues',
        'requirements',
        'tags',
        'description',
        'short_description',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'category_id',
        'product_id',
        'uploaded_by',
        'approved_by',
        'dmca_protected',
        'license_type',
        'terms_of_use',
        'user_agreement_required',
        'cdn_provider',
        'storage_region',
        'cdn_cache_days',
        'mirror_urls',
        'metadata',
        'admin_notes'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'release_date' => 'date',
        'verified_at' => 'datetime',
        'token_expires_at' => 'datetime',
        'last_downloaded_at' => 'datetime',
        'available_from' => 'datetime',
        'expires_at' => 'datetime',
        'is_verified' => 'boolean',
        'is_public' => 'boolean',
        'requires_auth' => 'boolean',
        'is_premium' => 'boolean',
        'is_featured' => 'boolean',
        'is_recommended' => 'boolean',
        'dmca_protected' => 'boolean',
        'user_agreement_required' => 'boolean',
        'compatible_models' => 'array',
        'changelog' => 'array',
        'installation_notes' => 'array',
        'known_issues' => 'array',
        'requirements' => 'array',
        'tags' => 'array',
        'mirror_urls' => 'array',
        'metadata' => 'array',
        'price' => 'decimal:2',
        'size' => 'integer',
        'download_count' => 'integer',
        'view_count' => 'integer'
    ];
    
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'access_token',
        'admin_notes'
    ];
    
    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        // Generate UUID and slug on creation
        static::creating(function ($file) {
            if (empty($file->uuid)) {
                $file->uuid = (string) Str::uuid();
            }
            if (empty($file->slug)) {
                $file->slug = static::generateSlug($file->name);
            }
            if (empty($file->size_formatted) && $file->size) {
                $file->size_formatted = static::formatBytes($file->size);
            }
        });
        
        // Update formatted size when size changes
        static::updating(function ($file) {
            if ($file->isDirty('size')) {
                $file->size_formatted = static::formatBytes($file->size);
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
     * Scope for public files.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true)
                    ->where('status', 'active');
    }
    
    /**
     * Scope for premium files.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }
    
    /**
     * Scope for featured files.
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
     * Check if file is available for download.
     *
     * @return bool
     */
    public function isAvailable()
    {
        if ($this->status !== 'active') {
            return false;
        }
        
        if ($this->available_from && $this->available_from->isFuture()) {
            return false;
        }
        
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        
        if ($this->max_downloads && $this->download_count >= $this->max_downloads) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if user can download this file.
     *
     * @param User $user
     * @return bool
     */
    public function canBeDownloadedBy($user)
    {
        if (!$this->isAvailable()) {
            return false;
        }
        
        if (!$this->is_public && !$user) {
            return false;
        }
        
        if ($this->is_premium && $user) {
            return $user->hasActiveSubscription();
        }
        
        return true;
    }
    
    /**
     * Increment download count.
     *
     * @return void
     */
    public function incrementDownloads()
    {
        $this->increment('download_count');
        $this->update(['last_downloaded_at' => now()]);
    }
    
    /**
     * Get average rating.
     *
     * @return float
     */
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?: 0;
    }
    
    /**
     * Get total reviews count.
     *
     * @return int
     */
    public function getReviewsCountAttribute()
    {
        return $this->reviews()->count();
    }
    
    // ==================== RELATIONSHIPS ====================
    
    /**
     * Get the category that owns the file.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    /**
     * Get the product that owns the file.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    /**
     * Get the user who uploaded the file.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
    
    /**
     * Get the user who approved the file.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    /**
     * Get the downloads for this file.
     */
    public function downloads()
    {
        return $this->hasMany(Download::class);
    }
    
    /**
     * Get the reviews for this file.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    
    /**
     * Get the users who have this file in their wishlist.
     */
    public function wishlistedBy()
    {
        return $this->belongsToMany(User::class, 'wishlists')
            ->withTimestamps();
    }
    
    /**
     * Get the orders that include this file.
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_files')
            ->withPivot('price', 'download_limit')
            ->withTimestamps();
    }
    
    /**
     * Get the related files.
     */
    public function relatedFiles()
    {
        return $this->belongsToMany(File::class, 'related_files', 'file_id', 'related_file_id')
            ->withTimestamps();
    }
    
    /**
     * Get the file's comments.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    
    /**
     * Get the file's activity logs.
     */
    public function activities()
    {
        return $this->hasMany(ActivityLog::class);
    }
}