<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\Auth\LoginController as StaffLoginController;
use App\Http\Controllers\Admin\Auth\LogoutController as StaffLogoutController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest.staff')->group(function () {
    Route::get('/login', [StaffLoginController::class, 'create'])->name('admin.login');
    Route::post('/login', [StaffLoginController::class, 'store'])->name('admin.login.store');
});

Route::middleware(['auth', 'staff'])->group(function () {
    Route::post('/logout', StaffLogoutController::class)->name('admin.logout');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/page', [AdminController::class, 'page'])->name('admin.page');
    Route::redirect('/', '/dashboard');
});
