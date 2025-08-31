<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'orders';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_number',
        'invoice_number',
        'reference_id',
        'user_id',
        'customer_email',
        'customer_name',
        'customer_phone',
        'type',
        'status',
        'subtotal',
        'tax_amount',
        'tax_rate',
        'discount_amount',
        'total_amount',
        'currency',
        'exchange_rate',
        'payment_status',
        'payment_method',
        'transaction_id',
        'payment_details',
        'paid_at',
        'item_count',
        'items_summary',
        'package_id',
        'subscription_id',
        'file_ids',
        'download_limit',
        'downloads_used',
        'download_expires_at',
        'download_token',
        'download_ips',
        'bandwidth_limit_bytes',
        'bandwidth_used_bytes',
        'bandwidth_used_formatted',
        'billing_name',
        'billing_email',
        'billing_phone',
        'billing_address',
        'billing_city',
        'billing_state',
        'billing_country',
        'billing_postal_code',
        'requires_shipping',
        'shipping_method',
        'shipping_cost',
        'shipping_address',
        'tracking_number',
        'shipped_at',
        'delivered_at',
        'coupon_code',
        'coupon_id',
        'coupon_discount',
        'discount_type',
        'refunded_amount',
        'refunded_at',
        'refund_reason',
        'refund_transaction_id',
        'refund_details',
        'tax_id',
        'tax_exempt_id',
        'is_tax_exempt',
        'tax_lines',
        'invoice_sent',
        'invoice_sent_at',
        'invoice_url',
        'invoice_notes',
        'affiliate_id',
        'affiliate_code',
        'affiliate_commission',
        'affiliate_paid',
        'customer_ip',
        'customer_country',
        'customer_region',
        'customer_city',
        'geo_location',
        'user_agent',
        'device_type',
        'browser',
        'os',
        'fraud_score',
        'fraud_checks',
        'is_fraudulent',
        'requires_verification',
        'source',
        'channel',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'customer_notes',
        'admin_notes',
        'internal_notes',
        'metadata',
        'tags',
        'processed_at',
        'processed_by',
        'completed_at',
        'cancelled_at'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'payment_details' => 'array',
        'paid_at' => 'datetime',
        'item_count' => 'integer',
        'items_summary' => 'array',
        'file_ids' => 'array',
        'download_limit' => 'integer',
        'downloads_used' => 'integer',
        'download_expires_at' => 'datetime',
        'download_ips' => 'array',
        'bandwidth_limit_bytes' => 'integer',
        'bandwidth_used_bytes' => 'integer',
        'requires_shipping' => 'boolean',
        'shipping_cost' => 'decimal:2',
        'shipping_address' => 'array',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'coupon_discount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'refunded_at' => 'datetime',
        'refund_details' => 'array',
        'is_tax_exempt' => 'boolean',
        'tax_lines' => 'array',
        'invoice_sent' => 'boolean',
        'invoice_sent_at' => 'datetime',
        'affiliate_commission' => 'decimal:2',
        'affiliate_paid' => 'boolean',
        'geo_location' => 'array',
        'fraud_score' => 'decimal:2',
        'fraud_checks' => 'array',
        'is_fraudulent' => 'boolean',
        'requires_verification' => 'boolean',
        'metadata' => 'array',
        'tags' => 'array',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime'
    ];
    
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'payment_details',
        'admin_notes',
        'internal_notes'
    ];
    
    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        // Generate order number and download token on creation
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
            if (empty($order->download_token)) {
                $order->download_token = static::generateDownloadToken();
            }
            // Format bandwidth
            if ($order->bandwidth_used_bytes && empty($order->bandwidth_used_formatted)) {
                $order->bandwidth_used_formatted = static::formatBytes($order->bandwidth_used_bytes);
            }
        });
        
        // Update formatted bandwidth when bytes change
        static::updating(function ($order) {
            if ($order->isDirty('bandwidth_used_bytes')) {
                $order->bandwidth_used_formatted = static::formatBytes($order->bandwidth_used_bytes);
            }
        });
    }
    
    /**
     * Generate a unique order number.
     *
     * @return string
     */
    protected static function generateOrderNumber()
    {
        do {
            $number = 'ORD-' . date('Ymd') . '-' . mt_rand(1000, 9999);
        } while (static::where('order_number', $number)->exists());
        
        return $number;
    }
    
    /**
     * Generate a unique download token.
     *
     * @return string
     */
    protected static function generateDownloadToken()
    {
        do {
            $token = bin2hex(random_bytes(32));
        } while (static::where('download_token', $token)->exists());
        
        return $token;
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
     * Scope for paid orders.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }
    
    /**
     * Scope for pending orders.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    /**
     * Scope for completed orders.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
    
    /**
     * Check if order is paid.
     *
     * @return bool
     */
    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }
    
    /**
     * Check if order is completed.
     *
     * @return bool
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }
    
    /**
     * Check if order can be refunded.
     *
     * @return bool
     */
    public function canBeRefunded()
    {
        return $this->isPaid() && 
               !in_array($this->status, ['refunded', 'partially_refunded']);
    }
    
    /**
     * Check if download is available.
     *
     * @return bool
     */
    public function isDownloadAvailable()
    {
        if (!$this->isPaid()) {
            return false;
        }
        
        if ($this->download_expires_at && $this->download_expires_at->isPast()) {
            return false;
        }
        
        if ($this->download_limit && $this->downloads_used >= $this->download_limit) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if bandwidth limit is reached.
     *
     * @return bool
     */
    public function hasBandwidthLimitReached()
    {
        if (!$this->bandwidth_limit_bytes) {
            return false;
        }
        
        return $this->bandwidth_used_bytes >= $this->bandwidth_limit_bytes;
    }
    
    /**
     * Get remaining bandwidth.
     *
     * @return int
     */
    public function getRemainingBandwidth()
    {
        if (!$this->bandwidth_limit_bytes) {
            return PHP_INT_MAX;
        }
        
        return max(0, $this->bandwidth_limit_bytes - $this->bandwidth_used_bytes);
    }
    
    /**
     * Get remaining bandwidth formatted.
     *
     * @return string
     */
    public function getRemainingBandwidthFormatted()
    {
        return static::formatBytes($this->getRemainingBandwidth());
    }
    
    /**
     * Calculate total with tax.
     *
     * @return float
     */
    public function calculateTotalWithTax()
    {
        $total = $this->subtotal - $this->discount_amount;
        
        if (!$this->is_tax_exempt) {
            $total += $this->tax_amount;
        }
        
        return $total;
    }
    
    /**
     * Mark order as paid.
     *
     * @param string $transactionId
     * @return void
     */
    public function markAsPaid($transactionId)
    {
        $this->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
            'transaction_id' => $transactionId
        ]);
    }
    
    /**
     * Process refund.
     *
     * @param float $amount
     * @param string $reason
     * @param string $transactionId
     * @return void
     */
    public function processRefund($amount, $reason, $transactionId)
    {
        $status = ($amount >= $this->total_amount) ? 'refunded' : 'partially_refunded';
        
        $this->update([
            'status' => $status,
            'refunded_amount' => $amount,
            'refunded_at' => now(),
            'refund_reason' => $reason,
            'refund_transaction_id' => $transactionId
        ]);
    }
    
    // ==================== RELATIONSHIPS ====================
    
    /**
     * Get the user that owns the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the package associated with the order.
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
    
    /**
     * Get the subscription associated with the order.
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
    
    /**
     * Get the coupon used in the order.
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
    
    /**
     * Get the order items.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    
    /**
     * Get the files associated with the order.
     */
    public function files()
    {
        return $this->belongsToMany(File::class, 'order_files')
            ->withPivot('price', 'download_limit')
            ->withTimestamps();
    }
    
    /**
     * Get the downloads for this order.
     */
    public function downloads()
    {
        return $this->hasMany(Download::class);
    }
    
    /**
     * Get the affiliate user.
     */
    public function affiliate()
    {
        return $this->belongsTo(User::class, 'affiliate_id');
    }
    
    /**
     * Get the user who processed the order.
     */
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}