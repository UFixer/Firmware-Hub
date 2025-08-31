<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Download extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'downloads';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'download_id',
        'token',
        'user_id',
        'subscription_id',
        'order_id',
        'user_email',
        'file_id',
        'file_name',
        'file_version',
        'file_url',
        'file_size_bytes',
        'file_size_formatted',
        'status',
        'bytes_downloaded',
        'bytes_remaining',
        'bandwidth_used_formatted',
        'progress_percent',
        'resume_count',
        'download_speed_kbps',
        'peak_speed_kbps',
        'throttled_speed_kbps',
        'duration_seconds',
        'started_at',
        'completed_at',
        'failed_at',
        'ip_address',
        'ip_country',
        'ip_region',
        'ip_city',
        'ip_isp',
        'is_vpn',
        'is_proxy',
        'is_tor',
        'user_agent',
        'device_type',
        'device_brand',
        'device_model',
        'browser',
        'browser_version',
        'os',
        'os_version',
        'method',
        'download_manager',
        'protocol',
        'server_id',
        'server_location',
        'cdn_provider',
        'server_response_time_ms',
        'is_authorized',
        'authorization_method',
        'token_expires_at',
        'download_attempts',
        'max_attempts',
        'expires_at',
        'is_expired',
        'expiry_reason',
        'bandwidth_exceeded',
        'rate_limited',
        'supports_resume',
        'resume_token',
        'resume_position',
        'resume_chunks',
        'error_code',
        'error_message',
        'retry_count',
        'last_retry_at',
        'error_log',
        'referrer_url',
        'source',
        'campaign',
        'affiliate_code',
        'bandwidth_cost',
        'storage_cost',
        'billing_region',
        'is_free_tier',
        'connection_drops',
        'packet_loss_percent',
        'latency_ms',
        'speed_samples',
        'performance_metrics',
        'file_hash',
        'hash_verified',
        'is_corrupted',
        'antivirus_scan',
        'scanned_at',
        'parent_download_id',
        'part_number',
        'total_parts',
        'is_multipart',
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
        'file_size_bytes' => 'integer',
        'bytes_downloaded' => 'integer',
        'bytes_remaining' => 'integer',
        'progress_percent' => 'decimal:2',
        'resume_count' => 'integer',
        'download_speed_kbps' => 'integer',
        'peak_speed_kbps' => 'integer',
        'throttled_speed_kbps' => 'integer',
        'duration_seconds' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'is_vpn' => 'boolean',
        'is_proxy' => 'boolean',
        'is_tor' => 'boolean',
        'server_response_time_ms' => 'integer',
        'is_authorized' => 'boolean',
        'token_expires_at' => 'datetime',
        'download_attempts' => 'integer',
        'max_attempts' => 'integer',
        'expires_at' => 'datetime',
        'is_expired' => 'boolean',
        'bandwidth_exceeded' => 'boolean',
        'rate_limited' => 'boolean',
        'supports_resume' => 'boolean',
        'resume_position' => 'integer',
        'resume_chunks' => 'array',
        'retry_count' => 'integer',
        'last_retry_at' => 'datetime',
        'error_log' => 'array',
        'bandwidth_cost' => 'decimal:4',
        'storage_cost' => 'decimal:4',
        'is_free_tier' => 'boolean',
        'connection_drops' => 'integer',
        'packet_loss_percent' => 'integer',
        'latency_ms' => 'integer',
        'speed_samples' => 'array',
        'performance_metrics' => 'array',
        'hash_verified' => 'boolean',
        'is_corrupted' => 'boolean',
        'scanned_at' => 'datetime',
        'part_number' => 'integer',
        'total_parts' => 'integer',
        'is_multipart' => 'boolean',
        'metadata' => 'array',
        'tags' => 'array'
    ];
    
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'token',
        'admin_notes'
    ];
    
    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        // Generate download ID and token on creation
        static::creating(function ($download) {
            if (empty($download->download_id)) {
                $download->download_id = static::generateDownloadId();
            }
            if (empty($download->token)) {
                $download->token = static::generateToken();
            }
            // Calculate remaining bytes
            if ($download->file_size_bytes && !$download->bytes_remaining) {
                $download->bytes_remaining = $download->file_size_bytes - $download->bytes_downloaded;
            }
            // Format bandwidth
            if ($download->bytes_downloaded && empty($download->bandwidth_used_formatted)) {
                $download->bandwidth_used_formatted = static::formatBytes($download->bytes_downloaded);
            }
        });
        
        // Update calculations when bytes change
        static::updating(function ($download) {
            if ($download->isDirty('bytes_downloaded')) {
                $download->bytes_remaining = $download->file_size_bytes - $download->bytes_downloaded;
                $download->bandwidth_used_formatted = static::formatBytes($download->bytes_downloaded);
                $download->progress_percent = static::calculateProgress($download->bytes_downloaded, $download->file_size_bytes);
            }
        });
    }
    
    /**
     * Generate a unique download ID.
     *
     * @return string
     */
    protected static function generateDownloadId()
    {
        do {
            $id = 'DL-' . date('YmdHis') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
        } while (static::where('download_id', $id)->exists());
        
        return $id;
    }
    
    /**
     * Generate a unique token.
     *
     * @return string
     */
    protected static function generateToken()
    {
        do {
            $token = bin2hex(random_bytes(32));
        } while (static::where('token', $token)->exists());
        
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
     * Calculate download progress percentage.
     *
     * @param int $downloaded
     * @param int $total
     * @return float
     */
    protected static function calculateProgress($downloaded, $total)
    {
        if ($total <= 0) {
            return 0;
        }
        
        return round(($downloaded / $total) * 100, 2);
    }
    
    /**
     * Scope for active downloads.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'started', 'paused', 'resumed']);
    }
    
    /**
     * Scope for completed downloads.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
    
    /**
     * Scope for failed downloads.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
    
    // ==================== BANDWIDTH CHECKING METHODS ====================
    
    /**
     * Check if bandwidth limit is exceeded.
     *
     * @return bool
     */
    public function isBandwidthExceeded()
    {
        if (!$this->subscription_id) {
            return false;
        }
        
        $subscription = $this->subscription;
        if (!$subscription) {
            return false;
        }
        
        return $subscription->hasReachedBandwidthLimit();
    }
    
    /**
     * Get available bandwidth for this download.
     *
     * @return int
     */
    public function getAvailableBandwidth()
    {
        if ($this->subscription_id) {
            $subscription = $this->subscription;
            return $subscription ? $subscription->getRemainingBandwidthBytes() : PHP_INT_MAX;
        }
        
        if ($this->order_id) {
            $order = $this->order;
            return $order ? $order->getRemainingBandwidth() : PHP_INT_MAX;
        }
        
        return PHP_INT_MAX;
    }
    
    /**
     * Check if download can proceed with bandwidth check.
     *
     * @param int $requestedBytes
     * @return bool
     */
    public function canDownload($requestedBytes = null)
    {
        // Check if download is expired
        if ($this->is_expired || ($this->expires_at && $this->expires_at->isPast())) {
            return false;
        }
        
        // Check authorization
        if (!$this->is_authorized) {
            return false;
        }
        
        // Check attempts
        if ($this->download_attempts >= $this->max_attempts) {
            return false;
        }
        
        // Check bandwidth
        $bytesNeeded = $requestedBytes ?: $this->bytes_remaining;
        $availableBandwidth = $this->getAvailableBandwidth();
        
        return $bytesNeeded <= $availableBandwidth;
    }
    
    /**
     * Update bandwidth usage.
     *
     * @param int $bytesTransferred
     * @return void
     */
    public function updateBandwidthUsage($bytesTransferred)
    {
        // Update download record
        $this->increment('bytes_downloaded', $bytesTransferred);
        
        // Update subscription bandwidth if applicable
        if ($this->subscription_id) {
            $subscription = $this->subscription;
            if ($subscription) {
                $subscription->increment('bandwidth_used_bytes', $bytesTransferred);
                $subscription->increment('total_bandwidth_bytes', $bytesTransferred);
            }
        }
        
        // Update order bandwidth if applicable
        if ($this->order_id) {
            $order = $this->order;
            if ($order) {
                $order->increment('bandwidth_used_bytes', $bytesTransferred);
            }
        }
    }
    
    /**
     * Calculate download speed.
     *
     * @return float|null
     */
    public function calculateSpeed()
    {
        if (!$this->started_at || $this->duration_seconds <= 0) {
            return null;
        }
        
        // Calculate speed in KB/s
        $speedKBps = ($this->bytes_downloaded / 1024) / $this->duration_seconds;
        
        // Convert to Kbps (kilobits per second)
        return round($speedKBps * 8, 2);
    }
    
    /**
     * Check if download should be throttled.
     *
     * @return bool
     */
    public function shouldThrottle()
    {
        if (!$this->subscription_id) {
            return false;
        }
        
        $subscription = $this->subscription;
        if (!$subscription || !$subscription->package) {
            return false;
        }
        
        // Check if package has speed limit
        return $subscription->package->download_speed_kbps > 0;
    }
    
    /**
     * Get throttle speed in kbps.
     *
     * @return int|null
     */
    public function getThrottleSpeed()
    {
        if (!$this->shouldThrottle()) {
            return null;
        }
        
        return $this->subscription->package->download_speed_kbps;
    }
    
    /**
     * Mark download as started.
     *
     * @return void
     */
    public function markAsStarted()
    {
        $this->update([
            'status' => 'started',
            'started_at' => now(),
            'download_attempts' => $this->download_attempts + 1
        ]);
    }
    
    /**
     * Mark download as completed.
     *
     * @return void
     */
    public function markAsCompleted()
    {
        $duration = $this->started_at ? now()->diffInSeconds($this->started_at) : 0;
        
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'duration_seconds' => $duration,
            'progress_percent' => 100,
            'download_speed_kbps' => $this->calculateSpeed()
        ]);
        
        // Update file download count
        if ($this->file) {
            $this->file->incrementDownloads();
        }
        
        // Update subscription download count
        if ($this->subscription) {
            $this->subscription->increment('downloads_used_today');
            $this->subscription->increment('downloads_used_month');
            $this->subscription->increment('total_downloads');
            $this->subscription->update(['last_download_at' => now()]);
        }
    }
    
    /**
     * Mark download as failed.
     *
     * @param string $errorCode
     * @param string $errorMessage
     * @return void
     */
    public function markAsFailed($errorCode, $errorMessage)
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error_code' => $errorCode,
            'error_message' => $errorMessage
        ]);
    }
    
    /**
     * Check if download can be resumed.
     *
     * @return bool
     */
    public function canResume()
    {
        return $this->supports_resume && 
               in_array($this->status, ['paused', 'failed']) &&
               $this->resume_position > 0 &&
               $this->resume_position < $this->file_size_bytes;
    }
    
    // ==================== RELATIONSHIPS ====================
    
    /**
     * Get the user that owns the download.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the file being downloaded.
     */
    public function file()
    {
        return $this->belongsTo(File::class);
    }
    
    /**
     * Get the subscription associated with the download.
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
    
    /**
     * Get the order associated with the download.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
    /**
     * Get the parent download (for multi-part downloads).
     */
    public function parentDownload()
    {
        return $this->belongsTo(Download::class, 'parent_download_id');
    }
    
    /**
     * Get the child downloads (for multi-part downloads).
     */
    public function childDownloads()
    {
        return $this->hasMany(Download::class, 'parent_download_id');
    }
}