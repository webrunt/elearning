<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\Auth\LoginController as StaffLoginController;
use App\Http\Controllers\Admin\Auth\LogoutController as StaffLogoutController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\CourseReviewController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\SectionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest.staff')->group(function () {
    Route::get('/login', [StaffLoginController::class, 'create'])->name('admin.login');
    Route::post('/login', [StaffLoginController::class, 'store'])->name('admin.login.store');
});

Route::middleware(['auth', 'staff'])->name('admin.')->group(function () {
    Route::post('/logout', StaffLogoutController::class)->name('logout');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::redirect('/', '/dashboard');

    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('courses/pending', [CourseReviewController::class, 'pending'])->name('courses.review.pending');
    Route::get('courses/{course}/review', [CourseReviewController::class, 'show'])->name('courses.review.show');
    Route::post('courses/{course}/submit-review', [CourseReviewController::class, 'submit'])->name('courses.review.submit');
    Route::post('courses/{course}/approve', [CourseReviewController::class, 'approve'])->name('courses.review.approve');
    Route::post('courses/{course}/reject', [CourseReviewController::class, 'reject'])->name('courses.review.reject');

    Route::resource('courses', CourseController::class)->except(['show']);

    Route::post('courses/{course}/sections', [SectionController::class, 'store'])->name('courses.sections.store');
    Route::patch('courses/{course}/sections/{section}', [SectionController::class, 'update'])->name('courses.sections.update');
    Route::delete('courses/{course}/sections/{section}', [SectionController::class, 'destroy'])->name('courses.sections.destroy');

    Route::post('sections/{section}/lessons', [LessonController::class, 'store'])->name('sections.lessons.store');
    Route::get('lessons/{lesson}/edit', [LessonController::class, 'edit'])->name('lessons.edit');
    Route::match(['put', 'patch'], 'lessons/{lesson}', [LessonController::class, 'update'])->name('lessons.update');
    Route::delete('lessons/{lesson}', [LessonController::class, 'destroy'])->name('lessons.destroy');
});
