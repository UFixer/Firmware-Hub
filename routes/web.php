<?php
/**
 * Web Routes
 * Lightweight routing for shared hosting
 */

use App\Core\Router;

// Get router instance
$router = $router ?? new Router();

// Homepage
$router->get('/', 'HomeController@index');

// Product routes
$router->get('/products', 'ProductController@index');
$router->get('/products/category/{slug}', 'ProductController@category');
$router->get('/products/brand/{slug}', 'ProductController@brand');
$router->get('/product/{slug}', 'ProductController@show');
$router->get('/products/search', 'ProductController@search');
$router->get('/products/filter', 'ProductController@filter');

// Cart routes
$router->get('/cart', 'CartController@index');
$router->post('/cart/add', 'CartController@add');
$router->post('/cart/update', 'CartController@update');
$router->post('/cart/remove', 'CartController@remove');
$router->get('/cart/count', 'CartController@count');
$router->post('/cart/clear', 'CartController@clear');

// Checkout routes
$router->get('/checkout', 'CheckoutController@index');
$router->post('/checkout/process', 'CheckoutController@process');
$router->get('/checkout/success', 'CheckoutController@success');
$router->get('/checkout/cancel', 'CheckoutController@cancel');
$router->post('/checkout/calculate-shipping', 'CheckoutController@calculateShipping');
$router->post('/checkout/apply-coupon', 'CheckoutController@applyCoupon');

// Payment routes
$router->post('/payment/stripe/process', 'PaymentController@stripeProcess');
$router->post('/payment/stripe/webhook', 'PaymentController@stripeWebhook');
$router->post('/payment/paypal/process', 'PaymentController@paypalProcess');
$router->post('/payment/paypal/callback', 'PaymentController@paypalCallback');

// User authentication routes
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->post('/logout', 'AuthController@logout');
$router->get('/forgot-password', 'AuthController@showForgotPassword');
$router->post('/forgot-password', 'AuthController@sendResetLink');
$router->get('/reset-password/{token}', 'AuthController@showResetPassword');
$router->post('/reset-password', 'AuthController@resetPassword');
$router->get('/verify-email/{token}', 'AuthController@verifyEmail');

// User account routes (protected)
$router->group(['middleware' => 'auth'], function($router) {
    // Dashboard
    $router->get('/account', 'AccountController@dashboard');
    $router->get('/account/orders', 'AccountController@orders');
    $router->get('/account/order/{id}', 'AccountController@orderDetail');
    $router->get('/account/downloads', 'AccountController@downloads');
    $router->get('/account/download/{id}', 'AccountController@download');
    
    // Profile
    $router->get('/account/profile', 'AccountController@profile');
    $router->post('/account/profile', 'AccountController@updateProfile');
    $router->post('/account/change-password', 'AccountController@changePassword');
    
    // Wishlist
    $router->get('/account/wishlist', 'WishlistController@index');
    $router->post('/wishlist/add', 'WishlistController@add');
    $router->post('/wishlist/remove', 'WishlistController@remove');
    
    // Address book
    $router->get('/account/addresses', 'AddressController@index');
    $router->get('/account/address/create', 'AddressController@create');
    $router->post('/account/address/store', 'AddressController@store');
    $router->get('/account/address/{id}/edit', 'AddressController@edit');
    $router->post('/account/address/{id}/update', 'AddressController@update');
    $router->post('/account/address/{id}/delete', 'AddressController@delete');
});

// Admin routes (protected)
$router->group(['prefix' => 'admin', 'middleware' => 'admin'], function($router) {
    // Dashboard
    $router->get('/', 'Admin\DashboardController@index');
    
    // Products
    $router->get('/products', 'Admin\ProductController@index');
    $router->get('/products/create', 'Admin\ProductController@create');
    $router->post('/products/store', 'Admin\ProductController@store');
    $router->get('/products/{id}/edit', 'Admin\ProductController@edit');
    $router->post('/products/{id}/update', 'Admin\ProductController@update');
    $router->post('/products/{id}/delete', 'Admin\ProductController@delete');
    
    // Categories
    $router->get('/categories', 'Admin\CategoryController@index');
    $router->post('/categories/store', 'Admin\CategoryController@store');
    $router->post('/categories/{id}/update', 'Admin\CategoryController@update');
    $router->post('/categories/{id}/delete', 'Admin\CategoryController@delete');
    
    // Orders
    $router->get('/orders', 'Admin\OrderController@index');
    $router->get('/orders/{id}', 'Admin\OrderController@show');
    $router->post('/orders/{id}/status', 'Admin\OrderController@updateStatus');
    
    // Users
    $router->get('/users', 'Admin\UserController@index');
    $router->get('/users/{id}', 'Admin\UserController@show');
    $router->post('/users/{id}/status', 'Admin\UserController@updateStatus');
    
    // Settings
    $router->get('/settings', 'Admin\SettingsController@index');
    $router->post('/settings/update', 'Admin\SettingsController@update');
});

// Static pages
$router->get('/about', 'PageController@about');
$router->get('/contact', 'PageController@contact');
$router->post('/contact', 'PageController@sendContact');
$router->get('/faq', 'PageController@faq');
$router->get('/terms', 'PageController@terms');
$router->get('/privacy', 'PageController@privacy');
$router->get('/refund-policy', 'PageController@refundPolicy');

// Sitemap and robots
$router->get('/sitemap.xml', 'SitemapController@index');
$router->get('/robots.txt', 'RobotsController@index');

// Error pages
$router->get('/404', 'ErrorController@notFound');
$router->get('/500', 'ErrorController@serverError');
$router->get('/maintenance', 'ErrorController@maintenance');

// Catch-all route (must be last)
$router->any('/{any}', 'ErrorController@notFound')->where('any', '.*');

return $router;