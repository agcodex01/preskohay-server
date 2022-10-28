<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('orders', OrderController::class);
Route::post('/order/update/{order}', [OrderController::class, 'updateByUser'])->name('order.user.update');
Route::get('/user-orders', [OrderController::class, 'orderByUser'])->name('order.user');
Route::apiResource('products', ProductController::class);
