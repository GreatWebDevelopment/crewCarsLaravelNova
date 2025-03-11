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
use App\Http\Controllers\CouponController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\Payments\FlutterwaveController;
use App\Http\Controllers\Payments\KhaltiController;
use App\Http\Controllers\Payments\MerpagoController;
use App\Http\Controllers\Payments\MidtransController;
use App\Http\Controllers\Payments\PayfastController;
use App\Http\Controllers\Payments\PaypalController;
use App\Http\Controllers\Payments\PaytmController;

// Public Routes
Route::post('/auth/register', [UserController::class, 'register']);
Route::post('/auth/login', [UserController::class, 'login']);
Route::post('/auth/forgot-password', [UserController::class, 'forgotPassword']);
Route::post('/mobile/check', [MobileController::class, 'checkMobile']);
Route::get('/decode-vin/{vin}', [VinDecoderController::class, 'decodeVin']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::group(['prefix' => 'payment'], function () {
        Route::post('/flutterwave/index', [FlutterwaveController::class, 'index']);
        Route::get('/flutterwave/success', [FlutterwaveController::class, 'success']);

        Route::post('/khalti/index', [KhaltiController::class, 'index']);
        Route::get('/khalti/success', [FlutterwaveController::class, 'success']);

        Route::post('/merpago/index', [MerpagoController::class, 'index']);
        Route::get('/merpago/success', [MerpagoController::class, 'success']);
        Route::get('/merpago/fail', [MerpagoController::class, 'fail']);
        Route::get('/merpago/pending', [MerpagoController::class, 'pending']);

        Route::post('/midtrans/index', [MidtransController::class, 'index']);
        Route::get('/midtrans/success', [MidtransController::class, 'success']);

        Route::post('/payfast/index', [PayfastController::class, 'index']);
        Route::get('/khalti/success', [PayfastController::class, 'success']);
        Route::get('/khalti/cancel', [PayfastController::class, 'cancel']);

        Route::get('/paypal/success', [PaypalController::class, 'success']);
        Route::get('/paypal/cancel', [PaypalController::class, 'cancel']);

        Route::post('/paytm/index', [PaytmController::class, 'index']);
        Route::get('/paytm/success', [PaytmController::class, 'success']);
    });

    Route::get('/user/refer-data', [UserController::class, 'referData']);
    Route::put('/user/{id}', [UserController::class, 'update']);
    Route::delete('/user/{id}', [UserController::class, 'destroy']);
    Route::post('/user/picture', [UserController::class, 'uploadPicture']);

    Route::get('/cars-list', [CarController::class, 'index']);
    Route::post('/cars', [CarController::class, 'store']);
    Route::get('/cars/{id}', [CarController::class, 'show']);
    Route::post('/cars/{id}', [CarController::class, 'update']);
    Route::delete('/cars/{id}', [CarController::class, 'destroy']);
    Route::post('/car/info', [CarController::class, 'info']);
    Route::post('/car/brandwise', [CarController::class, 'brandWise']);
    Route::post('/car/typewise', [CarController::class, 'typeWise']);
    Route::post('/car/citywise', [CarController::class, 'cityWise']);
    Route::get('/car/features', [CarController::class, 'featureList']);
    Route::get('/car/popular', [CarController::class, 'popularList']);

    Route::get('/car-type', [CarTypeController::class, 'index']);
    Route::get('/car-type/{id}', [CarTypeController::class, 'show']);

    Route::get('/car-brand', [CarBrandController::class, 'index']);
    Route::get('/car-brand/{id}', [CarBrandController::class, 'show']);

    Route::get('/gallery', [GalleryController::class, 'index']);
    Route::post('/gallery', [GalleryController::class, 'store']);
    Route::post('/gallery/{id}', [GalleryController::class, 'update']);

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
    Route::post('/booking/my-history', [BookingController::class, 'myBookHistory']);
    Route::post('/booking/my-details', [BookingController::class, 'myBookDetails']);
    Route::post('/booking/complete', [BookingController::class, 'update']);
    Route::get('/booking/rate/{id}', [BookingController::class, 'rateList']);
    Route::put('/booking/rate/{id}', [BookingController::class, 'updateRate']);
    Route::post('/booking/drop', [BookingController::class, 'bookDrop']);
    Route::post('/booking/cancel', [BookingController::class, 'bookCancel']);
    Route::post('/booking/pickup', [BookingController::class, 'pickUp']);
    Route::post('/booking/verify-otp', [BookingController::class, 'verifyOTP']);
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::delete('/bookings/{id}', [BookingController::class, 'destroy']);

    Route::get('/coupon/list', [CouponController::class, 'index']);
    Route::post('/coupon/check', [CouponController::class, 'check']);

    Route::get('/facility/list', [FacilityController::class, 'index']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/payments/gateway', [PaymentController::class, 'gateway']);
    Route::post('/payments', [PaymentController::class, 'processPayment']);
    Route::post('/request-withdraw', [PaymentController::class, 'requestWithdraw']);
    Route::get('/payout/list', [PaymentController::class, 'payoutSettingsList']);

    Route::post('/wallet-up', [WalletController::class, 'walletUp']);
    Route::get('/wallet-report', [WalletController::class, 'walletReport']);

    Route::get('/notification', [NotificationController::class, 'index']);

    Route::post('/auth/refresh-token', [UserController::class, 'refreshToken']);
    Route::post('/auth/logout', [UserController::class, 'logout']);
});
