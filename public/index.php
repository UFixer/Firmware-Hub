<?php
/**
 * FirmwareHub - Mobile Firmware E-commerce Platform
 * Entry point optimized for shared hosting
 * 
 * @package FirmwareHub
 * @version 1.0.0
 */

// Set memory and time limits for shared hosting
ini_set('memory_limit', '256M');
ini_set('max_execution_time', '25');
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

// Define base paths
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('PUBLIC_PATH', __DIR__);

// Check PHP version
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die('FirmwareHub requires PHP 7.4 or higher. Current version: ' . PHP_VERSION);
}

// Load Composer autoloader
if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    require ROOT_PATH . '/vendor/autoload.php';
} else {
    die('Please run "composer install" to install dependencies.');
}

// Load environment variables
try {
    $dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
    $dotenv->load();
} catch (Exception $e) {
    // .env file not found, use defaults
}

// Initialize application
session_start();

// Simple routing system for shared hosting
$request_uri = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// Remove query string and decode URL
$path = parse_url($request_uri, PHP_URL_PATH);
$path = urldecode($path);

// Remove base directory if in subdirectory
$base_path = dirname($_SERVER['SCRIPT_NAME']);
if ($base_path !== '/') {
    $path = str_replace($base_path, '', $path);
}

// Clean path
$path = '/' . trim($path, '/');

// Initialize router
$router = new App\Core\Router();

// Load route definitions
require ROOT_PATH . '/routes/web.php';
require ROOT_PATH . '/routes/api.php';

// Dispatch request
try {
    $response = $router->dispatch($request_method, $path);
    
    // Send response
    if (is_array($response)) {
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        echo $response;
    }
} catch (Exception $e) {
    // Error handling
    if (env('APP_DEBUG', false)) {
        echo '<pre>Error: ' . $e->getMessage() . '</pre>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
    } else {
        // Production error page
        http_response_code(500);
        include ROOT_PATH . '/resources/views/errors/500.php';
    }
}

// Clean up and log execution time
if (env('APP_DEBUG', false)) {
    $execution_time = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
    error_log("Execution time: " . round($execution_time, 4) . " seconds");
}