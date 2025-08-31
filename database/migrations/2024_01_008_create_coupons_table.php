<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            
            // Coupon Identification
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('slug', 120)->unique()->nullable();
            
            // Discount Configuration
            $table->enum('type', ['fixed', 'percentage', 'free_trial', 'upgrade', 'credit'])->default('percentage');
            $table->decimal('discount_amount', 10, 2)->default(0.00); // For fixed discount
            $table->decimal('discount_percent', 5, 2)->default(0.00); // For percentage discount
            $table->decimal('max_discount_amount', 10, 2)->nullable(); // Cap for percentage discounts
            $table->string('currency', 3)->default('USD'); // For fixed discounts
            
            // Credit/Wallet Configuration
            $table->decimal('credit_amount', 10, 2)->default(0.00); // For credit type
            $table->integer('bonus_downloads')->default(0); // Extra downloads
            $table->bigInteger('bonus_bandwidth_bytes')->default(0); // Extra bandwidth
            $table->string('bonus_bandwidth_formatted', 20)->nullable();
            
            // Trial Configuration
            $table->integer('trial_days')->default(0); // For free_trial type
            $table->boolean('convert_to_paid')->default(true); // Auto-convert after trial
            
            // Applicability
            $table->enum('applies_to', ['all', 'packages', 'subscriptions', 'files', 'categories'])->default('all');
            $table->json('applicable_packages')->nullable(); // Specific package IDs
            $table->json('applicable_categories')->nullable(); // Specific category IDs
            $table->json('applicable_files')->nullable(); // Specific file IDs
            $table->json('applicable_tiers')->nullable(); // ['basic', 'premium']
            $table->decimal('minimum_amount', 10, 2)->default(0.00); // Min order amount
            $table->decimal('maximum_amount', 10, 2)->nullable(); // Max order amount
            
            // Usage Limits
            $table->integer('usage_limit')->nullable(); // Total usage limit
            $table->integer('usage_count')->default(0); // Times used
            $table->integer('usage_limit_per_user')->default(1); // Per user limit
            $table->boolean('new_users_only')->default(false);
            $table->boolean('existing_users_only')->default(false);
            $table->boolean('first_purchase_only')->default(false);
            
            // Validity Period
            $table->timestamp('valid_from');
            $table->timestamp('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('valid_days')->nullable(); // ['monday', 'tuesday']
            $table->json('valid_hours')->nullable(); // [9, 10, 11, 12]
            $table->string('timezone', 50)->default('UTC');
            
            // Recurring Discounts
            $table->boolean('apply_to_renewals')->default(false);
            $table->integer('recurring_months')->nullable(); // Apply for X months
            $table->integer('recurring_count')->nullable(); // Apply X times
            $table->boolean('lifetime_discount')->default(false);
            
            // Stacking Rules
            $table->boolean('stackable')->default(false); // Can combine with other coupons
            $table->json('stackable_with')->nullable(); // Specific coupon IDs
            $table->json('not_stackable_with')->nullable(); // Exclude specific coupons
            $table->integer('priority')->default(0); // Order of application
            
            // User Restrictions
            $table->json('allowed_users')->nullable(); // Specific user IDs
            $table->json('blocked_users')->nullable(); // Blocked user IDs
            $table->json('allowed_emails')->nullable(); // Email whitelist
            $table->json('allowed_domains')->nullable(); // Email domains
            $table->json('allowed_roles')->nullable(); // ['customer', 'vip']
            $table->json('allowed_countries')->nullable(); // Country codes
            $table->json('blocked_countries')->nullable();
            
            // Affiliate/Partner Configuration
            $table->boolean('is_affiliate_coupon')->default(false);
            $table->unsignedBigInteger('affiliate_id')->nullable();
            $table->decimal('affiliate_commission', 5, 2)->default(0.00); // Commission %
            $table->string('partner_code', 50)->nullable();
            $table->json('tracking_parameters')->nullable(); // UTM params
            
            // Display Settings
            $table->boolean('show_on_pricing_page')->default(false);
            $table->boolean('auto_apply')->default(false); // Apply automatically if eligible
            $table->string('badge_text', 50)->nullable(); // "SPECIAL OFFER"
            $table->string('badge_color', 7)->nullable(); // Hex color
            $table->string('banner_url', 500)->nullable(); // Promo banner URL
            $table->text('terms_and_conditions')->nullable();
            
            // Campaign Information
            $table->string('campaign_name', 100)->nullable();
            $table->string('campaign_type', 50)->nullable(); // seasonal, launch, retention
            $table->string('source', 50)->nullable(); // email, social, affiliate
            $table->string('medium', 50)->nullable();
            $table->json('campaign_metadata')->nullable();
            
            // Performance Metrics
            $table->integer('redemption_count')->default(0);
            $table->decimal('total_discount_given', 12, 2)->default(0.00);
            $table->decimal('total_revenue_generated', 12, 2)->default(0.00);
            $table->decimal('conversion_rate', 5, 2)->default(0.00);
            $table->integer('views_count')->default(0); // Times viewed
            $table->integer('attempts_count')->default(0); // Failed attempts
            
            // Notification Settings
            $table->boolean('notify_on_use')->default(false);
            $table->string('notification_email', 100)->nullable();
            $table->integer('low_usage_threshold')->nullable(); // Alert when X uses remaining
            $table->boolean('low_usage_notified')->default(false);
            
            // Expiry Actions
            $table->string('expiry_action', 50)->nullable(); // extend, convert, delete
            $table->integer('auto_extend_days')->nullable();
            $table->string('convert_to_coupon', 50)->nullable(); // Convert to another coupon
            
            // A/B Testing
            $table->string('test_group', 20)->nullable(); // A, B, control
            $table->string('test_variant', 50)->nullable();
            $table->json('test_parameters')->nullable();
            
            // Security
            $table->boolean('requires_verification')->default(false);
            $table->string('verification_method', 50)->nullable(); // email, sms, manual
            $table->integer('failed_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->json('fraud_indicators')->nullable();
            
            // Integration
            $table->string('external_id', 100)->nullable(); // ID in external system
            $table->string('integration_source', 50)->nullable(); // mailchimp, hubspot
            $table->json('integration_data')->nullable();
            
            // Metadata
            $table->json('custom_fields')->nullable();
            $table->text('internal_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('code');
            $table->index('type');
            $table->index('is_active');
            $table->index('valid_from');
            $table->index('valid_until');
            $table->index(['is_active', 'valid_from', 'valid_until']);
            $table->index('usage_count');
            $table->index('affiliate_id');
            $table->index('campaign_name');
            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupons');
    }
}