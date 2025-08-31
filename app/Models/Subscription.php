<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'subscriptions';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'subscription_id',
        'reference_number',
        'user_id',
        'package_id',
        'package_name',
        'package_tier',
        'status',
        'amount',
        'currency',
        'billing_cycle',
        'billing_frequency',
        'next_billing_date',
        'last_billing_date',
        'billing_attempts',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'cancelled_at',
        'paused_at',
        'resumed_at',
        'downloads_used_today',
        'downloads_used_month',
        'bandwidth_used_bytes',
        'bandwidth_used_formatted',
        'bandwidth_reset_date',
        'daily_limit',
        'monthly_limit',
        'monthly_bandwidth_limit',
        'total_downloads',
        'total_bandwidth_bytes',
        'last_download_at',
        'api_calls_today',
        'api_calls_month',
        'active_devices',
        'payment_method',
        'payment_profile_id',
        'payment_subscription_id',
        'payment_metadata',
        'auto_renew',
        'coupon_code',
        'discount_amount',
        'discount_percent',
        'discount_valid_until',
        'renewal_count',
        'renewal_price',
        'price_locked',
        'grace_period_ends',
        'previous_package_id',
        'upgraded_at',
        'downgraded_at',
        'proration_amount',
        'cancellation_reason',
        'cancellation_feedback',
        'cancellation_requested',
        'cancellation_effective_date',
        'suspension_reason',
        'suspended_at',
        'suspension_count',
        'custom_limits',
        'custom_features',
        'is_complimentary',
        'renewal_reminder_sent',
        'expiry_warning_sent',
        'bandwidth_warning_sent',
        'notification_count',
        'authorized_devices',
        'device_history',
        'max_devices',
        'allowed_ips',
        'blocked_ips',
        'last_ip_address',
        'last_user_agent',
        'referred_by',
        'affiliate_code',
        'affiliate_commission',
        'user_notes',
        'admin_notes',
        'metadata',
        'tags'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'billing_frequency' => 'integer',
        'next_billing_date' => 'date',
        'last_billing_date' => 'date',
        'billing_attempts' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'paused_at' => 'datetime',
        'resumed_at' => 'datetime',
        'downloads_used_today' => 'integer',
        'downloads_used_month' => 'integer',
        'bandwidth_used_bytes' => 'integer',
        'bandwidth_reset_date' => 'date',
        'daily_limit' => 'integer',
        'monthly_limit' => 'integer',
        'monthly_bandwidth_limit' => 'integer',
        'total_downloads' => 'integer',
        'total_bandwidth_bytes' => 'integer',
        'last_download_at' => 'datetime',
        'api_calls_today' => 'integer',
        'api_calls_month' => 'integer',
        'active_devices' => 'integer',
        'payment_metadata' => 'array',
        'auto_renew' => 'boolean',
        'discount_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_valid_until' => 'datetime',
        'renewal_count' => 'integer',
        'renewal_price' => 'decimal:2',
        'price_locked' => 'boolean',
        'grace_period_ends' => 'datetime',
        'upgraded_at' => 'datetime',
        'downgraded_at' => 'datetime',
        'proration_amount' => 'decimal:2',
        'cancellation_requested' => 'boolean',
        'cancellation_effective_date' => 'datetime',
        'suspended_at' => 'datetime',
        'suspension_count' => 'integer',
        'custom_limits' => 'array',
        'custom_features' => 'array',
        'is_complimentary' => 'boolean',
        'renewal_reminder_sent' => 'datetime',
        'expiry_warning_sent' => 'datetime',
        'bandwidth_warning_sent' => 'datetime',
        'notification_count' => 'integer',
        'authorized_devices' => 'array',
        'device_history' => 'array',
        'max_devices' => 'integer',
        'allowed_ips' => 'array',
        'blocked_ips' => 'array',
        'affiliate_commission' => 'decimal:2',
        'metadata' => 'array',
        'tags' => 'array'
    ];
    
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'payment_profile_id',
        'payment_subscription_id',
        'admin_notes'
    ];
    
    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        // Generate subscription ID on creation
        static::creating(function ($subscription) {
            if (empty($subscription->subscription_id)) {
                $subscription->subscription_id = static::generateSubscriptionId();
            }
            if (empty($subscription->reference_number)) {
                $subscription->reference_number = static::generateReferenceNumber();
            }
            // Update formatted bandwidth
            if ($subscription->bandwidth_used_bytes && empty($subscription->bandwidth_used_formatted)) {
                $subscription->bandwidth_used_formatted = static::formatBytes($subscription->bandwidth_used_bytes);
            }
        });
        
        // Update formatted bandwidth when bytes change
        static::updating(function ($subscription) {
            if ($subscription->isDirty('bandwidth_used_bytes')) {
                $subscription->bandwidth_used_formatted = static::formatBytes($subscription->bandwidth_used_bytes);
            }
        });
    }
    
    /**
     * Generate a unique subscription ID.
     *
     * @return string
     */
    protected static function generateSubscriptionId()
    {
        do {
            $id = 'SUB-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 8));
        } while (static::where('subscription_id', $id)->exists());
        
        return $id;
    }
    
    /**
     * Generate a unique reference number.
     *
     * @return string
     */
    protected static function generateReferenceNumber()
    {
        do {
            $ref = 'REF-' . mt_rand(100000, 999999);
        } while (static::where('reference_number', $ref)->exists());
        
        return $ref;
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
     * Scope for active subscriptions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('ends_at', '>', now());
    }
    
    /**
     * Scope for expiring subscriptions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpiring($query, $days = 7)
    {
        $endDate = now()->addDays($days);
        return $query->where('status', 'active')
                    ->whereBetween('ends_at', [now(), $endDate]);
    }
    
    /**
     * Check if subscription is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status === 'active' && 
               (!$this->ends_at || $this->ends_at->isFuture());
    }
    
    /**
     * Check if subscription is in trial period.
     *
     * @return bool
     */
    public function isOnTrial()
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }
    
    /**
     * Check if subscription is in grace period.
     *
     * @return bool
     */
    public function isInGracePeriod()
    {
        return $this->grace_period_ends && $this->grace_period_ends->isFuture();
    }
    
    /**
     * Check if download limit is reached for today.
     *
     * @return bool
     */
    public function hasReachedDailyDownloadLimit()
    {
        return $this->downloads_used_today >= $this->daily_limit;
    }
    
    /**
     * Check if download limit is reached for month.
     *
     * @return bool
     */
    public function hasReachedMonthlyDownloadLimit()
    {
        return $this->downloads_used_month >= $this->monthly_limit;
    }
    
    /**
     * Check if bandwidth limit is reached.
     *
     * @return bool
     */
    public function hasReachedBandwidthLimit()
    {
        return $this->bandwidth_used_bytes >= $this->monthly_bandwidth_limit;
    }
    
    /**
     * Get remaining bandwidth in bytes.
     *
     * @return int
     */
    public function getRemainingBandwidthBytes()
    {
        return max(0, $this->monthly_bandwidth_limit - $this->bandwidth_used_bytes);
    }
    
    /**
     * Get remaining bandwidth formatted.
     *
     * @return string
     */
    public function getRemainingBandwidthFormatted()
    {
        return static::formatBytes($this->getRemainingBandwidthBytes());
    }
    
    /**
     * Get bandwidth usage percentage.
     *
     * @return float
     */
    public function getBandwidthUsagePercentage()
    {
        if ($this->monthly_bandwidth_limit <= 0) {
            return 0;
        }
        
        return round(($this->bandwidth_used_bytes / $this->monthly_bandwidth_limit) * 100, 2);
    }
    
    /**
     * Reset daily counters.
     *
     * @return void
     */
    public function resetDailyCounters()
    {
        $this->update([
            'downloads_used_today' => 0,
            'api_calls_today' => 0
        ]);
    }
    
    /**
     * Reset monthly counters.
     *
     * @return void
     */
    public function resetMonthlyCounters()
    {
        $this->update([
            'downloads_used_month' => 0,
            'api_calls_month' => 0,
            'bandwidth_used_bytes' => 0,
            'bandwidth_used_formatted' => '0 MB',
            'bandwidth_reset_date' => now()->addMonth()
        ]);
    }
    
    /**
     * Add device to authorized list.
     *
     * @param string $deviceId
     * @return bool
     */
    public function authorizeDevice($deviceId)
    {
        $devices = $this->authorized_devices ?: [];
        
        if (count($devices) >= $this->max_devices) {
            return false;
        }
        
        if (!in_array($deviceId, $devices)) {
            $devices[] = $deviceId;
            $this->update(['authorized_devices' => $devices]);
        }
        
        return true;
    }
    
    // ==================== RELATIONSHIPS ====================
    
    /**
     * Get the user that owns the subscription.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the package associated with the subscription.
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
    
    /**
     * Get the previous package (for upgrades/downgrades).
     */
    public function previousPackage()
    {
        return $this->belongsTo(Package::class, 'previous_package_id');
    }
    
    /**
     * Get the downloads for this subscription.
     */
    public function downloads()
    {
        return $this->hasMany(Download::class);
    }
    
    /**
     * Get the orders for this subscription.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    
    /**
     * Get the invoices for this subscription.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    
    /**
     * Get the user who referred this subscription.
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }
}