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
use App\Http\Controllers\CustomerBalanceDepositController;
use App\Http\Controllers\ItemTransferController;
use App\Http\Controllers\InventoryDetailController;

Route::post('/register', RegisterController::class);
Route::post('/login', LoginController::class);
Route::middleware('auth:sanctum')->post('/logout', LogoutController::class);
Route::middleware('auth:sanctum')->post('/change-password', [AuthController::class, 'changePassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('warehouses', WarehouseController::class);
    Route::apiResource('items', ItemController::class);
    Route::apiResource('incoming-items', IncomingItemController::class);
    Route::get('incoming-item-last-row', [IncomingItemController::class, 'getLastItem']);
    Route::apiResource('batches', BatchController::class);
    Route::apiResource('inventories', InventoryController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('customer-credit-limits', CustomerCreditLimitController::class);    
    Route::apiResource('customer-balances', CustomerBalanceController::class);
    Route::apiResource('customer-balance-deposits', CustomerBalanceDepositController::class);
    Route::apiResource('item-transfers', ItemTransferController::class);
    Route::get('item-transfer-last-row', [ItemTransferController::class, 'getLastItem']);
    Route::apiResource('inventory-details', InventoryDetailController::class);
});



// Route::get('fetch-merged-raw-wfg', [RawMaterialController::class, 'getMergedRawAndWFG']);
