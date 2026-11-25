<?php

use App\Http\Controllers\MediaController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Standard CRUD routes for Media (index, show, store, update, destroy)
Route::apiResource('media', MediaController::class)->parameters([
    'media' => 'media',
]);

// User & collection routes
Route::apiResource('users', UserController::class)->only(['index', 'show', 'store']);

// Custom actions for managing a user's personal media collection (belongsToMany)
Route::get('/users/{user}/collection', [UserController::class, 'collection']);
Route::post('/users/{user}/collection', [UserController::class, 'addToCollection']);
Route::delete('/users/{user}/collection/{media}', [UserController::class, 'removeFromCollection']);
