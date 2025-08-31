<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;

class User extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'username',
        'password',
        'phone',
        'country_code',
        'role',
        'status',
        'is_verified',
        'email_verified_at',
        'phone_verified_at',
        'date_of_birth',
        'gender',
        'avatar_url',
        'bio',
        'two_factor_secret',
        'two_factor_enabled',
        'last_login_at',
        'last_login_ip',
        'login_attempts',
        'locked_until',
        'password_reset_token',
        'password_reset_expires',
        'email_verification_token',
        'email_verification_expires',
        'newsletter_subscribed',
        'sms_notifications',
        'email_notifications',
        'timezone',
        'language',
        'currency',
        'wallet_balance',
        'loyalty_points',
        'customer_type',
        'total_spent',
        'total_orders',
        'referral_code',
        'referred_by',
        'referral_count',
        'referral_earnings',
        'preferences',
        'metadata',
        'notes'
    ];
    
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'password_reset_token',
        'email_verification_token'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
        'password_reset_expires' => 'datetime',
        'email_verification_expires' => 'datetime',
        'date_of_birth' => 'date',
        'is_verified' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'newsletter_subscribed' => 'boolean',
        'sms_notifications' => 'boolean',
        'email_notifications' => 'boolean',
        'wallet_balance' => 'decimal:2',
        'total_spent' => 'decimal:2',
        'referral_earnings' => 'decimal:2',
        'preferences' => 'array',
        'metadata' => 'array'
    ];
    
    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        // Generate referral code on user creation
        static::creating(function ($user) {
            if (empty($user->referral_code)) {
                $user->referral_code = static::generateReferralCode();
            }
        });
    }
    
    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
    
    /**
     * Set the user's password (hashed).
     *
     * @param string $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }
    
    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }
    
    /**
     * Check if the user is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status === 'active';
    }
    
    /**
     * Check if the user has an active subscription.
     *
     * @return bool
     */
    public function hasActiveSubscription()
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->exists();
    }
    
    /**
     * Generate a unique referral code.
     *
     * @return string
     */
    protected static function generateReferralCode()
    {
        do {
            $code = strtoupper(substr(md5(uniqid()), 0, 8));
        } while (static::where('referral_code', $code)->exists());
        
        return $code;
    }
    
    // ==================== RELATIONSHIPS ====================
    
    /**
     * Get the user's subscriptions.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    
    /**
     * Get the user's active subscription.
     */
    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->latest();
    }
    
    /**
     * Get the user's orders.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    
    /**
     * Get the user's downloads.
     */
    public function downloads()
    {
        return $this->hasMany(Download::class);
    }
    
    /**
     * Get the user's addresses.
     */
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
    
    /**
     * Get the user's payment methods.
     */
    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }
    
    /**
     * Get the user's wishlist items.
     */
    public function wishlist()
    {
        return $this->belongsToMany(File::class, 'wishlists')
            ->withTimestamps();
    }
    
    /**
     * Get the user's cart items.
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
    
    /**
     * Get the user's reviews.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    
    /**
     * Get the user who referred this user.
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }
    
    /**
     * Get the users referred by this user.
     */
    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by');
    }
    
    /**
     * Get the user's notifications.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
    
    /**
     * Get the user's activity logs.
     */
    public function activities()
    {
        return $this->hasMany(ActivityLog::class);
    }
    
    /**
     * Get the user's API tokens.
     */
    public function apiTokens()
    {
        return $this->hasMany(ApiToken::class);
    }
    
    /**
     * Get the coupons used by this user.
     */
    public function usedCoupons()
    {
        return $this->belongsToMany(Coupon::class, 'coupon_usages')
            ->withPivot('used_at', 'order_id')
            ->withTimestamps();
    }
}