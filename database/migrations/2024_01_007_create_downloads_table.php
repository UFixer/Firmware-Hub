<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDownloadsTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('downloads', function (Blueprint $table) {
            $table->id();
            
            // Download Identification
            $table->string('download_id', 50)->unique();
            $table->string('token', 100)->unique()->nullable(); // One-time download token
            
            // User & Subscription
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('user_email', 100); // Cached for reporting
            
            // File Information
            $table->unsignedBigInteger('file_id');
            $table->string('file_name', 255); // Cached file name
            $table->string('file_version', 50)->nullable(); // Cached version
            $table->string('file_url', 500); // Actual download URL
            $table->bigInteger('file_size_bytes'); // Cached file size
            $table->string('file_size_formatted', 20); // e.g., "125.5 MB"
            
            // Download Status
            $table->enum('status', [
                'pending',
                'started',
                'completed',
                'failed',
                'cancelled',
                'expired',
                'paused',
                'resumed'
            ])->default('pending');
            
            // Bandwidth Tracking
            $table->bigInteger('bytes_downloaded')->default(0);
            $table->bigInteger('bytes_remaining')->default(0);
            $table->string('bandwidth_used_formatted', 20)->default('0 MB');
            $table->decimal('progress_percent', 5, 2)->default(0.00);
            $table->integer('resume_count')->default(0); // Times download was resumed
            
            // Speed & Performance
            $table->integer('download_speed_kbps')->nullable(); // Average speed
            $table->integer('peak_speed_kbps')->nullable(); // Peak speed achieved
            $table->integer('throttled_speed_kbps')->nullable(); // Speed limit applied
            $table->integer('duration_seconds')->default(0); // Total download time
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            
            // Connection Information
            $table->string('ip_address', 45);
            $table->string('ip_country', 2)->nullable();
            $table->string('ip_region', 100)->nullable();
            $table->string('ip_city', 100)->nullable();
            $table->string('ip_isp', 100)->nullable();
            $table->boolean('is_vpn')->default(false);
            $table->boolean('is_proxy')->default(false);
            $table->boolean('is_tor')->default(false);
            
            // Device & Browser
            $table->string('user_agent', 500)->nullable();
            $table->string('device_type', 50)->nullable(); // mobile, desktop, tablet
            $table->string('device_brand', 50)->nullable();
            $table->string('device_model', 100)->nullable();
            $table->string('browser', 50)->nullable();
            $table->string('browser_version', 20)->nullable();
            $table->string('os', 50)->nullable();
            $table->string('os_version', 20)->nullable();
            
            // Download Method
            $table->enum('method', [
                'direct',
                'redirect',
                'stream',
                'torrent',
                'api',
                'cli',
                'app'
            ])->default('direct');
            $table->string('download_manager', 100)->nullable(); // IDM, wget, curl, etc.
            $table->string('protocol', 10)->default('https'); // http, https, ftp
            
            // Server Information
            $table->string('server_id', 50)->nullable(); // Which CDN/server
            $table->string('server_location', 100)->nullable();
            $table->string('cdn_provider', 50)->nullable(); // cloudflare, s3, etc.
            $table->integer('server_response_time_ms')->nullable();
            
            // Access Control
            $table->boolean('is_authorized')->default(true);
            $table->string('authorization_method', 50)->nullable(); // token, session, api_key
            $table->timestamp('token_expires_at')->nullable();
            $table->integer('download_attempts')->default(0);
            $table->integer('max_attempts')->default(3);
            
            // Limits & Restrictions
            $table->timestamp('expires_at')->nullable(); // Download link expiry
            $table->boolean('is_expired')->default(false);
            $table->string('expiry_reason', 100)->nullable();
            $table->boolean('bandwidth_exceeded')->default(false);
            $table->boolean('rate_limited')->default(false);
            
            // Resume Support
            $table->boolean('supports_resume')->default(true);
            $table->string('resume_token', 100)->nullable();
            $table->bigInteger('resume_position')->default(0); // Byte position
            $table->json('resume_chunks')->nullable(); // For multi-part downloads
            
            // Error Handling
            $table->string('error_code', 50)->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('last_retry_at')->nullable();
            $table->json('error_log')->nullable(); // Detailed error info
            
            // Referrer & Source
            $table->string('referrer_url', 500)->nullable();
            $table->string('source', 50)->default('website'); // website, api, app
            $table->string('campaign', 100)->nullable();
            $table->string('affiliate_code', 50)->nullable();
            
            // Cost & Billing
            $table->decimal('bandwidth_cost', 10, 4)->default(0.0000); // CDN cost
            $table->decimal('storage_cost', 10, 4)->default(0.0000);
            $table->string('billing_region', 50)->nullable(); // For CDN billing
            $table->boolean('is_free_tier')->default(false);
            
            // Analytics & Metrics
            $table->integer('connection_drops')->default(0);
            $table->integer('packet_loss_percent')->default(0);
            $table->integer('latency_ms')->nullable();
            $table->json('speed_samples')->nullable(); // Speed over time
            $table->json('performance_metrics')->nullable();
            
            // Security & Verification
            $table->string('file_hash', 64)->nullable(); // SHA256 of downloaded file
            $table->boolean('hash_verified')->default(false);
            $table->boolean('is_corrupted')->default(false);
            $table->string('antivirus_scan', 50)->nullable(); // clean, infected, unknown
            $table->timestamp('scanned_at')->nullable();
            
            // Parent/Child Downloads (for multi-part)
            $table->unsignedBigInteger('parent_download_id')->nullable();
            $table->integer('part_number')->nullable();
            $table->integer('total_parts')->nullable();
            $table->boolean('is_multipart')->default(false);
            
            // Notes & Metadata
            $table->text('user_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->json('metadata')->nullable();
            $table->json('tags')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('download_id');
            $table->index('token');
            $table->index('user_id');
            $table->index('subscription_id');
            $table->index('order_id');
            $table->index('file_id');
            $table->index('status');
            $table->index('ip_address');
            $table->index('created_at');
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'created_at']);
            $table->index(['file_id', 'status']);
            $table->index(['subscription_id', 'created_at']);
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('file_id')->references('id')->on('files')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('downloads');
    }
}