<?php

use Illuminate\Support\Facades\Route;

// Admin Controllers
use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Api\Admin\SettingsController;
use App\Http\Controllers\Api\Admin\ActivityLogController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Admin\ReportsController;
use App\Http\Controllers\Api\Admin\CMSController;
use App\Http\Controllers\Api\Admin\PromotionController;

// Client Controllers
use App\Http\Controllers\Api\Client\AuthController as ClientAuthController;
use App\Http\Controllers\Api\Client\HomeController;
use App\Http\Controllers\Api\Client\ProductController as ClientProductController;
use App\Http\Controllers\Api\Client\CartController;
use App\Http\Controllers\Api\Client\OrderController as ClientOrderController;
use App\Http\Controllers\Api\Client\WishlistController;
use App\Http\Controllers\Api\Client\PromotionController as ClientPromotionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// =====================
// PUBLIC CLIENT ROUTES
// =====================

// Home & Site Data
Route::get('/home', [HomeController::class, 'index']);
Route::get('/settings', [HomeController::class, 'settings']);
Route::get('/categories', [HomeController::class, 'categories']);

// Products (Public)
Route::get('/products', [ClientProductController::class, 'index']);
Route::get('/products/{slug}', [ClientProductController::class, 'show']);
Route::get('/categories/{slug}', [ClientProductController::class, 'category']);

// Checkout Data (Public)
Route::get('/checkout/shipping-zones', [ClientOrderController::class, 'shippingZones']);
Route::get('/checkout/payment-methods', [ClientOrderController::class, 'paymentMethods']);

// Promotions (Public - visible promotions only)
Route::get('/promotions', [ClientPromotionController::class, 'index']);

// Cart (Session-based, works for guests)
Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index']);
    Route::post('/items', [CartController::class, 'addItem']);
    Route::put('/items/{cartItem}', [CartController::class, 'updateItem']);
    Route::delete('/items/{cartItem}', [CartController::class, 'removeItem']);
    Route::delete('/', [CartController::class, 'clear']);
    Route::post('/coupon', [CartController::class, 'applyCoupon']);
    Route::delete('/coupon', [CartController::class, 'removeCoupon']);
});

// =====================
// CLIENT AUTH ROUTES
// =====================

Route::prefix('auth')->group(function () {
    Route::post('/register', [ClientAuthController::class, 'register']);
    Route::post('/login', [ClientAuthController::class, 'login']);
    Route::post('/refresh', [ClientAuthController::class, 'refresh']); // Public refresh endpoint
    
    // Google OAuth routes need session middleware for state storage
    Route::middleware('web')->group(function () {
        Route::get('/google', [ClientAuthController::class, 'redirectToGoogle']); // Initiate Google OAuth
        Route::get('/google/callback', [ClientAuthController::class, 'handleGoogleCallback']); // Handle Google OAuth callback
    });
});

// Protected Client Routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::prefix('auth')->group(function () {
        Route::get('/me', [ClientAuthController::class, 'me']);
        Route::post('/logout', [ClientAuthController::class, 'logout']);
        Route::put('/profile', [ClientAuthController::class, 'updateProfile']);
        Route::put('/password', [ClientAuthController::class, 'changePassword']);
        Route::get('/account-stats', [ClientAuthController::class, 'getAccountStats']);
    });

    // Addresses
    Route::prefix('addresses')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\Client\UserAddressController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\Client\UserAddressController::class, 'store']);
        Route::put('/{userAddress}', [\App\Http\Controllers\Api\Client\UserAddressController::class, 'update']);
        Route::delete('/{userAddress}', [\App\Http\Controllers\Api\Client\UserAddressController::class, 'destroy']);
        Route::post('/{userAddress}/default-shipping', [\App\Http\Controllers\Api\Client\UserAddressController::class, 'setDefaultShipping']);
        Route::post('/{userAddress}/default-billing', [\App\Http\Controllers\Api\Client\UserAddressController::class, 'setDefaultBilling']);
    });

    // Orders
    Route::prefix('orders')->group(function () {
        Route::get('/', [ClientOrderController::class, 'index']);
        Route::post('/', [ClientOrderController::class, 'store']);
        Route::get('/{orderNumber}', [ClientOrderController::class, 'show']);
        Route::post('/{orderNumber}/payment', [ClientOrderController::class, 'submitPayment']);
        Route::post('/{orderNumber}/cancel', [ClientOrderController::class, 'cancel']);
    });

    // Wishlist
    Route::prefix('wishlist')->group(function () {
        Route::get('/', [WishlistController::class, 'index']);
        Route::post('/', [WishlistController::class, 'store']);
        Route::delete('/{productId}', [WishlistController::class, 'destroy']);
        Route::get('/check/{productId}', [WishlistController::class, 'check']);
        Route::post('/{productId}/move-to-cart', [WishlistController::class, 'moveToCart']);
    });
});

// =====================
// ADMIN AUTH ROUTES
// =====================

Route::prefix('admin')->group(function () {
    // Public Admin Routes
    Route::post('/auth/login', [AdminAuthController::class, 'login']);
    Route::post('/auth/refresh', [AdminAuthController::class, 'refresh']); // Public refresh endpoint

    // Protected Admin Routes
    Route::middleware(['auth:sanctum', 'admin.only'])->group(function () {
        // Auth
        Route::prefix('auth')->group(function () {
            Route::get('/me', [AdminAuthController::class, 'me']);
            Route::post('/logout', [AdminAuthController::class, 'logout']);
            Route::put('/profile', [AdminAuthController::class, 'updateProfile']);
            Route::put('/password', [AdminAuthController::class, 'changePassword']);
        });

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/quick-stats', [DashboardController::class, 'quickStats']);

        // Reports & Analytics
        Route::prefix('reports')->group(function () {
            Route::get('/summary', [ReportsController::class, 'summary']);
            Route::get('/sales', [ReportsController::class, 'sales']);
            Route::get('/orders', [ReportsController::class, 'orders']);
            Route::get('/products', [ReportsController::class, 'products']);
            Route::get('/users', [ReportsController::class, 'users']);
        });

        // Products
        Route::prefix('products')->group(function () {
            Route::get('/', [AdminProductController::class, 'index']);
            Route::post('/', [AdminProductController::class, 'store']);
            Route::get('/{product}', [AdminProductController::class, 'show']);
            Route::put('/{product}', [AdminProductController::class, 'update']);
            Route::delete('/{product}', [AdminProductController::class, 'destroy']);
            Route::post('/bulk', [AdminProductController::class, 'bulkUpdate']);
            Route::put('/{product}/stock', [AdminProductController::class, 'updateStock']);
            Route::get('/{product}/stock-history', [AdminProductController::class, 'getStockHistory']);
        });

        // Categories
        Route::prefix('categories')->group(function () {
            Route::get('/', [AdminCategoryController::class, 'index']);
            Route::post('/', [AdminCategoryController::class, 'store']);
            Route::get('/{category}', [AdminCategoryController::class, 'show']);
            Route::put('/{category}', [AdminCategoryController::class, 'update']);
            Route::delete('/{category}', [AdminCategoryController::class, 'destroy']);
            Route::post('/reorder', [AdminCategoryController::class, 'reorder']);
        });

        // Orders
        Route::prefix('orders')->group(function () {
            Route::get('/', [AdminOrderController::class, 'index']);
            Route::get('/statistics', [AdminOrderController::class, 'statistics']);
            Route::get('/{order}', [AdminOrderController::class, 'show']);
            Route::put('/{order}/status', [AdminOrderController::class, 'updateStatus']);
            Route::put('/{order}/shipping', [AdminOrderController::class, 'updateShipping']);
            Route::post('/{order}/notes', [AdminOrderController::class, 'addNote']);
            Route::post('/{order}/cancel', [AdminOrderController::class, 'cancel']);
        });

        // Users
        Route::prefix('users')->group(function () {
            Route::get('/', [AdminUserController::class, 'index']);
            Route::get('/{user}', [AdminUserController::class, 'show']);
            Route::put('/{user}', [AdminUserController::class, 'update']);
            Route::delete('/{user}', [AdminUserController::class, 'destroy']);
            Route::post('/{user}/ban', [AdminUserController::class, 'ban']);
            Route::post('/{user}/unban', [AdminUserController::class, 'unban']);
            Route::get('/{user}/orders', [AdminUserController::class, 'orders']);
        });

        // Payments
        Route::prefix('payments')->group(function () {
            Route::get('/', [AdminPaymentController::class, 'index']);
            Route::get('/statistics', [AdminPaymentController::class, 'statistics']);
            Route::get('/{payment}', [AdminPaymentController::class, 'show']);
            Route::post('/{payment}/verify', [AdminPaymentController::class, 'verify']);
            Route::post('/{payment}/reject', [AdminPaymentController::class, 'reject']);
        });

        // Settings
        Route::prefix('settings')->group(function () {
            Route::get('/', [SettingsController::class, 'index']);
            Route::put('/', [SettingsController::class, 'update']);
            Route::get('/payment-methods', [SettingsController::class, 'paymentMethods']);
            Route::put('/payment-methods/{paymentMethod}', [SettingsController::class, 'updatePaymentMethod']);
            Route::get('/shipping-zones', [SettingsController::class, 'shippingZones']);
            Route::put('/shipping-zones/{shippingZone}', [SettingsController::class, 'updateShippingZone']);
            Route::get('/couriers', [SettingsController::class, 'couriers']);
            Route::put('/couriers/{courier}', [SettingsController::class, 'updateCourier']);
        });

        // Shipping Zones (dedicated controller)
        Route::prefix('shipping')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Admin\ShippingController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\Api\Admin\ShippingController::class, 'store']);
            Route::get('/{shippingZone}', [\App\Http\Controllers\Api\Admin\ShippingController::class, 'show']);
            Route::put('/{shippingZone}', [\App\Http\Controllers\Api\Admin\ShippingController::class, 'update']);
            Route::delete('/{shippingZone}', [\App\Http\Controllers\Api\Admin\ShippingController::class, 'destroy']);
        });

        // File Uploads
        Route::prefix('upload')->group(function () {
            Route::post('/image', [\App\Http\Controllers\Api\Admin\FileUploadController::class, 'uploadImage']);
            Route::delete('/file', [\App\Http\Controllers\Api\Admin\FileUploadController::class, 'deleteFile']);
        });

        // Activity Logs
        Route::prefix('activity-logs')->group(function () {
            Route::get('/', [ActivityLogController::class, 'index']);
            Route::get('/statistics', [ActivityLogController::class, 'statistics']);
            Route::get('/{activityLog}', [ActivityLogController::class, 'show']);
        });

        // Admins (Super Admin only for create/update/delete)
        Route::prefix('admins')->group(function () {
            Route::get('/', [AdminController::class, 'index']);
            Route::get('/roles', [AdminController::class, 'roles']);
            Route::get('/{admin}', [AdminController::class, 'show']);
            Route::post('/', [AdminController::class, 'store']); // Super Admin only
            Route::put('/{admin}', [AdminController::class, 'update']); // Super Admin only (or self for profile)
            Route::delete('/{admin}', [AdminController::class, 'destroy']); // Super Admin only
        });

        // CMS (Content Management System)
        Route::prefix('cms')->group(function () {
            // Homepage Sections
            Route::get('/sections', [CMSController::class, 'getSections']);
            Route::get('/sections/{section}', [CMSController::class, 'getSection']);
            Route::post('/sections', [CMSController::class, 'saveSection']);
            Route::put('/sections/{section}', [CMSController::class, 'saveSection']);
            Route::delete('/sections/{section}', [CMSController::class, 'deleteSection']);

            // Banners
            Route::get('/banners', [CMSController::class, 'getBanners']);
            Route::get('/banners/{banner}', [CMSController::class, 'getBanner']);
            Route::post('/banners', [CMSController::class, 'saveBanner']);
            Route::put('/banners/{banner}', [CMSController::class, 'saveBanner']);
            Route::delete('/banners/{banner}', [CMSController::class, 'deleteBanner']);
        });

        // Promotions
        Route::prefix('promotions')->group(function () {
            Route::get('/', [PromotionController::class, 'index']);
            Route::get('/{promotion}', [PromotionController::class, 'show']);
            Route::post('/', [PromotionController::class, 'store']);
            Route::put('/{promotion}', [PromotionController::class, 'update']);
            Route::delete('/{promotion}', [PromotionController::class, 'destroy']);
            Route::post('/{promotion}/toggle', [PromotionController::class, 'toggle']);
        });
    });
});
