<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EgresoController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('auth/logout', [AuthController::class, 'logout']);

    Route::apiResource('egresos', EgresoController::class);
});
