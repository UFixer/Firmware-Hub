<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('email', 100)->unique();
            $table->string('username', 50)->unique()->nullable();
            $table->string('password');
            
            // Contact Information
            $table->string('phone', 20)->nullable();
            $table->string('phone_verified_at')->nullable();
            $table->string('country_code', 5)->default('+1');
            
            // Account Status & Type
            $table->enum('role', ['customer', 'admin', 'moderator'])->default('customer');
            $table->enum('status', ['active', 'inactive', 'suspended', 'banned'])->default('active');
            $table->boolean('is_verified')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            
            // Profile Information
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable();
            $table->string('avatar_url', 500)->nullable(); // External URL only
            $table->text('bio')->nullable();
            
            // Authentication & Security
            $table->string('remember_token', 100)->nullable();
            $table->string('two_factor_secret')->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->integer('login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            
            // Password Reset
            $table->string('password_reset_token', 100)->nullable();
            $table->timestamp('password_reset_expires')->nullable();
            
            // Email Verification
            $table->string('email_verification_token', 100)->nullable();
            $table->timestamp('email_verification_expires')->nullable();
            
            // Preferences
            $table->boolean('newsletter_subscribed')->default(false);
            $table->boolean('sms_notifications')->default(false);
            $table->boolean('email_notifications')->default(true);
            $table->string('timezone', 50)->default('UTC');
            $table->string('language', 5)->default('en');
            $table->string('currency', 3)->default('USD');
            
            // Customer Specific Fields
            $table->decimal('wallet_balance', 10, 2)->default(0.00);
            $table->integer('loyalty_points')->default(0);
            $table->enum('customer_type', ['regular', 'wholesale', 'vip'])->default('regular');
            $table->decimal('total_spent', 12, 2)->default(0.00);
            $table->integer('total_orders')->default(0);
            
            // Referral System
            $table->string('referral_code', 20)->unique()->nullable();
            $table->unsignedBigInteger('referred_by')->nullable();
            $table->integer('referral_count')->default(0);
            $table->decimal('referral_earnings', 10, 2)->default(0.00);
            
            // Metadata
            $table->json('preferences')->nullable(); // Store JSON preferences
            $table->json('metadata')->nullable(); // Additional flexible data
            $table->text('notes')->nullable(); // Admin notes
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('email');
            $table->index('username');
            $table->index('role');
            $table->index('status');
            $table->index('created_at');
            $table->index('referral_code');
            $table->index(['email', 'password']); // For login queries
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}