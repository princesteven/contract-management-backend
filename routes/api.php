<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\AuditLogController;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\API\BusinessUnitController;
use \App\Http\Controllers\API\ContractCounterPartyController;
use \App\Http\Controllers\API\ContractController;

// Auth Routes
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('refresh-token', [AuthController::class, 'refreshToken']);
    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'getAuthenticatedUser']);
    });
});

// User Management Routes
Route::middleware('auth:api')->group(function () {
    Route::resource('user', UserController::class)->only(['index', 'show', 'store', 'update']);
    Route::prefix('user')->group(function () {
        Route::post('{id}/deactivate', [UserController::class, 'deactivate']);
        Route::post('{id}/activate', [UserController::class, 'activate']);
    });
});

// Role Management Routes
Route::middleware('auth:api')->group(function () {
    Route::resource('role', RoleController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
});

// Permission Management Routes
Route::middleware('auth:api')->group(function () {
    Route::get('permissions', [PermissionController::class, 'index']);
});

// Audit Log Management Routes
Route::middleware('auth:api')->group(function () {
    Route::get('audit-logs', [AuditLogController::class, 'index']);
    Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show']);
});

// Contract Management Routes
Route::middleware('auth:api')->group(function () {
    // Business Units
    Route::resource('business-units', BusinessUnitController::class)->only(['index', 'show', 'store', 'update']);

    // Contract Counter Parties
    Route::resource('contract-counter-parties', ContractCounterPartyController::class)->only(['index', 'show', 'store', 'update']);

    // Contracts
    Route::resource('contracts', ContractController::class)->only(['index', 'show', 'store', 'update']);
});
