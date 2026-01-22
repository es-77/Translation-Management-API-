<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TranslationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);

    // Search must be defined before resource routes to avoid conflicts
    Route::get('/translations/search', [TranslationController::class, 'search']);

    // Translations CRUD
    Route::apiResource('translations', TranslationController::class);

    // Tags CRUD
    Route::apiResource('tags', TagController::class);

    // Export
    Route::get('/export', [ExportController::class, 'export']);
});
