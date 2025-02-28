<?php

use App\Http\Controllers\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\CarTypeController;
use App\Http\Controllers\CarBrandController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\FavController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\VinDecoderController;
use App\Http\Controllers\MobileController;

// Public Routes
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/forgot-password', [UserController::class, 'forgotPassword']);
Route::post('/api/login', [LoginController::class, 'login']);
Route::get('/decode-vin/{vin}', [VinDecoderController::class, 'decodeVin']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::put('/profile/{id}', [UserController::class, 'editProfile']);

    Route::post('/mobile/check', [MobileController::class, 'checkMobile']);

    Route::post('/cars-list', [CarController::class, 'index']);
    Route::get('/cars', [CarController::class, 'index']);
    Route::post('/cars', [CarController::class, 'store']);
    Route::get('/cars/{id}', [CarController::class, 'show']);
    Route::put('/cars/{id}', [CarController::class, 'update']);
    Route::delete('/cars/{id}', [CarController::class, 'destroy']);
    Route::post('/car/info', [CarController::class, 'info']);
    Route::post('/car/brandwise', [CarController::class, 'brandWise']);
    Route::post('/car/typewise', [CarController::class, 'typeWise']);

    Route::get('/carType', [CarTypeController::class, 'index']);
    Route::get('/carType/{id}', [CarTypeController::class, 'show']);

    Route::get('/carBrand', [CarBrandController::class, 'index']);
    Route::get('/carBrand/{id}', [CarBrandController::class, 'show']);

    Route::get('/gallery', [GalleryController::class, 'index']);
    Route::post('/gallery', [GalleryController::class, 'store']);
    Route::put('/gallery/{id}', [GalleryController::class, 'update']);

    Route::post('/home', [HomeController::class, 'get']);

    Route::get('/city', [CityController::class, 'index']);
    Route::get('/city/{id}', [CityController::class, 'show']);

    Route::get('/pagelist', [PageController::class, 'index']);
    Route::get('/faq', [FaqController::class, 'index']);

    Route::post('/fav-car', [FavController::class, 'index']);
    Route::post('/fav', [FavController::class, 'update']);

    Route::post('/booking/now', [BookingController::class, 'bookNow']);
    Route::post('/booking/range', [BookingController::class, 'bookRange']);
    Route::post('/booking/details', [BookingController::class, 'bookDetails']);
    Route::post('/booking/history', [BookingController::class, 'bookHistory']);
    Route::post('/booking/myHistory', [BookingController::class, 'myBookHistory']);
    Route::post('/booking/myDetails', [BookingController::class, 'myBookDetails']);
    Route::post('/booking/complete', [BookingController::class, 'update']);
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::delete('/bookings/{id}', [BookingController::class, 'destroy']);

    Route::get('/payments/gateway', [PaymentController::class, 'gateway']);
    Route::post('/payments', [PaymentController::class, 'processPayment']);

    Route::post('/walletUp', [WalletController::class, 'walletUp']);
    Route::post('/walletReport', [WalletController::class, 'walletReport']);

    Route::post('/logout', [UserController::class, 'logout']);
});
