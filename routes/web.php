<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Frontend\SitemapController;
use App\Http\Controllers\Frontend\ThemeRenderController;
use Illuminate\Support\Facades\Route;

// Admin Auth Routes
Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Admin Panel Routes
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'destroy']);
    Route::resource('posts', PostController::class)->except(['show']);
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
});

// Public Frontend & SEO Routes
Route::get('/', [ThemeRenderController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/team', [ThemeRenderController::class, 'team'])->name('team.index');
Route::get('/category/{slug}', [ThemeRenderController::class, 'category'])->name('category.show');
Route::get('/{slug}', [ThemeRenderController::class, 'single'])->name('post.show');
