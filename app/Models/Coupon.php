<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'coupons';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'slug',
        'type',
        'discount_amount',
        'discount_percent',
        'max_discount_amount',
        'currency',
        'credit_amount',
        'bonus_downloads',
        'bonus_bandwidth_bytes',
        'bonus_bandwidth_formatted',
        'trial_days',
        'convert_to_paid',
        'applies_to',
        'applicable_packages',
        'applicable_categories',
        'applicable_files',
        'applicable_tiers',
        'minimum_amount',
        'maximum_amount',
        'usage_limit',
        'usage_count',
        'usage_limit_per_user',
        'new_users_only',
        'existing_users_only',
        'first_purchase_only',
        'valid_from',
        'valid_until',
        'is_active',
        'valid_days',
        'valid_hours',
        'timezone',
        'apply_to_renewals',
        'recurring_months',
        'recurring_count',
        'lifetime_discount',
        'stackable',
        'stackable_with',
        'not_stackable_with',
        'priority',
        'allowed_users',
        'blocked_users',
        'allowed_emails',
        'allowed_domains',
        'allowed_roles',
        'allowed_countries',
        'blocked_countries',
        'is_affiliate_coupon',
        'affiliate_id',
        'affiliate_commission',
        'partner_code',
        'tracking_parameters',
        'show_on_pricing_page',
        'auto_apply',
        'badge_text',
        'badge_color',
        'banner_url',
        'terms_and_conditions',
        'campaign_name',
        'campaign_type',
        'source',
        'medium',
        'campaign_metadata',
        'redemption_count',
        'total_discount_given',
        'total_revenue_generated',
        'conversion_rate',
        'views_count',
        'attempts_count',
        'notify_on_use',
        'notification_email',
        'low_usage_threshold',
        'low_usage_notified',
        'expiry_action',
        'auto_extend_days',
        'convert_to_coupon',
        'test_group',
        'test_variant',
        'test_parameters',
        'requires_verification',
        'verification_method',
        'failed_attempts',
        'locked_until',
        'fraud_indicators',
        'external_id',
        'integration_source',
        'integration_data',
        'custom_fields',
        'internal_notes',
        'admin_notes',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'discount_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'bonus_downloads' => 'integer',
        'bonus_bandwidth_bytes' => 'integer',
        'trial_days' => 'integer',
        'convert_to_paid' => 'boolean',
        'applicable_packages' => 'array',
        'applicable_categories' => 'array',
        'applicable_files' => 'array',
        'applicable_tiers' => 'array',
        'minimum_amount' => 'decimal:2',
        'maximum_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'usage_limit_per_user' => 'integer',
        'new_users_only' => 'boolean',
        'existing_users_only' => 'boolean',
        'first_purchase_only' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
        'valid_days' => 'array',
        'valid_hours' => 'array',
        'apply_to_renewals' => 'boolean',
        'recurring_months' => 'integer',
        'recurring_count' => 'integer',
        'lifetime_discount' => 'boolean',
        'stackable' => 'boolean',
        'stackable_with' => 'array',
        'not_stackable_with' => 'array',
        'priority' => 'integer',
        'allowed_users' => 'array',
        'blocked_users' => 'array',
        'allowed_emails' => 'array',
        'allowed_domains' => 'array',
        'allowed_roles' => 'array',
        'allowed_countries' => 'array',
        'blocked_countries' => 'array',
        'is_affiliate_coupon' => 'boolean',
        'affiliate_commission' => 'decimal:2',
        'tracking_parameters' => 'array',
        'show_on_pricing_page' => 'boolean',
        'auto_apply' => 'boolean',
        'campaign_metadata' => 'array',
        'redemption_count' => 'integer',
        'total_discount_given' => 'decimal:2',
        'total_revenue_generated' => 'decimal:2',
        'conversion_rate' => 'decimal:2',
        'views_count' => 'integer',
        'attempts_count' => 'integer',
        'notify_on_use' => 'boolean',
        'low_usage_threshold' => 'integer',
        'low_usage_notified' => 'boolean',
        'auto_extend_days' => 'integer',
        'test_parameters' => 'array',
        'requires_verification' => 'boolean',
        'failed_attempts' => 'integer',
        'locked_until' => 'datetime',
        'fraud_indicators' => 'array',
        'integration_data' => 'array',
        'custom_fields' => 'array',
        'approved_at' => 'datetime'
    ];
    
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'internal_notes',
        'admin_notes'
    ];
    
    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        // Generate slug and format bandwidth on creation
        static::creating(function ($coupon) {
            if (empty($coupon->slug)) {
                $coupon->slug = static::generateSlug($coupon->name);
            }
            // Format bonus bandwidth
            if ($coupon->bonus_bandwidth_bytes && empty($coupon->bonus_bandwidth_formatted)) {
                $coupon->bonus_bandwidth_formatted = static::formatBytes($coupon->bonus_bandwidth_bytes);
            }
        });
        
        // Update formatted bandwidth when bytes change
        static::updating(function ($coupon) {
            if ($coupon->isDirty('bonus_bandwidth_bytes')) {
                $coupon->bonus_bandwidth_formatted = static::formatBytes($coupon->bonus_bandwidth_bytes);
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
     * Scope for active coupons.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        $now = now();
        return $query->where('is_active', true)
                    ->where('valid_from', '<=', $now)
                    ->where(function ($q) use ($now) {
                        $q->whereNull('valid_until')
                          ->orWhere('valid_until', '>=', $now);
                    });
    }
    
    /**
     * Scope for public coupons.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query)
    {
        return $query->where('show_on_pricing_page', true);
    }
    
    // ==================== VALIDATION METHODS ====================
    
    /**
     * Validate if coupon is valid for use.
     *
     * @param User|null $user
     * @param float|null $orderAmount
     * @return array ['valid' => bool, 'message' => string]
     */
    public function validate($user = null, $orderAmount = null)
    {
        // Check if active
        if (!$this->is_active) {
            return ['valid' => false, 'message' => 'Coupon is not active'];
        }
        
        // Check validity period
        $validityCheck = $this->validatePeriod();
        if (!$validityCheck['valid']) {
            return $validityCheck;
        }
        
        // Check usage limits
        $usageCheck = $this->validateUsageLimit();
        if (!$usageCheck['valid']) {
            return $usageCheck;
        }
        
        // Check user eligibility
        if ($user) {
            $userCheck = $this->validateUser($user);
            if (!$userCheck['valid']) {
                return $userCheck;
            }
        }
        
        // Check order amount
        if ($orderAmount !== null) {
            $amountCheck = $this->validateAmount($orderAmount);
            if (!$amountCheck['valid']) {
                return $amountCheck;
            }
        }
        
        // Check if locked due to failed attempts
        if ($this->locked_until && $this->locked_until->isFuture()) {
            return ['valid' => false, 'message' => 'Coupon is temporarily locked'];
        }
        
        return ['valid' => true, 'message' => 'Coupon is valid'];
    }
    
    /**
     * Validate coupon validity period.
     *
     * @return array
     */
    public function validatePeriod()
    {
        $now = now();
        
        // Check start date
        if ($this->valid_from && $this->valid_from->isFuture()) {
            return ['valid' => false, 'message' => 'Coupon is not yet valid'];
        }
        
        // Check end date
        if ($this->valid_until && $this->valid_until->isPast()) {
            return ['valid' => false, 'message' => 'Coupon has expired'];
        }
        
        // Check valid days
        if ($this->valid_days && !in_array(strtolower($now->format('l')), $this->valid_days)) {
            return ['valid' => false, 'message' => 'Coupon is not valid today'];
        }
        
        // Check valid hours
        if ($this->valid_hours && !in_array($now->hour, $this->valid_hours)) {
            return ['valid' => false, 'message' => 'Coupon is not valid at this time'];
        }
        
        return ['valid' => true, 'message' => 'Period is valid'];
    }
    
    /**
     * Validate coupon usage limit.
     *
     * @return array
     */
    public function validateUsageLimit()
    {
        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return ['valid' => false, 'message' => 'Coupon usage limit reached'];
        }
        
        return ['valid' => true, 'message' => 'Usage limit valid'];
    }
    
    /**
     * Validate if user can use the coupon.
     *
     * @param User $user
     * @return array
     */
    public function validateUser($user)
    {
        // Check if user is blocked
        if ($this->blocked_users && in_array($user->id, $this->blocked_users)) {
            return ['valid' => false, 'message' => 'You are not eligible for this coupon'];
        }
        
        // Check if user is in allowed list
        if ($this->allowed_users && !in_array($user->id, $this->allowed_users)) {
            return ['valid' => false, 'message' => 'This coupon is not available to you'];
        }
        
        // Check email restrictions
        if ($this->allowed_emails && !in_array($user->email, $this->allowed_emails)) {
            return ['valid' => false, 'message' => 'Your email is not eligible for this coupon'];
        }
        
        // Check email domain
        if ($this->allowed_domains) {
            $domain = substr(strrchr($user->email, "@"), 1);
            if (!in_array($domain, $this->allowed_domains)) {
                return ['valid' => false, 'message' => 'Your email domain is not eligible'];
            }
        }
        
        // Check user role
        if ($this->allowed_roles && !in_array($user->role, $this->allowed_roles)) {
            return ['valid' => false, 'message' => 'Your account type is not eligible'];
        }
        
        // Check new users only
        if ($this->new_users_only && $user->total_orders > 0) {
            return ['valid' => false, 'message' => 'This coupon is for new users only'];
        }
        
        // Check existing users only
        if ($this->existing_users_only && $user->total_orders == 0) {
            return ['valid' => false, 'message' => 'This coupon is for existing users only'];
        }
        
        // Check first purchase only
        if ($this->first_purchase_only && $user->total_orders > 0) {
            return ['valid' => false, 'message' => 'This coupon is for first purchase only'];
        }
        
        // Check per-user usage limit
        if ($this->usage_limit_per_user) {
            $userUsageCount = $this->usages()
                ->where('user_id', $user->id)
                ->count();
            
            if ($userUsageCount >= $this->usage_limit_per_user) {
                return ['valid' => false, 'message' => 'You have already used this coupon'];
            }
        }
        
        return ['valid' => true, 'message' => 'User is eligible'];
    }
    
    /**
     * Validate order amount.
     *
     * @param float $amount
     * @return array
     */
    public function validateAmount($amount)
    {
        if ($this->minimum_amount && $amount < $this->minimum_amount) {
            return [
                'valid' => false, 
                'message' => "Minimum order amount is {$this->currency} {$this->minimum_amount}"
            ];
        }
        
        if ($this->maximum_amount && $amount > $this->maximum_amount) {
            return [
                'valid' => false, 
                'message' => "Maximum order amount is {$this->currency} {$this->maximum_amount}"
            ];
        }
        
        return ['valid' => true, 'message' => 'Amount is valid'];
    }
    
    /**
     * Validate if coupon applies to a package.
     *
     * @param int $packageId
     * @return bool
     */
    public function appliesToPackage($packageId)
    {
        if ($this->applies_to === 'all') {
            return true;
        }
        
        if ($this->applies_to === 'packages' && $this->applicable_packages) {
            return in_array($packageId, $this->applicable_packages);
        }
        
        return false;
    }
    
    /**
     * Validate if coupon applies to a category.
     *
     * @param int $categoryId
     * @return bool
     */
    public function appliesToCategory($categoryId)
    {
        if ($this->applies_to === 'all') {
            return true;
        }
        
        if ($this->applies_to === 'categories' && $this->applicable_categories) {
            return in_array($categoryId, $this->applicable_categories);
        }
        
        return false;
    }
    
    /**
     * Calculate discount amount for an order.
     *
     * @param float $orderAmount
     * @return float
     */
    public function calculateDiscount($orderAmount)
    {
        if ($this->type === 'fixed') {
            return min($this->discount_amount, $orderAmount);
        }
        
        if ($this->type === 'percentage') {
            $discount = $orderAmount * ($this->discount_percent / 100);
            
            // Apply max discount cap if set
            if ($this->max_discount_amount) {
                $discount = min($discount, $this->max_discount_amount);
            }
            
            return min($discount, $orderAmount);
        }
        
        return 0;
    }
    
    /**
     * Check if coupon can stack with another coupon.
     *
     * @param string $otherCouponCode
     * @return bool
     */
    public function canStackWith($otherCouponCode)
    {
        if (!$this->stackable) {
            return false;
        }
        
        if ($this->not_stackable_with && in_array($otherCouponCode, $this->not_stackable_with)) {
            return false;
        }
        
        if ($this->stackable_with && !in_array($otherCouponCode, $this->stackable_with)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Record coupon usage.
     *
     * @param User $user
     * @param Order $order
     * @return void
     */
    public function recordUsage($user, $order)
    {
        // Increment usage count
        $this->increment('usage_count');
        $this->increment('redemption_count');
        
        // Update metrics
        $this->increment('total_discount_given', $order->coupon_discount);
        $this->increment('total_revenue_generated', $order->total_amount);
        
        // Create usage record
        $this->usages()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'discount_amount' => $order->coupon_discount,
            'used_at' => now()
        ]);
        
        // Send notification if enabled
        if ($this->notify_on_use && $this->notification_email) {
            // Send notification (implement email sending)
        }
        
        // Check low usage threshold
        if ($this->usage_limit && $this->low_usage_threshold) {
            $remaining = $this->usage_limit - $this->usage_count;
            if ($remaining <= $this->low_usage_threshold && !$this->low_usage_notified) {
                $this->update(['low_usage_notified' => true]);
                // Send low usage notification
            }
        }
    }
    
    /**
     * Record failed attempt.
     *
     * @return void
     */
    public function recordFailedAttempt()
    {
        $this->increment('attempts_count');
        $this->increment('failed_attempts');
        
        // Lock coupon after too many failed attempts
        if ($this->failed_attempts >= 10) {
            $this->update(['locked_until' => now()->addHours(1)]);
        }
    }
    
    // ==================== RELATIONSHIPS ====================
    
    /**
     * Get the affiliate user.
     */
    public function affiliate()
    {
        return $this->belongsTo(User::class, 'affiliate_id');
    }
    
    /**
     * Get the creator.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get the updater.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    
    /**
     * Get the approver.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    /**
     * Get the coupon usages.
     */
    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }
    
    /**
     * Get the orders that used this coupon.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    
    /**
     * Get the users who used this coupon.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'coupon_usages')
            ->withPivot('used_at', 'order_id', 'discount_amount')
            ->withTimestamps();
    }
}