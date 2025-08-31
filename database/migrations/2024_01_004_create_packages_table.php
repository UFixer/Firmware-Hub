<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackagesTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            
            // Package Identification
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->string('sku', 50)->unique()->nullable();
            $table->text('description');
            $table->text('short_description')->nullable();
            
            // Package Type & Tier
            $table->enum('type', ['subscription', 'one_time', 'lifetime', 'trial'])->default('subscription');
            $table->enum('tier', ['basic', 'standard', 'premium', 'enterprise', 'custom'])->default('basic');
            $table->integer('tier_level')->default(1); // Numeric tier for comparisons
            
            // Pricing
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable(); // For showing discounts
            $table->string('currency', 3)->default('USD');
            $table->decimal('setup_fee', 10, 2)->default(0.00);
            $table->boolean('is_free')->default(false);
            
            // Billing Cycle (for subscriptions)
            $table->enum('billing_cycle', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly', 'lifetime'])
                  ->default('monthly');
            $table->integer('billing_frequency')->default(1); // e.g., every 2 months
            $table->integer('trial_days')->default(0);
            $table->boolean('auto_renew')->default(true);
            
            // Download Limits & Bandwidth
            $table->integer('daily_download_limit')->default(10);
            $table->integer('monthly_download_limit')->default(300);
            $table->bigInteger('monthly_bandwidth_bytes')->default(10737418240); // 10GB default
            $table->string('monthly_bandwidth_formatted', 20)->default('10 GB');
            $table->integer('max_concurrent_downloads')->default(2);
            $table->integer('download_speed_kbps')->nullable(); // Speed limit in kbps
            
            // Access Permissions
            $table->json('allowed_categories')->nullable(); // Array of category IDs
            $table->json('allowed_file_types')->nullable(); // ['firmware', 'rom', 'tool']
            $table->json('allowed_brands')->nullable(); // Specific brands access
            $table->boolean('access_premium_files')->default(false);
            $table->boolean('access_beta_files')->default(false);
            $table->boolean('early_access')->default(false);
            $table->integer('early_access_days')->default(0); // Days before public release
            
            // Features & Benefits
            $table->json('features')->nullable(); // Array of feature descriptions
            $table->boolean('ad_free')->default(false);
            $table->boolean('priority_support')->default(false);
            $table->boolean('fast_download_servers')->default(false);
            $table->boolean('request_files')->default(false); // Can request new files
            $table->integer('max_devices')->default(5); // Device activation limit
            $table->boolean('commercial_use')->default(false);
            
            // API Access
            $table->boolean('api_access')->default(false);
            $table->integer('api_rate_limit')->default(100); // Requests per hour
            $table->bigInteger('api_monthly_calls')->default(10000);
            
            // Storage & History
            $table->integer('download_history_days')->default(30); // Keep history for X days
            $table->boolean('cloud_backup')->default(false); // Backup download history
            $table->integer('favorites_limit')->default(50);
            $table->integer('collections_limit')->default(10);
            
            // Display Settings
            $table->string('badge_text', 20)->nullable(); // e.g., "POPULAR", "BEST VALUE"
            $table->string('badge_color', 7)->nullable(); // Hex color
            $table->string('icon_url', 500)->nullable(); // External icon URL
            $table->string('highlight_color', 7)->nullable(); // UI highlight color
            $table->integer('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_recommended')->default(false);
            
            // Promotional Settings
            $table->decimal('discount_percent', 5, 2)->default(0.00);
            $table->timestamp('promo_starts_at')->nullable();
            $table->timestamp('promo_ends_at')->nullable();
            $table->string('promo_code', 50)->nullable();
            $table->integer('max_subscriptions')->nullable(); // Limit total subscriptions
            
            // Upgrade/Downgrade Rules
            $table->json('can_upgrade_to')->nullable(); // Array of package IDs
            $table->json('can_downgrade_to')->nullable(); // Array of package IDs
            $table->boolean('prorate_on_upgrade')->default(true);
            $table->boolean('prorate_on_downgrade')->default(true);
            $table->decimal('cancellation_fee', 10, 2)->default(0.00);
            
            // Status & Availability
            $table->enum('status', ['active', 'inactive', 'deprecated', 'beta'])->default('active');
            $table->timestamp('available_from')->nullable();
            $table->timestamp('available_until')->nullable();
            $table->json('available_countries')->nullable(); // Country restrictions
            $table->json('blocked_countries')->nullable();
            
            // SEO & Marketing
            $table->string('meta_title', 160)->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->json('selling_points')->nullable(); // Bullet points for marketing
            
            // Analytics & Metrics
            $table->integer('total_subscriptions')->default(0);
            $table->integer('active_subscriptions')->default(0);
            $table->decimal('total_revenue', 12, 2)->default(0.00);
            $table->decimal('average_rating', 3, 2)->default(0.00);
            $table->integer('total_reviews')->default(0);
            
            // Terms & Conditions
            $table->text('terms')->nullable();
            $table->text('restrictions')->nullable();
            $table->boolean('requires_agreement')->default(false);
            
            // Metadata
            $table->json('custom_fields')->nullable();
            $table->text('admin_notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('slug');
            $table->index('type');
            $table->index('tier');
            $table->index('tier_level');
            $table->index('status');
            $table->index('is_featured');
            $table->index('billing_cycle');
            $table->index(['status', 'type']);
            $table->index(['tier_level', 'price']);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('packages');
    }
}