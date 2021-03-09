<?php

use EscolaLms\Auth\Http\Controllers\AuthApiController;
use EscolaLms\Auth\Http\Controllers\LoginApiController;
use EscolaLms\Auth\Http\Controllers\LogoutApiController;
use EscolaLms\Auth\Http\Controllers\ProfileAPIController;
use EscolaLms\Auth\Http\Controllers\RegisterApiController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'api'], function () {
    Route::group(['prefix' => 'auth'], function () {
        Route::post('register', [RegisterApiController::class, 'register'])->name('register.api');
        Route::post('login', [LoginApiController::class, 'login'])->name('login.api');

        Route::group(['prefix' => 'password'], function () {
            Route::post('forgot', [AuthApiController::class, 'forgotPassword']);
            Route::post('reset', [AuthApiController::class, 'resetPassword']);
        });

        Route::group(['prefix' => 'social'], function () {
            Route::get('{provider}', [AuthApiController::class, 'socialRedirect']);
            Route::get('{provider}/callback', [AuthApiController::class, 'socialCallback']);
        });

        Route::group(['prefix' => 'email'], function () {
            Route::get('verify/{id}/{hash}', [AuthApiController::class, 'verifyEmail'])->name('verification.verify');
            Route::post('resend', [AuthApiController::class, 'resendEmailVerification'])->middleware(['auth:api', 'throttle:6,1'])->name('verification.send');
        });

        Route::group(['middleware' => 'auth:api'], function () {
            Route::post('logout', [LogoutApiController::class, 'logout'])->name('logout.api');
            Route::get('refresh', [AuthApiController::class, 'refresh']);
        });
    });

    Route::middleware('auth:api')->prefix('profile')->group(function () {
        Route::get('/me', [ProfileAPIController::class, 'me']);
        Route::put('/me', [ProfileAPIController::class, 'update']);
        Route::put('/me-auth', [ProfileAPIController::class, 'updateAuthData']);
        Route::put('/password', [ProfileAPIController::class, 'updatePassword']);
        Route::put('/interests', [ProfileAPIController::class, 'interests']);
        Route::get('/settings', [ProfileAPIController::class, 'settings']);
        Route::put('/settings', [ProfileAPIController::class, 'settingsUpdate']);
        Route::post('/upload-avatar', [ProfileAPIController::class, 'uploadAvatar']);
        Route::delete('/delete-avatar', [ProfileAPIController::class, 'deleteAvatar']);
    });
});
