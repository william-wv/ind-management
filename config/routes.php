<?php

use App\Controllers\PalletsController;
use App\Controllers\AuthenticationsController;
use App\Controllers\ProfileController;
use App\Controllers\UsersController;
use Core\Router\Route;


// Authentication
Route::get('/login', [AuthenticationsController::class, 'new'])->name('users.login');
Route::post('/login', [AuthenticationsController::class, 'authenticate'])->name('users.authenticate');

Route::middleware('auth')->group(function () {
    Route::get('/', [ProfileController::class, 'show'])->name('root');

    // Logout
    Route::get('/logout', [AuthenticationsController::class, 'destroy'])->name('users.logout');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');

    // Pallets Stock
      Route::get('/show-pallet', [PalletsController::class, 'show'])->name('pallets.show');

    // Admin only
    Route::middleware('admin')->group(function () {
        Route::get('/users', [UsersController::class, 'index'])->name('users.index');
        Route::post('/create-pallet',[PalletsController::class, 'registerPallet'])->name('pallets.create');
        Route::get('/create-pallet',[PalletsController::class,'new'])->name('pallets.form');
    });
});
