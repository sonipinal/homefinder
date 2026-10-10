<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// Public: anyone can search and log in
Route::get('/homes', [HomeController::class, 'index']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1'); // max 5 tries per minute

// Protected: a valid token is needed
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/homes', [HomeController::class, 'store']);
});