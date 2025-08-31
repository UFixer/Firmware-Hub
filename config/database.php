<?php
/**
 * Database Configuration
 * Optimized for MySQL 5.7 on shared hosting
 */

return [
    // Default database connection
    'default' => env('DB_CONNECTION', 'mysql'),
    
    // Database connections
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'firmware_hub'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => false, // Disabled for MySQL 5.7 compatibility
            'engine' => 'InnoDB',
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                PDO::ATTR_PERSISTENT => false, // Avoid persistent connections on shared hosting
                PDO::ATTR_TIMEOUT => 5, // Quick timeout for shared hosting
            ]) : [],
        ],
    ],
    
    // Migration settings
    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],
    
    // Queue database table (for database queue driver)
    'queue' => [
        'table' => 'jobs',
        'failed_table' => 'failed_jobs',
        'retry_after' => 90,
    ],
    
    // Cache database table (if using database cache)
    'cache' => [
        'table' => 'cache',
        'lock_table' => 'cache_locks',
    ],
    
    // Session database table (if using database sessions)
    'sessions' => [
        'table' => 'sessions',
        'lifetime' => 120,
        'expire_on_close' => false,
    ],
    
    // Connection pool settings for shared hosting
    'pool' => [
        'min' => 1,
        'max' => 5, // Low max connections for shared hosting
    ],
    
    // Query optimization settings
    'optimization' => [
        'cache_queries' => true,
        'cache_ttl' => 300, // 5 minutes
        'slow_query_threshold' => 1000, // Log queries over 1 second
        'index_hints' => true,
    ],
    
    // Backup settings (for manual backups)
    'backup' => [
        'path' => dirname(__DIR__) . '/storage/backups',
        'compress' => true,
        'chunk_size' => 1000, // Process 1000 rows at a time
    ],
];