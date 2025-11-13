<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BlogPostController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReviewController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.attempt');
    Route::get('/login', fn () => redirect()->route('admin.login'))->name('login');
});

Route::post('/admin/logout', [AuthController::class, 'logout'])->middleware('auth')->name('admin.logout');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('products', ProductController::class)->except('show');
    Route::resource('categories', ProductCategoryController::class)->except('show');
    Route::resource('blog', BlogPostController::class)->except('show');
    Route::resource('orders', OrderController::class)->only(['index', 'update']);
    Route::resource('reviews', ReviewController::class)->only(['index', 'destroy']);
    Route::resource('messages', ContactMessageController::class)->only(['index', 'destroy']);
    Route::post('messages/{message}/resolve', [ContactMessageController::class, 'resolve'])->name('messages.resolve');
});
