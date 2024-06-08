<?php

use App\Http\Controllers\Web\Admin\DashboardController;
use App\Http\Controllers\Web\Admin\Tools\FileManagerController;
use App\Http\Controllers\Web\Admin\Tools\GameNewsController;
use App\Http\Controllers\Web\Admin\Tools\LogViewerController;
use App\Http\Controllers\Web\Admin\Tools\UsersController;
use App\Http\Controllers\Web\GameFileController;

Route::redirect('', 'admin/dashboard');
Route::redirect('logs', 'log-viewer')->name(LogViewerController::class);

Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('admin.dashboard');

//Route::get('file-manager', [GameFileController::class, 'index'])->name(FileManagerController::class);
//Route::get('file-manager', [GameFileController::class, 'getPatchline'])->name('file.patchline');
//Route::post('file-manager', [GameFileController::class, 'store'])->name('file.store');
Route::resource('file-manager', GameFileController::class)->except(['edit', 'create', 'show']);

Route::get('gamenews', [GameNewsController::class, 'index'])->name(GameNewsController::class);
Route::post('gamenews/create', [GameNewsController::class, 'create'])->name('gamenews.create');
Route::post('gamenews/{news}', [GameNewsController::class, 'submit'])->name('gamenews.post');

Route::get('users', [UsersController::class, 'index'])->name(UsersController::class);
Route::post('user/{user}/edit', [UsersController::class, 'edit'])->name('user.edit');
Route::post('user/{user}/reset', [UsersController::class, 'reset'])->name('user.reset');
Route::get('user/{user}/bans', [UsersController::class, 'bans'])->name('user.bans');
Route::post('user/{user}/ban/{ban}', [UsersController::class, 'banPost'])->name('user.ban.post');
Route::get('user/{user}/ban/create', [UsersController::class, 'createBan'])->name('user.ban.create');
Route::get('user/{user}', [UsersController::class, 'details'])->name('user.details');

Route::fallback(function () {
    return redirect(route('admin.dashboard'));
});
