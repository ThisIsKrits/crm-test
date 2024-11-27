<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\UpdateProfileController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::group(['middleware' => 'auth:api'], function(){
    Route::middleware('role:superadmin,manager')->group(function () {
        Route::apiResource('users', UserController::class);
    });

    Route::middleware('role:superadmin')->group(function () {
        Route::apiResource('company', CompanyController::class);
    });

    Route::middleware('role:employee')->group(function () {
        Route::get('/user-employee', [UserController::class, 'index'])->name('users.index');
    });


    Route::get('/profile', [UpdateProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile-update', [UpdateProfileController::class, 'update_profile'])->name('profile.update');
    Route::post('/change-password', [AuthController::class, 'change_password'])->name('change_password');
});
