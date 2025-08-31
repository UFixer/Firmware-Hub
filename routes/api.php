<?php
/**
 * API Routes
 * Lightweight API routing for shared hosting
 */

use App\Core\Router;

// Get router instance
$router = $router ?? new Router();

// API routes with versioning
$router->group(['prefix' => 'api/v1', 'middleware' => 'api'], function($router) {
    
    // Public endpoints
    $router->get('/products', 'Api\ProductController@index');
    $router->get('/products/{id}', 'Api\ProductController@show');
    $router->get('/categories', 'Api\CategoryController@index');
    $router->get('/brands', 'Api\BrandController@index');
    $router->post('/search', 'Api\SearchController@search');
    
    // Authentication endpoints
    $router->post('/auth/login', 'Api\AuthController@login');
    $router->post('/auth/register', 'Api\AuthController@register');
    $router->post('/auth/forgot-password', 'Api\AuthController@forgotPassword');
    $router->post('/auth/reset-password', 'Api\AuthController@resetPassword');
    
    // Protected endpoints (require API token)
    $router->group(['middleware' => 'api.auth'], function($router) {
        // User account
        $router->get('/user', 'Api\UserController@profile');
        $router->put('/user', 'Api\UserController@update');
        $router->post('/user/change-password', 'Api\UserController@changePassword');
        $router->post('/auth/logout', 'Api\AuthController@logout');
        
        // Orders
        $router->get('/orders', 'Api\OrderController@index');
        $router->get('/orders/{id}', 'Api\OrderController@show');
        $router->post('/orders', 'Api\OrderController@create');
        $router->get('/orders/{id}/download', 'Api\OrderController@download');
        
        // Cart
        $router->get('/cart', 'Api\CartController@index');
        $router->post('/cart/add', 'Api\CartController@add');
        $router->put('/cart/update', 'Api\CartController@update');
        $router->delete('/cart/remove/{id}', 'Api\CartController@remove');
        $router->delete('/cart/clear', 'Api\CartController@clear');
        
        // Wishlist
        $router->get('/wishlist', 'Api\WishlistController@index');
        $router->post('/wishlist/add', 'Api\WishlistController@add');
        $router->delete('/wishlist/remove/{id}', 'Api\WishlistController@remove');
        
        // Addresses
        $router->get('/addresses', 'Api\AddressController@index');
        $router->post('/addresses', 'Api\AddressController@store');
        $router->put('/addresses/{id}', 'Api\AddressController@update');
        $router->delete('/addresses/{id}', 'Api\AddressController@delete');
    });
    
    // Webhook endpoints (no auth but verified by signature)
    $router->post('/webhooks/stripe', 'Api\WebhookController@stripe');
    $router->post('/webhooks/paypal', 'Api\WebhookController@paypal');
    
    // Health check endpoint
    $router->get('/health', function() {
        return [
            'status' => 'healthy',
            'timestamp' => time(),
            'version' => '1.0.0'
        ];
    });
    
    // Rate limit test endpoint
    $router->get('/rate-limit-test', 'Api\TestController@rateLimit');
});

// API documentation (optional)
$router->get('/api/docs', function() {
    return view('api.documentation');
});

return $router;