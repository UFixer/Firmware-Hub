<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            
            // Subscription Identification
            $table->string('subscription_id', 50)->unique(); // Public subscription ID
            $table->string('reference_number', 50)->unique()->nullable();
            
            // User & Package Relationship
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('package_id');
            $table->string('package_name', 100); // Cached package name
            $table->string('package_tier', 20); // Cached tier
            
            // Subscription Status
            $table->enum('status', [
                'pending',
                'active',
                'paused',
                'cancelled',
                'expired',
                'suspended',
                'past_due'
            ])->default('pending');
            
            // Billing Information
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('billing_cycle', 20); // monthly, yearly, etc.
            $table->integer('billing_frequency')->default(1);
            $table->date('next_billing_date')->nullable();
            $table->date('last_billing_date')->nullable();
            $table->integer('billing_attempts')->default(0);
            
            // Subscription Period
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('resumed_at')->nullable();
            
            // Download & Bandwidth Tracking
            $table->integer('downloads_used_today')->default(0);
            $table->integer('downloads_used_month')->default(0);
            $table->bigInteger('bandwidth_used_bytes')->default(0);
            $table->string('bandwidth_used_formatted', 20)->default('0 MB');
            $table->date('bandwidth_reset_date')->nullable();
            $table->integer('daily_limit')->default(10); // Cached from package
            $table->integer('monthly_limit')->default(300); // Cached from package
            $table->bigInteger('monthly_bandwidth_limit')->default(10737418240); // Cached limit
            
            // Usage Statistics
            $table->integer('total_downloads')->default(0);
            $table->bigInteger('total_bandwidth_bytes')->default(0);
            $table->timestamp('last_download_at')->nullable();
            $table->integer('api_calls_today')->default(0);
            $table->integer('api_calls_month')->default(0);
            $table->integer('active_devices')->default(0);
            
            // Payment Details
            $table->string('payment_method', 50)->nullable(); // stripe, paypal, etc.
            $table->string('payment_profile_id', 100)->nullable(); // Customer ID in payment gateway
            $table->string('payment_subscription_id', 100)->nullable(); // Subscription ID in gateway
            $table->json('payment_metadata')->nullable(); // Additional payment data
            $table->boolean('auto_renew')->default(true);
            
            // Discount & Promotions
            $table->string('coupon_code', 50)->nullable();
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('discount_percent', 5, 2)->default(0.00);
            $table->timestamp('discount_valid_until')->nullable();
            
            // Renewal Settings
            $table->integer('renewal_count')->default(0);
            $table->decimal('renewal_price', 10, 2)->nullable(); // Price for next renewal
            $table->boolean('price_locked')->default(false); // Lock price from increases
            $table->timestamp('grace_period_ends')->nullable(); // Grace period after failed payment
            
            // Upgrade/Downgrade History
            $table->unsignedBigInteger('previous_package_id')->nullable();
            $table->timestamp('upgraded_at')->nullable();
            $table->timestamp('downgraded_at')->nullable();
            $table->decimal('proration_amount', 10, 2)->default(0.00);
            
            // Cancellation Details
            $table->string('cancellation_reason', 500)->nullable();
            $table->text('cancellation_feedback')->nullable();
            $table->boolean('cancellation_requested')->default(false);
            $table->timestamp('cancellation_effective_date')->nullable();
            
            // Suspension Details
            $table->string('suspension_reason', 500)->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->integer('suspension_count')->default(0);
            
            // Features Override (Custom settings)
            $table->json('custom_limits')->nullable(); // Override package limits
            $table->json('custom_features')->nullable(); // Additional features
            $table->boolean('is_complimentary')->default(false); // Free/comp subscription
            
            // Notifications
            $table->timestamp('renewal_reminder_sent')->nullable();
            $table->timestamp('expiry_warning_sent')->nullable();
            $table->timestamp('bandwidth_warning_sent')->nullable();
            $table->integer('notification_count')->default(0);
            
            // Device Management
            $table->json('authorized_devices')->nullable(); // Array of device IDs
            $table->json('device_history')->nullable(); // History of device changes
            $table->integer('max_devices')->default(5); // Cached from package
            
            // IP & Security
            $table->json('allowed_ips')->nullable(); // IP whitelist
            $table->json('blocked_ips')->nullable(); // IP blacklist
            $table->string('last_ip_address', 45)->nullable();
            $table->string('last_user_agent', 500)->nullable();
            
            // Referral & Affiliate
            $table->unsignedBigInteger('referred_by')->nullable();
            $table->string('affiliate_code', 50)->nullable();
            $table->decimal('affiliate_commission', 10, 2)->default(0.00);
            
            // Notes & Metadata
            $table->text('user_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->json('metadata')->nullable();
            $table->json('tags')->nullable(); // For categorization
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('subscription_id');
            $table->index('user_id');
            $table->index('package_id');
            $table->index('status');
            $table->index('next_billing_date');
            $table->index('ends_at');
            $table->index(['user_id', 'status']);
            $table->index(['package_id', 'status']);
            $table->index(['status', 'next_billing_date']);
            $table->index('bandwidth_reset_date');
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
}