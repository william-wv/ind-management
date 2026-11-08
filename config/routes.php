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

    // Pallets
    Route::get('/pallets', [PalletsController::class, 'show'])->name('pallets.index');
    Route::get('/pallets/new', [PalletsController::class, 'new'])->name('pallets.new');
    Route::get('/pallets/{id}/edit', [PalletsController::class, 'showUpdateForm'])->name('pallets.edit');
    Route::put('/pallets/{id}', [PalletsController::class, 'updatePallet'])->name('pallets.update');
    Route::get('/pallets/{id}/upload', [PalletsController::class, 'showUpload'])->name('pallets.upload.show');
    Route::post('/pallets/upload', [PalletsController::class, 'upload'])->name('pallets.upload');

    // Admin only
    Route::middleware('admin')->group(function () {
        Route::get('/users', [UsersController::class, 'index'])->name('users.index');
        Route::post('/pallets', [PalletsController::class, 'registerPallet'])->name('pallets.create');
        Route::delete('/pallets/{id}', [PalletsController::class, 'deletePallet'])->name('pallets.destroy');
    });
});
