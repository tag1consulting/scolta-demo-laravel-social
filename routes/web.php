<?php

use App\Http\Controllers\ExploreController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HashtagController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FeedController::class, 'index'])->name('feed');
Route::get('/explore', [ExploreController::class, 'index'])->name('explore');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/user/{user:username}', [UserController::class, 'show'])->name('users.show');
Route::get('/post/{post}', [PostController::class, 'show'])->name('posts.show');
Route::get('/hashtag/{hashtag:name}', [HashtagController::class, 'show'])->name('hashtags.show');
