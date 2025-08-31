<?php
/**
 * Session Configuration
 * Using database sessions for better reliability on shared hosting
 */

return [
    // Session driver - database is more reliable on shared hosting
    'driver' => env('SESSION_DRIVER', 'database'),
    
    // Session lifetime in minutes
    'lifetime' => env('SESSION_LIFETIME', 120),
    
    // Expire session on browser close
    'expire_on_close' => false,
    
    // Session encryption for security
    'encrypt' => true,
    
    // Session file storage path (if using file driver)
    'files' => dirname(__DIR__) . '/storage/framework/sessions',
    
    // Session database connection
    'connection' => env('SESSION_CONNECTION', null),
    
    // Session database table
    'table' => 'sessions',
    
    // Session cache store (not used, but required for compatibility)
    'store' => env('SESSION_STORE', null),
    
    // Session lottery for garbage collection [chances, odds]
    'lottery' => [2, 100],
    
    // Session cookie configuration
    'cookie' => env('SESSION_COOKIE', 'firmwarehub_session'),
    
    // Session cookie path
    'path' => '/',
    
    // Session cookie domain
    'domain' => env('SESSION_DOMAIN', null),
    
    // HTTPS only cookies
    'secure' => env('SESSION_SECURE_COOKIE', true),
    
    // HTTP access only (no JavaScript access)
    'http_only' => true,
    
    // Same-site cookie attribute
    'same_site' => env('SESSION_SAME_SITE', 'lax'),
    
    // Session ID regeneration settings
    'regenerate' => [
        'on_login' => true,           // Regenerate session ID on login
        'on_logout' => true,          // Regenerate session ID on logout
        'interval' => 15,             // Regenerate every 15 minutes
        'destroy_old' => true,        // Destroy old session after regeneration
    ],
    
    // Session security settings
    'security' => [
        'ip_check' => false,          // Don't check IP (can change on mobile/shared hosting)
        'user_agent_check' => true,   // Check user agent for consistency
        'fingerprint' => true,        // Use browser fingerprinting
        'token_rotation' => true,     // Rotate CSRF tokens
    ],
    
    // Session data handling
    'data' => [
        'compression' => true,        // Compress session data
        'serialize' => 'php',         // PHP serialization (most compatible)
        'max_size' => 65535,          // Max session data size (MySQL TEXT limit)
    ],
    
    // Flash data configuration
    'flash' => [
        'old_input' => '_old_input',
        'errors' => '_errors',
        'success' => '_success',
        'warning' => '_warning',
        'info' => '_info',
    ],
    
    // Session segments for different parts of application
    'segments' => [
        'cart' => 'shopping_cart',
        'wishlist' => 'user_wishlist',
        'compare' => 'product_compare',
        'recently_viewed' => 'recent_products',
        'admin' => 'admin_session',
        'api' => 'api_session',
    ],
    
    // Garbage collection settings
    'gc' => [
        'probability' => 2,           // 2% chance of GC on each request
        'max_lifetime' => 7200,       // 2 hours max lifetime for inactive sessions
        'force_clean' => 86400,       // Force clean sessions older than 24 hours
    ],
    
    // Performance settings for shared hosting
    'performance' => [
        'lazy_write' => true,         // Only write session if changed
        'batch_size' => 100,          // Batch size for cleanup operations
        'lock_timeout' => 5,          // Session lock timeout in seconds
        'read_timeout' => 3,          // Read timeout in seconds
    ],
];