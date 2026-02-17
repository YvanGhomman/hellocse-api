<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

// Routes publiques
Route::post('/login', [AuthController::class, 'login']);

// Lister les profils actifs (public)
Route::get('/profiles', [ProfileController::class, 'index']);

// Routes protégées par authentification
Route::middleware('auth:sanctum')->group(function () {
    // Déconnexion
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Gestion des profils (CRUD protégé)
    Route::post('/profiles', [ProfileController::class, 'store']);
    Route::get('/profiles/{profile}', [ProfileController::class, 'show']);
    Route::put('/profiles/{profile}', [ProfileController::class, 'update']);
    Route::patch('/profiles/{profile}', [ProfileController::class, 'update']);
    Route::delete('/profiles/{profile}', [ProfileController::class, 'destroy']);
});