<?php
/**
 * FirmwareHub Application Configuration
 * Optimized for shared hosting environments
 */

return [
    // Application name and environment
    'name' => env('APP_NAME', 'FirmwareHub'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    
    // Timezone and locale settings
    'timezone' => 'UTC',
    'locale' => 'en',
    'fallback_locale' => 'en',
    
    // Encryption key for security
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',
    
    // Session configuration (file-based for shared hosting)
    'session' => [
        'driver' => env('SESSION_DRIVER', 'file'),
        'lifetime' => env('SESSION_LIFETIME', 120),
        'expire_on_close' => false,
        'encrypt' => true,
        'path' => '/',
        'domain' => env('SESSION_DOMAIN', null),
        'secure' => env('SESSION_SECURE_COOKIE', true),
        'http_only' => true,
        'same_site' => 'lax',
    ],
    
    // Cache configuration (file-based)
    'cache' => [
        'driver' => env('CACHE_DRIVER', 'file'),
        'path' => dirname(__DIR__) . '/storage/cache',
        'default_ttl' => 3600, // 1 hour default cache
    ],
    
    // Storage settings for firmware files (URLs only)
    'storage' => [
        'type' => env('STORAGE_TYPE', 'external'),
        'cdn_url' => env('CDN_URL'),
        'firmware_url' => env('FIRMWARE_STORAGE_URL'),
        'max_url_length' => env('MAX_URL_LENGTH', 500),
    ],
    
    // Payment gateways
    'payment' => [
        'stripe' => [
            'key' => env('STRIPE_KEY'),
            'secret' => env('STRIPE_SECRET'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        ],
        'paypal' => [
            'mode' => env('PAYPAL_MODE', 'live'),
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'secret' => env('PAYPAL_SECRET'),
        ],
    ],
    
    // Email configuration
    'mail' => [
        'mailer' => env('MAIL_MAILER', 'smtp'),
        'host' => env('MAIL_HOST'),
        'port' => env('MAIL_PORT', 587),
        'username' => env('MAIL_USERNAME'),
        'password' => env('MAIL_PASSWORD'),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'from' => [
            'address' => env('MAIL_FROM_ADDRESS'),
            'name' => env('MAIL_FROM_NAME'),
        ],
    ],
    
    // Pagination and limits
    'pagination' => [
        'per_page' => env('ITEMS_PER_PAGE', 20),
        'max_export' => env('MAX_EXPORT_ROWS', 5000),
    ],
    
    // Security settings
    'security' => [
        'bcrypt_rounds' => env('BCRYPT_ROUNDS', 10),
        'password_timeout' => 10800, // 3 hours
        'api_rate_limit' => env('API_RATE_LIMIT', 60),
        'api_rate_decay' => env('API_RATE_LIMIT_DECAY', 1),
    ],
    
    // Admin configuration
    'admin' => [
        'email' => env('ADMIN_EMAIL', 'admin@firmwarehub.com'),
        'prefix' => env('ADMIN_PREFIX', 'admin'),
    ],
    
    // Shared hosting optimizations
    'hosting' => [
        'memory_limit' => '256M', // Conservative for 512MB limit
        'max_execution_time' => 25, // Under 30-second limit
        'upload_max_filesize' => '10M',
        'post_max_size' => '10M',
    ],
];