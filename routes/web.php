<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Student\Auth\LoginController as StudentLoginController;
use App\Http\Controllers\Student\Auth\LogoutController as StudentLogoutController;
use App\Http\Controllers\Student\Auth\RegisterController as StudentRegisterController;
use App\Http\Controllers\Student\CatalogController;
use App\Http\Controllers\Student\CourseReviewController;
use App\Http\Controllers\Student\EnrollmentController;
use App\Http\Controllers\Student\LearnController;
use App\Http\Controllers\Student\LessonProgressController;
use App\Http\Controllers\Student\LessonQuizController;
use App\Http\Controllers\Student\LessonVideoController;
use App\Http\Controllers\Student\MyLearningController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/courses', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/courses/{slug}', [CatalogController::class, 'show'])->name('catalog.show');

Route::get('/learn/courses/{slug}/lessons/{lesson}', [LearnController::class, 'show'])->name('learn.show');
Route::get('/learn/lessons/{lesson}/video', [LessonVideoController::class, 'show'])->name('learn.lessons.video');

Route::middleware('guest.student')->group(function () {
    Route::get('/login', [StudentLoginController::class, 'create'])->name('student.login');
    Route::post('/login', [StudentLoginController::class, 'store'])->name('student.login.store');
    Route::get('/register', [StudentRegisterController::class, 'create'])->name('student.register');
    Route::post('/register', [StudentRegisterController::class, 'store'])->name('student.register.store');
});

Route::middleware(['auth', 'student'])->group(function () {
    Route::post('/logout', StudentLogoutController::class)->name('student.logout');
    Route::get('/my-learning', [MyLearningController::class, 'index'])->name('my-learning.index');
    Route::post('/courses/{slug}/enroll', [EnrollmentController::class, 'store'])->name('enrollments.store');
    Route::post('/courses/{slug}/reviews', [CourseReviewController::class, 'store'])->name('course-reviews.store');
    Route::get('/learn/courses/{slug}/continue', [LearnController::class, 'continue'])->name('learn.continue');
    Route::patch('/learn/lessons/{lesson}/progress', [LessonProgressController::class, 'update'])->name('learn.lessons.progress');
    Route::get('/learn/lessons/{lesson}/quiz', [LessonQuizController::class, 'show'])->name('learn.lessons.quiz');
    Route::post('/learn/lessons/{lesson}/quiz', [LessonQuizController::class, 'store'])->name('learn.lessons.quiz.submit');
});
