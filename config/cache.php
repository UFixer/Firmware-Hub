<?php
/**
 * Cache Configuration
 * File-based caching only for shared hosting compatibility
 */

return [
    // Default cache store
    'default' => env('CACHE_DRIVER', 'file'),
    
    // Cache stores configuration
    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => dirname(__DIR__) . '/storage/framework/cache/data',
            'permission' => 0755,
            // Organize cache files in subdirectories to avoid too many files in one directory
            'directory_level' => 2,
        ],
        
        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],
        
        // Database cache as fallback option
        'database' => [
            'driver' => 'database',
            'table' => 'cache',
            'connection' => null,
            'lock_table' => 'cache_locks',
        ],
    ],
    
    // Cache key prefix to avoid collisions
    'prefix' => env('CACHE_PREFIX', 'firmwarehub_cache_'),
    
    // Cache TTL defaults (in seconds)
    'ttl' => [
        'default' => 3600,        // 1 hour
        'short' => 300,           // 5 minutes
        'medium' => 1800,         // 30 minutes
        'long' => 7200,           // 2 hours
        'day' => 86400,           // 24 hours
        'week' => 604800,         // 7 days
    ],
    
    // Cache tags for organized invalidation
    'tags' => [
        'products' => 'products_cache',
        'categories' => 'categories_cache',
        'users' => 'users_cache',
        'orders' => 'orders_cache',
        'settings' => 'settings_cache',
    ],
    
    // Garbage collection settings
    'gc' => [
        'probability' => 2,       // 2% chance on each request
        'divisor' => 100,
        'max_files' => 10000,     // Max cache files before cleanup
        'cleanup_batch' => 100,   // Files to delete per cleanup
    ],
    
    // Performance optimizations for shared hosting
    'optimizations' => [
        'compress_data' => true,              // Compress cache data to save space
        'use_igbinary' => false,              // Disabled - not always available
        'lazy_delete' => true,                // Delete expired cache lazily
        'batch_delete_size' => 50,            // Files to delete in one operation
        'directory_separator' => DIRECTORY_SEPARATOR,
    ],
    
    // Specific cache configurations
    'specific' => [
        'query_cache' => [
            'enabled' => true,
            'ttl' => 300,          // 5 minutes for database queries
            'prefix' => 'query_',
        ],
        'view_cache' => [
            'enabled' => true,
            'ttl' => 3600,         // 1 hour for compiled views
            'prefix' => 'view_',
        ],
        'route_cache' => [
            'enabled' => true,
            'ttl' => 86400,        // 24 hours for routes
            'prefix' => 'route_',
        ],
        'config_cache' => [
            'enabled' => true,
            'ttl' => 86400,        // 24 hours for config
            'prefix' => 'config_',
        ],
    ],
];