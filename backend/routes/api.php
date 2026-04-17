<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\WholesalerController;
use Illuminate\Support\Facades\Route;

// Auth publique
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// Catalogue public (lecture seule)
Route::get('/products',          [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/promotions',        [PromotionController::class, 'index']);
Route::get('/wholesalers',       [WholesalerController::class, 'index']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // Produits (grossiste)
    Route::post('/products',             [ProductController::class, 'store']);
    Route::put('/products/{product}',    [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);

    // Commandes
    Route::get('/orders',           [OrderController::class, 'index']);
    Route::post('/orders',          [OrderController::class, 'store']);
    Route::get('/orders/{order}',   [OrderController::class, 'show']);
    Route::patch('/orders/{order}', [OrderController::class, 'update']);
    Route::delete('/orders/{order}',[OrderController::class, 'destroy']);

    // Promotions (grossiste)
    Route::post('/promotions',             [PromotionController::class, 'store']);
    Route::delete('/promotions/{promotion}',[PromotionController::class, 'destroy']);

    // Relations grossiste ↔ détaillant
    Route::post('/wholesalers/{wholesaler}/connect',         [WholesalerController::class, 'connect']);
    Route::patch('/retailers/{retailer}/approve',            [WholesalerController::class, 'approveRetailer']);
    Route::get('/my-retailers',                              [WholesalerController::class, 'myRetailers']);
    Route::get('/my-wholesalers',                            [WholesalerController::class, 'myWholesalers']);
});
