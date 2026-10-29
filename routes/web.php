<?php

use App\Http\Controllers\CmsAuthController;
use App\Http\Controllers\CmsController;
use App\Http\Controllers\CmsMediaController;
use App\Http\Controllers\SitePageController;
use Illuminate\Support\Facades\Route;

Route::get('/admin/login', [CmsAuthController::class, 'showLogin'])->name('cms.login');
Route::post('/admin/login', [CmsAuthController::class, 'login'])->middleware('throttle:5,1')->name('cms.login.submit');
Route::post('/admin/logout', [CmsAuthController::class, 'logout'])->middleware('cms.auth')->name('cms.logout');
Route::get('/admin', [CmsController::class, 'index'])->middleware('cms.auth')->name('cms.index');
Route::get('/admin/pages/{page}/preview', [CmsController::class, 'preview'])->middleware('cms.auth')->name('cms.pages.preview');
Route::patch('/admin/pages/{page}', [CmsController::class, 'update'])->middleware('cms.auth')->name('cms.pages.update');
Route::post('/admin/pages/{page}/sync-source', [CmsController::class, 'syncSource'])
    ->middleware('cms.auth')
    ->name('cms.pages.sync-source');
Route::post('/admin/media', [CmsMediaController::class, 'store'])
    ->middleware(['cms.auth', 'throttle:20,1'])
    ->name('cms.media.store');

Route::get('/', [SitePageController::class, 'home'])->name('site.home');
Route::get('/{path}', [SitePageController::class, 'show'])
    ->where('path', '.*')
    ->name('site.page');
