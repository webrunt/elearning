<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Student\Auth\LoginController as StudentLoginController;
use App\Http\Controllers\Student\Auth\LogoutController as StudentLogoutController;
use App\Http\Controllers\Student\Auth\RegisterController as StudentRegisterController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest.student')->group(function () {
    Route::get('/login', [StudentLoginController::class, 'create'])->name('student.login');
    Route::post('/login', [StudentLoginController::class, 'store'])->name('student.login.store');
    Route::get('/register', [StudentRegisterController::class, 'create'])->name('student.register');
    Route::post('/register', [StudentRegisterController::class, 'store'])->name('student.register.store');
});

Route::middleware(['auth', 'student'])->group(function () {
    Route::post('/logout', StudentLogoutController::class)->name('student.logout');
});
