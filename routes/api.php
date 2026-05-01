<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FarmerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RepaymentController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ─── Health check (Docker / monitoring) ──────────────────────────────
Route::get('/health', fn () => response()->json(['status' => 'ok']));

// ─── Authentification ────────────────────────────────────────────────
Route::post('/login', [AuthController::class, 'login']);

// ─── Routes protégées ────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // ── Admin uniquement ─────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('users', UserController::class);
    });

    // ── Admin + Supervisor ───────────────────────────────────────────
    Route::middleware('role:admin,supervisor')->group(function () {
        // Gestion (écriture) des catégories et produits réservée aux admins/supervisors
        Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
        Route::apiResource('products',   ProductController::class)->except(['index', 'show']);

        // Supervisor crée ses operators
        Route::post('operators', [UserController::class, 'store']);
        Route::get('operators',  [UserController::class, 'index']);
    });

    // ── Tous les rôles authentifiés ──────────────────────────────────

    // Lecture des catégories et produits accessible à tous (opérateur doit parcourir le catalogue)
    Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
    Route::apiResource('products',   ProductController::class)->only(['index', 'show']);

    Route::get('farmers/search',         [FarmerController::class, 'search']);
    Route::get('farmers/{farmer}/debts', [FarmerController::class, 'debts']);
    Route::apiResource('farmers',        FarmerController::class);

    Route::apiResource('transactions', TransactionController::class)->only(['index', 'show', 'store']);
    Route::post('repayments',          [RepaymentController::class, 'store']);
});