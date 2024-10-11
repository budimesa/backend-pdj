<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\IncomingItemController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerCreditLimitController;
use App\Http\Controllers\CustomerBalanceController;

Route::post('/register', RegisterController::class);
Route::post('/login', LoginController::class);
Route::middleware('auth:sanctum')->post('/logout', LogoutController::class);
Route::middleware('auth:sanctum')->post('/change-password', [AuthController::class, 'changePassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('users', UserController::class);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('suppliers', SupplierController::class);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('warehouses', WarehouseController::class);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('items', ItemController::class);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('incoming-items', IncomingItemController::class);
    Route::get('incoming-item-last-row', [IncomingItemController::class, 'getLastItem']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('batches', BatchController::class);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('inventories', InventoryController::class);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('customers', CustomerController::class);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('customer-credit-limits', CustomerCreditLimitController::class);    
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('customer-balances', CustomerBalanceController::class);    
});

// Route::get('fetch-merged-raw-wfg', [RawMaterialController::class, 'getMergedRawAndWFG']);
