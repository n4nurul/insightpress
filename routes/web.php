<?php

use App\Http\Controllers\Admin\AddonController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EditorUploadController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Frontend\SitemapController;
use App\Http\Controllers\Frontend\ThemeRenderController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

// Admin Auth Routes
Route::redirect('/admin', '/admin/dashboard');
Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Admin Panel Routes
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/posts/{post}/approve', [DashboardController::class, 'approvePost'])->name('posts.approve');
    Route::post('/quick-breaking', [DashboardController::class, 'saveQuickBreaking'])->name('quick-breaking.save');
    Route::post('/clear-cache', [DashboardController::class, 'clearCache'])->name('clear-cache');
    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'destroy']);
    Route::post('posts/bulk', [PostController::class, 'bulkAction'])->name('posts.bulk');
    Route::resource('posts', PostController::class)->except(['show']);
    Route::post('posts/{post}/archive', [PostController::class, 'archive'])->name('posts.archive');
    Route::post('posts/{id}/restore', [PostController::class, 'restore'])->name('posts.restore');
    Route::delete('posts/{id}/force-delete', [PostController::class, 'forceDelete'])->name('posts.force-delete');
    Route::post('editor/upload-image', [EditorUploadController::class, 'upload'])->name('editor.upload');
    Route::get('/media', [MediaController::class, 'index'])->name('media.index');
    Route::post('/media', [MediaController::class, 'store'])->name('media.store');
    Route::delete('/media', [MediaController::class, 'destroy'])->name('media.destroy');

    // Super Admin Only Routes
    Route::middleware(EnsureUserIsAdmin::class)->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::post('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');
        Route::post('/users/{user}/password', [UserController::class, 'updatePassword'])->name('users.password');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/addons', [AddonController::class, 'index'])->name('addons.index');
        Route::post('/addons/{key}/toggle', [AddonController::class, 'toggle'])->name('addons.toggle');
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});

// Public Frontend & SEO Routes
Route::get('/', [ThemeRenderController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/team', [ThemeRenderController::class, 'team'])->name('team.index');
Route::get('/category/{slug}', [ThemeRenderController::class, 'category'])->name('category.show');
Route::get('/{slug}', [ThemeRenderController::class, 'single'])->name('post.show');
