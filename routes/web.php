<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MemeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Admin\ReportModerationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [MemeController::class, 'index'])->name('memes.index');
Route::get('/contact', [ContactController::class, 'index'])->middleware('auth')->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware(['auth', 'throttle:10,1'])->name('contact.store');
Route::get('/memes/{meme:slug}', [MemeController::class, 'show'])->name('memes.show');
Route::get('/u/{user}', [UserController::class, 'show'])->name('users.show');

Route::middleware('auth')->group(function () {
    Route::post('/memes', [MemeController::class, 'store'])->name('memes.store');
    Route::post('/memes/{meme}/upvote', [MemeController::class, 'upvote'])->name('memes.upvote');
    Route::delete('/memes/{meme}', [MemeController::class, 'destroy'])->name('memes.destroy');
    Route::post('/memes/{meme}/bookmark', [MemeController::class, 'bookmark'])->name('memes.bookmark');
    Route::delete('/memes/{meme}/bookmark', [MemeController::class, 'unbookmark'])->name('memes.unbookmark');
    Route::get('/bookmarks', [MemeController::class, 'bookmarks'])->name('bookmarks.index');
    Route::post('/memes/{meme}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::patch('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    Route::post('/memes/{meme}/report', [ReportController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('memes.report');
    
    Route::post('/users/{user}/follow', [UserController::class, 'follow'])->name('users.follow');
    Route::delete('/users/{user}/follow', [UserController::class, 'unfollow'])->name('users.unfollow');
    
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    
    Route::get('/settings', [ProfileController::class, 'edit'])->name('settings');
    Route::post('/settings/language', [ProfileController::class, 'updateLanguage'])->name('settings.language.update');
    Route::get('/settings/profile', [ProfileController::class, 'editProfile'])->name('settings.profile.edit');
    Route::patch('/settings/profile', [ProfileController::class, 'updateProfile'])->name('settings.profile.update');
    Route::get('/settings/statistics', [ProfileController::class, 'statistics'])->name('settings.statistics');
    Route::get('/settings/statistics/data', [ProfileController::class, 'statisticsData'])->name('settings.statistics.data');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/reports', [ReportModerationController::class, 'index'])->name('reports.index');
        Route::patch('/reports/{report}', [ReportModerationController::class, 'update'])->name('reports.update');
        Route::patch('/contact-messages/{contactMessage}', [ReportModerationController::class, 'updateContactMessage'])->name('contact-messages.update');
    });
});

require __DIR__.'/auth.php';
