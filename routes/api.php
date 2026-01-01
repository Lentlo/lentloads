<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\Admin\AdminListingController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Admin\AdminCategoryController;
use App\Http\Controllers\Api\Admin\AdminReportController;
use App\Http\Controllers\Api\Admin\AdminSettingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('v1')->group(function () {

    // Auth routes - with rate limiting to prevent brute force attacks
    Route::prefix('auth')->middleware('throttle:5,1')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1');
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
        Route::get('verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
    });

    // Categories
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/featured', [CategoryController::class, 'featured']);
        Route::get('/tree', [CategoryController::class, 'tree']);
        Route::get('/search', [CategoryController::class, 'search']);
        Route::get('/{slug}', [CategoryController::class, 'show']);
        Route::get('/{id}/fields', [CategoryController::class, 'customFields']);
    });

    // Listings
    Route::prefix('listings')->group(function () {
        Route::get('/', [ListingController::class, 'index']);
        Route::get('/{slug}', [ListingController::class, 'show']);
        Route::get('/user/{userId}', [ListingController::class, 'userListings']);
    });

    // Search
    Route::prefix('search')->group(function () {
        Route::get('/', [SearchController::class, 'search']);
        Route::get('/suggestions', [SearchController::class, 'suggestions']);
        Route::get('/trending', [SearchController::class, 'trending']);
    });

    // Locations
    Route::prefix('locations')->group(function () {
        Route::get('/countries', [LocationController::class, 'countries']);
        Route::get('/states/{countryId}', [LocationController::class, 'states']);
        Route::get('/cities/{stateId}', [LocationController::class, 'cities']);
        Route::get('/popular-cities', [LocationController::class, 'popularCities']);
        Route::get('/search-cities', [LocationController::class, 'searchCities']);
        Route::get('/cities-with-listings', [LocationController::class, 'citiesWithListings']);
        Route::post('/detect', [LocationController::class, 'detectLocation']);
    });

    // Pages & Content
    Route::get('/home', [PageController::class, 'home']);
    Route::get('/pages', [PageController::class, 'pages']);
    Route::get('/pages/{slug}', [PageController::class, 'page']);
    Route::get('/settings', [PageController::class, 'settings']);
    Route::get('/banners/{position}', [PageController::class, 'banners']);
    Route::post('/banners/{id}/click', [PageController::class, 'trackBannerClick']);
    Route::get('/contact', [PageController::class, 'contact']);

    // Packages
    Route::get('/packages', [PackageController::class, 'index']);

    // Users public profile
    Route::prefix('users')->group(function () {
        Route::get('/{id}', [UserController::class, 'show']);
        Route::get('/{id}/listings', [UserController::class, 'listings']);
        Route::get('/{id}/reviews', [UserController::class, 'reviews']);
    });

    // Report reasons
    Route::get('/report-reasons', [ReportController::class, 'reasons']);

    // Reviews for user
    Route::get('/reviews/user/{userId}', [ReviewController::class, 'index']);
});

// Protected routes
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAll']);
        Route::get('user', [AuthController::class, 'user']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::post('avatar', [AuthController::class, 'updateAvatar']);
        Route::put('password', [AuthController::class, 'changePassword']);
        Route::post('resend-verification', [AuthController::class, 'resendVerification']);
        Route::delete('account', [AuthController::class, 'deleteAccount']);
    });

    // Listings management
    Route::prefix('listings')->group(function () {
        Route::post('/', [ListingController::class, 'store']);
        Route::put('/{id}', [ListingController::class, 'update']);
        Route::delete('/{id}', [ListingController::class, 'destroy']);
        Route::post('/{id}/sold', [ListingController::class, 'markAsSold']);
        Route::post('/{id}/renew', [ListingController::class, 'renew']);
        Route::post('/{id}/images', [ListingController::class, 'addImages']);
        Route::delete('/{id}/images/{imageId}', [ListingController::class, 'deleteImage']);
        Route::put('/{id}/images/{imageId}/primary', [ListingController::class, 'setPrimaryImage']);
    });

    // My listings
    Route::get('/my-listings', [ListingController::class, 'myListings']);

    // Favorites
    Route::prefix('favorites')->group(function () {
        Route::get('/', [FavoriteController::class, 'index']);
        Route::post('/toggle/{listingId}', [FavoriteController::class, 'toggle']);
        Route::get('/check/{listingId}', [FavoriteController::class, 'check']);
        Route::post('/bulk-check', [FavoriteController::class, 'bulkCheck']);
        Route::delete('/clear', [FavoriteController::class, 'clear']);
    });

    // Conversations & Messages
    Route::prefix('conversations')->group(function () {
        Route::get('/', [ConversationController::class, 'index']);
        Route::post('/', [ConversationController::class, 'store']);
        Route::get('/unread-count', [ConversationController::class, 'unreadCount']);
        Route::get('/{uuid}', [ConversationController::class, 'show']);
        Route::post('/{uuid}/messages', [ConversationController::class, 'sendMessage']);
        Route::delete('/{uuid}', [ConversationController::class, 'delete']);
        Route::post('/{uuid}/block', [ConversationController::class, 'block']);
        Route::post('/{uuid}/unblock', [ConversationController::class, 'unblock']);
    });

    Route::post('/messages/{messageId}/offer-response', [ConversationController::class, 'respondToOffer']);

    // Reviews
    Route::prefix('reviews')->group(function () {
        Route::post('/', [ReviewController::class, 'store']);
        Route::post('/{id}/respond', [ReviewController::class, 'respond']);
        Route::get('/my', [ReviewController::class, 'myReviews']);
        Route::delete('/{id}', [ReviewController::class, 'delete']);
    });

    // Reports
    Route::post('/reports', [ReportController::class, 'store']);

    // Saved searches
    Route::prefix('saved-searches')->group(function () {
        Route::get('/', [SearchController::class, 'savedSearches']);
        Route::post('/', [SearchController::class, 'saveSearch']);
        Route::put('/{id}', [SearchController::class, 'updateSavedSearch']);
        Route::delete('/{id}', [SearchController::class, 'deleteSavedSearch']);
        Route::get('/{id}/run', [SearchController::class, 'runSavedSearch']);
    });

    // Packages & Transactions
    Route::post('/packages/purchase', [PackageController::class, 'purchase']);
    Route::get('/my-packages', [PackageController::class, 'myPackages']);
    Route::get('/my-packages/active', [PackageController::class, 'activePackages']);

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'delete']);
        Route::delete('/clear', [NotificationController::class, 'clear']);
        Route::post('/push/subscribe', [NotificationController::class, 'subscribePush']);
        Route::post('/push/unsubscribe', [NotificationController::class, 'unsubscribePush']);
        Route::put('/preferences', [NotificationController::class, 'updatePreferences']);
    });

    // User actions
    Route::post('/users/{id}/follow', [UserController::class, 'follow']);
    Route::delete('/users/{id}/follow', [UserController::class, 'unfollow']);
    Route::post('/users/{id}/block', [UserController::class, 'block']);
});

// Admin routes
Route::prefix('v1/admin')->middleware(['auth:sanctum', 'admin'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);
    Route::get('/stats', [AdminDashboardController::class, 'stats']);

    // Users management
    Route::prefix('users')->group(function () {
        Route::get('/', [AdminUserController::class, 'index']);
        Route::get('/{id}', [AdminUserController::class, 'show']);
        Route::put('/{id}', [AdminUserController::class, 'update']);
        Route::post('/{id}/suspend', [AdminUserController::class, 'suspend']);
        Route::post('/{id}/activate', [AdminUserController::class, 'activate']);
        Route::post('/{id}/verify-seller', [AdminUserController::class, 'verifySeller']);
        Route::delete('/{id}', [AdminUserController::class, 'destroy']);
    });

    // Listings management
    Route::prefix('listings')->group(function () {
        Route::get('/', [AdminListingController::class, 'index']);
        Route::get('/pending', [AdminListingController::class, 'pending']);
        Route::post('/bulk-approve', [AdminListingController::class, 'bulkApprove']);
        Route::post('/bulk-delete', [AdminListingController::class, 'bulkDelete']);
        Route::get('/{id}', [AdminListingController::class, 'show']);
        Route::put('/{id}', [AdminListingController::class, 'update']);
        Route::post('/{id}/approve', [AdminListingController::class, 'approve']);
        Route::post('/{id}/reject', [AdminListingController::class, 'reject']);
        Route::post('/{id}/feature', [AdminListingController::class, 'feature']);
        Route::post('/{id}/toggle-feature', [AdminListingController::class, 'toggleFeature']);
        Route::delete('/{id}', [AdminListingController::class, 'destroy']);
    });

    // Categories management
    Route::prefix('categories')->group(function () {
        Route::get('/', [AdminCategoryController::class, 'index']);
        Route::post('/', [AdminCategoryController::class, 'store']);
        Route::put('/{id}', [AdminCategoryController::class, 'update']);
        Route::delete('/{id}', [AdminCategoryController::class, 'destroy']);
        Route::post('/reorder', [AdminCategoryController::class, 'reorder']);
    });

    // Reports management
    Route::prefix('reports')->group(function () {
        Route::get('/', [AdminReportController::class, 'index']);
        Route::get('/{id}', [AdminReportController::class, 'show']);
        Route::post('/{id}/resolve', [AdminReportController::class, 'resolve']);
        Route::post('/{id}/dismiss', [AdminReportController::class, 'dismiss']);
    });

    // Settings management
    Route::prefix('settings')->group(function () {
        Route::get('/', [AdminSettingController::class, 'index']);
        Route::put('/', [AdminSettingController::class, 'update']);
        Route::get('/pages', [AdminSettingController::class, 'pages']);
        Route::post('/pages', [AdminSettingController::class, 'createPage']);
        Route::put('/pages/{id}', [AdminSettingController::class, 'updatePage']);
        Route::delete('/pages/{id}', [AdminSettingController::class, 'deletePage']);
        Route::get('/banners', [AdminSettingController::class, 'banners']);
        Route::post('/banners', [AdminSettingController::class, 'createBanner']);
        Route::put('/banners/{id}', [AdminSettingController::class, 'updateBanner']);
        Route::delete('/banners/{id}', [AdminSettingController::class, 'deleteBanner']);
    });
});
