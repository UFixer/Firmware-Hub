<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            
            // Hierarchy
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('level')->default(0); // 0 for root, 1 for child, etc.
            $table->string('path', 500)->nullable(); // e.g., "1/3/5" for breadcrumb
            $table->integer('children_count')->default(0);
            
            // Display Settings
            $table->string('icon', 50)->nullable(); // Icon class or name
            $table->string('image_url', 500)->nullable(); // External CDN URL
            $table->string('banner_url', 500)->nullable(); // Category banner URL
            $table->string('thumbnail_url', 500)->nullable(); // Thumbnail URL
            $table->string('color_code', 7)->nullable(); // Hex color for UI
            
            // Status & Visibility
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('show_in_menu')->default(true);
            $table->boolean('show_in_homepage')->default(false);
            $table->integer('sort_order')->default(0);
            
            // Product Management
            $table->integer('product_count')->default(0); // Cached count
            $table->decimal('min_price', 10, 2)->nullable(); // Min product price
            $table->decimal('max_price', 10, 2)->nullable(); // Max product price
            
            // SEO Fields
            $table->string('meta_title', 160)->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('canonical_url', 500)->nullable();
            $table->json('schema_markup')->nullable(); // JSON-LD structured data
            
            // Category Specific Settings
            $table->enum('category_type', ['phones', 'tablets', 'accessories', 'tools', 'other'])
                  ->default('phones');
            $table->json('attributes')->nullable(); // Specific attributes for this category
            $table->json('filters')->nullable(); // Available filters
            $table->json('specifications')->nullable(); // Required specs for products
            
            // Commission & Pricing
            $table->decimal('commission_rate', 5, 2)->default(0.00); // % commission
            $table->decimal('flat_fee', 10, 2)->default(0.00); // Fixed fee per sale
            $table->boolean('allow_discount')->default(true);
            $table->decimal('max_discount_percent', 5, 2)->default(100.00);
            
            // Access Control
            $table->boolean('requires_auth')->default(false); // Requires login to view
            $table->enum('visibility', ['public', 'private', 'wholesale'])->default('public');
            $table->json('allowed_user_roles')->nullable(); // Which roles can access
            
            // Layout Settings
            $table->string('layout_type', 50)->default('grid'); // grid, list, masonry
            $table->integer('products_per_page')->default(20);
            $table->string('default_sort', 50)->default('featured'); // featured, newest, price
            
            // Additional Metadata
            $table->json('custom_fields')->nullable(); // Flexible additional data
            $table->text('admin_notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('slug');
            $table->index('parent_id');
            $table->index('level');
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('sort_order');
            $table->index('category_type');
            $table->index(['is_active', 'show_in_menu']);
            $table->index(['parent_id', 'is_active', 'sort_order']);
            
            // Foreign key constraint
            $table->foreign('parent_id')->references('id')->on('categories')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
}