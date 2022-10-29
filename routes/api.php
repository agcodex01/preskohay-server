<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;

use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\LoginController;
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

Route::post('/login', [LoginController::class, 'login'])->name('user.login');
Route::post('/user/register', [UserController::class, 'store'])->name('customers.register');

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('users', UserController::class)->except(['store']);
    Route::post('/logout', [LoginController::class, 'logout'])->name('user.logout');
});

Route::get('/orders', [OrderController::class, 'index'])->name('orders');
Route::post('/orders/store/{id}', [OrderController::class, 'store'])->name('order.product');
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('order.show');
Route::post('/orders/update/{order}', [OrderController::class, 'update'])->name('order.product.update');

Route::get('/user-orders/{id}', [OrderController::class, 'orderByUser'])->name('order.user');
Route::post('/user-orders/update/{order}', [OrderController::class, 'updateStatus'])->name('order.user.update');

Route::apiResource('products', ProductController::class);
