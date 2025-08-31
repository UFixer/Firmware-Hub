<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            
            // Order Identification
            $table->string('order_number', 50)->unique();
            $table->string('invoice_number', 50)->unique()->nullable();
            $table->string('reference_id', 100)->nullable(); // External reference
            
            // User Information
            $table->unsignedBigInteger('user_id');
            $table->string('customer_email', 100);
            $table->string('customer_name', 100);
            $table->string('customer_phone', 20)->nullable();
            
            // Order Type & Status
            $table->enum('type', ['purchase', 'subscription', 'renewal', 'upgrade', 'credit'])->default('purchase');
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'cancelled',
                'refunded',
                'partially_refunded',
                'failed',
                'on_hold'
            ])->default('pending');
            
            // Financial Information
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->decimal('exchange_rate', 10, 4)->default(1.0000);
            
            // Payment Information
            $table->enum('payment_status', [
                'pending',
                'paid',
                'partially_paid',
                'failed',
                'refunded',
                'cancelled'
            ])->default('pending');
            $table->string('payment_method', 50)->nullable(); // stripe, paypal, wallet
            $table->string('transaction_id', 100)->nullable();
            $table->json('payment_details')->nullable(); // Gateway response
            $table->timestamp('paid_at')->nullable();
            
            // Items Summary
            $table->integer('item_count')->default(0);
            $table->json('items_summary')->nullable(); // Quick summary of items
            
            // File/Package Association
            $table->unsignedBigInteger('package_id')->nullable();
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->json('file_ids')->nullable(); // For direct file purchases
            
            // Download Information
            $table->integer('download_limit')->nullable();
            $table->integer('downloads_used')->default(0);
            $table->timestamp('download_expires_at')->nullable();
            $table->string('download_token', 100)->unique()->nullable();
            $table->json('download_ips')->nullable(); // Track download IPs
            
            // Bandwidth Tracking
            $table->bigInteger('bandwidth_limit_bytes')->nullable();
            $table->bigInteger('bandwidth_used_bytes')->default(0);
            $table->string('bandwidth_used_formatted', 20)->default('0 MB');
            
            // Billing Address
            $table->string('billing_name', 100)->nullable();
            $table->string('billing_email', 100)->nullable();
            $table->string('billing_phone', 20)->nullable();
            $table->string('billing_address', 255)->nullable();
            $table->string('billing_city', 100)->nullable();
            $table->string('billing_state', 100)->nullable();
            $table->string('billing_country', 2)->nullable();
            $table->string('billing_postal_code', 20)->nullable();
            
            // Shipping (if applicable for physical items)
            $table->boolean('requires_shipping')->default(false);
            $table->string('shipping_method', 50)->nullable();
            $table->decimal('shipping_cost', 10, 2)->default(0.00);
            $table->json('shipping_address')->nullable();
            $table->string('tracking_number', 100)->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            // Coupon/Discount
            $table->string('coupon_code', 50)->nullable();
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->decimal('coupon_discount', 10, 2)->default(0.00);
            $table->string('discount_type', 20)->nullable(); // percentage, fixed
            
            // Refund Information
            $table->decimal('refunded_amount', 10, 2)->default(0.00);
            $table->timestamp('refunded_at')->nullable();
            $table->string('refund_reason', 500)->nullable();
            $table->string('refund_transaction_id', 100)->nullable();
            $table->json('refund_details')->nullable();
            
            // Tax Information
            $table->string('tax_id', 50)->nullable(); // Customer tax ID
            $table->string('tax_exempt_id', 50)->nullable();
            $table->boolean('is_tax_exempt')->default(false);
            $table->json('tax_lines')->nullable(); // Multiple tax types
            
            // Invoice Details
            $table->boolean('invoice_sent')->default(false);
            $table->timestamp('invoice_sent_at')->nullable();
            $table->string('invoice_url', 500)->nullable(); // External invoice URL
            $table->text('invoice_notes')->nullable();
            
            // Affiliate/Referral
            $table->unsignedBigInteger('affiliate_id')->nullable();
            $table->string('affiliate_code', 50)->nullable();
            $table->decimal('affiliate_commission', 10, 2)->default(0.00);
            $table->boolean('affiliate_paid')->default(false);
            
            // Customer IP & Location
            $table->string('customer_ip', 45)->nullable();
            $table->string('customer_country', 2)->nullable();
            $table->string('customer_region', 100)->nullable();
            $table->string('customer_city', 100)->nullable();
            $table->json('geo_location')->nullable(); // Lat/lng
            
            // Device & Browser Info
            $table->string('user_agent', 500)->nullable();
            $table->string('device_type', 50)->nullable(); // mobile, desktop, tablet
            $table->string('browser', 50)->nullable();
            $table->string('os', 50)->nullable();
            
            // Fraud Detection
            $table->decimal('fraud_score', 5, 2)->default(0.00);
            $table->json('fraud_checks')->nullable();
            $table->boolean('is_fraudulent')->default(false);
            $table->boolean('requires_verification')->default(false);
            
            // Order Source
            $table->string('source', 50)->default('website'); // website, api, admin, import
            $table->string('channel', 50)->nullable(); // direct, affiliate, social
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 100)->nullable();
            
            // Notes & Metadata
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->json('metadata')->nullable();
            $table->json('tags')->nullable();
            
            // Processing Information
            $table->timestamp('processed_at')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('order_number');
            $table->index('user_id');
            $table->index('status');
            $table->index('payment_status');
            $table->index('type');
            $table->index('created_at');
            $table->index('package_id');
            $table->index('subscription_id');
            $table->index(['user_id', 'status']);
            $table->index(['status', 'payment_status']);
            $table->index('download_token');
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}