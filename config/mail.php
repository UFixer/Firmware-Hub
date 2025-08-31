<?php
/**
 * Mail Configuration
 * SMTP configuration for shared hosting environments
 */

return [
    // Default mailer
    'default' => env('MAIL_MAILER', 'smtp'),
    
    // Mailer configurations
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.gmail.com'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => 10,  // 10 second timeout for shared hosting
            'auth_mode' => null,
            'verify_peer' => false,  // May need to be false on some shared hosts
            'verify_peer_name' => false,
        ],
        
        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs'),
        ],
        
        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL', 'mail'),
        ],
        
        'array' => [
            'transport' => 'array',
        ],
        
        'failover' => [
            'transport' => 'failover',
            'mailers' => ['smtp', 'sendmail'],
        ],
    ],
    
    // Global "From" address
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@firmwarehub.com'),
        'name' => env('MAIL_FROM_NAME', 'FirmwareHub'),
    ],
    
    // Global "Reply To" address
    'reply_to' => [
        'address' => env('MAIL_REPLY_TO_ADDRESS', null),
        'name' => env('MAIL_REPLY_TO_NAME', null),
    ],
    
    // Email templates configuration
    'templates' => [
        'paths' => [
            dirname(__DIR__) . '/resources/views/emails',
        ],
        'options' => [
            'cache_path' => dirname(__DIR__) . '/storage/framework/views',
        ],
    ],
    
    // Email types and their settings
    'types' => [
        'order_confirmation' => [
            'subject' => 'Order Confirmation - #{order_id}',
            'template' => 'emails.order-confirmation',
            'priority' => 'high',
        ],
        'payment_received' => [
            'subject' => 'Payment Received - Thank You!',
            'template' => 'emails.payment-received',
            'priority' => 'high',
        ],
        'download_link' => [
            'subject' => 'Your Firmware Download Link',
            'template' => 'emails.download-link',
            'priority' => 'high',
        ],
        'password_reset' => [
            'subject' => 'Password Reset Request',
            'template' => 'emails.password-reset',
            'priority' => 'high',
        ],
        'account_activation' => [
            'subject' => 'Activate Your Account',
            'template' => 'emails.account-activation',
            'priority' => 'normal',
        ],
        'newsletter' => [
            'subject' => 'FirmwareHub Newsletter',
            'template' => 'emails.newsletter',
            'priority' => 'low',
        ],
    ],
    
    // Queue settings for emails (using database queue)
    'queue' => [
        'enabled' => env('MAIL_QUEUE_ENABLED', true),
        'connection' => 'database',
        'queue' => 'emails',
        'delay' => 0,
        'tries' => 3,
        'timeout' => 30,
    ],
    
    // Rate limiting for shared hosting
    'rate_limit' => [
        'enabled' => true,
        'per_minute' => 30,        // Max 30 emails per minute
        'per_hour' => 500,         // Max 500 emails per hour
        'per_day' => 5000,         // Max 5000 emails per day
        'burst' => 10,             // Allow burst of 10 emails
    ],
    
    // Retry configuration
    'retry' => [
        'times' => 3,              // Retry 3 times
        'delay' => 300,            // Wait 5 minutes between retries
        'multiplier' => 2,         // Double delay each time
        'max_delay' => 3600,       // Max 1 hour delay
    ],
    
    // Email validation settings
    'validation' => [
        'dns_check' => false,      // Skip DNS check on shared hosting
        'disposable_check' => true,
        'max_size' => 10485760,    // 10MB max email size
    ],
    
    // Logging settings
    'log' => [
        'channel' => 'mail',
        'level' => 'info',
        'path' => dirname(__DIR__) . '/storage/logs/mail.log',
    ],
    
    // Development/testing settings
    'testing' => [
        'override_recipient' => env('MAIL_TEST_RECIPIENT'),
        'add_test_header' => env('APP_ENV') !== 'production',
    ],
];