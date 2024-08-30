<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\TagController;
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



//********************************* Authenticate Routes ***************************************************************

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/verify', [RegisterController::class, 'verify']);
Route::get('/is-code-sent/{phone}', [RegisterController::class, 'is_code_sent']);
Route::post('/login', [LoginController::class, 'login']);

//********************************* Tags Routes ***************************************************************

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tags', [TagController::class, 'index']);
    Route::post('/tags', [TagController::class, 'store']);
    Route::put('/tags/{tag}', [TagController::class, 'update']);
    Route::delete('/tags/{tag}', [TagController::class, 'destroy']);
});

//********************************* Post Routes ***************************************************************

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/posts', [PostController::class, 'index']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts/{post}', [PostController::class, 'show']);
    Route::put('/posts/{post}', [PostController::class, 'update']);

    // Soft deleted routes
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);
    Route::get('/posts/trashed', [PostController::class, 'trashed']);
    Route::post('/posts/{id}/restore', [PostController::class, 'restore']);
});

//********************************* stas Routes ***************************************************************

Route::middleware('auth:sanctum')->get('/stats', [StatsController::class, 'index']);
