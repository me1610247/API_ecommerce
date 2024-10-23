<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\AdminMiddleware;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Authenticated routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('categories', CategoryController::class);
    Route::post('/logout', [AuthController::class, 'logout']);
    // search api must be before products api to be able to search
    Route::get('/products/search', [ProductController::class, 'search']);

    // Products Route
    Route::apiResource('products', ProductController::class);

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'profile']);
    Route::put('/profile', [ProfileController::class, 'updateProfile']);
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword']);

    //Cart Routes
    Route::post('/cart', [CartController::class, 'addToCart']);           // Add item to cart
    Route::get('/cart', [CartController::class, 'viewCart']);             // View cart
    Route::put('/cart/{cartId}', [CartController::class, 'updateCart']);  // Update item in cart
    Route::delete('/cart/{cartId}', [CartController::class, 'removeFromCart']);  // Remove item from cart

    //WishList Routes
    Route::post('/wishlist', [WishlistController::class, 'addToWishlist']);   // Add item to wishlist
    Route::get('/wishlist', [WishlistController::class, 'viewWishlist']);     // View wishlist
    Route::delete('/wishlist/{id}', [WishlistController::class, 'removeFromWishlist']); // Remove item from wishlist

    // Order Routes
    Route::post('/order', [OrderController::class, 'createOrder']); // Create Order
    Route::get('/orders', [OrderController::class, 'getUserOrders']); // Get User Orders
    Route::get('/order/{id}', [OrderController::class, 'getOrder']); // Get Specific Order

    //Review Route
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/products/{productId}/reviews', [ReviewController::class, 'index']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);

    // Users Route (for Authorized Admin only)
    Route::middleware([AdminMiddleware::class])->group(function () {
        Route::get('/users', [UserController::class, 'index']); // Show all users
        Route::post('/users', [UserController::class, 'store']); // Add a new user
        Route::delete('/users/{id}', [UserController::class, 'destroy']); // Delete a user
    });

});
