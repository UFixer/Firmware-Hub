<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            
            // File Identification
            $table->string('uuid', 36)->unique(); // UUID for public reference
            $table->string('name', 255); // Display name
            $table->string('original_name', 255); // Original filename
            $table->string('slug', 300)->unique()->nullable();
            
            // External Storage URLs (No actual file storage)
            $table->string('file_url', 500); // Primary CDN/external URL
            $table->string('backup_url', 500)->nullable(); // Backup URL
            $table->string('thumbnail_url', 500)->nullable(); // Thumbnail for preview
            $table->string('preview_url', 500)->nullable(); // Preview/demo URL
            
            // File Type & Classification
            $table->enum('file_type', [
                'firmware', 
                'rom', 
                'tool', 
                'driver', 
                'documentation', 
                'image', 
                'video', 
                'other'
            ])->default('firmware');
            $table->string('mime_type', 100)->nullable();
            $table->string('extension', 10);
            
            // File Metadata
            $table->bigInteger('size')->default(0); // File size in bytes
            $table->string('size_formatted', 20)->nullable(); // e.g., "125.5 MB"
            $table->string('version', 50)->nullable(); // Software version
            $table->string('build_number', 50)->nullable();
            $table->date('release_date')->nullable();
            
            // Checksums & Verification
            $table->string('md5_hash', 32)->nullable();
            $table->string('sha1_hash', 40)->nullable();
            $table->string('sha256_hash', 64)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            
            // Device/Product Association
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('device_code', 100)->nullable();
            $table->json('compatible_models')->nullable(); // Array of compatible models
            $table->string('region', 50)->nullable(); // Geographic region
            $table->string('carrier', 100)->nullable(); // Mobile carrier
            
            // Android/iOS Specific
            $table->string('android_version', 20)->nullable();
            $table->string('ios_version', 20)->nullable();
            $table->string('baseband_version', 50)->nullable();
            $table->string('security_patch', 20)->nullable();
            $table->enum('platform', ['android', 'ios', 'windows', 'other'])->nullable();
            
            // Access & Security
            $table->boolean('is_public')->default(true);
            $table->boolean('requires_auth')->default(false);
            $table->boolean('is_premium')->default(false);
            $table->decimal('price', 10, 2)->default(0.00);
            $table->string('access_token', 100)->nullable(); // For protected downloads
            $table->timestamp('token_expires_at')->nullable();
            
            // Download Tracking
            $table->integer('download_count')->default(0);
            $table->integer('view_count')->default(0);
            $table->timestamp('last_downloaded_at')->nullable();
            $table->string('bandwidth_used', 20)->default('0 MB'); // Track bandwidth
            
            // Status & Availability
            $table->enum('status', ['active', 'inactive', 'archived', 'deleted'])->default('active');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_recommended')->default(false);
            $table->integer('sort_order')->default(0);
            
            // Expiration & Lifecycle
            $table->timestamp('available_from')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('max_downloads')->nullable(); // Download limit
            $table->integer('retention_days')->nullable(); // Auto-delete after X days
            
            // Related Content
            $table->json('changelog')->nullable(); // Version changelog
            $table->json('installation_notes')->nullable();
            $table->json('known_issues')->nullable();
            $table->json('requirements')->nullable(); // System requirements
            $table->json('tags')->nullable(); // Searchable tags
            
            // SEO & Discovery
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('meta_title', 160)->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            
            // Relationships
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            
            // Legal & Compliance
            $table->boolean('dmca_protected')->default(false);
            $table->string('license_type', 100)->nullable();
            $table->text('terms_of_use')->nullable();
            $table->boolean('user_agreement_required')->default(false);
            
            // CDN & Performance
            $table->string('cdn_provider', 50)->nullable(); // e.g., 'cloudflare', 's3'
            $table->string('storage_region', 50)->nullable();
            $table->integer('cdn_cache_days')->default(30);
            $table->json('mirror_urls')->nullable(); // Multiple mirror URLs
            
            // Additional Metadata
            $table->json('metadata')->nullable(); // Flexible additional data
            $table->text('admin_notes')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('uuid');
            $table->index('slug');
            $table->index('file_type');
            $table->index('status');
            $table->index('is_public');
            $table->index('is_featured');
            $table->index('brand');
            $table->index('model');
            $table->index('category_id');
            $table->index('product_id');
            $table->index('uploaded_by');
            $table->index(['file_type', 'status', 'is_public']);
            $table->index(['brand', 'model', 'version']);
            $table->fullText(['name', 'description'], 'files_fulltext');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
    }
}