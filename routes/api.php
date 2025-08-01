<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication routes (public)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // User management routes
    Route::get('/users', [UserController::class, 'index']); // admin only
    Route::post('/users/{id}/assign-role', [UserController::class, 'assignRole']); // admin only
    Route::get('/profile', [UserController::class, 'profile']); // all authenticated users
    
    // Article routes
    Route::get('/articles', [ArticleController::class, 'index']); // all authenticated users (published only)
    Route::get('/articles/mine', [ArticleController::class, 'mine']); // authors (own articles)
    Route::post('/articles', [ArticleController::class, 'store']); // authors
    Route::put('/articles/{id}', [ArticleController::class, 'update']); // authors (own articles)
    Route::delete('/articles/{id}', [ArticleController::class, 'destroy']); // admin only
    Route::patch('/articles/{id}/publish', [ArticleController::class, 'publish']); // editors and admins
    
});
